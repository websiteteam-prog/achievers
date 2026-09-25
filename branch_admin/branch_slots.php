<?php
/**
 * branch_slots.php  (branch_admin panel)
 * -------------------------------------------------------------
 * Branch admin apne branch ke liye date-wise appointment slots
 * add / delete karta hai. Yahi slots public "Schedule Appointment"
 * page pe (get_slots.php ke through) show hote hain.
 */
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = (int) $_SESSION['branch_id'];

/* Presets (branch chahe to "Other" se custom bhi daal sakta hai) */
$preset_slots = [
    '10:00 - 11:00 AM',
    '11:00 - 12:00 PM',
    '12:00 - 1:00 PM',
    '1:00 - 2:00 PM',
    '2:00 - 3:00 PM',
    '3:00 - 4:00 PM',
    '4:00 - 5:00 PM',
    '5:00 - 6:00 PM',
    '6:00 - 7:00 PM',
    '7:00 - 8:00 PM',
];
$allowed_modes = ['In Center', 'Online'];

/* ============ ADD SLOT ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slot'])) {

    $slot_date = trim($_POST['slot_date'] ?? '');
    $picked    = trim($_POST['time_slot'] ?? '');
    $custom    = trim($_POST['custom_slot'] ?? '');
    $mode      = trim($_POST['mode'] ?? 'In Center');

    $time_slot = ($picked === '__custom__') ? $custom : $picked;

    /* --- Validation --- */
    $d = DateTime::createFromFormat('Y-m-d', $slot_date);
    $valid_date = $d && $d->format('Y-m-d') === $slot_date;

    if (!$valid_date) {
        redirect_with_flash('danger', 'Please choose a valid date.', 'branch_slots.php');
    }
    if ($slot_date < date('Y-m-d')) {
        redirect_with_flash('danger', 'You cannot add slots for a past date.', 'branch_slots.php');
    }
    if ($time_slot === '') {
        redirect_with_flash('danger', 'Please select or enter a time slot.', 'branch_slots.php');
    }
    if (!in_array($mode, $allowed_modes, true)) {
        $mode = 'In Center';
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO branch_slots (branch_id, slot_date, time_slot, mode) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('isss', $branch_id, $slot_date, $time_slot, $mode);
        $stmt->execute();
        redirect_with_flash('success', 'Slot added successfully.', 'branch_slots.php');
    } catch (mysqli_sql_exception $e) {
        if ((int) $e->getCode() === 1062) {
            redirect_with_flash('warning', 'That slot already exists for this date.', 'branch_slots.php');
        }
        redirect_with_flash('danger', 'Could not add slot. Please try again.', 'branch_slots.php');
    }
}

/* ============ DELETE SLOT ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_slot'])) {

    $slot_id = (int) ($_POST['slot_id'] ?? 0);

    /* Slot isi branch ka hai? */
    $stmt = $conn->prepare("SELECT slot_date, time_slot FROM branch_slots WHERE id = ? AND branch_id = ?");
    $stmt->bind_param('ii', $slot_id, $branch_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        redirect_with_flash('danger', 'Slot not found.', 'branch_slots.php');
    }

    /* Booked slot delete na ho */
    $chk = $conn->prepare(
        "SELECT id FROM appointments
         WHERE branch_id = ? AND appointment_date = ? AND time_slot = ? AND status != 'Cancelled'
         LIMIT 1"
    );
    $chk->bind_param('iss', $branch_id, $row['slot_date'], $row['time_slot']);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        redirect_with_flash('warning', 'This slot is already booked and cannot be removed.', 'branch_slots.php');
    }

    $del = $conn->prepare("DELETE FROM branch_slots WHERE id = ? AND branch_id = ?");
    $del->bind_param('ii', $slot_id, $branch_id);
    $del->execute();
    redirect_with_flash('success', 'Slot removed.', 'branch_slots.php');
}

/* ============ LOAD UPCOMING SLOTS ============ */
$stmt = $conn->prepare(
    "SELECT bs.id, bs.slot_date, bs.time_slot, bs.mode,
            (SELECT COUNT(*) FROM appointments a
               WHERE a.branch_id = bs.branch_id
                 AND a.appointment_date = bs.slot_date
                 AND a.time_slot = bs.time_slot
                 AND a.status != 'Cancelled') AS booked_count
     FROM branch_slots bs
     WHERE bs.branch_id = ?
       AND bs.slot_date >= CURDATE()
     ORDER BY bs.slot_date ASC, bs.id ASC"
);
$stmt->bind_param('i', $branch_id);
$stmt->execute();
$result = $stmt->get_result();

$grouped = [];
$total_slots = 0;
while ($r = $result->fetch_assoc()) {
    $grouped[$r['slot_date']][] = $r;
    $total_slots++;
}
$total_days = count($grouped);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Appointment Slots | Branch Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>

    <!-- Header -->
    <div class="slots-head">
      <div>
        <h1 class="slots-title">
          <span class="grad">Appointment</span>
          <span class="accent-word">Slots</span>
        </h1>
        <p class="slots-sub">
          Add the days &amp; times when your branch is available for free assessments.
          Only the slots you add here will appear on the public
          <em>Schedule Appointment</em> page.
        </p>
      </div>
      <div class="slot-stats">
        <div class="slot-stat">
          <div class="num"><?= (int) $total_slots ?></div>
          <div class="lbl">Upcoming Slots</div>
        </div>
        <div class="slot-stat">
          <div class="num"><?= (int) $total_days ?></div>
          <div class="lbl">Days Open</div>
        </div>
      </div>
    </div>

    <!-- ADD SLOT -->
    <h5 class="section-title">Add a Slot</h5>
    <div class="slot-card">
      <form method="POST" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Date</label>
          <input type="date" name="slot_date" class="form-control"
                 min="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Time Slot</label>
          <select name="time_slot" id="time_slot" class="form-select" required
                  onchange="document.getElementById('customWrap').style.display = (this.value==='__custom__') ? 'block' : 'none';">
            <option value="">-- Select --</option>
            <?php foreach ($preset_slots as $ps): ?>
              <option value="<?= e($ps) ?>"><?= e($ps) ?></option>
            <?php endforeach; ?>
            <option value="__custom__">Other (type below)</option>
          </select>
          <div id="customWrap" style="display:none;margin-top:8px;">
            <input type="text" name="custom_slot" class="form-control"
                   placeholder="e.g. 9:00 - 10:00 AM">
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Mode</label>
          <select name="mode" class="form-select">
            <option value="In Center">In Center</option>
            <option value="Online">Online</option>
          </select>
        </div>

        <div class="col-12">
          <button type="submit" name="add_slot" class="btn-grad">
            <i class="bi bi-plus-lg me-1"></i>Add Slot
          </button>
        </div>
      </form>
    </div>

    <!-- EXISTING SLOTS -->
    <h5 class="section-title">Upcoming Slots</h5>

    <?php if (empty($grouped)): ?>
      <div class="empty-box">
        <i class="bi bi-calendar-x" style="font-size:26px;"></i>
        <p class="mb-0 mt-2">No upcoming slots yet. Add your first slot above.</p>
      </div>
    <?php else: ?>
      <?php foreach ($grouped as $date => $rows): ?>
        <div class="day-block">
          <h5><i class="bi bi-calendar3"></i><?= date('l, F j, Y', strtotime($date)) ?></h5>
          <?php foreach ($rows as $slot): ?>
            <span class="slot-pill">
              <strong><?= e($slot['time_slot']) ?></strong>
              <span class="badge badge-mode"><?= e($slot['mode']) ?></span>
              <?php if ($slot['booked_count'] > 0): ?>
                <span class="badge badge-booked">Booked</span>
              <?php else: ?>
                <span class="badge badge-avail">Available</span>
                <form method="POST" style="display:inline;"
                      onsubmit="return confirm('Remove this slot?');">
                  <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                  <button type="submit" name="delete_slot" class="btn-del" title="Remove">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              <?php endif; ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>