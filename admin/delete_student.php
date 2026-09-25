<?php
session_start();
include '../db_config.php';

if (!isset($_POST['id'])) {
    die("Invalid Request");
}

$student_id = (int) $_POST['id'];
$mode       = $_POST['mode'] ?? 'cancel';           // 'cancel' | 'restore'
$redirect   = "dashboard.php?page=manage_students.php";

mysqli_report(MYSQLI_REPORT_OFF);   // handle errors manually

/* ---------------- RESTORE (bring a cancelled student back) ---------------- */
if ($mode === 'restore') {

    mysqli_begin_transaction($conn);

    $ok = mysqli_query($conn, "UPDATE students        SET status = '1' WHERE id = $student_id");
           mysqli_query($conn, "UPDATE student_subjects SET status = '1' WHERE student_id = $student_id");

    if ($ok) {
        mysqli_commit($conn);
        echo "<script>alert('Student restored.');window.location.href='$redirect';</script>";
    } else {
        mysqli_rollback($conn);
        echo "<script>alert('Unable to restore student.');window.history.back();</script>";
    }
    exit;
}

/* ---------------- SOFT CANCEL (default) ---------------- */
mysqli_begin_transaction($conn);

$ok = mysqli_query($conn, "UPDATE students SET status = '0' WHERE id = $student_id");

mysqli_query($conn, "UPDATE enrollment_inquiries SET status = 'Cancelled' WHERE student_id = $student_id");
mysqli_query($conn, "UPDATE student_plan_history SET status = 'Cancelled', end_date = CURDATE() WHERE student_id = $student_id AND status = 'Active'");
mysqli_query($conn, "UPDATE student_subjects     SET status = '0'          WHERE student_id = $student_id");
mysqli_query($conn, "UPDATE course_enrollments   SET status = 'Cancelled'  WHERE student_id = $student_id");

if ($ok) {
    mysqli_commit($conn);
    echo "<script>alert('Student cancelled successfully.');window.location.href='$redirect';</script>";
} else {
    mysqli_rollback($conn);
    echo "<script>alert('Unable to cancel student.');window.history.back();</script>";
}
?>