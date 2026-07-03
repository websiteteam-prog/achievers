<?php
include 'db_config.php';

$student_id = $_POST['student_id'];
$subject_id = $_POST['subject_id'];
$chapter_title = $_POST['chapter_title'];

$sql = "INSERT INTO assigned_chapters (student_id, subject_id, chapter_title, assign_date)
        VALUES ('$student_id', '$subject_id', '$chapter_title', CURDATE())";

if (mysqli_query($conn, $sql)) {
echo "<script>
alert('Chapter assigned successfully!');
window.location.href='teacher_dashboard.php?page=assign_chapter.php';
</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
