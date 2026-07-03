<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    http_response_code(403);
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];
$subject_id = (int)($_GET['subject_id'] ?? 0);

$check = $conn->prepare("SELECT 1 FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
$check->bind_param("ii", $teacher_id, $subject_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo '<option value="">-- Not authorized --</option>';
    exit;
}

$stmt = $conn->prepare("SELECT s.id, s.first_name FROM students s
                         JOIN student_subjects ss ON s.id = ss.student_id
                         WHERE ss.subject_id = ?
                         ORDER BY s.first_name ASC");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<option value="">No students enrolled for this grade/subject</option>';
    exit;
}

echo '<option value="">-- Select --</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . (int)$row['id'] . '">' . htmlspecialchars($row['first_name']) . '</option>';
}
