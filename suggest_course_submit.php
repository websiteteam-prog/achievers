<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

session_start();

include 'db_config.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if(!isset($_SESSION['teacher_id'])){
    echo "<script>window.location.href='teacher_login.php';</script>";
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: teacher_dashboard.php?page=suggest_course_changes.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDATE INPUT
|--------------------------------------------------------------------------
*/

$subject_id = (int)$_POST['subject_id'];

$get=mysqli_query($conn,"
SELECT grade
FROM subjects
WHERE id='$subject_id'
");

$row=mysqli_fetch_assoc($get);

$grade_id=$row['grade'];
$suggestion = trim($_POST['suggestion'] ?? '');

if ($subject_id <= 0 || $grade_id <= 0 || $suggestion === '') {
    $_SESSION['suggestion_error'] = "Please fill in all fields before submitting.";
    header('Location: teacher_dashboard.php?page=suggest_course_changes.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| VERIFY THE SUBJECT ACTUALLY BELONGS TO THIS TEACHER (security check)
|--------------------------------------------------------------------------
*/

$check_sql = "
SELECT id
FROM teacher_subjects
WHERE teacher_id = ?
AND subject_id = ?
LIMIT 1
";

$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $teacher_id, $subject_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    $_SESSION['suggestion_error'] = "Invalid subject selected.";
    $check_stmt->close();
    header('Location: teacher_dashboard.php?page=suggest_course_changes.php');
    exit;
}
$check_stmt->close();

/*
|--------------------------------------------------------------------------
| INSERT SUGGESTION
|--------------------------------------------------------------------------
*/

$insert_sql = "
INSERT INTO course_suggestions
(teacher_id, subject_id, grade_id, suggestion, status, created_at)
VALUES
(?, ?, ?, ?, 'pending', NOW())
";

$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iiis", $teacher_id, $subject_id, $grade_id, $suggestion);

if ($insert_stmt->execute()) {
    $_SESSION['suggestion_success'] = "Your course suggestion has been submitted successfully.";
} else {
    $_SESSION['suggestion_error'] = "Something went wrong. Please try again.";
}

$insert_stmt->close();

header('Location: teacher_dashboard.php?page=suggest_course_changes.php');
exit;