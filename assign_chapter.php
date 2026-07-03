<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php');
    exit();
}

$teacher_id = (int)$_SESSION['teacher_id'];

// Grades this teacher teaches (a teacher can teach the same subject across multiple grades)
$grades = [];
$stmt = $conn->prepare("SELECT DISTINCT s.grade
                         FROM teacher_subjects ts
                         JOIN subjects s ON ts.subject_id = s.id
                         WHERE ts.teacher_id = ? AND s.grade IS NOT NULL AND s.grade != ''
                         ORDER BY CAST(s.grade AS UNSIGNED)");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $grades[] = $row['grade'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Chapters</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    body { background: #f4f6fb; font-family: 'Segoe UI', Arial, sans-serif; }
    .assign-card {
        max-width: 560px;
        margin: 40px auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .assign-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 24px 28px;
    }
    .assign-header h3 { margin: 0; font-weight: 600; }
    .assign-body { padding: 28px; }
    .assign-body label { font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 6px; }
    .form-select, .form-control { border-radius: 10px; padding: 10px 14px; }
    .btn-assign {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border: none;
        border-radius: 10px;
        padding: 10px 0;
        width: 100%;
        font-weight: 600;
        color: #fff;
    }
    .btn-assign:hover { opacity: 0.9; color: #fff; }
    .hint { font-size: 12px; color: #9ca3af; margin-top: 4px; }
  </style>
</head>
<body>

<div class="assign-card">
  <div class="assign-header">
    <h3><i class="bi bi-journal-bookmark-fill me-2"></i>Assign Chapter</h3>
  </div>

  <div class="assign-body">
    <form action="assign_chapter_submit.php" method="POST" id="assignForm">

      <div class="mb-3">
        <label>Grade</label>
        <select id="grade" class="form-select" required>
          <option value="">-- Select Grade --</option>
          <?php foreach ($grades as $g): ?>
            <option value="<?= htmlspecialchars($g) ?>">Grade <?= htmlspecialchars($g) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($grades)): ?>
          <div class="hint text-danger">No subjects/grades are linked to your account yet.</div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label>Subject</label>
        <select id="subject_id" name="subject_id" class="form-select" required disabled>
          <option value="">-- Select Grade First --</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Select Student</label>
        <select name="student_id" id="studentSelect" class="form-select" required disabled>
          <option value="">-- Select Subject First --</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Select Chapter</label>
        <select name="chapter_title" id="chapterSelect" class="form-select" required disabled>
          <option value="">-- Select Subject First --</option>
        </select>
        <div class="hint">Only chapters already created for this subject are listed.</div>
      </div>

      <button type="submit" class="btn-assign mt-2">
        <i class="bi bi-check2-circle me-1"></i> Assign Chapter
      </button>
    </form>
  </div>
</div>

<script>
$(document).ready(function () {

    // Grade -> Subjects (scoped to this teacher)
    $("#grade").change(function () {
        const grade = $(this).val();

        $("#subject_id").prop('disabled', true).html('<option value="">-- Select Grade First --</option>');
        resetDependent();

        if (!grade) return;

        $("#subject_id").html('<option value="">Loading...</option>');

        $.get("assign_chapter_get_subjects.php", { grade: grade }, function (data) {
            $("#subject_id").html('<option value="">-- Select Subject --</option>' + data).prop('disabled', false);
        });
    });

    // Subject -> Students + Chapters
    $("#subject_id").change(function () {
        const subjectId = $(this).val();

        resetDependent();

        if (!subjectId) return;

        $("#studentSelect, #chapterSelect").prop('disabled', false).html('<option value="">Loading...</option>');

        $.get("assign_chapter_get_students.php", { subject_id: subjectId }, function (data) {
            $("#studentSelect").html(data);
        });

        $.get("assign_chapter_get_chapters.php", { subject_id: subjectId }, function (data) {
            $("#chapterSelect").html(data);
        });
    });

    function resetDependent() {
        $("#studentSelect").prop('disabled', true).html('<option value="">-- Select Subject First --</option>');
        $("#chapterSelect").prop('disabled', true).html('<option value="">-- Select Subject First --</option>');
    }
});
</script>

</body>
</html>
