<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

include "../db_config.php";

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'branch_admin') {
    header("Location: login.php");
    exit();
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $subjects =  $_POST['subject'];
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
    
    $sql = "insert into teachers (name, email, password, branch, contact_no) values (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $password, $branch, $contact_no);
    
    $result = $stmt->execute();
    
    if($result){
        $teacher_id= $stmt->insert_id;
        
        $insertSubjectMap= $conn->prepare("insert into teacher_subjects (teacher_id, subject_id) values (?, ?)");
        
        foreach($subjects as $subject_id){
            $insertSubjectMap->bind_param("ii", $teacher_id, $subject_id);
            $insertSubjectMap->execute();
        }
    
         $_SESSION['message'] = "Teacher added successfully!";
    }
    else {
          $_SESSION['error'] = "Failed to add teacher: " . $stmt->error;
    }
    $stmt->close();
} else {
        $_SESSION['error'] = "SQL error: " . $conn->error;
    }
    
    $_SESSION['error'] = "This email is already registered. Please use a different one.";
    header("Location: branch_manage_users.php");
    exit();
?>