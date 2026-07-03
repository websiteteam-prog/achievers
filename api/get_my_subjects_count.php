<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    echo json_encode([
        "success" => false
    ]);
    exit;
}

include '../db_config.php';

$teacher_id = (int)$_SESSION['teacher_id'];

$sql = "
SELECT COUNT(*) total
FROM teacher_subjects
WHERE teacher_id = $teacher_id
";

$result = mysqli_query($conn,$sql);

$row = mysqli_fetch_assoc($result);

echo json_encode([
    "success"=>true,
    "data"=>[
        "count"=>(int)$row['total'],
        "trend"=>"Subjects Assigned"
    ]
]);