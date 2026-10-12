<?php
// Teacher calendar: add / edit the teacher's own entry
require_once __DIR__ . '/calendar_lib.php';
cal_handle_save($conn, 'teacher');