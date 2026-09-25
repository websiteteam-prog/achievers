<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo "<div class='alert alert-danger'>Unauthorized</div>";
    exit;
}
include '../db_config.php';

// Teacher list dropdown ke liye (agar table/columns alag hain to yaha adjust karo)
$teachers = [];
$tq = $conn->query("SELECT id, name FROM teachers ORDER BY name ASC");
if ($tq) {
    while ($r = $tq->fetch_assoc()) {
        $teachers[] = $r;
    }
}
?>

<style>
    .adm-cal-container h5.text {
        font-size: 26px;
        color: #05364d;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .adm-cal-card {
        background: #fff;
        padding: 20px;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    #admin-calendar { width: 100%; max-width: 100%; }
    .adm-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 16px;
        font-size: 14px;
        color: #05364d;
    }
    .adm-legend .dot {
        display: inline-block;
        width: 12px; height: 12px;
        border-radius: 50%;
        margin-right: 6px;
        vertical-align: middle;
    }
    .dot-global { background: #fd7e14; }
    .dot-target { background: #0dcaf0; }

    .adm-cal-container .fc-header-toolbar { flex-wrap: wrap !important; gap: 8px; }
    .adm-cal-container .fc-toolbar-title { font-size: 20px; font-weight: 600; }
    .adm-cal-container .fc-button { padding: 6px 10px !important; font-size: 14px !important; }
    .adm-cal-container .fc { font-size: 14px; }

    @media(max-width:575px){
        .adm-cal-container .fc-header-toolbar { flex-direction: column !important; align-items: center !important; }
        .adm-cal-container .fc-toolbar-title { font-size: 16px; text-align: center; }
    }
</style>

<div class="adm-cal-container">

    <h5 class="text">📅 Events Calendar</h5>

    <div class="adm-cal-card">
        <div id="admin-calendar"></div>

        <div class="adm-legend">
            <span><i class="dot dot-global"></i> All Teachers (global)</span>
            <span><i class="dot dot-target"></i> Specific Teacher</span>
        </div>
    </div>
</div>

<!-- Add / Edit Event Modal -->
<div class="modal fade" id="admEventModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="admEventForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="admModalTitle">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="event_id" id="admEventId">

                    <div class="mb-3">
                        <label>Event Title</label>
                        <input type="text" name="title" id="admTitle" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="date" id="admDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Time</label>
                        <input type="time" name="time" id="admTime" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Show To</label>
                        <select name="audience" id="admAudience" class="form-control">
                            <option value="all">All Teachers</option>
                            <option value="teacher">Specific Teacher</option>
                        </select>
                    </div>

                    <div class="mb-3" id="admTeacherWrap" style="display:none;">
                        <label>Select Teacher</label>
                        <select name="target_teacher" id="admTargetTeacher" class="form-control">
                            <option value="">-- choose teacher --</option>
                            <?php foreach ($teachers as $t): ?>
                                <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" id="admDesc" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger me-auto" type="button" id="admDeleteBtn" style="display:none;">Delete</button>
                    <button class="btn btn-primary" type="submit">Save</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {

    // ---- Load FullCalendar + Bootstrap JS if not already present ----
    function loadScript(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }
    function ensureBootstrap(cb) {
        if (window.bootstrap) { cb(); return; }
        loadScript('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', cb);
    }
    function ensureFullCalendar(cb) {
        if (window.FullCalendar) { cb(); return; }
        loadScript('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js', cb);
    }

    function initAdminCalendar() {

        var calEl = document.getElementById('admin-calendar');
        if (!calEl) return;

        function resetForm() {
            $('#admEventForm')[0].reset();
            $('#admEventId').val('');
            $('#admTeacherWrap').hide();
            $('#admDeleteBtn').hide();
            $('#admModalTitle').text('Add Event');
        }

        // Audience toggle
        $('#admAudience').off('change').on('change', function () {
            $('#admTeacherWrap').toggle(this.value === 'teacher');
        });

        var calendar = new FullCalendar.Calendar(calEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            contentHeight: 'auto',
            aspectRatio: 1.2,

            // Empty date click -> new event
            dateClick: function (info) {
                resetForm();
                $('#admDate').val(info.dateStr);
                new bootstrap.Modal(document.getElementById('admEventModal')).show();
            },

            // Existing event click -> edit
            eventClick: function (info) {
                resetForm();
                var p = info.event.extendedProps;
                var start = info.event.start;

                $('#admModalTitle').text('Edit Event');
                $('#admEventId').val(info.event.id);
                $('#admTitle').val(info.event.title);
                $('#admDesc').val(p.description || '');

                if (start) {
                    var pad = function (n) { return String(n).padStart(2, '0'); };
                    $('#admDate').val(start.getFullYear() + '-' + pad(start.getMonth() + 1) + '-' + pad(start.getDate()));
                    $('#admTime').val(pad(start.getHours()) + ':' + pad(start.getMinutes()));
                }

                if (p.teacherId) {
                    $('#admAudience').val('teacher').trigger('change');
                    $('#admTargetTeacher').val(String(p.teacherId));
                } else {
                    $('#admAudience').val('all').trigger('change');
                }

                $('#admDeleteBtn').show().data('id', info.event.id);
                new bootstrap.Modal(document.getElementById('admEventModal')).show();
            },

            events: 'admin_fetch_events.php'
        });

        calendar.render();

        // Save (insert or update)
        $('#admEventForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            $.post('admin_save_event.php', $(this).serialize(), function (res) {
                alert(res.message);
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('admEventModal')).hide();
                    calendar.refetchEvents();
                }
            }, 'json');
        });

        // Delete
        $('#admDeleteBtn').off('click').on('click', function () {
            var id = $(this).data('id');
            if (!id || !confirm('Delete this event?')) return;
            $.post('admin_delete_event.php', { id: id }, function (res) {
                alert(res.message);
                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('admEventModal')).hide();
                    calendar.refetchEvents();
                }
            }, 'json');
        });
    }

    ensureBootstrap(function () {
        ensureFullCalendar(initAdminCalendar);
    });

})();
</script>