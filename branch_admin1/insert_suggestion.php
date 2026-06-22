<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';

$branch_id = $_SESSION['branch_id'];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suggestion = $_POST['suggestion'];
    $subject = $_POST['subject'];
    
    $stmt = $conn->prepare("insert into course_suggestions (subject_id, suggestion, branch_id) values (?, ?, ?)");
    $stmt->bind_param("isi", $subject, $suggestion, $branch_id);
    
    
     if($stmt->execute()){
        echo "Suggestion sent Successfully";
    }
    else{
        echo "Failed to send suggestion";
    }
}
?>