<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];

$stmt = $conn->prepare(
    "SELECT students.first_name, students.parent_name, students.parent_contact,
            meetings.agenda, meetings.date
     FROM meetings
     JOIN students ON students.id = meetings.students_id
     WHERE students.branch_id = ?
       AND (meetings.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE() OR meetings.date > CURDATE())
     ORDER BY meetings.date ASC"
);
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Parent Meetings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h2 class="mb-4">Parent Meetings</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>Student's Name</th>
              <th>Parent's Name</th>
              <th>Parent's Contact</th>
              <th>Agenda</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= e($row['first_name']) ?></td>
                <td><?= e($row['parent_name']) ?></td>
                <td><?= e($row['parent_contact']) ?></td>
                <td><?= e($row['agenda']) ?></td>
                <td><?= e($row['date']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No upcoming or recent parent meetings.</div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
