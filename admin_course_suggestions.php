<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include '../db_config.php';

/*
|--------------------------------------------------------------------------
| AUTH CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_logged_in'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

/*
|--------------------------------------------------------------------------
| FLASH MESSAGE (set by admin_course_suggestion_action.php)
|--------------------------------------------------------------------------
*/

$flash_success = $_SESSION['suggestion_admin_success'] ?? null;
$flash_error   = $_SESSION['suggestion_admin_error'] ?? null;
unset($_SESSION['suggestion_admin_success'], $_SESSION['suggestion_admin_error']);

/*
|--------------------------------------------------------------------------
| FILTER (optional: ?status=pending / approved / rejected)
|--------------------------------------------------------------------------
*/

$status_filter = $_GET['status'] ?? 'pending';
$allowed_status = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($status_filter, $allowed_status)) {
    $status_filter = 'all';
}

/*
|--------------------------------------------------------------------------
| FETCH SUGGESTIONS (with teacher + subject info)
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
cs.id,
cs.suggestion,
cs.status,
cs.created_at,
cs.grade_id,
t.name AS teacher_name,
s.subject_name,
s.grade
FROM course_suggestions cs
LEFT JOIN teachers t ON t.id = cs.teacher_id
LEFT JOIN subjects s ON s.id = cs.subject_id
";

if ($status_filter !== 'all') {
    $sql .= " WHERE cs.status = ? ";
}

$sql .= " ORDER BY cs.created_at DESC ";

$stmt = $conn->prepare($sql);

if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();
$total_suggestions = $result->num_rows;

/*
|--------------------------------------------------------------------------
| PENDING COUNT (for the badge, regardless of filter)
|--------------------------------------------------------------------------
*/

$pending_count_res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM course_suggestions WHERE status='pending'");
$pending_count = mysqli_fetch_assoc($pending_count_res)['c'] ?? 0;

?>

<style>

.students-container{
padding:5px;
}

.students-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
flex-wrap:wrap;
gap:15px;
}

.header-left{
display:flex;
align-items:center;
gap:15px;
}

.header-icon{
width:55px;
height:55px;
border-radius:16px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
display:flex;
align-items:center;
justify-content:center;
color:#fff;
font-size:24px;
box-shadow:0 8px 20px rgba(30,60,114,.25);
}

.header-title{
margin:0;
font-size:24px;
font-weight:700;
color:#1e3c72;
letter-spacing:.2px;
}

.header-subtitle{
margin:0;
font-size:14px;
color:#6b7280;
}

.header-right{
display:flex;
align-items:center;
gap:12px;
flex-wrap:wrap;
}

.subject-badge{
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
padding:8px 18px;
border-radius:40px;
font-weight:600;
font-size:13px;
box-shadow:0 5px 15px rgba(0,0,0,.12);
}

.pending-badge{
background:linear-gradient(135deg,#e8063c,#c40530);
color:#fff;
padding:8px 18px;
border-radius:40px;
font-weight:600;
font-size:13px;
box-shadow:0 5px 15px rgba(232,6,60,.25);
}

.filter-tabs{
display:flex;
gap:10px;
margin-bottom:20px;
flex-wrap:wrap;
}

.filter-tabs a{
padding:8px 20px;
border-radius:30px;
font-size:13.5px;
font-weight:600;
text-decoration:none;
background:#fff;
color:#374151;
box-shadow:0 4px 12px rgba(0,0,0,.06);
transition:.2s;
}

.filter-tabs a.active{
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
}

.filter-tabs a:hover{
transform:translateY(-2px);
}

.card{
border:none;
border-radius:0px;
overflow:hidden;
box-shadow:0 10px 30px rgba(17,24,39,.08);
animation:fadeInUp .4s ease;
}

@keyframes fadeInUp{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

.students-table{
width:100%;
border-collapse:collapse;
}

.students-table th{
background:#2a5298;
color:#fff;
padding:16px 15px;
font-size:13px;
text-transform:uppercase;
letter-spacing:.6px;
font-weight:600;
}

.students-table td{
padding:16px 15px;
border-bottom:1px solid #edf1f7;
vertical-align:middle;
font-size:14.5px;
color:#374151;
}

.students-table tbody tr{
transition:background .2s ease;
}

.students-table tbody tr:hover{
background:#f8fbff;
}

.status-badge{
display:inline-flex;
align-items:center;
gap:6px;
padding:6px 14px;
border-radius:30px;
font-size:12px;
font-weight:600;
text-transform:capitalize;
}

.status-pending{
background:#fff4d6;
color:#a15c00;
}

.status-approved{
background:#d9f3ff;
color:#0c7abf;
}

.status-rejected{
background:#ffe0ef;
color:#d63384;
}

.suggestion-text{
max-width:320px;
white-space:normal;
}

.action-btns{
display:flex;
gap:8px;
flex-wrap:wrap;
}

.btn-approve,
.btn-reject{
display:inline-flex;
align-items:center;
gap:6px;
border:none;
padding:8px 16px;
border-radius:30px;
font-weight:600;
font-size:12.5px;
cursor:pointer;
transition:.2s ease;
}

.btn-approve{
background:linear-gradient(135deg,#11998e,#38ef7d);
color:#fff;
}

.btn-approve:hover{
transform:translateY(-2px);
box-shadow:0 8px 18px rgba(17,153,142,.3);
color:#fff;
}

.btn-reject{
background:linear-gradient(135deg,#e8063c,#c40530);
color:#fff;
}

.btn-reject:hover{
transform:translateY(-2px);
box-shadow:0 8px 18px rgba(232,6,60,.3);
color:#fff;
}

.empty-state{
padding:70px 20px;
text-align:center;
}

.empty-state i{
font-size:75px;
color:#d8d8d8;
}

@media(max-width:768px){

.students-header{
flex-direction:column;
align-items:flex-start;
}

.header-right{
width:100%;
justify-content:space-between;
}

.header-title{
font-size:22px;
}

.students-table{
min-width:800px;
}

}

</style>

<div class="students-container">

<div class="students-header">

<div class="header-left">

<div class="header-icon">
<i class="bi bi-signpost-split-fill"></i>
</div>

<div>
<h2 class="header-title">Course Change Suggestions</h2>
<p class="header-subtitle">
Review and approve or reject suggestions submitted by teachers
</p>
</div>

</div>

<div class="header-right">

<span class="pending-badge">
<i class="bi bi-hourglass-split me-1"></i>
Pending : <?= $pending_count ?>
</span>

<span class="subject-badge">
<i class="bi bi-chat-left-text-fill me-1"></i>
Total : <?= $total_suggestions ?>
</span>

</div>

</div>

<?php if ($flash_success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
<i class="bi bi-check-circle-fill me-2"></i>
<?= htmlspecialchars($flash_success) ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($flash_error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
<i class="bi bi-exclamation-circle-fill me-2"></i>
<?= htmlspecialchars($flash_error) ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ===================== FILTER TABS ===================== -->

<div class="filter-tabs">
<a href="?page=admin_course_suggestions.php&status=pending" class="<?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
<a href="?page=admin_course_suggestions.php&status=approved" class="<?= $status_filter === 'approved' ? 'active' : '' ?>">Approved</a>
<a href="?page=admin_course_suggestions.php&status=rejected" class="<?= $status_filter === 'rejected' ? 'active' : '' ?>">Rejected</a>
<a href="?page=admin_course_suggestions.php&status=all" class="<?= $status_filter === 'all' ? 'active' : '' ?>">All</a>
</div>

<!-- ===================== SUGGESTIONS TABLE ===================== -->

<?php if ($total_suggestions > 0): ?>

<div class="card">

<div class="card-body p-0">

<div class="table-responsive">

<table class="students-table">

<thead>
<tr>
<th width="50">Sr</th>
<th>Teacher</th>
<th>Subject</th>
<th>Grade</th>
<th>Suggestion</th>
<th>Status</th>
<th>Submitted On</th>
<th width="220">Action</th>
</tr>
</thead>

<tbody>

<?php
$i = 1;
while ($row = $result->fetch_assoc()):

    $status = strtolower($row['status'] ?? 'pending');

    $statusClass = match($status){
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        default    => 'status-pending',
    };

    $statusIcon = match($status){
        'approved' => 'bi-check-circle-fill',
        'rejected' => 'bi-x-circle-fill',
        default    => 'bi-hourglass-split',
    };
?>

<tr>

<td><strong><?= $i ?></strong></td>

<td>
<?= htmlspecialchars($row['teacher_name'] ?? '—') ?>
</td>

<td>
<?= htmlspecialchars($row['subject_name'] ?? '—') ?>
</td>

<td>
Grade <?= htmlspecialchars($row['grade'] ?? $row['grade_id'] ?? '—') ?>
</td>

<td class="suggestion-text">
<?= nl2br(htmlspecialchars($row['suggestion'])) ?>
</td>

<td>
<span class="status-badge <?= $statusClass ?>">
<i class="bi <?= $statusIcon ?>"></i>
<?= htmlspecialchars(ucfirst($status)) ?>
</span>
</td>

<td>
<?= date("d M Y", strtotime($row['created_at'])) ?>
</td>

<td>
<?php if ($status === 'pending'): ?>
<div class="action-btns">

<form method="POST" action="admin_course_suggestion_action.php" style="display:inline;">
<input type="hidden" name="suggestion_id" value="<?= $row['id'] ?>">
<input type="hidden" name="action" value="approve">
<input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
<button type="submit" class="btn-approve" onclick="return confirm('Approve this suggestion?');">
<i class="bi bi-check-lg"></i> Approve
</button>
</form>

<form method="POST" action="admin_course_suggestion_action.php" style="display:inline;">
<input type="hidden" name="suggestion_id" value="<?= $row['id'] ?>">
<input type="hidden" name="action" value="reject">
<input type="hidden" name="status_filter" value="<?= htmlspecialchars($status_filter) ?>">
<button type="submit" class="btn-reject" onclick="return confirm('Reject this suggestion?');">
<i class="bi bi-x-lg"></i> Reject
</button>
</form>

</div>
<?php else: ?>
<span class="text-muted small">No action needed</span>
<?php endif; ?>
</td>

</tr>

<?php
$i++;
endwhile;
?>

</tbody>

</table>

</div>

</div>

</div>

<?php else: ?>

<div class="empty-state">

<i class="bi bi-signpost-split"></i>

<h3 class="mt-4">No Suggestions Found</h3>

<p class="text-muted">
Suggestions submitted by teachers will appear here.
</p>

</div>

<?php endif; ?>

</div>

<?php
$stmt->close();
?>
