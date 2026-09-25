<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit();
}
$teacher_id = $_SESSION['teacher_id'];
?>

<style>
/* ========================= */
/* CONTAINER */
/* ========================= */
h5.text {
    font-size: 30px;
    color: #05364d;
    margin-bottom: 35px;
    font-family: "Love Ya Like A Sister", cursive;
}
.calendar-container { padding:15px; max-width:100%; }
.calendar-card {
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

/* Legend */
.calendar-legend {
    display:flex;
    flex-wrap:wrap;
    gap:16px;
    margin-top:16px;
    font-size:14px;
    color:#05364d;
}
.calendar-legend .dot {
    display:inline-block;
    width:12px;
    height:12px;
    border-radius:50%;
    margin-right:6px;
    vertical-align:middle;
}
.dot-teacher { background:#1e88e5; }   /* blue  - My Events */
.dot-admin   { background:#e63946; }   /* red   - Admin Events */
.dot-branch  { background:#0f766e; }   /* teal  - Branch Events */

/* ========================= */
/* FULLCALENDAR BASE FIX */
/* ========================= */
#calendar { width:100%; max-width:100%; }
.fc-header-toolbar { flex-wrap:wrap !important; gap:8px; }
.fc-toolbar-title { font-size:22px; font-weight:600; }
.fc-button { padding:6px 10px !important; font-size:14px !important; }
.fc { font-size:14px; }

/* ===========================
   MODAL STYLE (Same as My Students)
=========================== */

.calendar-modal .modal-content{
    border:0;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,.15);
}

.calendar-modal .modal-header{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:0;
    padding:18px 30px;
}

.calendar-modal .modal-title{
    font-weight:600;
}

.calendar-modal .btn-close{
    filter:invert(1);
    opacity:1;
}

.calendar-modal .modal-body{
    padding:28px 30px;
}

.calendar-modal .modal-footer{
    border-top:1px solid #eef2f7;
    padding:18px 30px;
}

.calendar-modal .form-control{
    border-radius:10px;
}

.calendar-modal textarea.form-control{
    min-height:100px;
}

#eventModal .modal-dialog,
#eventDetailModal .modal-dialog{
    width:95%;
    max-width:900px !important;
}

.calendar-modal .modal-body{
    padding:28px 32px;
}

.calendar-modal .modal-header,
.calendar-modal .modal-footer{
    padding:18px 32px;
}

/* ========================= */
/* TABLET */
/* ========================= */
@media(max-width:768px){
    .calendar-container { padding:12px; }
    .calendar-card { padding:16px; }
    .fc-toolbar-title { font-size:18px; }
    .fc { font-size:13px; }
}

/* ========================= */
/* MOBILE */
/* ========================= */
@media(max-width:575px){
    .calendar-container { padding:10px; }
    h5.text { font-size:20px; margin-bottom:25px; }
    .calendar-card { padding:14px; }
    .fc-header-toolbar { flex-direction:column !important; align-items:center !important; }
    .fc-toolbar-title { font-size:16px; text-align:center; }
    .fc-button { font-size:12px !important; padding:5px 8px !important; }
    .fc { font-size:12px; }
    .calendar-legend { justify-content:center; }
}

/* ========================= */
/* SMALL MOBILE */
/* ========================= */
@media(max-width:360px){
    .fc-toolbar-title { font-size:15px; }
    .fc-button { font-size:11px !important; padding:4px 6px !important; }
    .fc { font-size:11px; }
}

/* ========================= */
/* ULTRA SMALL */
/* ========================= */
@media(max-width:300px){
    .calendar-container { padding:6px; }
    .calendar-card { padding:10px; border-radius:10px; }
    .fc-toolbar-title { font-size:14px; }
    .fc-button { font-size:10px !important; padding:3px 5px !important; }
    .fc { font-size:10px; }
    .fc-daygrid-day { min-height:40px !important; }
}

/* ========================= */
/* MODAL FIX */
/* ========================= */
@media(max-width:575px){
    .modal-dialog { margin:10px; }
    .modal-content { padding:5px; }
    .form-control { font-size:14px; padding:8px; }
}
@media(max-width:300px){
    .modal-dialog { margin:5px; }
    .form-control { font-size:13px; padding:6px; }
    .btn { font-size:13px; padding:6px; }
}
</style>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">

<div class="calendar-container">
    <h5 class="text">📅 My Calendar &amp; Events</h5>

    <div class="calendar-card">
        <div id="calendar"></div>

        <div class="calendar-legend">
            <span><i class="dot dot-teacher"></i> My Events</span>
            <span><i class="dot dot-admin"></i> Admin Events</span>
            <span><i class="dot dot-branch"></i> Branch Admin Events</span>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade calendar-modal" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <form id="eventForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Add Event
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="teacher_id" value="<?= $teacher_id ?>">

                    <div class="mb-3">
                        <label>Event Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="date" id="eventDate" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Time</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                </div>
                </form>
        </div>
    </div>
</div>
<!-- Event Detail Modal -->
<div class="modal fade calendar-modal" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-event me-2"></i>
                    <span id="detailTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>When:</strong> <span id="detailWhen"></span></p>
                <p><strong>Added by:</strong> <span id="detailBy"></span></p>
                <p><strong>Description:</strong><br><span id="detailDesc"></span></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" type="button" id="deleteEventBtn">Delete</button>
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function initCalendar(){

    const calendarEl = document.getElementById('calendar');

    function roleLabel(role){
        if (role === 'admin')        return 'Admin';
        if (role === 'branch_admin') return 'Branch Admin';
        return 'You';
    }

    function formatWhen(d){
        if(!d) return '';
        return d.toLocaleString([], {
            weekday:'short', year:'numeric', month:'short',
            day:'numeric', hour:'2-digit', minute:'2-digit'
        });
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView:'dayGridMonth',
        height:'auto',
        contentHeight:'auto',
        aspectRatio:1.2,

        // Click on empty date -> add your own event
        dateClick:function(info){
            document.getElementById('eventForm').reset();
            $('#eventDate').val(info.dateStr);
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        },

        // Click on existing event -> see details (delete only if it's yours)
        eventClick:function(info){
            const p = info.event.extendedProps;
            $('#detailTitle').text(info.event.title);
            $('#detailWhen').text(formatWhen(info.event.start));
            $('#detailBy').text(roleLabel(p.creatorRole));
            $('#detailDesc').text(p.description ? p.description : '—');

            if (p.creatorRole === 'teacher') {
                $('#deleteEventBtn').show().data('id', info.event.id);
            } else {
                $('#deleteEventBtn').hide();
            }
            new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
        },

        events:'fetch_events.php'
    });

    calendar.render();

    // Save new event (teacher's own)
    $('#eventForm').off('submit').on('submit', function(e){
        e.preventDefault();
        $.post('save_event.php', $(this).serialize(), function(res){
            alert(res.message);
            if (res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                calendar.refetchEvents();
            }
        }, 'json');
    });

    // Delete own event
    $('#deleteEventBtn').off('click').on('click', function(){
        const id = $(this).data('id');
        if (!id || !confirm('Delete this event?')) return;
        $.post('delete_event.php', { id: id }, function(res){
            alert(res.message);
            if (res.status === 'success') {
                bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
                calendar.refetchEvents();
            }
        }, 'json');
    });
}
</script>