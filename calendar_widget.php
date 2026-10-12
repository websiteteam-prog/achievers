<?php
/**
 * Shared calendar screen used by:
 *   calendar.php                        (teacher)
 *   admin/admin_events_calendar.php     (admin)
 *   branch_admin/branch_calender.php    (branch admin)
 *
 * The page sets these before including this file:
 *   $CAL_CTX    'teacher' | 'admin' | 'branch'
 *   $CAL_FETCH  e.g. 'fetch_events.php'
 *   $CAL_SAVE   e.g. 'save_event.php'
 *   $CAL_DELETE e.g. 'delete_event.php'
 *   $CAL_TITLE  heading text
 */
require_once __DIR__ . '/calendar_lib.php';

$CAL_TITLE = $CAL_TITLE ?? 'Calendar & Events';
$calUser   = cal_current_user($conn, $CAL_CTX);
if (!$calUser) {
    echo "<div class='alert alert-danger'>Please log in again.</div>";
    return;
}
$calMeta = cal_meta($conn, $calUser);
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
.acal {
    --acal-primary: #2f55a4;
    --acal-dark: #1e3c72;
    --acal-accent: #e02121;
    --acal-text: #1e293b;
    --acal-muted: #64748b;
    --acal-border: #e8edf5;
    font-family: 'Poppins', 'Segoe UI', sans-serif;
    color: var(--acal-text);
    padding: 6px 2px;
}
.acal-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
.acal-title {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: "Love Ya Like A Sister", cursive;
    font-size: 30px;
    font-weight: 400;
}

/* icon sits OUTSIDE the gradient text, so it is not painted as a red block */
.acal-title-icon {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: linear-gradient(135deg, #2f55a4, #1e3c72);
    color: #fff;
    font-size: 21px;
    box-shadow: 0 6px 14px rgba(47, 85, 164, 0.25);
}

.acal-title-text {
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    /* -webkit-text-fill-color: transparent; */
    background-clip: text;
}

/* Undo page-wide table / button styles (e.g. branch.css) so all 3 calendars look the same */
.acal table { background: transparent; border-radius: 0; overflow: visible; }
.acal table th,
.acal table td { padding: 0; border: 1px solid var(--fc-border-color, #e8edf5); font-size: 1em; vertical-align: top; text-align: start; }
.acal table th { text-align: center; }
.acal table thead th { background: transparent; color: inherit; border-color: var(--fc-border-color, #e8edf5); }
.acal table > tbody > tr:nth-child(even) { background: transparent; }
.acal .fc { --fc-border-color: #e8edf5; }
.acal-card, #acalCalendar { min-width: 0; max-width: 100%; }
.acal .fc .fc-col-header-cell { background: #f5f7fb; }
.acal .fc .fc-col-header-cell-cushion { padding: 8px 4px; font-weight: 600; color: var(--acal-dark); }
.acal-modal .btn { border-radius: 50px; font-weight: 600; padding: 8px 20px; }
.acal-modal .btn-primary { background: linear-gradient(135deg, #2f55a4, #1e3c72); border: none; box-shadow: none; color: #fff; }
.acal-modal .btn-primary:hover { background: #e02121; }
.acal-sub { margin: 2px 0 0; font-size: 13px; color: var(--acal-muted); }
.acal-add {
    display: inline-flex; align-items: center; gap: 8px;
    border: none; border-radius: 50px; padding: 9px 20px;
    background: var(--acal-accent); color: #fff; font-weight: 600; font-size: 14px;
    box-shadow: 0 6px 14px rgba(224, 33, 33, 0.25); transition: 0.25s;
}
.acal-add:hover { background: var(--acal-primary); }
.acal-card {
    background: #fff; border: 1px solid var(--acal-border); border-radius: 16px;
    padding: 18px; box-shadow: 0 4px 16px rgba(47, 85, 164, 0.06);
}

/* type filter chips = legend */
.acal-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
.acal-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 50px; border: 1px solid var(--acal-border);
    background: #fff; font-size: 12.5px; font-weight: 500; color: var(--acal-text);
    cursor: pointer; user-select: none; transition: 0.2s;
}
.acal-chip .dot { width: 10px; height: 10px; border-radius: 50%; }
.acal-chip.off { opacity: 0.4; text-decoration: line-through; }

/* FullCalendar look */
.acal .fc { font-size: 14px; }
.acal .fc .fc-toolbar { flex-wrap: wrap; gap: 8px; }
.acal .fc .fc-toolbar-title { font-size: 20px; font-weight: 600; color: var(--acal-dark); }
.acal .fc .fc-button {
    background: #fff; border: 1px solid #d5def0; color: var(--acal-dark);
    font-size: 13px; padding: 6px 12px; text-transform: capitalize; box-shadow: none !important;
}
.acal .fc .fc-button:hover,
.acal .fc .fc-button-active {
    background: var(--acal-primary) !important; border-color: var(--acal-primary) !important; color: #fff !important;
}
.acal .fc .fc-day-today { background: #eef2fb !important; }
.acal .fc .fc-daygrid-day-number,
.acal .fc .fc-col-header-cell-cushion { color: var(--acal-text); text-decoration: none; }
.acal .fc .fc-daygrid-day:hover { background: #f8faff; cursor: pointer; }
.acal .fc-event { border-radius: 6px; padding: 1px 4px; font-size: 12px; cursor: pointer; }
.acal .fc-daygrid-event { color: #fff; }

/* Modals */
.acal-modal .modal-content { border: 0; border-radius: 18px; overflow: hidden; font-family: 'Poppins', 'Segoe UI', sans-serif; }
.acal-modal .modal-header { background: linear-gradient(135deg, #2f55a4, #1e3c72); color: #fff; border: 0; }
.acal-modal .modal-header .btn-close { filter: invert(1); opacity: 1; }
.acal-modal .form-label { font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 4px; }
.acal-modal .form-control,
.acal-modal .form-select { border-radius: 10px; font-size: 14px; }
.acal-note { font-size: 12.5px; color: #64748b; background: #f5f7fb; border-radius: 10px; padding: 8px 12px; }
.acal-detail-type {
    display: inline-flex; align-items: center; gap: 6px; padding: 3px 12px; border-radius: 50px;
    color: #fff; font-size: 12px; font-weight: 600; margin-bottom: 12px;
}
.acal-detail-row { display: flex; gap: 10px; margin-bottom: 10px; font-size: 14px; }
.acal-detail-row i { color: #2f55a4; font-size: 16px; margin-top: 1px; }
.acal-join {
    display: inline-flex; align-items: center; gap: 6px; margin-top: 2px;
    padding: 5px 14px; border-radius: 50px; background: #16a34a; color: #fff !important;
    font-size: 13px; font-weight: 600; text-decoration: none;
}
.acal-join:hover { background: #2f55a4; }
.acal-join-url { margin-top: 4px; font-size: 12px; color: #64748b; word-break: break-all; }

.acal-detail-row small { display: block; color: #64748b; font-size: 12px; }

/* Toast */
.acal-toast {
    position: fixed; right: 20px; bottom: 20px; z-index: 2000;
    padding: 12px 18px; border-radius: 12px; color: #fff;
    font-family: 'Poppins', 'Segoe UI', sans-serif; font-size: 14px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    opacity: 0; transform: translateY(10px); transition: 0.3s; pointer-events: none;
}
.acal-toast.show { opacity: 1; transform: translateY(0); }

/* List view: clean day headers instead of blue underlined links */
.acal .fc a { text-decoration: none; }
.acal .fc .fc-list-day-cushion { background: #f5f7fb; }
.acal .fc .fc-list-day-cushion a { color: var(--acal-dark); font-weight: 600; }
.acal .fc .fc-list-event:hover td { background: #f8faff; }
.acal .fc .fc-list-event-title a { color: var(--acal-text); font-weight: 500; }
.acal .fc .fc-list-empty { background: #fff; color: var(--acal-muted); }

/* ===== Responsive ===== */

/* Tablet & small laptop */
@media (max-width: 991px) {
    .acal-title { font-size: 26px; }
    .acal-card { padding: 14px; }
    .acal .fc .fc-toolbar-title { font-size: 18px; }
    /* hide the time inside month cells so the event name has room */
    .acal .fc-daygrid-event .fc-event-time { display: none; }
}

/* Large phones / small tablets */
@media (max-width: 767px) {
    .acal { padding: 2px 0; }
    .acal-head { align-items: flex-start; }
    /* type chips become one swipeable row */
    .acal-chips {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: 6px;
        margin-bottom: 10px;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
    }
    .acal-chips::-webkit-scrollbar { display: none; }
    .acal-chip { flex-shrink: 0; }
    .acal .fc { font-size: 13px; }
    .acal .fc .fc-daygrid-day-number { font-size: 12px; padding: 2px 4px; }
    .acal .fc-daygrid-event { font-size: 11px; padding: 0 3px; }
    .acal .fc .fc-col-header-cell-cushion { padding: 6px 2px; font-size: 12px; }
    .acal-modal .modal-body { padding: 16px; }
    .acal-modal .modal-title { font-size: 17px; }
}

/* Phones */
@media (max-width: 575px) {
    .acal-title { font-size: 22px; gap: 9px; }
    .acal-title-icon { width: 36px; height: 36px; font-size: 17px; border-radius: 10px; }
    .acal-sub { font-size: 12px; }
    .acal-card { padding: 10px; border-radius: 14px; }
    .acal .fc .fc-toolbar { flex-direction: column; align-items: stretch; gap: 10px; }
    .acal .fc .fc-toolbar-chunk { display: flex; justify-content: center; }
    .acal .fc .fc-toolbar-title { font-size: 17px; }
    .acal .fc .fc-button { font-size: 13px; padding: 7px 12px; }
    .acal .fc .fc-toolbar-chunk:last-child .fc-button-group { width: 100%; }
    .acal .fc .fc-toolbar-chunk:last-child .fc-button { flex: 1; }
    .acal .fc .fc-list-event td { padding: 10px 8px; }
    .acal .fc .fc-list-event-time { white-space: nowrap; font-size: 12px; }
    .acal-add { width: 100%; justify-content: center; padding: 11px 20px; }
    .acal-modal .modal-dialog { margin: 8px; }
    .acal-modal .modal-footer { flex-wrap: wrap; gap: 8px; padding: 12px 16px; }
    .acal-modal .modal-footer .btn { flex: 1 1 auto; margin: 0; }
    .acal-modal .modal-footer #acalDeleteBtn { flex-basis: 100%; order: 3; }
    .acal-detail-row { font-size: 13.5px; }
    .acal-join { width: 100%; justify-content: center; padding: 8px 14px; }
    .acal-toast { left: 12px; right: 12px; bottom: 12px; text-align: center; }
}
</style>

<div class="acal" id="acalRoot"
     data-fetch="<?= htmlspecialchars($CAL_FETCH) ?>"
     data-save="<?= htmlspecialchars($CAL_SAVE) ?>"
     data-delete="<?= htmlspecialchars($CAL_DELETE) ?>">
    <script type="application/json" id="acalMeta"><?= json_encode($calMeta, JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

    <div class="acal-head">
        <div>
            <h5 class="acal-title">
                <span class="acal-title-icon"><i class="bi bi-calendar-event"></i></span>
                <span class="acal-title-text"><?= htmlspecialchars($CAL_TITLE) ?></span>
            </h5>
            <p class="acal-sub" id="acalSub">Click a date to add an entry, click an entry to see details.</p>
        </div>
        <button type="button" class="acal-add" id="acalAddBtn"><i class="bi bi-plus-lg"></i> Add Event</button>
    </div>

    <div class="acal-card">
        <div class="acal-chips" id="acalChips"></div>
        <div id="acalCalendar"></div>
    </div>
</div>

<!-- ===== Add / Edit modal ===== -->
<div class="modal fade acal-modal" id="acalFormModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form id="acalForm" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i><span id="acalFormTitle">Add Event</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="event_id" id="acalEventId">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" id="acalTitle" class="form-control" maxlength="150"
                                   placeholder="e.g. Grade 5 Maths Unit Test" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type *</label>
                            <select name="event_type" id="acalType" class="form-select"></select>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="all_day" value="1" id="acalAllDay">
                                <label class="form-check-label" for="acalAllDay">All day (e.g. holiday, sports day)</label>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Start date *</label>
                            <input type="date" name="start_date" id="acalStartDate" class="form-control" required>
                        </div>
                        <div class="col-6 col-md-3 acal-time">
                            <label class="form-label">Start time *</label>
                            <input type="time" name="start_time" id="acalStartTime" class="form-control">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">End date</label>
                            <input type="date" name="end_date" id="acalEndDate" class="form-control">
                        </div>
                        <div class="col-6 col-md-3 acal-time">
                            <label class="form-label">End time</label>
                            <input type="time" name="end_time" id="acalEndTime" class="form-control">
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label">Grade from</label>
                            <select name="grade_from" id="acalGradeFrom" class="form-select"></select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label">Grade to</label>
                            <select name="grade_to" id="acalGradeTo" class="form-select"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location / meeting link</label>
                            <input type="text" name="location" id="acalLocation" class="form-control"
                                   maxlength="255" placeholder="e.g. Room 4, Main hall or Zoom link">
                        </div>

                        <!-- Audience: admins only -->
                        <div class="col-md-6" id="acalAudienceWrap">
                            <label class="form-label">Show to *</label>
                            <select name="audience" id="acalAudience" class="form-select"></select>
                        </div>
                        <div class="col-md-6" id="acalBranchWrap" style="display:none;">
                            <label class="form-label">Branch *</label>
                            <select name="target_branch" id="acalBranch" class="form-select"></select>
                        </div>
                        <div class="col-md-6" id="acalTeacherWrap" style="display:none;">
                            <label class="form-label">Teacher *</label>
                            <select name="target_teacher" id="acalTeacher" class="form-select"></select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="acalDesc" class="form-control" rows="3"
                                      placeholder="Syllabus, things to bring, agenda…"></textarea>
                        </div>

                        <div class="col-12">
                            <div class="acal-note" id="acalVisibilityNote"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="acalSaveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== Detail modal ===== -->
<div class="modal fade acal-modal" id="acalDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i><span id="acalDTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <span class="acal-detail-type" id="acalDType"></span>
                <div class="acal-detail-row"><i class="bi bi-clock"></i><div><small>When</small><span id="acalDWhen"></span></div></div>
                <div class="acal-detail-row"><i class="bi bi-mortarboard"></i><div><small>Grades</small><span id="acalDGrades"></span></div></div>
                <div class="acal-detail-row" id="acalDLocRow"><i class="bi bi-geo-alt" id="acalDLocIcon"></i><div><small id="acalDLocLabel">Location</small><span id="acalDLoc"></span></div></div>
                <div class="acal-detail-row"><i class="bi bi-people"></i><div><small>Visible to</small><span id="acalDAud"></span></div></div>
                <div class="acal-detail-row"><i class="bi bi-person-badge"></i><div><small>Added by</small><span id="acalDBy"></span></div></div>
                <div class="acal-detail-row" id="acalDDescRow"><i class="bi bi-card-text"></i><div><small>Description</small><span id="acalDDesc" style="white-space:pre-line"></span></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger me-auto" id="acalDeleteBtn"><i class="bi bi-trash me-1"></i>Delete</button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="acalEditBtn"><i class="bi bi-pencil me-1"></i>Edit</button>
            </div>
        </div>
    </div>
</div>

<div class="acal-toast" id="acalToast"></div>

<script>
(function () {
    function load(src, cb) {
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        document.head.appendChild(s);
    }
    function ready(cb) {
        var needBs = !window.bootstrap || !window.bootstrap.Modal;
        var needFc = !window.FullCalendar || !window.FullCalendar.Calendar;
        var afterBs = function () {
            if (needFc) load('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js', cb);
            else cb();
        };
        if (needBs) load('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', afterBs);
        else afterBs();
    }

    function start() {
        var root = document.getElementById('acalRoot');
        if (!root || root.dataset.inited === '1') return;
        root.dataset.inited = '1';

        var URLS = { fetch: root.dataset.fetch, save: root.dataset.save, delete: root.dataset.delete };
        var el = function (id) { return document.getElementById(id); };

        var formModal   = bootstrap.Modal.getOrCreateInstance(el('acalFormModal'));
        var detailModal = bootstrap.Modal.getOrCreateInstance(el('acalDetailModal'));

        var META = { role: '', types: [], branches: [], teachers: [] };
        var activeTypes = {};
        var currentEvent = null;

        /* ---------- helpers ---------- */
        function toast(msg, ok) {
            var t = el('acalToast');
            t.textContent = msg;
            t.style.background = ok ? '#16a34a' : '#e02121';
            t.classList.add('show');
            clearTimeout(t._h);
            t._h = setTimeout(function () { t.classList.remove('show'); }, 2600);
        }
        function opt(value, text) {
            var o = document.createElement('option');
            o.value = value;
            o.textContent = text;
            return o;
        }
        function pad(n) { return String(n).padStart(2, '0'); }
        function ymd(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
        function hm(d) { return pad(d.getHours()) + ':' + pad(d.getMinutes()); }
        function fmtWhen(ev) {
            var p = ev.extendedProps;
            var s = new Date(p.startRaw.replace(' ', 'T'));
            var e = p.endRaw ? new Date(p.endRaw.replace(' ', 'T')) : null;
            var dOpt = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
            var tOpt = { hour: '2-digit', minute: '2-digit' };
            if (ev.allDay) {
                var txt = s.toLocaleDateString([], dOpt);
                if (e && ymd(e) !== ymd(s)) txt += '  →  ' + e.toLocaleDateString([], dOpt);
                return txt + ' (all day)';
            }
            var out = s.toLocaleDateString([], dOpt) + ', ' + s.toLocaleTimeString([], tOpt);
            if (e) {
                out += ymd(e) === ymd(s)
                    ? ' – ' + e.toLocaleTimeString([], tOpt)
                    : '  →  ' + e.toLocaleDateString([], dOpt) + ', ' + e.toLocaleTimeString([], tOpt);
            }
            return out;
        }

        /* ---------- form setup (depends on role) ---------- */
        function buildForm() {
            var type = el('acalType');
            type.innerHTML = '';
            META.types.forEach(function (t) { type.appendChild(opt(t.key, t.label)); });

            ['acalGradeFrom', 'acalGradeTo'].forEach(function (id) {
                var s = el(id);
                s.innerHTML = '';
                s.appendChild(opt('', 'All grades'));
                for (var g = 1; g <= 12; g++) s.appendChild(opt(g, 'Grade ' + g));
            });

            var aud = el('acalAudience');
            aud.innerHTML = '';
            if (META.role === 'admin') {
                aud.appendChild(opt('all', 'Everyone (all branches)'));
                aud.appendChild(opt('branch', 'One branch'));
                aud.appendChild(opt('teacher', 'One teacher'));
            } else if (META.role === 'branch_admin') {
                aud.appendChild(opt('branch', 'All teachers of my branch'));
                aud.appendChild(opt('teacher', 'One teacher'));
            } else {
                el('acalAudienceWrap').style.display = 'none';
            }

            var br = el('acalBranch');
            br.innerHTML = '';
            br.appendChild(opt('', '-- choose branch --'));
            META.branches.forEach(function (b) { br.appendChild(opt(b.id, b.name)); });

            var te = el('acalTeacher');
            te.innerHTML = '';
            te.appendChild(opt('', '-- choose teacher --'));
            META.teachers.forEach(function (t) { te.appendChild(opt(t.id, t.name)); });

            var sub = {
                admin: 'You see every entry. Admin events can go to everyone, one branch or one teacher.',
                branch_admin: 'You see admin events, your branch events and your teachers’ entries.',
                teacher: 'You see admin & branch events plus your own entries. Your entries are shared with your branch admin and admin.'
            };
            el('acalSub').textContent = sub[META.role] || '';
        }

        function syncAudience() {
            var a = el('acalAudience').value;
            el('acalBranchWrap').style.display  = (META.role === 'admin' && a === 'branch') ? '' : 'none';
            el('acalTeacherWrap').style.display = (a === 'teacher' && META.role !== 'teacher') ? '' : 'none';

            var note = {
                all: 'Everyone in every branch will see this (all branch admins and teachers).',
                branch: META.role === 'admin'
                    ? 'The selected branch admin and all its teachers will see this.'
                    : 'All teachers of your branch and the admin will see this.',
                teacher: 'Only the selected teacher, their branch admin and the admin will see this.'
            };
            el('acalVisibilityNote').innerHTML = '<i class="bi bi-eye me-1"></i>' +
                (META.role === 'teacher' ? 'Visible to you, your branch admin and the admin.' : note[a]);
        }

        function syncAllDay() {
            var on = el('acalAllDay').checked;
            document.querySelectorAll('#acalForm .acal-time').forEach(function (d) {
                d.style.display = on ? 'none' : '';
            });
        }

        el('acalAudience').addEventListener('change', syncAudience);
        el('acalAllDay').addEventListener('change', syncAllDay);
        el('acalType').addEventListener('change', function () {
            if (this.value === 'holiday') { el('acalAllDay').checked = true; syncAllDay(); }
        });

        function openForm(ev, dateStr) {
            el('acalForm').reset();
            el('acalEventId').value = '';
            el('acalFormTitle').textContent = ev ? 'Edit Event' : 'Add Event';
            if (el('acalAudience').options.length) el('acalAudience').selectedIndex = 0;

            if (ev) {
                var p = ev.extendedProps;
                var s = new Date(p.startRaw.replace(' ', 'T'));
                var e = p.endRaw ? new Date(p.endRaw.replace(' ', 'T')) : null;

                el('acalEventId').value = ev.id;
                el('acalTitle').value = ev.title;
                el('acalType').value = p.type;
                el('acalAllDay').checked = ev.allDay;
                el('acalStartDate').value = ymd(s);
                el('acalStartTime').value = ev.allDay ? '' : hm(s);
                el('acalEndDate').value = e ? ymd(e) : '';
                el('acalEndTime').value = (e && !ev.allDay) ? hm(e) : '';
                el('acalGradeFrom').value = p.gradeFrom !== null ? p.gradeFrom : '';
                el('acalGradeTo').value = p.gradeTo !== null ? p.gradeTo : '';
                el('acalLocation').value = p.location || '';
                el('acalDesc').value = p.description || '';

                // a teacher's personal entry keeps its audience
                var lockAudience = p.creatorRole === 'teacher';
                el('acalAudienceWrap').style.display = (META.role === 'teacher' || lockAudience) ? 'none' : '';
                if (!lockAudience && META.role !== 'teacher') {
                    el('acalAudience').value = p.audience === 'self' ? el('acalAudience').options[0].value : p.audience;
                    if (p.branchId) el('acalBranch').value = p.branchId;
                    if (p.teacherId) el('acalTeacher').value = p.teacherId;
                }
            } else {
                el('acalStartDate').value = dateStr || ymd(new Date());
                el('acalStartTime').value = '09:00';
                el('acalAudienceWrap').style.display = META.role === 'teacher' ? 'none' : '';
            }

            syncAllDay();
            syncAudience();
            if (ev && ev.extendedProps.creatorRole === 'teacher' && META.role !== 'teacher') {
                el('acalBranchWrap').style.display = 'none';
                el('acalTeacherWrap').style.display = 'none';
                el('acalVisibilityNote').innerHTML = '<i class="bi bi-eye me-1"></i>Teacher’s personal entry: visible to that teacher, their branch admin and the admin.';
            }
            formModal.show();
        }

        function openDetail(ev) {
            var p = ev.extendedProps;
            currentEvent = ev;

            el('acalDTitle').textContent = ev.title;
            el('acalDType').textContent = p.typeLabel;
            el('acalDType').style.background = ev.backgroundColor;
            el('acalDWhen').textContent = fmtWhen(ev);
            el('acalDGrades').textContent = p.gradeText;
            // A web link (Google Meet, Zoom...) is shown as a meeting link with a Join button
            var loc = (p.location || '').trim();
            var isLink = /^https?:\/\//i.test(loc);
            el('acalDLocLabel').textContent = isLink ? 'Meeting link' : 'Location';
            el('acalDLocIcon').className = isLink ? 'bi bi-camera-video' : 'bi bi-geo-alt';
            el('acalDLoc').textContent = '';
            if (isLink) {
                var a = document.createElement('a');
                a.href = loc;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.className = 'acal-join';
                a.innerHTML = '<i class="bi bi-box-arrow-up-right"></i> Join meeting';
                var u = document.createElement('div');
                u.className = 'acal-join-url';
                u.textContent = loc;
                el('acalDLoc').appendChild(a);
                el('acalDLoc').appendChild(u);
            } else {
                el('acalDLoc').textContent = loc;
            }
            el('acalDLocRow').style.display = loc ? '' : 'none';
            el('acalDAud').textContent = p.audienceText;
            el('acalDBy').textContent = p.creatorText + (p.isMine ? ' — you' : '');
            el('acalDDesc').textContent = p.description || '';
            el('acalDDescRow').style.display = p.description ? '' : 'none';

            el('acalEditBtn').style.display = p.canEdit ? '' : 'none';
            el('acalDeleteBtn').style.display = p.canEdit ? '' : 'none';
            detailModal.show();
        }

        /* ---------- type chips (legend + filter) ---------- */
        function buildChips() {
            var box = el('acalChips');
            box.innerHTML = '';
            META.types.forEach(function (t) {
                activeTypes[t.key] = true;
                var c = document.createElement('span');
                c.className = 'acal-chip';
                c.innerHTML = '<span class="dot"></span>';
                c.querySelector('.dot').style.background = t.color;
                c.appendChild(document.createTextNode(t.label));
                c.title = 'Click to show / hide';
                c.addEventListener('click', function () {
                    activeTypes[t.key] = !activeTypes[t.key];
                    c.classList.toggle('off', !activeTypes[t.key]);
                    calendar.refetchEvents();
                });
                box.appendChild(c);
            });
        }

        /* ---------- calendar ---------- */
        // Phones get a list-based week view (7 time columns don't fit on a small screen)
        function isPhone() { return window.innerWidth < 576; }
        function toolbarFor(phone) {
            return {
                left: 'prev,next today',
                center: 'title',
                right: phone ? 'dayGridMonth,listWeek,listMonth' : 'dayGridMonth,timeGridWeek,listMonth'
            };
        }
        var wasPhone = isPhone();

        var calendar = new FullCalendar.Calendar(el('acalCalendar'), {
            initialView: isPhone() ? 'listMonth' : 'dayGridMonth',
            headerToolbar: toolbarFor(isPhone()),
            buttonText: { today: 'Today' },
            views: {
                dayGridMonth: { buttonText: 'Month' },
                timeGridWeek: { buttonText: 'Week' },
                listWeek:     { buttonText: 'Week' },
                listMonth:    { buttonText: 'List' }
            },
            // switch layout when the screen is resized / phone is rotated
            windowResize: function () {
                var phone = isPhone();
                if (phone === wasPhone) return;
                wasPhone = phone;
                calendar.setOption('headerToolbar', toolbarFor(phone));
                var v = calendar.view.type;
                if (phone && v === 'timeGridWeek') calendar.changeView('listWeek');
                else if (phone && v === 'dayGridMonth') calendar.changeView('listMonth');
                else if (!phone && v === 'listWeek') calendar.changeView('timeGridWeek');
                else if (!phone && v === 'listMonth') calendar.changeView('dayGridMonth');
            },
            height: 'auto',
            dayMaxEvents: 3,
            eventDisplay: 'block',
            nowIndicator: true,
            slotMinTime: '07:00:00',
            slotMaxTime: '21:00:00',
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: 'short' },

            events: function (info, success, failure) {
                fetch(URLS.fetch + '?start=' + encodeURIComponent(info.startStr) + '&end=' + encodeURIComponent(info.endStr),
                      { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        if (!Array.isArray(list)) { failure(list); return; }
                        success(list.filter(function (e) { return activeTypes[e.extendedProps.type] !== false; }));
                    })
                    .catch(failure);
            },

            dateClick: function (info) { openForm(null, info.dateStr.substring(0, 10)); },
            eventClick: function (info) { info.jsEvent.preventDefault(); openDetail(info.event); },
            eventDidMount: function (info) {
                info.el.title = info.event.extendedProps.typeLabel + ' • ' + info.event.extendedProps.gradeText;
            }
        });

        /* ---------- actions ---------- */
        el('acalAddBtn').addEventListener('click', function () { openForm(null); });

        el('acalEditBtn').addEventListener('click', function () {
            detailModal.hide();
            if (currentEvent) openForm(currentEvent);
        });

        el('acalDeleteBtn').addEventListener('click', function () {
            if (!currentEvent || !confirm('Delete "' + currentEvent.title + '"?')) return;
            var fd = new URLSearchParams();
            fd.append('id', currentEvent.id);
            fetch(URLS.delete, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    toast(res.message, res.status === 'success');
                    if (res.status === 'success') { detailModal.hide(); calendar.refetchEvents(); }
                })
                .catch(function () { toast('Something went wrong.', false); });
        });

        el('acalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!el('acalTitle').value.trim() || !el('acalStartDate').value) {
                toast('Please enter a title and start date.', false);
                return;
            }
            var btn = el('acalSaveBtn');
            btn.disabled = true;
            fetch(URLS.save, { method: 'POST', body: new URLSearchParams(new FormData(this)), credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    toast(res.message, res.status === 'success');
                    if (res.status === 'success') { formModal.hide(); calendar.refetchEvents(); }
                })
                .catch(function () { toast('Something went wrong.', false); })
                .finally(function () { btn.disabled = false; });
        });

        /* ---------- boot ---------- */
        META = JSON.parse(el('acalMeta').textContent);
        buildForm();
        buildChips();
        calendar.render();

        // Re-measure whenever the calendar's box changes size (dashboard loaded by AJAX,
        // scrollbar appears, sidebar opens/closes, fonts finish loading...)
        var box = el('acalCalendar');
        var lastW = box.offsetWidth;
        function fit() {
            if (box.offsetWidth !== lastW) { lastW = box.offsetWidth; calendar.updateSize(); }
        }
        if (window.ResizeObserver) {
            new ResizeObserver(fit).observe(box);
        }
        window.addEventListener('resize', fit);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { calendar.updateSize(); });
        setTimeout(function () { calendar.updateSize(); }, 300);
    }

    // Teacher dashboard calls initCalendar() after loading this page via AJAX
    window.initCalendar = function () { ready(start); };
    ready(start);
})();
</script>