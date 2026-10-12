<?php
// Teacher calendar: events this teacher may see (JSON for FullCalendar)
require_once __DIR__ . '/calendar_lib.php';
cal_handle_fetch($conn, 'teacher');