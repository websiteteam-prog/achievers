<?php
ini_set ("display_errors", 1);
ini_set ("display_startup_erros", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $date = $_POST['date'];
    $title = $_POST['title'];
    $agenda = $_POST['agenda'];
    
    $stmt = $conn->prepare("insert into events (branch_id, event_name, description, date) values (?, ?, ?, ?)");
    $stmt->bind_param("isss", $branch_id, $title, $agenda, $date);
    
    if($stmt->execute()){
        echo "event added successfully";
    } else{
        echo "event is not added";
    }
     $stmt->close();
    $conn->close();
}
?>
