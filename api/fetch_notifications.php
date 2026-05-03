<?php
// fetch_notifications.php - API endpoint for student notifications
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

$notifications = [];
$unread_count = 0;

if ($is_admin) {
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_notifications'");
    if ($table_check->num_rows > 0) {
        $sql = "SELECT notification_id, type, title, message, is_read, created_at 
                FROM admin_notifications 
                ORDER BY created_at DESC 
                LIMIT 20";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['time_ago'] = timeAgo($row['created_at']);
                $notifications[] = $row;
            }
            $stmt->close();
        }

        $sql_count = "SELECT COUNT(*) as cnt FROM admin_notifications WHERE is_read = 0";
        if ($stmt = $conn->prepare($sql_count)) {
            $stmt->execute();
            $unread_count = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();
        }
    }
} elseif ($is_student) {
    $regdno = $_SESSION["num"];
    $table_check = $conn->query("SHOW TABLES LIKE 'notifications'");
    if ($table_check->num_rows > 0) {
        $sql = "SELECT notification_id, type, title, message, is_read, created_at 
                FROM notifications 
                WHERE student_regdno = ? 
                ORDER BY created_at DESC 
                LIMIT 20";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $regdno);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $row['time_ago'] = timeAgo($row['created_at']);
                $notifications[] = $row;
            }
            $stmt->close();
        }

        $sql_count = "SELECT COUNT(*) as cnt FROM notifications WHERE student_regdno = ? AND is_read = 0";
        if ($stmt = $conn->prepare($sql_count)) {
            $stmt->bind_param("s", $regdno);
            $stmt->execute();
            $unread_count = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();
        }
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => (int)$unread_count
]);

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
?>
