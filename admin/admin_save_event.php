<?php
// Admin calendar: add / edit an event (everyone, one branch or one teacher)
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_save($conn, 'admin');