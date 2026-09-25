<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include '../db_config.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_logged_in'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

/*
|--------------------------------------------------------------------------
| METHOD CHECK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?page=admin_course_suggestions.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| VALIDATE INPUT
|--------------------------------------------------------------------------
*/

$suggestion_id = (int)($_POST['suggestion_id'] ?? 0);
$action        = $_POST['action'] ?? '';
$status_filter = $_POST['status_filter'] ?? 'pending';

$allowed_status_filter = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($status_filter, $allowed_status_filter)) {
    $status_filter = 'pending';
}

$redirect = 'dashboard.php?page=admin_course_suggestions.php&status=' . urlencode($status_filter);

if ($suggestion_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['suggestion_admin_error'] = "Invalid request.";
    header("Location: $redirect");
    exit;
}

$new_status = $action === 'approve' ? 'approved' : 'rejected';

/*
|--------------------------------------------------------------------------
| CONFIRM SUGGESTION EXISTS AND IS PENDING
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id, status FROM course_suggestions WHERE id = ? LIMIT 1";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $suggestion_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $_SESSION['suggestion_admin_error'] = "Suggestion not found.";
    $check_stmt->close();
    header("Location: $redirect");
    exit;
}

$existing = $check_result->fetch_assoc();
$check_stmt->close();

if (strtolower($existing['status']) !== 'pending') {
    $_SESSION['suggestion_admin_error'] = "This suggestion has already been reviewed.";
    header("Location: $redirect");
    exit;
}

/*
|--------------------------------------------------------------------------
| UPDATE STATUS
|--------------------------------------------------------------------------
| If you later add columns like reviewed_by / reviewed_at to
| course_suggestions, bind them in here too.
*/

$update_sql = "
UPDATE course_suggestions
SET status = ?
WHERE id = ?
";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $new_status, $suggestion_id);

if ($update_stmt->execute()) {
    $_SESSION['suggestion_admin_success'] = $action === 'approve'
        ? "Suggestion approved successfully."
        : "Suggestion rejected.";
} else {
    $_SESSION['suggestion_admin_error'] = "Something went wrong. Please try again.";
}

$update_stmt->close();

header("Location: $redirect");
exit;
