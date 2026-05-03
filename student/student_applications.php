<?php
// student_applications.php (Modernized)
require_once '../includes/config.php';
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["num"])){
    header("location: student_login.php");
    exit;
}
$regdno = $_SESSION["num"];

// Fetch applications
$applications = [];
$sql = "SELECT a.application_date, a.status, j.company_name, j.job_title, j.package_lpa
        FROM applications a
        JOIN jobs j ON a.job_id = j.job_id
        WHERE a.student_regdno = ?
        ORDER BY a.application_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $regdno);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $applications[] = $row;
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
    <title>My Applications - Student Portal</title>
    <?php include '../includes/header_includes.php'; ?>
    <style>
        .app-status {
            padding: 0.3rem 0.85rem;
            border-radius: 2rem;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            letter-spacing: 0.01em;
            border: 1px solid transparent;
        }
        /* Amber — waiting/submitted */
        .status-applied { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        /* Purple — progress/shortlisted */
        .status-shortlisted { background: #f3e8ff; color: #7c3aed; border-color: #e9d5ff; }
        /* Blue — active/interviewing */
        .status-interviewing { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
        /* Green — success/placed */
        .status-selected { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        /* Red — rejected */
        .status-rejected { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        /* Orange — pending/default */
        .status-pending { background: #ffedd5; color: #c2410c; border-color: #fed7aa; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
            
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">My Applications</h1>
                    <p class="text-muted">Track the status of your job applications</p>
                </div>

                <div class="card" style="padding: 0; overflow: hidden;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead style="background: var(--bg-body); border-bottom: 1px solid var(--border);">
                                <tr>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.9rem; color: var(--text-muted);">Company & Role</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.9rem; color: var(--text-muted);">Package</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.9rem; color: var(--text-muted);">Applied On</th>
                                    <th style="padding: 1rem 1.5rem; font-weight: 600; font-size: 0.9rem; color: var(--text-muted);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($applications)): ?>
                                    <tr>
                                        <td colspan="4" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                                            You haven't applied to any jobs yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($applications as $app): 
                                        $statusLower = strtolower(trim($app['status']));
                                        $statusClass = 'status-pending';
                                        $statusIcon = 'clock';
                                        
                                        if (strpos($statusLower, 'selected') !== false || strpos($statusLower, 'placed') !== false) {
                                            $statusClass = 'status-selected';
                                            $statusIcon = 'check-circle';
                                        } elseif (strpos($statusLower, 'reject') !== false) {
                                            $statusClass = 'status-rejected';
                                            $statusIcon = 'x-circle';
                                        } elseif (strpos($statusLower, 'interview') !== false) {
                                            $statusClass = 'status-interviewing';
                                            $statusIcon = 'video';
                                        } elseif (strpos($statusLower, 'shortlist') !== false) {
                                            $statusClass = 'status-shortlisted';
                                            $statusIcon = 'star';
                                        } elseif (strpos($statusLower, 'applied') !== false) {
                                            $statusClass = 'status-applied';
                                            $statusIcon = 'send';
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid var(--border);">
                                        <td style="padding: 1rem 1.5rem;">
                                            <div class="font-bold text-main"><?php echo htmlspecialchars($app['company_name']); ?></div>
                                            <div class="text-sm text-muted"><?php echo htmlspecialchars($app['job_title']); ?></div>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <?php echo htmlspecialchars($app['package_lpa']); ?> LPA
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <?php echo date("d M, Y", strtotime($app['application_date'])); ?>
                                        </td>
                                        <td style="padding: 1rem 1.5rem;">
                                            <span class="app-status <?php echo $statusClass; ?>">
                                                <i data-lucide="<?php echo $statusIcon; ?>" style="width: 13px; height: 13px;"></i>
                                                <?php echo htmlspecialchars($app['status']); ?>
                                            </span>
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