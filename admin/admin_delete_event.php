<?php
// Admin calendar: delete any event
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_delete($conn, 'admin');