<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];

$stmt = $conn->prepare(
    "SELECT id, first_name, course_title, payment_id, payment_status, price, gst, total, payment_type
     FROM students
     WHERE branch_id = ?
     ORDER BY created_at DESC"
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
  <title>Financial Report</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h2 class="mb-4">Financial Report</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>First Name</th>
              <th>Course Title</th>
              <th>Payment ID</th>
              <th>Payment Status</th>
              <th>Price</th>
              <th>GST</th>
              <th>Total</th>
              <th>Payment Type</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= e($row['id']) ?></td>
                <td><?= e($row['first_name']) ?></td>
                <td><?= e($row['course_title']) ?></td>
                <td><?= e($row['payment_id']) ?></td>
                <td><?= e($row['payment_status']) ?></td>
                <td><?= e($row['price']) ?></td>
                <td><?= e($row['gst']) ?></td>
                <td><?= e($row['total']) ?></td>
                <td><?= e($row['payment_type']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">No data found for this branch.</div>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
