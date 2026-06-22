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
    $column = $_POST['detail'];
    $student_id = $_POST['student_id'];
    $new_value = $_POST['new_value'];
    
    $allowed_columns = ['first_name', 'parent_email', 'grade', 'email', 'phone', 'parent_name', 'parent_contact', 'address', 'mode_of_education'];
    
        if (!in_array($column, $allowed_columns)) {
        die("Invalid column name.");
    }
    
    $sql = "update students set `$column` = ? where id = ?";
    $stmt=$conn->prepare($sql);
    $stmt->bind_param("si", $new_value, $student_id);
    
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE id = ?");
$check_stmt->bind_param("i", $student_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows == 0) {
    die("student ID does not exist.");
}
$check_stmt->close();
    
    if($stmt->execute()){
        echo "updated successfully";
    }
    else{
        echo "Failed to update";
    }
    
    $stmt->close();
}

?>