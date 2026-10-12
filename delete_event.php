<?php
// Teacher calendar: delete the teacher's own entry
require_once __DIR__ . '/calendar_lib.php';
cal_handle_delete($conn, 'teacher');