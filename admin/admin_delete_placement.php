<?php

session_name("staff");
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: admin_login.php");
    exit;
}

require_once "../includes/config.php";

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])){
    header("location: admin_manage_placements.php");
    exit;
}

require_once "../includes/config.php";

$placement_id = $_GET["id"];

$conn->begin_transaction();

try {

    $student_regdno = null;
    $sql_get_student = "SELECT student_regdno FROM placements WHERE placement_id = ?";
    if($stmt_get = $conn->prepare($sql_get_student)){
        $stmt_get->bind_param("i", $placement_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        if($row = $result->fetch_assoc()){
            $student_regdno = $row['student_regdno'];
        }
        $stmt_get->close();
    }

    if($student_regdno){

        $sql_delete = "DELETE FROM placements WHERE placement_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $placement_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        $sql_update = "UPDATE student SET placement_status = 'Not Placed' WHERE regdno = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $student_regdno);
        $stmt_update->execute();
        $stmt_update->close();

        $conn->commit();
        $_SESSION['flash_message'] = "Placement record has been removed. Student status is now 'Not Placed'.";
    } else {

        throw new Exception("Placement record not found.");
    }

} catch (Exception $e) {

    $conn->rollback();
    $_SESSION['flash_message'] = "Error: Could not remove the placement record."; // You can set an error flash message
}

$conn->close();

header("location: admin_manage_placements.php");
exit;
?>