<?php
session_start();
include '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success'=>false,'message'=>'Session expired']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

if (empty($_FILES['profile_photo']['name'])) {
    echo json_encode(['success'=>false,'message'=>'No file selected']);
    exit;
}

$allowed = ['jpg','jpeg','png','webp'];
$ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode(['success'=>false,'message'=>'Only JPG, PNG, WEBP allowed']);
    exit;
}

if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success'=>false,'message'=>'Upload error']);
    exit;
}

if ($_FILES['profile_photo']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success'=>false,'message'=>'Max file size is 2MB']);
    exit;
}

// Validate it's actually an image (not just renamed file)
if (@getimagesize($_FILES['profile_photo']['tmp_name']) === false) {
    echo json_encode(['success'=>false,'message'=>'Invalid image file']);
    exit;
}

$uploadDir = '../uploads/teachers/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName   = 'teacher_' . $teacher_id . '_' . time() . '.' . $ext;
$targetFile = $uploadDir . $fileName;

if (!move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFile)) {
    echo json_encode(['success'=>false,'message'=>'Failed to save file']);
    exit;
}

$dbPath = 'uploads/teachers/' . $fileName;

// fetch old photo to delete it after successful update
$stmt = $conn->prepare("SELECT profile_photo FROM teachers WHERE id=?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("UPDATE teachers SET profile_photo=? WHERE id=?");
$stmt->bind_param("si", $dbPath, $teacher_id);

if ($stmt->execute()) {
    if (!empty($old['profile_photo']) && file_exists('../' . $old['profile_photo'])) {
        @unlink('../' . $old['profile_photo']);
    }
    $_SESSION['teacher_photo'] = $dbPath;
    $baseUrl = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

    echo json_encode([
        'success'   => true,
        'image_url' => $baseUrl . '/' . $dbPath
    ]);
} else {
    @unlink($targetFile);
    echo json_encode(['success'=>false,'message'=>'Database update failed']);
}