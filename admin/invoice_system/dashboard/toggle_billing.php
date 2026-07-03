<?php
include "../../../db_config.php";

$student_id = intval($_POST['student_id'] ?? 0);
$pause      = intval($_POST['pause'] ?? 0);   // 1 = pause, 0 = resume

if($student_id){
    $pause = $pause ? 1 : 0;
    mysqli_query($conn, "
        UPDATE enrollment_inquiries 
        SET billing_paused = '$pause'
        WHERE student_id = '$student_id'
    ");
}

echo "ok";