<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}
require_once "../includes/config.php";

$selected_job_id = $_GET['job_id'] ?? null;
$feedback_msg = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $new_status = $_POST['status'];
    
    $sql_update = "UPDATE applications SET status = ? WHERE application_id = ?";
    if($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param("si", $new_status, $application_id);
        if($stmt_update->execute()){
            $_SESSION['flash_message'] = "Application status updated successfully.";

            $notif_check = $conn->query("SHOW TABLES LIKE 'notifications'");
            if ($notif_check && $notif_check->num_rows > 0) {

                $info_sql = "SELECT a.student_regdno, j.company_name, j.job_title FROM applications a JOIN jobs j ON a.job_id = j.job_id WHERE a.application_id = ?";
                if ($info_stmt = $conn->prepare($info_sql)) {
                    $info_stmt->bind_param("i", $application_id);
                    $info_stmt->execute();
                    $info_result = $info_stmt->get_result()->fetch_assoc();
                    $info_stmt->close();
                    
                    if ($info_result) {
                        $notif_title = "Application Status Updated";
                        $notif_msg = "Your application for {$info_result['job_title']} at {$info_result['company_name']} has been updated to: $new_status";
                        $student_regdno = $info_result['student_regdno'];
                        
                        $notif_sql = "INSERT INTO notifications (student_regdno, type, title, message) VALUES (?, 'status_update', ?, ?)";
                        if ($notif_stmt = $conn->prepare($notif_sql)) {
                            $notif_stmt->bind_param("sss", $student_regdno, $notif_title, $notif_msg);
                            $notif_stmt->execute();
                            $notif_stmt->close();
                        }
                    }
                }
            }
        }
        $stmt_update->close();
    }
    header("location: admin_view_applications.php?job_id=" . $selected_job_id);
    exit;
}

$jobs_with_app_counts = [];
$sql_jobs = "SELECT j.job_id, j.job_title, j.company_name, COUNT(a.application_id) as application_count
             FROM jobs j
             LEFT JOIN applications a ON j.job_id = a.job_id
             GROUP BY j.job_id
             ORDER BY j.company_name, j.job_title";
if ($result = $conn->query($sql_jobs)) {
    while ($row = $result->fetch_assoc()) {
        $jobs_with_app_counts[] = $row;
    }
    $result->free();
}

$applicants = [];
$selected_job_title = "";
if ($selected_job_id && is_numeric($selected_job_id)) {
    $sql_applicants = "SELECT s.regdno, s.name, s.email, s.contact, m.cgpa, m.backlogs, a.application_id, a.status, a.application_date
                       FROM applications a
                       JOIN student s ON a.student_regdno = s.regdno
                       LEFT JOIN marks m ON s.regdno = m.regdno
                       WHERE a.job_id = ?
                       ORDER BY FIELD(a.status, 'Shortlisted', 'Interviewing', 'Applied', 'Rejected'), m.cgpa DESC";

    if ($stmt = $conn->prepare($sql_applicants)) {
        $stmt->bind_param("i", $selected_job_id);
        $stmt->execute();
        $result_applicants = $stmt->get_result();
        while ($row = $result_applicants->fetch_assoc()) {
            $applicants[] = $row;
        }
        $stmt->close();
    }
    foreach($jobs_with_app_counts as $job) {
        if ($job['job_id'] == $selected_job_id) {
            $selected_job_title = $job['company_name'] . ' - ' . $job['job_title'];
            break;
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .job-list-item { 
            padding: 1rem; 
            border-bottom: 1px solid var(--border); 
            cursor: pointer; 
            transition: background 0.2s;
        }
        .job-list-item:hover, .job-list-item.active { background: var(--bg-hover); }
        .job-list-item.active { border-left: 3px solid var(--primary); background: var(--primary-light); }
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
        .table-container table th:nth-child(1),
        .table-container table td:nth-child(1) { width: 30%; text-align: left; }
        
        .table-container table th:nth-child(2),
        .table-container table td:nth-child(2) { width: 20%; text-align: left; }
        
        .table-container table th:nth-child(3),
        .table-container table td:nth-child(3) { width: 15%; text-align: center; }
        
        .table-container table th:nth-child(4),
        .table-container table td:nth-child(4) { width: 35%; text-align: center; }
        .table-container table td:nth-child(4) form {
            justify-content: center;
        }

        .app-view-card {
            grid-template-columns: 350px 1fr;
        }

        @media (max-width: 1024px) {
            .app-view-card {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }
            .app-view-card > div:first-child {
                border-right: none;
                border-bottom: 1px solid var(--border);
                max-height: 300px;
            }
        }

        @media (max-width: 768px) {
            .mobile-stack {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div class="card app-view-card" style="min-height: 80vh; padding: 0; overflow: hidden; display: grid;">
                    <div style="border-right: 1px solid var(--border); display: flex; flex-direction: column;">
                        <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
                            <h3 class="font-bold text-lg mb-2">Job Postings</h3>
                            <input type="text" id="job-search-input" placeholder="Search..." class="form-control" style="font-size: 0.9rem;">
                        </div>
                        <div id="job-list-container" style="overflow-y: auto; flex: 1;">
                            <?php foreach($jobs_with_app_counts as $job): ?>
                                <div class="job-list-item <?php echo ($selected_job_id == $job['job_id']) ? 'active' : ''; ?>" 
                                     data-search="<?php echo htmlspecialchars(strtolower($job['company_name'] . ' ' . $job['job_title'])); ?>"
                                     onclick="window.location.href='admin_view_applications.php?job_id=<?php echo $job['job_id']; ?>'">
                                    <div class="flex justify-between items-start mb-1">
                                        <span class="font-bold text-sm"><?php echo htmlspecialchars($job['company_name']); ?></span>
                                        <span class="badge" style="font-size: 0.75rem; padding: 0.1rem 0.4rem;"><?php echo $job['application_count']; ?></span>
                                    </div>
                                    <div class="text-xs text-muted"><?php echo htmlspecialchars($job['job_title']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; overflow-y: hidden;">
                        <?php if ($selected_job_id): ?>
                            <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 1rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <h2 class="text-xl font-bold"><?php echo htmlspecialchars($selected_job_title); ?></h2>
                                        <p class="text-sm text-muted">Total Applicants: <?php echo count($applicants); ?></p>
                                    </div>

                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="text" id="applicant-search" placeholder="Search by name or regd no..." class="form-control" style="padding: 0.5rem; font-size: 0.9rem;">
                                    <div style="width: 200px;">
                                        <select id="filter-status" class="form-select" style="padding: 0.5rem; font-size: 0.9rem; border: 1px solid var(--border); border-radius: var(--radius-md); width: 100%;">
                                            <option value="">All Statuses</option>
                                            <option value="Applied">Applied</option>
                                            <option value="Shortlisted">Shortlisted</option>
                                            <option value="Interviewing">Interviewing</option>
                                            <option value="Rejected">Rejected</option>
                                            <option value="Selected">Selected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="table-container" style="flex: 1; overflow-y: auto; padding: 1.5rem;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Academic</th>
                                            <th>Status</th>
                                            <th>Update Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($applicants)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">No applications received yet.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($applicants as $applicant): 
                                                $searchText = strtolower($applicant['name'] . ' ' . $applicant['regdno']);
                                            ?>
                                                <tr class="applicant-row" 
                                                    data-search="<?php echo htmlspecialchars($searchText); ?>"
                                                    data-status="<?php echo htmlspecialchars($applicant['status']); ?>">
                                                    <td>
                                                        <div class="font-bold"><?php echo htmlspecialchars($applicant['name']); ?></div>
                                                        <div class="text-xs text-muted"><?php echo htmlspecialchars($applicant['regdno']); ?></div>
                                                        <div class="text-xs text-muted" style="display: flex; align-items: center; gap: 4px; margin-top: 4px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                            <i data-lucide="mail" style="width: 10px; flex-shrink: 0;"></i> 
                                                            <span style="overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($applicant['email']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-sm">
                                                        <div>CGPA: <?php echo htmlspecialchars(number_format($applicant['cgpa'] ?? 0, 2)); ?></div>
                                                        <div>Backlogs: <?php echo htmlspecialchars($applicant['backlogs'] ?? 0); ?></div>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $statusColors = match($applicant['status']) {
                                                                'Shortlisted' => ['bg' => '#f3e8ff', 'color' => '#7c3aed', 'border' => '#e9d5ff', 'icon' => 'star'],
                                                                'Interviewing' => ['bg' => '#dbeafe', 'color' => '#1d4ed8', 'border' => '#bfdbfe', 'icon' => 'video'],
                                                                'Rejected' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#fecaca', 'icon' => 'x-circle'],
                                                                'Applied' => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fde68a', 'icon' => 'send'],
                                                                default => ['bg' => '#ffedd5', 'color' => '#c2410c', 'border' => '#fed7aa', 'icon' => 'clock']
                                                            };
                                                        ?>
                                                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; border-radius: 2rem; font-size: 0.78rem; font-weight: 600; background: <?php echo $statusColors['bg']; ?>; color: <?php echo $statusColors['color']; ?>; border: 1px solid <?php echo $statusColors['border']; ?>;">
                                                            <i data-lucide="<?php echo $statusColors['icon']; ?>" style="width: 12px; height: 12px;"></i>
                                                            <?php echo htmlspecialchars($applicant['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <form action="admin_view_applications.php?job_id=<?php echo $selected_job_id; ?>" method="post" style="display: flex; gap: 0.5rem;">
                                                            <input type="hidden" name="application_id" value="<?php echo $applicant['application_id']; ?>">
                                                            <div class="select-wrapper" style="width: 140px;">
                                                                <select name="status" class="form-select form-select-sm w-full" onchange="this.form.submit()">
                                                                    <option value="Applied" <?php echo ($applicant['status'] == 'Applied') ? 'selected' : ''; ?>>Applied</option>
                                                                    <option value="Shortlisted" <?php echo ($applicant['status'] == 'Shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                                                                    <option value="Interviewing" <?php echo ($applicant['status'] == 'Interviewing') ? 'selected' : ''; ?>>Interviewing</option>
                                                                    <option value="Rejected" <?php echo ($applicant['status'] == 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
                                <i data-lucide="mouse-pointer-2" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>Select a job posting from the left sidebar to view applicants.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </main>
    </div>
    
    <script>
        lucide.createIcons();

        document.getElementById('job-search-input')?.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('.job-list-item').forEach(item => {
                const text = item.getAttribute('data-search');
                item.style.display = text.includes(term) ? 'block' : 'none';
            });
        });

        const appSearch = document.getElementById('applicant-search');
        const statusFilter = document.getElementById('filter-status');
        const appRows = document.querySelectorAll('.applicant-row');

        function filterApplicants() {
            const term = appSearch ? appSearch.value.toLowerCase().trim() : '';
            const status = statusFilter ? statusFilter.value : '';

            appRows.forEach(row => {
                const text = row.getAttribute('data-search');
                const rowStatus = row.getAttribute('data-status');
                
                const matchesSearch = text.includes(term);
                const matchesStatus = status === '' || rowStatus === status;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if(appSearch) appSearch.addEventListener('keyup', filterApplicants);
        if(statusFilter) statusFilter.addEventListener('change', filterApplicants);

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof TablePagination !== 'undefined') {

                if (document.querySelector('table')) {
                    new TablePagination('table', 10);
                }
            }
        });
    </script>
</body>
</html>