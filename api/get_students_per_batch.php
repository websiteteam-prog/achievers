<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include '../db_config.php';

$teacher_id = (int)$_SESSION['teacher_id'];

/* Har batch (subject) ke students */
$sql = "SELECT
            s.subject_name,
            s.grade,
            s.course_type,
            COUNT(DISTINCT ss.student_id) AS student_count
        FROM teacher_subjects ts
        INNER JOIN subjects s ON s.id = ts.subject_id
        LEFT JOIN student_subjects ss ON ss.subject_id = s.id
        WHERE ts.teacher_id = ?
        GROUP BY s.id, s.subject_name, s.grade, s.course_type
        ORDER BY s.grade, s.subject_name";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();

$batches = [];
while ($row = $res->fetch_assoc()) {
    $batches[] = [
        'subject_name'  => $row['subject_name'],
        'grade'         => $row['grade'],
        'course_type'   => $row['course_type'],
        'student_count' => (int)$row['student_count'],
    ];
}
$stmt->close();

/* Total distinct students (ek student multiple batch me ho sakta hai) */
$totSql = "SELECT COUNT(DISTINCT ss.student_id) AS total
           FROM teacher_subjects ts
           JOIN student_subjects ss ON ss.subject_id = ts.subject_id
           WHERE ts.teacher_id = ?";
$tStmt = $conn->prepare($totSql);
$tStmt->bind_param("i", $teacher_id);
$tStmt->execute();
$tRow = $tStmt->get_result()->fetch_assoc();
$total_students = (int)$tRow['total'];
$tStmt->close();

$branchSql = "SELECT COUNT(*) AS total_branches
              FROM teachers
              WHERE id = ?
              AND branch IS NOT NULL
              AND branch <> ''";

$branchStmt = $conn->prepare($branchSql);
$branchStmt->bind_param("i", $teacher_id);
$branchStmt->execute();

$branchRow = $branchStmt->get_result()->fetch_assoc();

$total_branches = (int)$branchRow['total_branches'];

$branchStmt->close();

echo json_encode([
    'success' => true,
    'data' => [
        'total_students' => $total_students,
        'total_branches' => $total_branches,
        'batches'        => $batches
    ]
]);