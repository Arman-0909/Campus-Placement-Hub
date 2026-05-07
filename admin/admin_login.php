<?php

session_name("staff");
session_start();
require_once "../includes/config.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: admin_dashboard.php");
    exit;
}
$login_error = ""; 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])){
    $uname = trim($_POST['uname']);
    $password = trim($_POST['password']);
    if(empty($uname) || empty($password)){
        $login_error = "Please enter username and password.";
    } else {
        $sql = "SELECT staff_name, password, role FROM staff WHERE staff_name = ? AND role = 'Admin'";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $uname);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $stmt->bind_result($staff_name, $hashed_password, $role);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            session_regenerate_id(true);
                            $_SESSION["loggedin"] = true;
                            $_SESSION["username"] = $staff_name;
                            $_SESSION["role"] = $role;
                            header("location: admin_dashboard.php");
                            exit();
                        } else {
                            $login_error = "Invalid credentials.";
                        }
                    }
                } else {
                    $login_error = "Invalid credentials or not an Admin account.";
                }
            }
            $stmt->close();
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
    <title>Admin Login - Campus Placement Hub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; overflow: hidden; opacity: 0.1;">
        <video autoplay muted loop playsinline style="width: 100%; height: 100%; object-fit: cover;">
            <source src="../assets/media/bg.mp4" type="video/mp4">
        </video>
    </div>
    <div class="login-card card" style="position: relative; z-index: 10;">
        <div class="login-header">
            <div class="login-logo" style="background: var(--secondary);">
                <i data-lucide="shield-check"></i>
            </div>
            <h2>Admin Portal</h2>
            <p class="text-muted">Restricted access for authorized personnel only</p>
        </div>

        <?php if(!empty($login_error)): ?>
            <div style="background: #fef2f2; color: #ef4444; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; border: 1px solid #fee2e2;">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <form action="../admin/admin_login.php" method="post">
            <div class="form-group">
                <label class="form-label" for="uname">Username</label>
                <div style="position: relative;">
                    <i data-lucide="user-cog" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="text" id="uname" name="uname" class="form-control" style="padding-left: 2.75rem;" placeholder="Enter admin username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div style="position: relative;">
                    <i data-lucide="lock" style="position: absolute; left: 1rem; top: 0.75rem; color: var(--text-muted); width: 18px;"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left: 2.75rem; padding-right: 2.75rem;" placeholder="••••••••" required>
                    <button type="button" id="togglePassword" style="position: absolute; right: 1rem; top: 0.5rem; background: none; border: none; cursor: pointer; color: var(--text-muted);">
                        <i data-lucide="eye" style="width: 18px;"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-end items-center" style="margin-bottom: 1.5rem;">
                <a href="../admin/admin_forgot_password.php" class="text-sm font-medium text-primary hover:underline">Forgot password?</a>
            </div>
            
            <button type="submit" name="submit" class="btn btn-dark" style="width: 100%; justify-content: center;">
                Admin Sign In
                <i data-lucide="arrow-right" style="width: 18px;"></i>
            </button>
        </form>

        <div class="text-center" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
            <a href="index.php" class="text-sm text-muted hover:text-primary flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" style="width: 14px;"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            if(type === 'text'){
                togglePassword.innerHTML = '<i data-lucide="eye-off" style="width: 18px;"></i>';
            } else {
                togglePassword.innerHTML = '<i data-lucide="eye" style="width: 18px;"></i>';
            }
            lucide.createIcons();
        });
    </script>
    <?php include '../includes/toast_snippet.php'; ?>
</body>
</html>