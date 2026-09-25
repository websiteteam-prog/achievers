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

  <style>
    .students-container{
        padding:5px;
    }

    .students-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:25px;
        flex-wrap:wrap;
        gap:15px;
    }

    .header-left{
        display:flex;
        align-items:center;
        gap:15px;
    }

    .header-icon{
        width:55px;
        height:55px;
        border-radius:16px;
        background:linear-gradient(135deg,#1e3c72,#2a5298);
        display:flex;
        align-items:center;
        justify-content:center;
        color:#fff;
        font-size:24px;
        box-shadow:0 8px 20px rgba(30,60,114,.25);
    }

    .header-title{
        margin:0;
        font-size:24px;
        font-weight:700;
        color:#1e3c72;
        letter-spacing:.2px;
    }

    .header-subtitle{
        margin:0;
        font-size:14px;
        color:#6b7280;
    }

    .header-right{
        display:flex;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .subject-badge{
        background:linear-gradient(135deg,#1e3c72,#2a5298);
        color:#fff;
        padding:8px 18px;
        border-radius:40px;
        font-weight:600;
        font-size:13px;
        box-shadow:0 5px 15px rgba(0,0,0,.12);
    }

    .card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    animation:fadeInUp .4s ease;
    }

    @keyframes fadeInUp{
    from{opacity:0;transform:translateY(10px);}
    to{opacity:1;transform:translateY(0);}
    }

    .form-card{
        padding:28px;
    }

    .form-card label{
        font-weight:600;
        font-size:13.5px;
        color:#374151;
        margin-bottom:6px;
    }

    .form-card .form-select,
    .form-card .form-control{
        border-radius:10px;
        border:1px solid #e2e8f0;
        padding:10px 14px;
        font-size:14.5px;
    }

    .form-card .form-select:focus,
    .form-card .form-control:focus{
        border-color:#2a5298;
        box-shadow:0 0 0 .2rem rgba(42,82,152,.15);
    }

    .btn-submit-suggestion{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      background:linear-gradient(135deg,#1e3c72,#2a5298);
      color:#fff;
      border:none;
      padding:11px 26px;
      border-radius:40px;
      font-weight:600;
      font-size:14px;
      transition:.25s ease;
  }

    .btn-submit-suggestion:hover{
        transform:translateY(-2px);
        box-shadow:0 12px 26px rgba(30,60,114,.32);
        color:#fff;
    }

    .hint{
        font-size:12px;
        color:#9ca3af;
        margin-top:4px;
    }

    @media(max-width:768px){

    .students-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .header-right{
        width:100%;
        justify-content:space-between;
    }

    .header-title{
        font-size:22px;
    }

    }
  </style>


<div class="students-container">

    <div class="students-header">

        <div class="header-left">

            <div class="header-icon">
                  <i class="bi bi-book"></i>
            </div>

            <div>
                <h2 class="header-title">Assign Chapter</h2>
                <p class="header-subtitle">
                    Assign chapters to students based on grade and subject
                </p>
            </div>

        </div>

        <div class="header-right">

            <span class="subject-badge">
              <i class="bi bi-journal-bookmark-fill me-1"></i>
            Grades : <?= count($grades) ?>
          </span>

        </div>

    </div>

    <div class="card">

        <div class="form-card">

            <form action="assign_chapter_submit.php" method="POST" id="assignForm">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Grade</label>
                        <select id="grade" class="form-select" required>
                            <option value="">-- Select Grade --</option>

                            <?php foreach ($grades as $g): ?>

                                <option value="<?= htmlspecialchars($g) ?>">
                                    Grade <?= htmlspecialchars($g) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?php if (empty($grades)): ?>

                            <div class="hint text-danger">
                                No subjects/grades are linked to your account yet.
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label>Subject</label>

                        <select
                            id="subject_id"
                            name="subject_id"
                            class="form-select"
                            required
                            disabled>

                            <option value="">-- Select Grade First --</option>

                        </select>

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label>Select Student</label>

                        <select
                            name="student_id"
                            id="studentSelect"
                            class="form-select"
                            required
                            disabled>

                            <option value="">-- Select Subject First --</option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label>Select Chapter</label>

                        <select
                            name="chapter_title"
                            id="chapterSelect"
                            class="form-select"
                            required
                            disabled>

                            <option value="">-- Select Subject First --</option>

                        </select>

                        <div class="hint">
                            Only chapters already created for this subject are listed.
                        </div>

                    </div>

                </div>
              <div class="row mt-4">
              <div class="col-12">

                  <button type="submit" class="btn-submit-suggestion">
                      <i class="bi bi-send-fill"></i>
                      Assign Chapter
                  </button>

              </div>
          </div>
            </form>

        </div>

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
