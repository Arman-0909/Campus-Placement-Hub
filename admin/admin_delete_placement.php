<?php

session_name("staff");
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: admin_manage_placements.php");
    exit;
}

$placement_id = $_GET["id"];

$conn->begin_transaction();

try {
    $student_regdno = null;
    $stmt_get = $conn->prepare("SELECT student_regdno FROM placements WHERE placement_id = ?");
    $stmt_get->bind_param("i", $placement_id);
    $stmt_get->execute();
    $row = $stmt_get->get_result()->fetch_assoc();
    $stmt_get->close();

    if (!$row) {
        throw new Exception("Placement record not found.");
    }

    $student_regdno = $row['student_regdno'];

    $stmt_delete = $conn->prepare("DELETE FROM placements WHERE placement_id = ?");
    $stmt_delete->bind_param("i", $placement_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    $stmt_update = $conn->prepare("UPDATE student SET placement_status = 'Not Placed' WHERE regdno = ?");
    $stmt_update->bind_param("s", $student_regdno);
    $stmt_update->execute();
    $stmt_update->close();

    $conn->commit();
    $_SESSION['flash_success'] = "Placement record has been removed. Student status is now 'Not Placed'.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['flash_error'] = "Error: Could not remove the placement record.";
}

$conn->close();

header("location: admin_manage_placements.php");
exit;
?>