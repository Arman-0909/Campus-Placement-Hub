<?php

require_once "../includes/config.php";
session_start();

$feedback_msg = "";
$feedback_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $regdno = $_POST['regdno'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($regdno) || empty($current_password) || empty($new_password) || empty($confirm_password)){
        $feedback_msg = "Please fill in all fields.";
        $feedback_class = "alert-error";
    } elseif ($new_password !== $confirm_password) {
        $feedback_msg = "New password and confirm password do not match.";
        $feedback_class = "alert-error";
    } elseif (strlen($new_password) < 6) {
        $feedback_msg = "New password must be at least 6 characters long.";
        $feedback_class = "alert-error";
    } else {

        $sql = "SELECT password FROM student WHERE regdno = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $regdno);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($current_password, $user['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE student SET password = ? WHERE regdno = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $new_hashed_password, $regdno);
            
            if($stmt_update->execute()){
                $_SESSION['flash_message'] = "Password changed successfully! You can now log in with your new password.";
                header("Location: student_login.php");
                exit;
            } else {
                $feedback_msg = "Oops! Something went wrong. Please try again.";
                $feedback_class = "alert-error";
            }
            $stmt_update->close();
        } else {
            $feedback_msg = "Incorrect Registration Number or Current Password.";
            $feedback_class = "alert-error";
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Student Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .alert-error { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; }
        .alert-success { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body class="login-page">
    
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden; opacity: 0.1;">
        <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;">
            <source src="../assets/media/bg.mp4" type="video/mp4">
        </video>
    </div>

    <div class="login-card card" style="position: relative; z-index: 10;">
        <div class="login-header">
            <div class="login-logo">
                <i data-lucide="key"></i>
            </div>
            <h2>Change Password</h2>
            <p class="text-muted">Secure your student account</p>
        </div>

        <?php if(!empty($feedback_msg)): ?>
            <div class="<?php echo $feedback_class; ?>">
                <?php echo $feedback_msg; ?>
            </div>
        <?php endif; ?>

        <form action="../student/student_forgot_password.php" method="post">
            <div class="form-group">
                <label class="form-label" for="regdno">Registration Number</label>
                <div style="position: relative;">
                    <i data-lucide="hash" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="text" id="regdno" name="regdno" class="form-control" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <div style="position: relative;">
                    <i data-lucide="lock" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="password" id="current_password" name="current_password" class="form-control" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="new_password">New Password</label>
                <div style="position: relative;">
                    <i data-lucide="lock" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="password" id="new_password" name="new_password" class="form-control" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm New Password</label>
                <div style="position: relative;">
                    <i data-lucide="check-circle" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                Update Password
            </button>
        </form>

        <div class="text-center" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <a href="../student/student_login.php" class="text-sm text-muted hover:text-primary flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" style="width: 14px;"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>