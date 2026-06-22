<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h2 class="mb-4">Attendance</h2>

    <div class="card-box">
      <div class="dashboard-card">
        <h5>Current Month Attendance</h5>
        <form method="post" action="view_attendance.php" class="d-flex gap-2 flex-wrap">
          <select class="form-select" name="grade" required>
            <?php for ($g = 1; $g <= 10; $g++): ?>
              <option value="<?= $g ?>">Grade <?= $g ?></option>
            <?php endfor; ?>
          </select>
          <select class="form-select" name="subject_id" required>
            <option value="1">English</option>
            <option value="2">Science</option>
            <option value="3">Maths</option>
            <option value="4">History</option>
          </select>
          <button type="submit" class="btn btn-primary">View Attendance</button>
        </form>
      </div>

      <div class="dashboard-card">
        <h5>Previous Month's Attendance</h5>
        <form method="post" action="month_attendance.php" class="d-flex gap-2 flex-wrap">
          <select class="form-select" name="grade" required>
            <?php for ($g = 1; $g <= 10; $g++): ?>
              <option value="<?= $g ?>">Grade <?= $g ?></option>
            <?php endfor; ?>
          </select>
          <select class="form-select" name="subject_id" required>
            <option value="1">English</option>
            <option value="2">Science</option>
            <option value="3">Maths</option>
            <option value="4">History</option>
          </select>
          <select class="form-select" name="month" required>
            <?php
            $months = ['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'];
            foreach ($months as $num => $label) {
                echo '<option value="' . $num . '">' . $label . '</option>';
            }
            ?>
          </select>
          <button type="submit" class="btn btn-primary">View Attendance</button>
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
