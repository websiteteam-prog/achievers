<?php
/**
 * Shared calendar rules for Teacher, Branch Admin and Super Admin.
 *
 * WHO SEES WHAT
 *  audience = 'all'     -> created by Super Admin, visible to everyone (all branches)
 *  audience = 'branch'  -> visible to every teacher of branch_id + that branch admin + super admin
 *  audience = 'teacher' -> visible to one teacher (teacher_id) + their branch admin + super admin
 *  audience = 'self'    -> a teacher's own entry, visible to that teacher + their branch admin + super admin
 *
 * WHO CAN EDIT / DELETE
 *  super admin  -> every event
 *  branch admin -> events they (any branch admin of the same branch) created
 *  teacher      -> only their own entries
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';

const CAL_TYPES = [
    'class'      => ['label' => 'Class / Lesson',         'color' => '#2f55a4'],
    'test'       => ['label' => 'Test / Exam',            'color' => '#e02121'],
    'assignment' => ['label' => 'Assignment Due',         'color' => '#f59e0b'],
    'holiday'    => ['label' => 'Holiday',                'color' => '#16a34a'],
    'ptm'        => ['label' => 'Parent-Teacher Meeting', 'color' => '#8b5cf6'],
    'meeting'    => ['label' => 'Staff Meeting',          'color' => '#0f766e'],
    'event'      => ['label' => 'School Event',           'color' => '#ec4899'],
    'other'      => ['label' => 'Other',                  'color' => '#64748b'],
];

/**
 * How a teacher is linked to a branch. teachers.branch may hold the branch NAME
 * (any case / extra spaces) or the branch ID - both are matched.
 */
const CAL_TB_JOIN = "(TRIM(LOWER(t.branch)) = TRIM(LOWER(b.branch_name)) OR TRIM(t.branch) = CAST(b.id AS CHAR))";

const CAL_ROLE_LABELS = [
    'admin'        => 'Admin',
    'branch_admin' => 'Branch Admin',
    'teacher'      => 'Teacher',
];

/**
 * Work out who is calling, for the page context the widget was opened from.
 * $ctx is 'teacher' | 'branch' | 'admin' so a browser logged in as two roles still works.
 * Returns null when the session does not match the context.
 */
function cal_current_user(mysqli $conn, string $ctx): ?array
{
    if ($ctx === 'teacher' && !empty($_SESSION['teacher_id'])) {
        $tid  = (int)$_SESSION['teacher_id'];
        $stmt = $conn->prepare(
            "SELECT t.name, b.id AS branch_id
             FROM teachers t
             LEFT JOIN branches b ON " . CAL_TB_JOIN . "
             WHERE t.id = ?"
        );
        $stmt->bind_param("i", $tid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: [];

        return [
            'role'      => 'teacher',
            'id'        => $tid,
            'name'      => $row['name'] ?? 'Teacher',
            'branch_id' => isset($row['branch_id']) ? (int)$row['branch_id'] : null,
        ];
    }

    if (empty($_SESSION['admin_logged_in'])) {
        return null;
    }

    $role = $_SESSION['role'] ?? '';

    if ($ctx === 'branch' && $role === 'branch_admin') {
        return [
            'role'      => 'branch_admin',
            'id'        => (int)($_SESSION['admin_id'] ?? 0),
            'name'      => $_SESSION['admin_name'] ?? cal_admin_name($conn, (int)($_SESSION['admin_id'] ?? 0), 'Branch Admin'),
            'branch_id' => (int)($_SESSION['branch_id'] ?? 0),
        ];
    }

    if ($ctx === 'admin' && $role === 'super_admin') {
        return [
            'role'      => 'admin',
            'id'        => (int)($_SESSION['admin_id'] ?? 0),
            'name'      => cal_admin_name($conn, (int)($_SESSION['admin_id'] ?? 0), 'Admin'),
            'branch_id' => null,
        ];
    }

    return null;
}

function cal_admin_name(mysqli $conn, int $id, string $fallback): string
{
    if (!$id) {
        return $fallback;
    }
    $stmt = @$conn->prepare("SELECT name FROM admins WHERE id = ?");
    if (!$stmt) {
        return $fallback;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return !empty($row['name']) ? $row['name'] : $fallback;
}

/** SQL condition + params limiting rows to what this user may SEE. */
function cal_visibility_sql(array $u): array
{
    switch ($u['role']) {
        case 'admin':
            return ['1 = 1', '', []];

        case 'branch_admin':
            // admin events + this branch's events + entries of / for this branch's teachers
            return [
                "(e.audience = 'all'
                  OR e.branch_id = ?
                  OR e.teacher_id IN (SELECT t.id FROM teachers t JOIN branches b ON " . CAL_TB_JOIN . " WHERE b.id = ?))",
                'ii',
                [$u['branch_id'], $u['branch_id']],
            ];

        default: // teacher
            return [
                "(e.audience = 'all'
                  OR (e.audience = 'branch' AND e.branch_id = ?)
                  OR e.teacher_id = ?)",
                'ii',
                [(int)$u['branch_id'], $u['id']],
            ];
    }
}

/** Can this user edit / delete the given event row? */
function cal_can_edit(array $u, array $row): bool
{
    if ($u['role'] === 'admin') {
        return true;
    }
    if ($u['role'] === 'branch_admin') {
        return $row['creator_role'] === 'branch_admin' && (int)$row['branch_id'] === (int)$u['branch_id'];
    }
    return $row['creator_role'] === 'teacher' && (int)$row['creator_id'] === (int)$u['id'];
}

function cal_grade_text($from, $to): string
{
    if ($from === null && $to === null) {
        return 'All grades';
    }
    if ($from !== null && $to !== null && (int)$from !== (int)$to) {
        return 'Grade ' . (int)$from . ' – ' . (int)$to;
    }
    return 'Grade ' . (int)($from ?? $to);
}

function cal_audience_text(array $row): string
{
    switch ($row['audience']) {
        case 'all':
            return 'Everyone (all branches)';
        case 'branch':
            return 'All teachers of ' . ($row['branch_name'] ?: 'the branch');
        case 'teacher':
            return 'Only ' . ($row['teacher_name'] ?: 'one teacher');
        default:
            return 'Personal (teacher, branch admin & admin)';
    }
}

/** Convert a DB row into a FullCalendar event object. */
function cal_row_to_event(array $u, array $row): array
{
    $type    = isset(CAL_TYPES[$row['event_type']]) ? $row['event_type'] : 'other';
    $allDay  = (int)$row['all_day'] === 1;
    $toIso   = function ($dt) use ($allDay) {
        if (!$dt) {
            return null;
        }
        return $allDay ? substr($dt, 0, 10) : str_replace(' ', 'T', $dt);
    };

    $end = $toIso($row['end_datetime']);
    if ($allDay && $end) {
        // FullCalendar treats an all-day end date as exclusive -> add one day
        $end = date('Y-m-d', strtotime($end . ' +1 day'));
    }

    return [
        'id'              => (int)$row['id'],
        'title'           => $row['title'],
        'start'           => $toIso($row['event_datetime']),
        'end'             => $end,
        'allDay'          => $allDay,
        'backgroundColor' => CAL_TYPES[$type]['color'],
        'borderColor'     => CAL_TYPES[$type]['color'],
        'extendedProps'   => [
            'type'         => $type,
            'typeLabel'    => CAL_TYPES[$type]['label'],
            'description'  => $row['description'] ?? '',
            'location'     => $row['location'] ?? '',
            'audience'     => $row['audience'],
            'audienceText' => cal_audience_text($row),
            'branchId'     => $row['branch_id'] !== null ? (int)$row['branch_id'] : null,
            'teacherId'    => $row['teacher_id'] !== null ? (int)$row['teacher_id'] : null,
            'gradeFrom'    => $row['grade_from'] !== null ? (int)$row['grade_from'] : null,
            'gradeTo'      => $row['grade_to'] !== null ? (int)$row['grade_to'] : null,
            'gradeText'    => cal_grade_text($row['grade_from'], $row['grade_to']),
            'creatorRole'  => $row['creator_role'],
            'creatorText'  => !empty($row['creator_name'])
                              ? $row['creator_name'] . ' (' . (CAL_ROLE_LABELS[$row['creator_role']] ?? 'User') . ')'
                              : (CAL_ROLE_LABELS[$row['creator_role']] ?? 'User'),
            'isMine'       => $row['creator_role'] === $u['role'] && (int)$row['creator_id'] === (int)$u['id'],
            'canEdit'      => cal_can_edit($u, $row),
            'startRaw'     => $row['event_datetime'],
            'endRaw'       => $row['end_datetime'],
        ],
    ];
}


/* =====================================================================
   REQUEST HANDLERS - called by the small fetch/save/delete files
   ===================================================================== */

function cal_out(array $data): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/** Stop with "please log in" unless the session matches this calendar. */
function cal_require_user(mysqli $conn, string $ctx): array
{
    $u = cal_current_user($conn, $ctx);
    if (!$u) {
        http_response_code(401);
        cal_out(['status' => 'error', 'message' => 'Please log in again.']);
    }
    return $u;
}

/** Load one event row (or null). */
function cal_find(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM calendar_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}


/** fetch_events.php / admin_fetch_events.php / branch_fetch_events.php */
function cal_handle_fetch(mysqli $conn, string $ctx): void
{
    $u = cal_current_user($conn, $ctx);
    if (!$u) {
        cal_out([]);
    }

    [$where, $types, $params] = cal_visibility_sql($u);

    $sql = "SELECT e.*, b.branch_name, t.name AS teacher_name
            FROM calendar_events e
            LEFT JOIN branches b ON b.id = e.branch_id
            LEFT JOIN teachers t ON t.id = e.teacher_id
            WHERE $where";

    // Only the range FullCalendar is showing
    if (!empty($_GET['start']) && !empty($_GET['end'])) {
        $start = date('Y-m-d H:i:s', strtotime($_GET['start']));
        $end   = date('Y-m-d H:i:s', strtotime($_GET['end']));
        $sql  .= " AND e.event_datetime < ? AND COALESCE(e.end_datetime, e.event_datetime) >= ?";
        $types .= 'ss';
        $params[] = $end;
        $params[] = $start;
    }
    $sql .= " ORDER BY e.event_datetime ASC";

    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $events = [];
    while ($row = $res->fetch_assoc()) {
        $events[] = cal_row_to_event($u, $row);
    }

    cal_out($events);
}

/** Dropdown data for the form (embedded into the calendar page). */
function cal_meta(mysqli $conn, array $u): array
{
    $branches = [];
    $teachers = [];

    if ($u['role'] === 'admin') {
        $r = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name");
        while ($r && $row = $r->fetch_assoc()) {
            $branches[] = ['id' => (int)$row['id'], 'name' => $row['branch_name']];
        }
        $r = $conn->query(
            "SELECT t.id, t.name, b.branch_name
             FROM teachers t LEFT JOIN branches b ON " . CAL_TB_JOIN . "
             ORDER BY t.name"
        );
        while ($r && $row = $r->fetch_assoc()) {
            $teachers[] = [
                'id'   => (int)$row['id'],
                'name' => $row['name'] . ($row['branch_name'] ? ' — ' . $row['branch_name'] : ''),
            ];
        }
    } elseif ($u['role'] === 'branch_admin') {
        $stmt = $conn->prepare(
            "SELECT t.id, t.name FROM teachers t
             JOIN branches b ON " . CAL_TB_JOIN . "
             WHERE b.id = ? ORDER BY t.name"
        );
        $stmt->bind_param("i", $u['branch_id']);
        $stmt->execute();
        $r = $stmt->get_result();
        while ($row = $r->fetch_assoc()) {
            $teachers[] = ['id' => (int)$row['id'], 'name' => $row['name']];
        }
    }

    $types = [];
    foreach (CAL_TYPES as $key => $t) {
        $types[] = ['key' => $key, 'label' => $t['label'], 'color' => $t['color']];
    }

    return [
        'role'     => $u['role'],
        'name'     => $u['name'],
        'types'    => $types,
        'branches' => $branches,
        'teachers' => $teachers,
    ];
}

/** delete_event.php / admin_delete_event.php / branch_delete_event.php */
function cal_handle_delete(mysqli $conn, string $ctx): void
{
    $u = cal_require_user($conn, $ctx);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        cal_out(['status' => 'error', 'message' => 'Invalid request.']);
    }

    $id  = (int)($_POST['id'] ?? 0);
    $row = $id ? cal_find($conn, $id) : null;

    if (!$row || !cal_can_edit($u, $row)) {
        cal_out(['status' => 'error', 'message' => 'You can only delete events you created.']);
    }

    $stmt = $conn->prepare("DELETE FROM calendar_events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    cal_out(['status' => 'success', 'message' => 'Event deleted.']);
}

/** save_event.php / admin_save_event.php / branch_save_event.php */
function cal_handle_save(mysqli $conn, string $ctx): void
{
    $u = cal_require_user($conn, $ctx);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        cal_out(['status' => 'error', 'message' => 'Invalid request.']);
    }

    $id          = (int)($_POST['event_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['event_type'] ?? 'event';
    $allDay      = !empty($_POST['all_day']) ? 1 : 0;
    $startDate   = $_POST['start_date'] ?? '';
    $startTime   = $_POST['start_time'] ?? '';
    $endDate     = $_POST['end_date'] ?? '';
    $endTime     = $_POST['end_time'] ?? '';
    $location    = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $gradeFrom   = ($_POST['grade_from'] ?? '') === '' ? null : (int)$_POST['grade_from'];
    $gradeTo     = ($_POST['grade_to'] ?? '') === '' ? null : (int)$_POST['grade_to'];
    $audience    = $_POST['audience'] ?? '';

    /* ---- basic validation ---- */
    if ($title === '' || $startDate === '') {
        cal_out(['status' => 'error', 'message' => 'Title and start date are required.']);
    }
    if (mb_strlen($title) > 150) {
        cal_out(['status' => 'error', 'message' => 'Title is too long (max 150 characters).']);
    }
    if (!isset(CAL_TYPES[$type])) {
        $type = 'other';
    }
    if (!$allDay && $startTime === '') {
        cal_out(['status' => 'error', 'message' => 'Please choose a start time or tick "All day".']);
    }

    $start = $startDate . ' ' . ($allDay ? '00:00:00' : $startTime . ':00');
    $end   = null;
    if ($endDate !== '' || (!$allDay && $endTime !== '')) {
        $end = ($endDate !== '' ? $endDate : $startDate) . ' ' . ($allDay ? '00:00:00' : (($endTime ?: $startTime) . ':00'));
        if (strtotime($end) < strtotime($start)) {
            cal_out(['status' => 'error', 'message' => 'End must be after the start.']);
        }
    }

    foreach ([$gradeFrom, $gradeTo] as $g) {
        if ($g !== null && ($g < 1 || $g > 12)) {
            cal_out(['status' => 'error', 'message' => 'Grades must be between 1 and 12.']);
        }
    }
    if ($gradeFrom !== null && $gradeTo === null) $gradeTo = $gradeFrom;
    if ($gradeTo !== null && $gradeFrom === null) $gradeFrom = $gradeTo;
    if ($gradeFrom !== null && $gradeFrom > $gradeTo) {
        [$gradeFrom, $gradeTo] = [$gradeTo, $gradeFrom];
    }

    /* ---- who gets to see it (decided by role, never trusted from the browser) ---- */
    $branchId  = null;
    $teacherId = null;

    if ($u['role'] === 'teacher') {
        $audience  = 'self';
        $teacherId = $u['id'];
        $branchId  = $u['branch_id'];

    } elseif ($u['role'] === 'branch_admin') {
        $branchId = $u['branch_id'];
        if ($audience === 'teacher') {
            $teacherId = (int)($_POST['target_teacher'] ?? 0);
            $chk = $conn->prepare(
                "SELECT t.id FROM teachers t JOIN branches b ON " . CAL_TB_JOIN . "
                 WHERE t.id = ? AND b.id = ?"
            );
            $chk->bind_param("ii", $teacherId, $branchId);
            $chk->execute();
            if (!$teacherId || !$chk->get_result()->fetch_assoc()) {
                cal_out(['status' => 'error', 'message' => 'Please choose a teacher from your branch.']);
            }
        } else {
            $audience = 'branch';
        }

    } else { // super admin
        if ($audience === 'branch') {
            $branchId = (int)($_POST['target_branch'] ?? 0);
            if (!$branchId) {
                cal_out(['status' => 'error', 'message' => 'Please choose a branch.']);
            }
        } elseif ($audience === 'teacher') {
            $teacherId = (int)($_POST['target_teacher'] ?? 0);
            $chk = $conn->prepare(
                "SELECT b.id AS branch_id FROM teachers t
                 LEFT JOIN branches b ON " . CAL_TB_JOIN . " WHERE t.id = ?"
            );
            $chk->bind_param("i", $teacherId);
            $chk->execute();
            $trow = $chk->get_result()->fetch_assoc();
            if (!$teacherId || !$trow) {
                cal_out(['status' => 'error', 'message' => 'Please choose a teacher.']);
            }
            $branchId = $trow['branch_id'] !== null ? (int)$trow['branch_id'] : null;
        } else {
            $audience = 'all';
        }
    }

    /* ---- update ---- */
    if ($id) {
        $row = cal_find($conn, $id);
        if (!$row || !cal_can_edit($u, $row)) {
            cal_out(['status' => 'error', 'message' => 'You can only edit events you created.']);
        }

        // A teacher's personal entry stays personal even when the admin edits it
        if ($row['creator_role'] === 'teacher' && $u['role'] !== 'teacher') {
            $audience  = 'self';
            $teacherId = (int)$row['teacher_id'];
            $branchId  = $row['branch_id'] !== null ? (int)$row['branch_id'] : null;
        }

        $stmt = $conn->prepare(
            "UPDATE calendar_events
             SET title = ?, description = ?, event_type = ?, event_datetime = ?, end_datetime = ?,
                 all_day = ?, location = ?, audience = ?, grade_from = ?, grade_to = ?,
                 branch_id = ?, teacher_id = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "sssssissiiiii",
            $title, $description, $type, $start, $end,
            $allDay, $location, $audience, $gradeFrom, $gradeTo,
            $branchId, $teacherId, $id
        );
        $ok = $stmt->execute();
        cal_out(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Event updated.' : 'Could not update the event.']);
    }

    /* ---- insert ---- */
    $stmt = $conn->prepare(
        "INSERT INTO calendar_events
            (title, description, event_type, event_datetime, end_datetime, all_day, location,
             audience, grade_from, grade_to, creator_role, creator_id, creator_name, branch_id, teacher_id)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "sssssissiisisii",
        $title, $description, $type, $start, $end, $allDay, $location,
        $audience, $gradeFrom, $gradeTo, $u['role'], $u['id'], $u['name'], $branchId, $teacherId
    );
    $ok = $stmt->execute();
    cal_out(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Event added.' : 'Could not add the event.']);
}