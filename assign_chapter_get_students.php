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

// Get the grade this subject belongs to, so we only show students
// whose CURRENT grade still matches — not just an old student_subjects link.
$gradeStmt = $conn->prepare("SELECT grade FROM subjects WHERE id = ?");
$gradeStmt->bind_param("i", $subject_id);
$gradeStmt->execute();
$subjectGrade = $gradeStmt->get_result()->fetch_assoc()['grade'] ?? null;

if ($subjectGrade === null) {
    echo '<option value="">-- Invalid subject --</option>';
    exit;
}

$stmt = $conn->prepare("SELECT s.id, s.first_name FROM students s
                         JOIN student_subjects ss ON s.id = ss.student_id
                         WHERE ss.subject_id = ?
                         AND s.grade = ?
                         ORDER BY s.first_name ASC");
$stmt->bind_param("is", $subject_id, $subjectGrade);
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