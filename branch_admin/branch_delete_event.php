<?php
// Branch calendar: delete this branch's own events
require_once __DIR__ . '/auth.php';          // opens the separate branch-admin session
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_delete($conn, 'branch');