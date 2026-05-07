<?php
session_name("staff");

session_start();

$_SESSION = array();

session_destroy();

header("location: admin_login.php");
exit;
?>