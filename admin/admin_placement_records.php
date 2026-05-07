<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$students = [];
$sql = "SELECT 
            s.regdno,
            s.name,
            s.department,
            s.email,
            s.placement_status,
            m.cgpa,
            m.backlogs,
            p.package_lpa,
            p.placement_date,
            j.company_name,
            j.job_title,
            (SELECT COUNT(*) FROM applications a WHERE a.student_regdno = s.regdno) as total_applications,
            (SELECT COUNT(*) FROM applications a WHERE a.student_regdno = s.regdno AND a.status = 'Shortlisted') as shortlisted_count
        FROM student s
        LEFT JOIN marks m ON s.regdno = m.regdno
        LEFT JOIN placements p ON s.regdno = p.student_regdno
        LEFT JOIN jobs j ON p.job_id = j.job_id
        WHERE 1=1";

switch($filter) {
    case 'placed':
        $sql .= " AND p.student_regdno IS NOT NULL";
        break;
    case 'shortlisted':
        $sql .= " AND s.regdno IN (SELECT student_regdno FROM applications WHERE status = 'Shortlisted')";
        break;
    case 'not_interested':
        $sql .= " AND s.placement_status = 'Not Interested'";
        break;
    case 'seekers':
        $sql .= " AND s.placement_status = 'Not Placed' AND s.regdno IN (SELECT student_regdno FROM applications)";
        break;
    case 'not_placed':
        $sql .= " AND s.placement_status = 'Not Placed' AND s.regdno NOT IN (SELECT student_regdno FROM applications)";
        break;
}

if (!empty($search)) {
    $sql .= " AND (s.name LIKE ? OR s.regdno LIKE ?)";
}

$sql .= " GROUP BY s.regdno"; // Add GROUP BY to handle potential duplicates properly

$sql .= " ORDER BY 
    CASE 
        WHEN p.package_lpa IS NOT NULL THEN 1
        WHEN s.placement_status = 'Shortlisted' THEN 2
        ELSE 3
    END,
    m.cgpa DESC";

if($stmt = $conn->prepare($sql)) {
    if (!empty($search)) {
        $search_param = "%{$search}%";
        $stmt->bind_param("ss", $search_param, $search_param);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

$stats = [
    'total' => 0,
    'placed' => 0,
    'shortlisted' => 0,
    'not_interested' => 0,
    'seekers' => 0,
    'not_placed' => 0
];

$sql_stats = "SELECT 
    COUNT(DISTINCT s.regdno) as total,
    COUNT(DISTINCT p.student_regdno) as placed,
    COUNT(DISTINCT CASE WHEN s.placement_status = 'Not Interested' THEN s.regdno END) as not_interested,
    COUNT(DISTINCT CASE WHEN s.placement_status = 'Not Placed' AND s.regdno IN (SELECT student_regdno FROM applications) THEN s.regdno END) as seekers,
    COUNT(DISTINCT CASE WHEN s.placement_status = 'Not Placed' AND s.regdno NOT IN (SELECT student_regdno FROM applications) THEN s.regdno END) as not_placed
FROM student s
LEFT JOIN placements p ON s.regdno = p.student_regdno";

if($result = $conn->query($sql_stats)) {
    $stats = $result->fetch_assoc();
    $result->free();
}

$sql_shortlisted = "SELECT COUNT(DISTINCT student_regdno) as count FROM applications WHERE status = 'Shortlisted'";
if($result = $conn->query($sql_shortlisted)) {
    $stats['shortlisted'] = $result->fetch_assoc()['count'];
    $result->free();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Records - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 1.25rem;
            border: 1px solid var(--border);
            transition: all 0.2s;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            height: 100%;
            text-decoration: none;
        }
        .stat-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }
        .stat-card.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.5rem;
        }
        .table-container {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.9rem;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        th {
            background-color: var(--bg-input);
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: var(--bg-body);
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Placement Records</h1>
                    <p class="text-muted">Track student placement status and application progress.</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; margin-bottom: 2rem;">
                    <a href="?filter=all" class="stat-card <?php echo ($filter == 'all') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Total Students</div>
                    </a>
                    <a href="?filter=placed" class="stat-card <?php echo ($filter == 'placed') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value" style="color: #059669;"><?php echo $stats['placed']; ?></div>
                        <div class="stat-label">Placed</div>
                    </a>
                    <a href="?filter=shortlisted" class="stat-card <?php echo ($filter == 'shortlisted') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value" style="color: #f59e0b;"><?php echo $stats['shortlisted']; ?></div>
                        <div class="stat-label">Shortlisted</div>
                    </a>
                    <a href="?filter=seekers" class="stat-card <?php echo ($filter == 'seekers') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value" style="color: #3b82f6;"><?php echo $stats['seekers']; ?></div>
                        <div class="stat-label">Seekers</div>
                    </a>
                    <a href="?filter=not_interested" class="stat-card <?php echo ($filter == 'not_interested') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value" style="color: #6b7280;"><?php echo $stats['not_interested']; ?></div>
                        <div class="stat-label">Not Interested</div>
                    </a>
                    <a href="?filter=not_placed" class="stat-card <?php echo ($filter == 'not_placed') ? 'active' : ''; ?>" style="text-decoration: none;">
                        <div class="stat-value" style="color: #ef4444;"><?php echo $stats['not_placed']; ?></div>
                        <div class="stat-label">Not Placed</div>
                    </a>
                </div>
                <div class="card" style="margin-bottom: 2rem;">
                    <form action="" method="get" class="grid grid-cols-2 gap-4" style="grid-template-columns: 1fr auto;">
                        <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <div class="form-group" style="margin-bottom: 0;">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or registration number..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group" style="margin-bottom: 0; display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="?filter=<?php echo htmlspecialchars($filter); ?>" class="btn btn-secondary">
                                    <i data-lucide="x"></i> Clear
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="card">
                    <div class="card-header" style="margin-bottom: 1.5rem;">
                        <h3>
                            <?php 
                            $filter_labels = [
                                'all' => 'All Students',
                                'placed' => 'Placed Students',
                                'shortlisted' => 'Shortlisted Students',
                                'seekers' => 'Job Seekers',
                                'not_interested' => 'Not Interested',
                                'not_placed' => 'Not Placed'
                            ];
                            echo $filter_labels[$filter] ?? 'All Students';
                            ?> (<?php echo count($students); ?>)
                        </h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Department</th>
                                    <th>Academic</th>
                                    <th>Applications</th>
                                    <th>Placement Status</th>
                                    <th>Company & Role</th>
                                    <th>Package</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr><td colspan="7" class="text-center text-muted">No students found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="font-bold"><?php echo htmlspecialchars($student['name']); ?></div>
                                                <div class="text-xs text-muted"><?php echo htmlspecialchars($student['regdno']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></td>
                                            <td class="text-sm">
                                                <div>CGPA: <?php echo number_format($student['cgpa'] ?? 0, 2); ?></div>
                                                <div>Backlogs: <?php echo $student['backlogs'] ?? 0; ?></div>
                                            </td>
                                            <td class="text-sm">
                                                <div>Total: <?php echo $student['total_applications']; ?></div>
                                                <?php if ($student['shortlisted_count'] > 0): ?>
                                                    <div style="color: #f59e0b;">Shortlisted: <?php echo $student['shortlisted_count']; ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php 
                                                    echo match($student['placement_status']) {
                                                        'Placed' => 'success',
                                                        'Not Interested' => 'neutral',
                                                        'Seeking Better Offer' => 'warning',
                                                        default => 'error'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($student['placement_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($student['company_name'])): ?>
                                                    <div class="font-bold"><?php echo htmlspecialchars($student['company_name']); ?></div>
                                                    <div class="text-xs text-muted"><?php echo htmlspecialchars($student['job_title'] ?? ''); ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($student['package_lpa'])): ?>
                                                    <strong><?php echo number_format($student['package_lpa'], 2); ?> LPA</strong>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
