<?php
include 'db_config.php';

$student_id = (int)$_POST['student_id'];
$subject_id = (int)$_POST['subject_id'];
$chapter_title = $_POST['chapter_title'];

$stmt = $conn->prepare("INSERT INTO assigned_chapters (student_id, subject_id, chapter_title, assign_date)
        VALUES (?, ?, ?, CURDATE())");
$stmt->bind_param("iis", $student_id, $subject_id, $chapter_title);

if ($stmt->execute()) {
    echo "<script>alert('Chapter assigned successfully!'); window.location.href='assign_chapter.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
