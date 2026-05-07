<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

require_once "../includes/config.php";

if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: admin_manage_companies.php");
    exit;
}

require_once "../includes/config.php";
$company_name = $_GET["id"];

$sql = "DELETE FROM company WHERE companyname = ?";

if($stmt = $conn->prepare($sql)){
    $stmt->bind_param("s", $company_name);
    
    if($stmt->execute()){

        $_SESSION['flash_message'] = "Company '" . htmlspecialchars($company_name) . "' has been deleted.";
    }
    $stmt->close();
}
$conn->close();

header("location: admin_manage_companies.php");
exit;
?>