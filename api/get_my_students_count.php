<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../db_config.php';

$teacher_id = (int)$_SESSION['teacher_id'];

$sql = "SELECT COUNT(DISTINCT ss.student_id) AS cnt
        FROM teacher_subjects ts
        JOIN student_subjects ss ON ss.subject_id = ts.subject_id
        WHERE ts.teacher_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$count = (int)$row['cnt'];

echo json_encode([
    'success' => true,
    'data' => [
        'count' => $count,
        'trend' => $count > 0 ? '+'.$count.' this month' : ''
    ]
]);

$stmt->close();
?>