<?php
// hodcompanylist.php (Modernized)
require_once "../includes/config.php";
session_name("hod");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: hod_login.php");
    exit;
}

$backlog = $_POST['backlogs'] ?? '';
$cgpa = $_POST['cgpa'] ?? '';    
$company = $_POST['company'] ?? '';
$eligible_students = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT s.regdno, s.name, s.contact, s.email, s.dob, m.backlogs, m.cgpa 
            FROM student s
            JOIN marks m ON s.regdno = m.regdno
            WHERE m.cgpa >= ? AND m.backlogs <= ?";
    
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("di", $cgpa, $backlog);
        $stmt->execute();
        $result = $stmt->get_result();
        $eligible_students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligible Students - HOD Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div class="card">
                    <div class="card-header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>Eligible: <?php echo htmlspecialchars($company); ?></h3>
                            <p class="text-muted text-sm mt-1">
                                Criteria: Min CGPA <strong><?php echo htmlspecialchars($cgpa); ?></strong>, Max Backlogs <strong><?php echo htmlspecialchars($backlog); ?></strong>
                            </p>
                        </div>
                        <a href="../admin/hod_access2.php" class="btn btn-secondary btn-sm">
                            <i data-lucide="arrow-left" style="width: 14px;"></i> Back to Filter
                        </a>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Contact Info</th>
                                    <th>Age/DOB</th>
                                    <th>CGPA</th>
                                    <th>Backlogs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($eligible_students)): ?>
                                    <?php foreach($eligible_students as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="font-bold"><?php echo htmlspecialchars($student['name']); ?></div>
                                            <div class="text-xs text-muted"><?php echo htmlspecialchars($student['regdno']); ?></div>
                                        </td>
                                        <td>
                                            <div class="text-sm"><?php echo htmlspecialchars($student['email']); ?></div>
                                            <div class="text-xs text-muted"><?php echo htmlspecialchars($student['contact']); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['dob']); ?></td>
                                        <td>
                                            <span class="badge" style="background: var(--bg-hover);"><?php echo htmlspecialchars(number_format($student['cgpa'], 2)); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['backlogs']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted" style="padding: 3rem;">
                                            <i data-lucide="users" style="width: 32px; height: 32px; opacity: 0.2; margin-bottom: 0.5rem; display: block; margin: 0 auto 0.5rem;"></i>
                                            No eligible students found for these criteria.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if(!empty($eligible_students)): ?>
                        <div class="mt-4 pt-4 border-t border-border flex justify-end">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i data-lucide="printer"></i> Print List
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>