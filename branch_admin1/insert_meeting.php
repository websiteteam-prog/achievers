<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $date = $_POST['date'];
    $id = $_POST['id'];
    $agenda = $_POST['agenda'];
    
    $stmt= $conn->prepare("insert into meetings (students_id, agenda, date) values (?, ?, ?)");
    $stmt->bind_param("iss", $id, $agenda, $date);
    
    if($stmt->execute()){
        echo "Meeting Scheduled Successfully";
    }
    else{
        echo "Failed to Schedule Meeting";
    }
}
$stmt->close();
$conn->close();

?>