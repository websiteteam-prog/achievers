<?php
include "../../../db_config.php";

/* Buttons ?id= (GET) se call karte hain — POST nahi.
   Ye id enrollment_inquiries.id hai, usse student_id nikaalo. */
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo "<script>alert('Invalid request.');history.back();</script>";
    exit;
}

$row = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT student_id FROM enrollment_inquiries WHERE id='$id' LIMIT 1
"));
$student_id = (int)($row['student_id'] ?? 0);

if ($student_id <= 0) {
    echo "<script>alert('Student not found.');history.back();</script>";
    exit;
}

mysqli_begin_transaction($conn);

try {

    // Cancel enrollment
    mysqli_query($conn, "
        UPDATE enrollment_inquiries SET status='Cancelled'
        WHERE student_id='$student_id'
    ");

    // Close active plan
    mysqli_query($conn, "
        UPDATE student_plan_history
        SET status='Cancelled', end_date=CURDATE()
        WHERE student_id='$student_id' AND status='Active'
    ");

    // Disable student
    mysqli_query($conn, "
        UPDATE students SET status=0 WHERE id='$student_id'
    ");

    // Disable subject assignments
    // mysqli_query($conn, "
    //     UPDATE student_subjects SET status=0 WHERE student_id='$student_id'
    // ");

    // Cancel course enrollments
    // mysqli_query($conn, "
    //     UPDATE course_enrollments SET status='Cancelled' WHERE student_id='$student_id'
    // ");

    mysqli_commit($conn);

    echo "<script>
        alert('Enrollment Cancelled');
        window.location.href='dashboard.php?page=invoice_system/dashboard/invoice_dashboard.php';
    </script>";

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<script>
        alert('Unable to cancel enrollment.');
        window.location.href='dashboard.php?page=invoice_system/dashboard/invoice_dashboard.php';
    </script>";
}
?>