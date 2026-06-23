<?php
session_start();
include 'db_config.php';

$teacher_id = (int)($_SESSION['teacher_id'] ?? 0);

// All subject+grade combinations this teacher teaches
$teacher_subjects = [];
$stmt = $conn->prepare("SELECT s.id AS subject_id, s.subject_name, s.grade
                         FROM teacher_subjects ts
                         JOIN subjects s ON ts.subject_id = s.id
                         WHERE ts.teacher_id = ?
                         ORDER BY s.grade ASC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $teacher_subjects[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Chapters</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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
        <select id="gradeSelect" class="form-select" required>
          <option value="">-- Select Grade --</option>
          <?php foreach ($teacher_subjects as $ts): ?>
            <option value="<?= (int)$ts['subject_id'] ?>">
              Grade <?= htmlspecialchars($ts['grade']) ?> &mdash; <?= htmlspecialchars($ts['subject_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (empty($teacher_subjects)): ?>
          <div class="hint text-danger">No subjects/grades are linked to your account yet.</div>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label>Select Student</label>
        <select name="student_id" id="studentSelect" class="form-select" required disabled>
          <option value="">-- Select Grade First --</option>
        </select>
      </div>

      <div class="mb-3">
        <label>Select Chapter</label>
        <select name="chapter_title" id="chapterSelect" class="form-select" required disabled>
          <option value="">-- Select Grade First --</option>
        </select>
        <div class="hint">Only chapters already created for this subject are listed.</div>
      </div>

      <input type="hidden" name="subject_id" id="subjectIdInput" value="">

      <button type="submit" class="btn-assign mt-2">
        <i class="bi bi-check2-circle me-1"></i> Assign Chapter
      </button>
    </form>
  </div>
</div>

<script>
const gradeSelect = document.getElementById('gradeSelect');
const studentSelect = document.getElementById('studentSelect');
const chapterSelect = document.getElementById('chapterSelect');
const subjectIdInput = document.getElementById('subjectIdInput');

gradeSelect.addEventListener('change', function () {
    const subjectId = this.value;
    subjectIdInput.value = subjectId;

    if (!subjectId) {
        studentSelect.disabled = true;
        chapterSelect.disabled = true;
        studentSelect.innerHTML = '<option value="">-- Select Grade First --</option>';
        chapterSelect.innerHTML = '<option value="">-- Select Grade First --</option>';
        return;
    }

    studentSelect.disabled = false;
    chapterSelect.disabled = false;
    studentSelect.innerHTML = '<option value="">Loading...</option>';
    chapterSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('assign_chapter_get_students.php?subject_id=' + encodeURIComponent(subjectId))
        .then(r => r.text())
        .then(html => studentSelect.innerHTML = html);

    fetch('assign_chapter_get_chapters.php?subject_id=' + encodeURIComponent(subjectId))
        .then(r => r.text())
        .then(html => chapterSelect.innerHTML = html);
});
</script>

</body>
</html>
