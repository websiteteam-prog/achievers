<?php
include '../db_config.php';

$status_filter = $_GET['status'] ?? 'all';
$allowed = ['active', 'inactive', 'all'];
if (!in_array($status_filter, $allowed)) {
    $status_filter = 'all';
}

if ($status_filter === 'all') {
    $sql = "SELECT * FROM teachers WHERE status != 'deleted' ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);
} else {
    $stmt = $conn->prepare("SELECT * FROM teachers WHERE status = ? ORDER BY id DESC");
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

$total_teachers = $result->num_rows;

$active_res = mysqli_query($conn, "SELECT COUNT(*) AS c FROM teachers WHERE status='active'");
$active_count = mysqli_fetch_assoc($active_res)['c'] ?? 0;
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
background:linear-gradient(135deg,#11998e,#38ef7d);
color:#fff;
padding:8px 18px;
border-radius:40px;
font-weight:600;
font-size:13px;
box-shadow:0 5px 15px rgba(17,153,142,.25);
}

.btn-add-teacher{
background:linear-gradient(135deg,#11998e,#38ef7d);
color:#fff;
padding:9px 20px;
border-radius:40px;
font-weight:600;
font-size:13.5px;
text-decoration:none;
box-shadow:0 5px 15px rgba(17,153,142,.25);
transition:.2s;
display:inline-flex;
align-items:center;
gap:6px;
border:none;
}

.btn-add-teacher:hover{
transform:translateY(-2px);
color:#fff;
box-shadow:0 8px 18px rgba(17,153,142,.35);
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
white-space:nowrap;
}

.students-table td{
padding:16px 15px;
border-bottom:1px solid #edf1f7;
vertical-align:middle;
font-size:14.5px;
color:#374151;
white-space:nowrap;
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

.status-approved{
background:#d9f3ff;
color:#0c7abf;
}

.status-rejected{
background:#f1f1f1;
color:#6b7280;
}

.action-btns{
display:flex;
gap:8px;
flex-wrap:wrap;
}

.btn-approve,
.btn-reject,
.btn-edit-teacher{
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
text-decoration:none;
}

.btn-edit-teacher{
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
}

.btn-edit-teacher:hover{
transform:translateY(-2px);
box-shadow:0 8px 18px rgba(30,60,114,.3);
color:#fff;
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
<i class="bi bi-person-badge"></i>
</div>

<div>
<h2 class="header-title">Manage Teachers</h2>
<p class="header-subtitle">
View, add, and manage teacher accounts
</p>
</div>

</div>

<div class="header-right">

<span class="pending-badge">
<i class="bi bi-check-circle-fill me-1"></i>
Active : <?= $active_count ?>
</span>

<span class="subject-badge">
<i class="bi bi-people-fill me-1"></i>
Total : <?= $total_teachers ?>
</span>

<a href="javascript:void(0)" class="btn-add-teacher menu-link" data-page="add_teacher.php">
<i class="bi bi-plus-circle"></i> Add Teacher
</a>

</div>

</div>

<!-- ===================== FILTER TABS ===================== -->

<div class="filter-tabs">
<a href="?page=manage_teachers.php&status=all" class="<?= $status_filter === 'all' ? 'active' : '' ?>">All</a>
<a href="?page=manage_teachers.php&status=active" class="<?= $status_filter === 'active' ? 'active' : '' ?>">Active</a>
<a href="?page=manage_teachers.php&status=inactive" class="<?= $status_filter === 'inactive' ? 'active' : '' ?>">Inactive</a>
</div>

<!-- ===================== TEACHERS TABLE ===================== -->

<?php if ($total_teachers > 0): ?>

<div class="card">

<div class="card-body p-0">

<div class="table-responsive">

<table class="students-table">

<thead>
<tr>
<th width="50">Sr</th>
<th>Name</th>
<th>Email</th>
<th>Subject</th>
<th>Status</th>
<th>Created</th>
<th width="240">Action</th>
</tr>
</thead>

<tbody>

<?php $i = 1;
while ($row = mysqli_fetch_assoc($result)):

    $status = strtolower($row['status'] ?? 'inactive');
    $statusClass = $status === 'active' ? 'status-approved' : 'status-rejected';
    $statusIcon  = $status === 'active' ? 'bi-check-circle-fill' : 'bi-dash-circle-fill';
?>

<tr id="row-<?= $row['id'] ?>">

<td class="sr-no"><strong><?= $i++ ?></strong></td>

<td><?= htmlspecialchars($row['name']) ?></td>

<td><?= htmlspecialchars($row['email']) ?></td>

<td><?= htmlspecialchars($row['subject']) ?></td>

<td>
<span class="status-badge <?= $statusClass ?>">
<i class="bi <?= $statusIcon ?>"></i>
<?= htmlspecialchars(ucfirst($status)) ?>
</span>
</td>

<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>

<td>
<div class="action-btns">

<a href="javascript:void(0)"
    class="btn-edit-teacher menu-link"
    data-page="edit_teacher.php?id=<?= $row['id'] ?>">
    <i class="bi bi-pencil"></i> Edit
</a>

<?php if ($status === 'active'): ?>
<button class="status-btn btn-reject" data-id="<?= $row['id'] ?>" data-status="inactive">
<i class="bi bi-x-lg"></i> Deactivate
</button>
<?php else: ?>
<button class="status-btn btn-approve" data-id="<?= $row['id'] ?>" data-status="active">
<i class="bi bi-check-lg"></i> Activate
</button>
<?php endif; ?>

<button class="delete-btn btn-reject" data-id="<?= $row['id'] ?>">
<i class="bi bi-trash"></i> Delete
</button>

</div>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php else: ?>

<div class="empty-state">
<i class="bi bi-person-badge"></i>
<h3 class="mt-4">No Teachers Found</h3>
<p class="text-muted">Teachers you add will appear here.</p>
</div>

<?php endif; ?>

</div>

<script>
// Baaki rows ke Sr number dobara set karo (1,2,3...)
function renumberTeacherRows() {
    $('.students-table tbody tr').each(function (index) {
        $(this).find('.sr-no strong').text(index + 1);
    });
}

$(document).off('click', '.delete-btn').on('click', '.delete-btn', function() {

    let teacherId = $(this).data('id');

    if (confirm('Are you sure you want to delete this teacher?')) {

        $.ajax({
            url: 'Remove_teacher.php',
            type: 'POST',
            data: { id: teacherId },
            success: function(res) {
                let result = JSON.parse(res);

                if (result.status) {
                    $('#row-' + teacherId).fadeOut(300, function() {
                        $(this).remove();

                        // ✅ Serial numbers fix (bina refresh ke)
                        renumberTeacherRows();

                        // ✅ Total badge bhi update kar do
                        let n = $('.students-table tbody tr').length;
                        $('.subject-badge').html('<i class="bi bi-people-fill me-1"></i> Total : ' + n);

                        // agar koi teacher bacha hi nahi to empty-state dikhane ke liye reload
                        if (n === 0) { location.reload(); }
                    });
                } else {
                    alert(result.message || 'Unable to delete teacher.');
                }
            }
        });

    }

});

$(document).off('click', '.status-btn').on('click', '.status-btn', function() {

    let id = $(this).data('id');
    let status = $(this).data('status');

    $.ajax({
        url: 'update_teacher_status.php',
        type: 'POST',
        data: { id: id, status: status },
        success: function(res) {
            let result = JSON.parse(res);

            if (result.status) {
                $('.menu-link.active').trigger('click');
            } else {
                alert(result.message || 'Unable to update teacher status.');
            }
        }
    });

});
</script>