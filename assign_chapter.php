<?php
session_start();
include 'db_config.php';

// Get teacher_id from session
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Fetch teacher's subject
$subject_q = mysqli_query($conn, "SELECT subject_id FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$subject_row = mysqli_fetch_assoc($subject_q);
$subject_id = $subject_row['subject_id'] ?? null;

// Fetch students of that subject
$students_q = mysqli_query($conn, "SELECT s.id, s.first_name FROM students s
JOIN student_subjects ss ON s.id = ss.student_id
WHERE ss.subject_id = '$subject_id'");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Chapters</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h3>📘 Assign Chapter</h3>
  <form action="assign_chapter_submit.php" method="POST" class="mt-4">
    <div class="mb-3">
      <label>Select Student</label>
      <select name="student_id" class="form-select" required>
        <option value="">-- Select --</option>
        <?php while ($row = mysqli_fetch_assoc($students_q)) {
          echo "<option value='{$row['id']}'>{$row['first_name']}</option>";
        } ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Chapter Title</label>
      <input type="text" name="chapter_title" class="form-control" required>
    </div>
    <input type="hidden" name="subject_id" value="<?= $subject_id ?>">
    <button type="submit" class="btn btn-primary">Assign Chapter</button>
  </form>
</body>
</html>
