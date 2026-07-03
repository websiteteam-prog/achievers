<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['teacher_id'])){
    echo json_encode(['success'=>false]);
    exit;
}

include '../db_config.php';

$teacher_id=(int)$_SESSION['teacher_id'];

$sql="
SELECT COUNT(*) total
FROM student_documents sd
INNER JOIN teacher_subjects ts
ON ts.subject_id=sd.subject_id
WHERE ts.teacher_id=$teacher_id
AND sd.status='pending'
";

$result=mysqli_query($conn,$sql);

$row=mysqli_fetch_assoc($result);

echo json_encode([
"success"=>true,
"data"=>[
"count"=>(int)$row['total'],
"trend"=>"Pending Approval"
]
]);