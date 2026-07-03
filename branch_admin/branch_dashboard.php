<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$admin_email = $_SESSION['admin_email'];

$stmt = $conn->prepare(
    "SELECT admins.name AS admin_name, branches.branch_name, branches.branch_address
     FROM admins
     JOIN branches ON admins.branch_id = branches.id
     WHERE admins.email = ?"
);
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Branch Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>

    <div class="welcome-card card shadow-sm p-4 mb-4">
      <h2 class="mb-3">Welcome, <?= e($data['admin_name'] ?? 'Admin') ?> &#128075;</h2>
      <p class="mb-1"><strong>Branch Name:</strong> <?= e($data['branch_name'] ?? '—') ?></p>
      <p class="mb-0"><strong>Branch Address:</strong> <?= e($data['branch_address'] ?? '—') ?></p>
    </div>

    <h1>Branch Admin Dashboard</h1>

    <div class="card-box">
      <div class="dashboard-card">
        <h5>Manage Teachers &amp; Students</h5>
        <div class="row g-3">
          <div class="col-6">
            <h6>Students</h6>
            <img src="../images/vector-student-1.png" alt="Students" />
            <a href="branch_students.php" class="btn btn-primary btn-sm">View Students</a>
          </div>
          <div class="col-6">
            <h6>Teachers</h6>
            <img src="../images/teacher-2-1.png" alt="Teachers" />
            <a href="branch_teacher.php" class="btn btn-primary btn-sm">View Teachers</a>
          </div>
        </div>
      </div>

      <div class="dashboard-card">
        <h5>Suggest Course Changes</h5>
        <textarea class="form-control" id="suggestion" placeholder="Enter your suggestions..."></textarea>
        <select class="form-select" id="subject">
          <option value="">Choose Subject</option>
          <option value="1">English</option>
          <option value="2">Science</option>
          <option value="3">Maths</option>
          <option value="4">History</option>
        </select>
        <button class="btn btn-warning" id="course_suggest">Submit</button>
      </div>

      <div class="dashboard-card">
        <h5>Branch Calendar</h5>
        <input type="date" class="form-control" id="event_date" />
        <input type="text" class="form-control" placeholder="Event Title" id="event_title" />
        <input type="text" class="form-control" placeholder="Event Agenda" id="event_agenda" />
        <button type="button" class="btn btn-info text-white" id="add_event">Add Event</button>
      </div>

      <div class="dashboard-card">
        <h5>Branch Financials</h5>
        <img src="../images/financial_icon.png" alt="Financials" class="dashboard-img" />
        <button class="btn btn-primary" onclick="window.location.href='financial_report.php'">View Report</button>
      </div>

      <div class="dashboard-card">
        <h5>Parent Meetings</h5>
        <input type="date" class="form-control" id="meeting_date" />
        <input type="number" class="form-control" placeholder="Enter Student ID" id="student_id" />
        <input type="text" class="form-control" placeholder="Agenda of Meeting" id="agenda" />
        <button type="button" class="btn btn-secondary" id="add_meeting">Schedule</button>
      </div>

      <div class="dashboard-card">
        <h5>Attendance</h5>
        <p class="text-muted mb-0">View grade &amp; subject wise attendance records.</p>
        <button class="btn btn-outline-success" onclick="window.location.href='click_attendance.php'">Track Attendance</button>
      </div>

      <div class="dashboard-card">
        <h5>Enrollment</h5>
        <p class="text-muted mb-0">Add new students &amp; teachers to this branch.</p>
        <button class="btn btn-dark" onclick="window.location.href='branch_manage_users.php'">Open Form</button>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="branch_dashboard.js"></script>
</body>
</html>
