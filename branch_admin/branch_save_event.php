<?php
// Branch calendar: add / edit a branch event (whole branch or one teacher)
require_once __DIR__ . '/auth.php';          // opens the separate branch-admin session
require_once __DIR__ . '/../calendar_lib.php';
cal_handle_save($conn, 'branch');