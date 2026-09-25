<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['teacher_id'])){
    echo json_encode(['success'=>false]);
    exit;
}

include '../db_config.php';

$teacher_id=(int)$_SESSION['teacher_id'];

$sql = "
SELECT COUNT(*) AS total
FROM student_documents sd
INNER JOIN teacher_subjects ts
ON ts.subject_id = sd.subject_id
WHERE ts.teacher_id = ?
AND sd.status='Pending'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$teacher_id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

echo json_encode([
    "success"=>true,
    "data"=>[
        "count"=>(int)$row['total'],
        "trend"=>"Awaiting Review"
    ]
]);