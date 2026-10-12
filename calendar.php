<?php
// Teacher calendar (loaded inside teacher_dashboard.php)
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit();
}

$CAL_CTX    = 'teacher';
$CAL_FETCH  = 'fetch_events.php';
$CAL_SAVE   = 'save_event.php';
$CAL_DELETE = 'delete_event.php';
$CAL_TITLE  = 'My Calendar & Events';
include __DIR__ . '/calendar_widget.php';