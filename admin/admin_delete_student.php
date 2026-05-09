<?php

session_name("staff");
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

if (!isset($_GET["regdno"]) || empty(trim($_GET["regdno"]))) {
    header("location: admin_manage_students.php");
    exit;
}

$regdno = trim($_GET["regdno"]);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("DELETE FROM marks WHERE regdno = ?");
    $stmt->bind_param("s", $regdno);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM package WHERE regdno = ?");
    $stmt->bind_param("s", $regdno);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM student WHERE regdno = ?");
    $stmt->bind_param("s", $regdno);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}

$conn->close();

header("location: admin_manage_students.php");
exit;
?>