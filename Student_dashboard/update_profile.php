<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int) $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $first_name = mysqli_real_escape_string($conn, trim($_POST['first_name'] ?? ''));
    $last_name  = mysqli_real_escape_string($conn, trim($_POST['last_name'] ?? ''));
    $phone      = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $dob        = trim($_POST['dob'] ?? '');
    $address    = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));

    $dob_sql = 'NULL';
    if ($dob !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $dob_sql = "'" . mysqli_real_escape_string($conn, $dob) . "'";
    }

    $sql = "UPDATE students SET
                first_name = '$first_name',
                last_name  = '$last_name',
                phone      = '$phone',
                dob        = $dob_sql,
                address    = '$address'
            WHERE id = $student_id";

    mysqli_query($conn, $sql);
}

header("Location: student_dashboard.php");
exit();