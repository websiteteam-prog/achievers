<?php
include "../../db_config.php";

$program = $_GET['program'] ?? '';

$type = '';

if($program == "Early Starters"){
    $type = "early_learner";
}
elseif($program == "Elementary"){
    $type = "elementary";
}
elseif($program == "Advanced Learners"){
    $type = "advanced";
}

$result = mysqli_query($conn, "SELECT * FROM subjects WHERE course_type='$type'");

$subjects = [];

while($row = mysqli_fetch_assoc($result)){
    $subjects[] = $row;
}

echo json_encode($subjects);