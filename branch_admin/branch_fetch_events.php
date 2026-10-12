<?php
// Branch calendar: admin events + this branch's events + its teachers' entries
require_once __DIR__ . '/auth.php';          // opens the separate branch-admin session
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_fetch($conn, 'branch');