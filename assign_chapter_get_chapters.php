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

$stmt = $conn->prepare("SELECT chapter_name FROM chapters WHERE subject_id = ? ORDER BY chapter_name ASC");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<option value="">No chapters found for this subject</option>';
    exit;
}

echo '<option value="">-- Select --</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . htmlspecialchars($row['chapter_name']) . '">' . htmlspecialchars($row['chapter_name']) . '</option>';
}
