<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
include '../db_config.php';



if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

$branch_id = $_SESSION['branch_id'];
$admin_email = $_SESSION['admin_email'];


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $subject_id = mysqli_real_escape_string($conn, $_POST['subject']);
    $teacher_id = mysqli_real_escape_string($conn, $_POST['teacher_id']);
    $action = mysqli_real_escape_string($conn, $_POST['action']);
    
    // check if the teacher id exists in the database
    
    $check_stmt = $conn->prepare("select id from teachers where id = ?");
    $check_stmt->bind_param("i", $teacher_id);
    $check_stmt->execute();
    $check_stmt ->store_result();
    
    if ($check_stmt->num_rows === 0){
        echo "Error: Teacher ID does not exist";
        $check_stmt->close();
        exit();
    }
    
    $check_stmt->close();
    
    // proceed with the selected option
    if($action === 'add') {
        $sqlAdd = $conn->prepare( "Insert into teacher_subjects (teacher_id , subject_id) values (?, ?)");
        $sqlAdd->bind_param("ii", $teacher_id, $subject_id);
        if ($sqlAdd->execute()){
            echo "Subject added successfully";
        } else {
            echo "Failed to add subject or May be it akready exists";
        }
        
        } elseif($action === 'delete') {
            $sqlDelete = $conn->prepare( "delete from teacher_subjects where teacher_id = ? and subject_id = ?");
            $sqlDelete->bind_param("ii", $teacher_id, $subject_id);
            
            if($sqlDelete->execute()){
                echo "Subject from teacher removed successfully";
            } else {
                echo "Failed to remove the subject";
            }
        } else {
            echo "invalid action";
        }
        
    
}
?>