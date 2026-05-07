<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

if(!isset($_GET["regdno"]) || empty(trim($_GET["regdno"]))){
    header("location: admin_manage_students.php");
    exit;
}

require_once "../includes/config.php";
$regdno = trim($_GET["regdno"]);

mysqli_autocommit($conn, false);

$all_queries_succeeded = true;

$sql1 = "DELETE FROM marks WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql1)) {
    $all_queries_succeeded = false;
}

$sql2 = "DELETE FROM package WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql2)) {
    $all_queries_succeeded = false;
}

$sql3 = "DELETE FROM student WHERE regdno = '$regdno'";
if (!mysqli_query($conn, $sql3)) {
    $all_queries_succeeded = false;
}

if ($all_queries_succeeded) {

    mysqli_commit($conn);
} else {

    mysqli_rollback($conn);
}

mysqli_close($conn);

header("location: admin_manage_students.php");
exit;
?>