<?php
session_start();
include 'db_config.php';

$student_id   = (int)$_POST['student_id'];
$subject_id   = (int)$_POST['subject_id'];
$chapter_title = trim($_POST['chapter_title']);

// Check if already assigned
$check = $conn->prepare("
    SELECT id
    FROM assigned_chapters
    WHERE student_id = ?
      AND subject_id = ?
      AND chapter_title = ?
");

$check->bind_param("iis", $student_id, $subject_id, $chapter_title);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {

    echo "<script>
    alert('This chapter has already been assigned to this student.');
    window.location.href='teacher_dashboard.php?page=assign_chapter.php';
    </script>";
    exit;
}

// Insert new assignment
$stmt = $conn->prepare("
    INSERT INTO assigned_chapters
    (student_id, subject_id, chapter_title, assign_date)
    VALUES (?, ?, ?, CURDATE())
");

$stmt->bind_param("iis", $student_id, $subject_id, $chapter_title);

if ($stmt->execute()) {

    echo "<script>
    alert('Chapter assigned successfully!');
    window.location.href='teacher_dashboard.php?page=assign_chapter.php';
    </script>";

} else {

    echo "<script>
    alert('Something went wrong.');
    window.location.href='teacher_dashboard.php?page=assign_chapter.php';
    </script>";

}

$stmt->close();
$check->close();
$conn->close();
?>