<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$subject_id = (int)($_POST['subject_id'] ?? 0);

function redirect_with_error($code) {
    header("Location: student_documents.php?error=" . $code);
    exit();
}

// Subject must actually belong to this student
$sql_check = "SELECT id FROM student_subjects WHERE student_id = ? AND subject_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $student_id, $subject_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    redirect_with_error('subject');
}
$stmt->close();

if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    redirect_with_error('upload');
}

$file = $_FILES['document'];

$max_size = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $max_size) {
    redirect_with_error('size');
}

$allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext, true)) {
    redirect_with_error('type');
}

$upload_dir = __DIR__ . '/../uploads/student_documents/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$safe_name = time() . '_' . $student_id . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
$target_path = $upload_dir . $safe_name;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    redirect_with_error('upload');
}

$relative_path='uploads/student_documents/'.$safe_name;
$original_name = mysqli_real_escape_string(
$conn,
basename($file['name'])
);

$sql_insert = "INSERT INTO student_documents (student_id, subject_id, file_name, file_path)
               VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("iiss", $student_id, $subject_id, $original_name, $relative_path);
$stmt->execute();
$stmt->close();

header("Location: student_documents.php?uploaded=1");
exit();