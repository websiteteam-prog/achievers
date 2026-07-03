<?php
include "../../../db_config.php";

$program = $_GET['program'] ?? '';
$grade   = $_GET['grade'] ?? '';

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

/*
|-----------------------------------------
| Convert grade dynamically
|-----------------------------------------
*/

$dbGrade = trim($grade);

// Grade 1 -> 1
if (preg_match('/^Grade\s+(\d+)$/i', $dbGrade, $match)) {

    $dbGrade = $match[1];

} else {

    // Pre-School -> pre-school
    // Kindergarten -> kindergarten
    $dbGrade = strtolower($dbGrade);

}

$result = mysqli_query(
    $conn,
    "SELECT *
     FROM subjects
     WHERE LOWER(course_type)=LOWER('$type')
     AND LOWER(grade)=LOWER('$dbGrade')"
);

$subjects = [];

while($row = mysqli_fetch_assoc($result)){
    $subjects[] = $row;
}

echo json_encode($subjects);