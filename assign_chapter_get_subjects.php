<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403);
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];
$grade = $_GET['grade'] ?? '';

if ($grade === '') {
    echo '<option value="">-- Select Grade First --</option>';
    exit;
}

$stmt = $conn->prepare("SELECT s.id, s.subject_name
                         FROM teacher_subjects ts
                         JOIN subjects s ON ts.subject_id = s.id
                         WHERE ts.teacher_id = ? AND s.grade = ?
                         ORDER BY s.subject_name ASC");
$stmt->bind_param("is", $teacher_id, $grade);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<option value="">No subjects found for this grade</option>';
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo '<option value="' . (int)$row['id'] . '">' . htmlspecialchars($row['subject_name']) . '</option>';
}
