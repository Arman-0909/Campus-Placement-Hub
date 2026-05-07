<?php

if (isset($_GET['role']) && $_GET['role'] === 'admin') {
    session_name("staff");
}
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once "../includes/config.php";

$is_admin = isset($_SESSION['role']);
$is_student = isset($_SESSION['num']) && !isset($_SESSION['role']);

if ($is_admin) {
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_notifications'");
    if ($table_check->num_rows > 0) {
        $sql = "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute();
            $stmt->close();
        }
    }
} elseif ($is_student) {
    $regdno = $_SESSION["num"];
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows > 0) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE student_regdno = ? AND is_read = 0";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $regdno);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
