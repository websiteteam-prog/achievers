<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

include '../db_config.php';

$teacher_id = (int)$_SESSION['teacher_id'];

$sql = "
SELECT

s.id,
s.grade,
s.subject_name,
s.course_type,

COUNT(DISTINCT ss.student_id) AS total_students

FROM teacher_subjects ts

INNER JOIN subjects s
ON s.id = ts.subject_id

LEFT JOIN student_subjects ss
ON ss.subject_id = s.id

WHERE ts.teacher_id = $teacher_id

GROUP BY

s.id,
s.grade,
s.subject_name,
s.course_type

ORDER BY

s.grade,
s.subject_name
";

$res = mysqli_query($conn, $sql);

$data = [];
while($row = mysqli_fetch_assoc($res)){
    $row['total_students'] = (int)$row['total_students'];
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $data
]);