<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];
$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

$subjectNames = [1 => 'English', 2 => 'Science', 3 => 'Maths', 4 => 'History'];
$students = [];
$dates = [];
$attendance = []; // [student_id][date] = status
$grade = 0;
$subject = 0;

if ($submitted) {
    $grade   = (int) ($_POST['grade'] ?? 0);
    $subject = (int) ($_POST['subject_id'] ?? 0);

    // Students of this grade in this branch
    $s1 = $conn->prepare("SELECT id AS student_id, first_name FROM students WHERE grade = ? AND branch_id = ?");
    $s1->bind_param("ii", $grade, $branch_id);
    $s1->execute();
    $res = $s1->get_result();
    while ($r = $res->fetch_assoc()) {
        $students[] = $r;
    }

    // Distinct attendance dates this month for the subject
    $s2 = $conn->prepare("SELECT DISTINCT date FROM attendance_records WHERE subject_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE()) ORDER BY date ASC");
    $s2->bind_param("i", $subject);
    $s2->execute();
    $dres = $s2->get_result();
    while ($d = $dres->fetch_assoc()) {
        $dates[] = $d['date'];
    }

    // All records for the subject this month in one query (no N+1)
    $s3 = $conn->prepare("SELECT student_id, date, status FROM attendance_records WHERE subject_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())");
    $s3->bind_param("i", $subject);
    $s3->execute();
    $ares = $s3->get_result();
    while ($a = $ares->fetch_assoc()) {
        $attendance[$a['student_id']][$a['date']] = strtolower($a['status']);
    }
}

function status_badge($status): string
{
    switch ($status) {
        case 'present': return '<span class="badge bg-success">Present</span>';
        case 'absent':  return '<span class="badge bg-danger">Absent</span>';
        case 'late':    return '<span class="badge bg-warning text-dark">Late</span>';
        default:        return '<span class="badge bg-light text-dark">--</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Grade Wise Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
  <style>
    .attendance-card { background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); margin: 0 auto; max-width: 100%; }
    .attendance-header { background: linear-gradient(135deg, #4facfe, #00f2fe); color: #fff; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
    .attendance-header h4 { margin: 0; font-weight: 600; }
  </style>
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php if (!$submitted): ?>
      <div class="alert alert-info">Please choose a grade and subject from the <a href="click_attendance.php">Attendance</a> page.</div>
    <?php else: ?>
      <div class="attendance-card">
        <div class="attendance-header">
          <h4>&#128202; Attendance &mdash; Grade <?= e($grade) ?> | <?= e($subjectNames[$subject] ?? 'Subject') ?></h4>
        </div>
        <?php if (empty($students)): ?>
          <div class="alert alert-warning mb-0">No students found for this grade in your branch.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
              <thead class="table-light">
                <tr>
                  <th>Student's Name</th>
                  <?php foreach ($dates as $date): ?>
                    <th><?= e(date("d M", strtotime($date))) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $student): ?>
                  <tr>
                    <td class="fw-medium text-start ps-3"><?= e($student['first_name']) ?></td>
                    <?php foreach ($dates as $date): ?>
                      <td><?= status_badge($attendance[$student['student_id']][$date] ?? null) ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
