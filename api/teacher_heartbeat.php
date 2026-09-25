<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
include '../db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(["success" => false]);
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];
$now = date("Y-m-d H:i:s");
mysqli_query($conn, "UPDATE teachers SET last_activity = '$now' WHERE id = '$teacher_id'");

echo json_encode(["success" => true]);
?>