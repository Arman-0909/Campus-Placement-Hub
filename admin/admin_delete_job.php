<?php

session_name("staff");
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("location: admin_manage_jobs.php");
    exit;
}

$job_id = $_GET["id"];

$sql = "DELETE FROM jobs WHERE job_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $job_id);
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "The job posting has been deleted.";
    }
    $stmt->close();
}
$conn->close();

header("location: admin_manage_jobs.php");
exit;
?>