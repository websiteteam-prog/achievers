<?php
require_once __DIR__ . '/auth.php';
require_branch_admin();

$branch_id = $_SESSION['branch_id'];

// Is branch ke teachers (specific-teacher option ke liye)
$teachers = [];
$tstmt = $conn->prepare(
    "SELECT t.id, t.name
     FROM teachers t
     JOIN branches b ON t.branch = b.branch_name
     WHERE b.id = ?
     ORDER BY t.name ASC"
);
$tstmt->bind_param("i", $branch_id);
$tstmt->execute();
$tres = $tstmt->get_result();
while ($tr = $tres->fetch_assoc()) $teachers[] = $tr;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Branch Calendar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="branch.css" />
  <style>
    #calendar {
      max-width: 1000px; margin: 15px auto; background:#fff;
      padding: 20px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .cal-legend { max-width:1000px; margin:0 auto 8px; display:flex; gap:18px; flex-wrap:wrap; font-size:14px; }
    .cal-legend .dot { width:12px; height:12px; border-radius:50%; display:inline-block; margin-right:6px; vertical-align:middle; }
    .dot-branch { background:#0f766e; }
    .dot-one    { background:#1e88e5; }
    .fc-daygrid-event, .fc-h-event { color:#fff !important; }
  </style>
</head>
<body>
  <?php include 'branch_dashboard_sidebar.php'; ?>

  <main class="main">
    <?php flash_render(); ?>
    <h2 class="mb-2">Event Calendar</h2>
   
    <div class="cal-legend">
      <span><i class="dot dot-branch"></i> All Branch Teachers</span>
      <span><i class="dot dot-one"></i> Specific Teacher</span>
    </div>

    <div id="calendar"></div>
  </main>

  <!-- Add / Edit Modal -->
  <div class="modal fade" id="brEventModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="brEventForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="brModalTitle">Add Event</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="event_id" id="brEventId">

            <div class="mb-3">
              <label>Event Title</label>
              <input type="text" name="title" id="brTitle" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Date</label>
              <input type="date" name="date" id="brDate" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Time</label>
              <input type="time" name="time" id="brTime" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Show To</label>
              <select name="audience" id="brAudience" class="form-control">
                <option value="branch">All Teachers (this branch)</option>
                <option value="teacher">Specific Teacher</option>
              </select>
            </div>
            <div class="mb-3" id="brTeacherWrap" style="display:none;">
              <label>Select Teacher</label>
              <select name="target_teacher" id="brTargetTeacher" class="form-control">
                <option value="">-- choose teacher --</option>
                <?php foreach ($teachers as $t): ?>
                  <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Description</label>
              <textarea name="description" id="brDesc" class="form-control"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-danger me-auto" type="button" id="brDeleteBtn" style="display:none;">Delete</button>
            <button class="btn btn-primary" type="submit">Save</button>
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    var pad = function(n){ return String(n).padStart(2,'0'); };
    var $ = function(id){ return document.getElementById(id); };
    var modal = new bootstrap.Modal($('brEventModal'));

    function resetForm(){
      $('brEventForm').reset();
      $('brEventId').value = '';
      $('brTeacherWrap').style.display = 'none';
      $('brDeleteBtn').style.display = 'none';
      $('brModalTitle').textContent = 'Add Event';
    }

    $('brAudience').addEventListener('change', function(){
      $('brTeacherWrap').style.display = (this.value === 'teacher') ? 'block' : 'none';
    });

    var calendar = new FullCalendar.Calendar($('calendar'), {
      initialView: 'dayGridMonth',
      themeSystem: 'bootstrap5',
      eventDisplay: 'block',
      events: 'branch_fetch_events.php',

      dateClick: function (info) {
        resetForm();
        $('brDate').value = info.dateStr;
        modal.show();
      },

      eventClick: function (info) {
        resetForm();
        var p = info.event.extendedProps, s = info.event.start;
        $('brModalTitle').textContent = 'Edit Event';
        $('brEventId').value = info.event.id;
        $('brTitle').value = info.event.title;
        $('brDesc').value = p.description || '';
        if (s) {
          $('brDate').value = s.getFullYear()+'-'+pad(s.getMonth()+1)+'-'+pad(s.getDate());
          $('brTime').value = pad(s.getHours())+':'+pad(s.getMinutes());
        }
        if (p.teacherId) {
          $('brAudience').value = 'teacher';
          $('brTeacherWrap').style.display = 'block';
          $('brTargetTeacher').value = String(p.teacherId);
        } else {
          $('brAudience').value = 'branch';
        }
        $('brDeleteBtn').style.display = 'inline-block';
        $('brDeleteBtn').setAttribute('data-id', info.event.id);
        modal.show();
      },

      eventDidMount: function (info) {
        if (info.event.extendedProps.description) {
          new bootstrap.Tooltip(info.el, {
            title: info.event.extendedProps.description,
            placement: 'top', trigger: 'hover', container: 'body'
          });
        }
      }
    });

    calendar.render();

    // Save (add / edit)
    $('brEventForm').addEventListener('submit', function(e){
      e.preventDefault();
      var fd = new URLSearchParams(new FormData(this));
      fetch('branch_save_event.php', { method:'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res){
          alert(res.message);
          if (res.status === 'success') { modal.hide(); calendar.refetchEvents(); }
        })
        .catch(function(){ alert('Something went wrong.'); });
    });

    // Delete
    $('brDeleteBtn').addEventListener('click', function(){
      var id = this.getAttribute('data-id');
      if (!id || !confirm('Delete this event?')) return;
      var fd = new URLSearchParams(); fd.append('id', id);
      fetch('branch_delete_event.php', { method:'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(res){
          alert(res.message);
          if (res.status === 'success') { modal.hide(); calendar.refetchEvents(); }
        });
    });
  });
  </script>
</body>
</html>