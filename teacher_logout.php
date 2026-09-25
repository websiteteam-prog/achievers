<?php
session_start();
include 'db_config.php';

if (isset($_SESSION['teacher_id'])) {

    $teacher_id = (int)$_SESSION['teacher_id'];

    // Make teacher offline immediately
    mysqli_query($conn, "
        UPDATE teachers
        SET last_activity = NULL
        WHERE id = $teacher_id
    ");
}

if (isset($_SESSION['activity_log_id'])) {

    $activity_log_id = (int)$_SESSION['activity_log_id'];
    $logout_time = date("Y-m-d H:i:s");

    mysqli_query($conn, "
        UPDATE teacher_activity_logs
        SET logout_time = '$logout_time',
            duration = TIMEDIFF('$logout_time', login_time)
        WHERE id = $activity_log_id
    ");
}

session_unset();
session_destroy();

header("Location: teacher_login.php");
exit();