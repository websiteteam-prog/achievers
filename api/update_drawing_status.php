<?php
session_start();
include '../db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$id     = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['0', '1', 'reset'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

if ($action === 'reset') {
    $sql = "UPDATE student_drawings SET is_drawing_correct = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} else {
    $value = (int)$action;
    $sql = "UPDATE student_drawings SET is_drawing_correct = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $value, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
