<?php
include '../db_config.php';

/*
 | Active students only (status = 1 -> cancelled ones are hidden).
 | Phone + Enroll Date come from the student's LATEST enrollment_inquiries row.
 | Phone = the payer's number, based on payment_by (Guardian / Mother / Father),
 | falling back to the student's own phone.
*/
$sql = "
    SELECT
        s.id,
        s.first_name,
        s.last_name,
        s.email,
        s.phone,
        s.dob,

        (SELECT image_path
           FROM student_images
          WHERE student_id = s.id
          ORDER BY id DESC
          LIMIT 1) AS image_path,

        ei.enroll_date,

        CASE
            WHEN ei.payment_by = 'Guardian' THEN ei.guardian_phone
            WHEN ei.payment_by = 'Mother'   THEN ei.mother_phone
            WHEN ei.payment_by = 'Father'   THEN ei.father_phone
            ELSE s.phone
        END AS payer_phone

    FROM students s

    INNER JOIN enrollment_inquiries ei
        ON  ei.student_id = s.id
        AND ei.student_id IS NOT NULL
        AND ei.student_id <> 0
        AND ei.id = (
            SELECT ei2.id
              FROM enrollment_inquiries ei2
             WHERE ei2.student_id = s.id
             ORDER BY ei2.id DESC
             LIMIT 1
        )

    WHERE s.status = 1
    ORDER BY s.id DESC
";
$result = mysqli_query($conn, $sql);

$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}
$total_students = count($students);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
.students-container{ padding:10px; }
.students-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
    flex-wrap:wrap;
    gap:15px;
}
.header-left{ display:flex; align-items:center; gap:15px; }
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
.header-title{ margin:0; font-size:24px; font-weight:700; color:#1e3c72; letter-spacing:.2px; }
.header-subtitle{ margin:0; font-size:14px; color:#6b7280; }
.header-right{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.subject-badge{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    padding:8px 18px;
    border-radius:40px;
    font-weight:600;
    font-size:13px;
    box-shadow:0 5px 15px rgba(0,0,0,.12);
}
.btn-add-student{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:linear-gradient(135deg,#11998e,#38ef7d);
    color:#fff;
    border:none;
    padding:10px 22px;
    border-radius:40px;
    font-weight:600;
    font-size:14px;
    text-decoration:none;
    box-shadow:0 8px 20px rgba(17,153,142,.25);
    transition:.25s ease;
    cursor:pointer;
}
.btn-add-student:hover{ transform:translateY(-2px); box-shadow:0 12px 26px rgba(17,153,142,.32); color:#fff; }
.card{
    border:none;
    border-radius:0;
    background:#fff;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    overflow:hidden;
    animation:fadeInUp .4s ease;
}
@keyframes fadeInUp{ from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }
.table-responsive{ background:#fff; overflow-x:auto; -webkit-overflow-scrolling:touch; }
.students-table{ width:100%; min-width:900px; border-collapse:collapse; }
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
    padding:14px 15px;
    border-bottom:1px solid #edf1f7;
    vertical-align:middle;
    font-size:14.5px;
    color:#374151;
    white-space:nowrap;
}
.students-table tbody tr{ transition:.2s ease; }
.students-table tbody tr:hover{ background:#f7fbff; }
.student-cell{ display:flex; align-items:center; gap:12px; }
.avatar{
    width:50px;
    height:50px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    font-weight:700;
    font-size:18px;
    overflow:hidden;
    border:3px solid #fff;
    box-shadow:0 3px 12px rgba(0,0,0,.18);
    flex-shrink:0;
}
.student-photo{
    width:50px;
    height:50px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #fff;
    box-shadow:0 3px 12px rgba(0,0,0,.18);
    flex-shrink:0;
}
.action-group{ display:flex; gap:6px; flex-wrap:wrap; }
.btn-action{
    border:none;
    border-radius:8px;
    padding:8px 12px;
    font-size:13px;
    font-weight:600;
    display:inline-flex;
    align-items:center;
    gap:6px;
    cursor:pointer;
    text-decoration:none;
    transition:.2s ease;
}
.btn-edit{ background:#e8f0fe; color:#1e6fd9; }
.btn-edit:hover{ background:#1e6fd9; color:#fff; }
.btn-cancel{ background:#fdeaea; color:#c40530; }
.btn-cancel:hover{ background:#c40530; color:#fff; }
.empty-state{ padding:70px 20px; text-align:center; }
.empty-state i{ font-size:75px; color:#d8d8d8; }

@media(max-width:768px){
    .students-header{ flex-direction:column; align-items:flex-start; }
    .header-right{ width:100%; justify-content:space-between; }
    .header-title{ font-size:22px; }
    .students-table{ min-width:820px; }
}
</style>

<div class="students-container">

    <div class="students-header">

        <div class="header-left">
            <div class="header-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <h2 class="header-title">Manage Students</h2>
                <p class="header-subtitle">
                    <?= $total_students ?> active student<?= $total_students == 1 ? '' : 's' ?> in the system
                </p>
            </div>
        </div>

        <div class="header-right">
            <span class="subject-badge">
                <i class="bi bi-mortarboard-fill me-1"></i>
                Total : <?= $total_students ?>
            </span>

        </div>

    </div>

    <?php if ($total_students > 0): ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th width="60">Sr</th>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>DOB</th>
                            <th>Enroll Date</th>
                            <th width="200">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($students as $row):

                            $name = trim($row['first_name'] . ' ' . ($row['last_name'] ?? ''));
                            if ($name === '') $name = 'Unknown';

                            $phone = $row['payer_phone'] ?? '';
                            $email = $row['email'] ?? '';
                        ?>
                        <tr>

                            <td><strong><?= $i ?></strong></td>

                            <td>
                                <div class="student-cell">
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img src="../Student_dashboard/<?= htmlspecialchars($row['image_path']) ?>"
                                             class="student-photo" alt="Student">
                                    <?php else: ?>
                                        <div class="avatar"><?= strtoupper(substr($name, 0, 1)) ?></div>
                                    <?php endif; ?>
                                    <div class="fw-bold"><?= htmlspecialchars($name) ?></div>
                                </div>
                            </td>

                            <td><?= $email !== '' ? htmlspecialchars($email) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= $phone !== '' ? htmlspecialchars($phone) : '<span class="text-muted">—</span>' ?></td>

                            <td><?= !empty($row['dob']) ? date('d M Y', strtotime($row['dob'])) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= !empty($row['enroll_date']) ? date('d M Y', strtotime($row['enroll_date'])) : '<span class="text-muted">—</span>' ?></td>

                            <td>
                                <div class="action-group">

                                    <a href="dashboard.php?page=edit_student.php?id=<?= $row['id'] ?>"
                                       class="btn-action btn-edit" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>

                                    <form method="POST" action="delete_student.php" style="display:inline;"
                                          onsubmit="return confirm('Cancel this student? The record is kept but hidden from this list.');">
                                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                        <input type="hidden" name="mode" value="cancel">
                                        <button type="submit" class="btn-action btn-cancel" title="Cancel">
                                            <i class="bi bi-slash-circle"></i> Cancel
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php else: ?>

    <div class="empty-state">
        <i class="bi bi-people"></i>
        <h3 class="mt-4">No Students Found</h3>
        <p class="text-muted">Active students will appear here.</p>
    </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>