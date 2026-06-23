<?php
session_start();
include 'db_config.php';

// Get teacher_id from session
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Fetch teacher's subject
$subject_q = mysqli_query($conn, "SELECT subject_id FROM teacher_subjects WHERE teacher_id = '$teacher_id'");
$subject_row = mysqli_fetch_assoc($subject_q);
$subject_id = $subject_row['subject_id'] ?? null;

// Fetch students of that subject, with their average score % on this teacher's
// submitted assessments (used as a learning-progress signal) and how many
// chapters have already been assigned to them.
$students_q = mysqli_query($conn, "
    SELECT
        s.id,
        s.first_name,
        ROUND(AVG(CASE WHEN aa.submitted_at IS NOT NULL AND aa.total_questions > 0
                       THEN (aa.score / (aa.total_questions * 10)) * 100 END)) AS avg_score,
        COUNT(DISTINCT ac.id) AS chapters_assigned
    FROM students s
    JOIN student_subjects ss ON s.id = ss.student_id
    LEFT JOIN assessment_assignments aa ON aa.student_id = s.id
    LEFT JOIN assessments a ON a.id = aa.assessment_id AND a.teacher_id = '$teacher_id'
    LEFT JOIN assigned_chapters ac ON ac.student_id = s.id AND ac.subject_id = '$subject_id'
    WHERE ss.subject_id = '$subject_id'
    GROUP BY s.id, s.first_name
");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Chapters</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h3>📘 Assign Chapter</h3>

  <table class="table table-bordered table-sm mt-4">
    <thead class="table-light">
      <tr>
        <th>Student</th>
        <th>Avg. Assessment Score</th>
        <th>Chapters Assigned (this subject)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $students = [];
      while ($row = mysqli_fetch_assoc($students_q)) {
        $students[] = $row;
        $score = $row['avg_score'];
        $score_label = $score === null ? '<span class="text-muted">No assessments yet</span>' : "{$score}%";
      ?>
        <tr>
          <td><?= htmlspecialchars($row['first_name']) ?></td>
          <td><?= $score_label ?></td>
          <td><?= (int)$row['chapters_assigned'] ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <form action="assign_chapter_submit.php" method="POST" class="mt-4">
    <div class="mb-3">
      <label>Select Student</label>
      <select name="student_id" class="form-select" required>
        <option value="">-- Select --</option>
        <?php foreach ($students as $row):
          $score = $row['avg_score'];
          $label = $row['first_name'] . ($score !== null ? " ({$score}% avg)" : " (no assessments yet)");
        ?>
          <option value="<?= (int)$row['id'] ?>"><?= htmlspecialchars($label) ?></option>
        <?php endforeach; ?>
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
