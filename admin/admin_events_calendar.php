<?php
// Admin calendar (loaded inside admin/dashboard.php)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_logged_in']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    // Loaded by AJAX inside the dashboard -> send the whole browser to the login page
    echo "<script>window.location.href = 'login.php';</script>";
    exit;
}

$CAL_CTX    = 'admin';
$CAL_FETCH  = 'admin_fetch_events.php';
$CAL_SAVE   = 'admin_save_event.php';
$CAL_DELETE = 'admin_delete_event.php';
$CAL_TITLE  = 'Events Calendar';
include __DIR__ . '/../calendar_widget.php';