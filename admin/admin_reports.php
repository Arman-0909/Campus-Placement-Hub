<?php
// admin_reports.php
session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}
require_once "../includes/config.php";

// Fetch Filter Options
$departments = [];
if($r = $conn->query("SELECT DISTINCT department FROM student ORDER BY department")) {
    while($row = $r->fetch_assoc()) $departments[] = $row['department'];
}

$companies = [];
if($r = $conn->query("SELECT DISTINCT company_name FROM jobs ORDER BY company_name")) {
    while($row = $r->fetch_assoc()) $companies[] = $row['company_name'];
}

// Build Query
$where_clauses = [];
$params = [];
$types = "";

// 1. Department Filter
if (!empty($_GET['dept'])) {
    $where_clauses[] = "s.department = ?";
    $params[] = $_GET['dept'];
    $types .= "s";
}

// 2. Status Filter
if (!empty($_GET['status'])) {
    $where_clauses[] = "s.placement_status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

// 3. Company Filter (Complex Join)
if (!empty($_GET['company'])) {
    $where_clauses[] = "j.company_name = ?";
    $params[] = $_GET['company'];
    $types .= "s";
}

$sql = "SELECT s.regdno, s.name, s.department, s.placement_status, 
               p.package_lpa, j.company_name, j.job_title
        FROM student s
        LEFT JOIN placements p ON s.regdno = p.student_regdno
        LEFT JOIN jobs j ON p.job_id = j.job_id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY s.regdno ASC";

$students = [];
if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Placement Reports - Admin</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        @media print {
            .dashboard-layout { display: block; }
            .sidebar, header, .no-print { display: none !important; }
            .dashboard-main { 
                margin-left: 0 !important; 
                padding: 0 !important; 
                width: 100% !important;
            }
            .container { 
                padding: 0 !important; 
                max-width: 100% !important; 
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            body { font-size: 12px; background: white; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            h1 { font-size: 18px; color: black; margin-bottom: 10px; }
            /* Branding for Print */
            .print-header { display: block !important; margin-bottom: 20px; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
        }
        .print-header { display: none; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div class="flex justify-between items-center mb-4 no-print">
                    <div>
                        <h1 style="font-size: 1.75rem; color: var(--secondary);">Placement Reports</h1>
                        <p class="text-muted">Generate and print student placement lists.</p>
                    </div>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i data-lucide="printer"></i> Print Report
                    </button>
                </div>

                <!-- Filters -->
                <div class="card mb-4 no-print" style="margin-bottom: 2rem;">
                    <form action="" method="get" class="grid grid-cols-4 gap-4" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <input type="hidden" name="filter" value="1">
                        
                        <div class="form-group mb-0">
                            <label class="form-label">Department</label>
                            <select name="dept" class="form-control">
                                <option value="">All Departments</option>
                                <?php foreach($departments as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d); ?>" <?php echo (isset($_GET['dept']) && $_GET['dept'] == $d) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="Placed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Placed') ? 'selected' : ''; ?>>Placed</option>
                                <option value="Not Placed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Not Placed') ? 'selected' : ''; ?>>Not Placed</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label">Company</label>
                            <select name="company" class="form-control">
                                <option value="">All Companies</option>
                                <?php foreach($companies as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c); ?>" <?php echo (isset($_GET['company']) && $_GET['company'] == $c) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-0" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-secondary w-full">Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Printable Area -->
                <div class="card">
                    <div class="print-header">
                        <h2>Campus Placement Hub</h2>
                        <h3>Student Placement Report</h3>
                        <p>Generated on: <?php echo date('d-m-Y H:i'); ?></p>
                    </div>

                    <div class="table-container">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th>Regd No</th>
                                    <th>Name</th>
                                    <th>Dept</th>
                                    <th>Status</th>
                                    <th>Company</th>
                                    <th>Package</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No records found matching filters.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($students as $s): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($s['regdno']); ?></td>
                                        <td>
                                            <div class="font-medium"><?php echo htmlspecialchars($s['name']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($s['department']); ?></td>
                                        <td>
                                            <?php if($s['placement_status'] == 'Placed'): ?>
                                                <span style="color: #059669; font-weight: 600;">Placed</span>
                                            <?php else: ?>
                                                <span class="text-muted">Not Placed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $s['company_name'] ? htmlspecialchars($s['company_name']) : '-'; ?></td>
                                        <td><?php echo $s['package_lpa'] ? htmlspecialchars($s['package_lpa']) . ' LPA' : '-'; ?></td>
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
    <script>lucide.createIcons();</script>
</body>
</html>
