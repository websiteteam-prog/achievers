<?php
// Admin calendar: every event (JSON for FullCalendar)
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_fetch($conn, 'admin');