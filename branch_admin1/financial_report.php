<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

session_start();

include '../db_config.php';
include 'branch_dashboard_sidebar.php';

$branch_id = $_SESSION['branch_id'] ?? null;

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] != 'branch_admin'){
    header("Location: ../login.php");
    exit();
}

// Fetch all students data for financial report
$stmt = $conn->prepare("
    SELECT id, first_name, course_title, 
           payment_id, payment_status, price, gst, total, payment_type
    FROM students
    WHERE branch_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Financial Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="branch.css"/>
</head>

<body>
<section class="main">
    <div class="container mt-4">
        <h2 class="mb-4">Financial Report</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
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
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['first_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['course_title'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['payment_id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['payment_status'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['price'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['gst'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['total'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['payment_type'] ?? '') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No data found for this branch.</div>
        <?php endif; ?>
    </div>
</section>
</body>
</html>
