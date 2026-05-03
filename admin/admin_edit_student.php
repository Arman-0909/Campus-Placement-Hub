<?php
// edit_student.php (Modernized)
require_once "../includes/config.php";
session_name("staff");
session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

$regdno = "";
$student_data = [];
$feedback_message = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regdno'])) {
    $regdno = $_POST['regdno'];
    $conn->begin_transaction();
    try {
        $sql_student = "UPDATE student SET name = ?, department = ?, contact = ?, email = ?, dob = ?, placement_status = ?, last_updated_by = ?, last_updated_on = NOW() WHERE regdno = ?";
        $stmt_student = $conn->prepare($sql_student);
        $admin_username = $_SESSION["username"];
        $stmt_student->bind_param("ssssssss", $_POST['name'], $_POST['department'], $_POST['contact'], $_POST['email'], $_POST['dob'], $_POST['placement_status'], $admin_username, $regdno);
        $stmt_student->execute();
        $stmt_student->close();

        $sql_marks = "INSERT INTO marks (regdno, cgpa, backlogs) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE cgpa = VALUES(cgpa), backlogs = VALUES(backlogs)";
        $stmt_marks = $conn->prepare($sql_marks);
        $stmt_marks->bind_param("sdi", $regdno, $_POST['cgpa'], $_POST['backlogs']);
        $stmt_marks->execute();
        $stmt_marks->close();

        $conn->commit();
        $conn->commit();
        $_SESSION['flash_message'] = "Success! Student profile has been updated.";
        header("Location: admin_edit_student.php?regdno=" . urlencode($regdno));
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $feedback_message = "Error: Could not update the record.";
        $feedback_class = "alert-error";
    }
} 
else if (isset($_GET['regdno'])) {
    $regdno = $_GET['regdno'];

    // Check for flash messages

}

if (!empty($regdno)) {
    $sql_fetch = "SELECT s.*, m.cgpa, m.backlogs FROM student s LEFT JOIN marks m ON s.regdno = m.regdno WHERE s.regdno = ?";
    if($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("s", $regdno);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows == 1) {
            $student_data = $result->fetch_assoc();
        } else {
            $feedback_message = "Student not found.";
            $feedback_class = "alert-error";
        }
        $stmt_fetch->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Admin Dashboard</title>
    <?php include '../includes/header_includes.php'; ?>
</head>
<body>
    <div class="dashboard-layout">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-main">
            <?php include '../includes/header.php'; ?>
            
            <div class="container" style="padding-top: 2rem;">
                
                <div class="card" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-header" style="margin-bottom: 1.5rem;">
                        <h3>Edit Student Profile</h3>
                    </div>

                    <?php if(!empty($feedback_message)): ?>
                        <div class="alert <?php echo $feedback_class; ?>" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; <?php echo ($feedback_class == 'alert-success') ? 'background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;' : 'background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;'; ?>">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if($student_data): ?>
                    <form action="../admin/admin_edit_student.php" method="post">
                        <input type="hidden" name="regdno" value="<?php echo htmlspecialchars($student_data['regdno']); ?>">

                        <h4 class="text-muted text-sm uppercase tracking-wide font-bold mb-4 mt-2">Personal Details</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Registration No</label>
                                <input type="text" value="<?php echo htmlspecialchars($student_data['regdno']); ?>" disabled class="form-control" style="background: var(--bg-hover);">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student_data['name']); ?>" required class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="department">Department</label>
                            <div class="select-wrapper">
                                <select id="department" name="department" required class="form-select">
                                    <option value="">-- Select --</option>
                                    <?php 
                                        $depts = ['CSE', 'ECE', 'ME', 'CE', 'EEE', 'BCA'];
                                        foreach($depts as $d) {
                                            $sel = ($student_data['department'] ?? '') == $d ? 'selected' : '';
                                            echo "<option value='$d' $sel>$d</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group"><label class="form-label" for="contact">Contact</label><input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($student_data['contact'] ?? ''); ?>" class="form-control"></div>
                            <div class="form-group"><label class="form-label" for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student_data['email'] ?? ''); ?>" class="form-control"></div>
                        </div>

                        <div class="form-group"><label class="form-label" for="dob">Date of Birth</label><input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student_data['dob'] ?? ''); ?>" class="form-control"></div>

                        <h4 class="text-muted text-sm uppercase tracking-wide font-bold mb-4 mt-6">Academic & Placement Status</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label" for="cgpa">CGPA</label>
                                <input type="number" step="0.01" id="cgpa" name="cgpa" value="<?php echo htmlspecialchars($student_data['cgpa'] ?? '0.0'); ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="backlogs">Backlogs</label>
                                <input type="number" id="backlogs" name="backlogs" value="<?php echo htmlspecialchars($student_data['backlogs'] ?? '0'); ?>" class="form-control">
                            </div>
                             <div class="form-group">
                                <label class="form-label" for="placement_status">Status</label>
                                <div class="select-wrapper">
                                    <select id="placement_status" name="placement_status" class="form-select">
                                        <option value="Not Placed" <?php echo ($student_data['placement_status'] ?? '') == 'Not Placed' ? 'selected' : ''; ?>>Not Placed</option>
                                        <option value="Placed" <?php echo ($student_data['placement_status'] ?? '') == 'Placed' ? 'selected' : ''; ?>>Placed</option>
                                        <option value="Seeking Better Offer" <?php echo ($student_data['placement_status'] ?? '') == 'Seeking Better Offer' ? 'selected' : ''; ?>>Seeking Better Offer</option>
                                        <option value="Not Interested" <?php echo ($student_data['placement_status'] ?? '') == 'Not Interested' ? 'selected' : ''; ?>>Not Interested</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> Save Changes
                            </button>
                        </div>

                    </form>
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