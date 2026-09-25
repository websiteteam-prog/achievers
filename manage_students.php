    <?php
    session_start();
    if (!isset($_SESSION['teacher_id'])) {
        echo "<script>window.location.href='teacher_login.php';</script>";
        exit;
    }
    include 'db_config.php';
    $teacher_id = (int)$_SESSION['teacher_id'];

    $sql = "
    SELECT DISTINCT

        s.id,
        si.image_path,
        CONCAT(s.first_name,' ',IFNULL(s.last_name,'')) AS name,
        s.email,

        CASE
            WHEN ei.payment_by='Guardian' THEN ei.guardian_phone
            WHEN ei.payment_by='Mother' THEN ei.mother_phone
            WHEN ei.payment_by='Father' THEN ei.father_phone
            ELSE s.phone
        END AS phone,

        s.gender,
        s.dob,
        ei.enroll_date,
        ei.enrolled_by

    FROM teacher_subjects ts

    INNER JOIN student_subjects ss
        ON ts.subject_id = ss.subject_id

    INNER JOIN students s
        ON s.id = ss.student_id

    LEFT JOIN enrollment_inquiries ei
        ON ei.student_id = s.id

    LEFT JOIN student_images si
        ON si.student_id = s.id

    WHERE
        ts.teacher_id = ?
        AND s.status = 1

    ORDER BY s.first_name
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_students = $result->num_rows;
    ?>
    <style>
    
    .students-container{
        padding:10px;
        background:transparent;
        border-radius:20px;
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
    .btn-add-student{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:linear-gradient(135deg,#e8063c,#c40530);
    color:#fff;
    border:none;
    padding:10px 22px;
    border-radius:40px;
    font-weight:600;
    font-size:14px;
    text-decoration:none;
    box-shadow:0 8px 20px rgba(232,6,60,.25);
    transition:.25s ease;
    cursor:pointer;
    }
    .btn-add-student:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 26px rgba(232,6,60,.32);
    color:#fff;
    }

    .card{
        border:none;
        border-radius:0px;
        background:#fff;
        box-shadow:0 10px 30px rgba(17,24,39,.08);
        overflow:hidden;
        animation:fadeInUp .4s ease;
        font-weight:600;
    }

    .card-body{
        padding:0;
        background:#fff;
    }

    .table-responsive{
        background:#fff;
        /* border-radius:20px; */
        overflow-x:auto;
        overflow-y:hidden;
        margin:0;
    }

    .students-table{

        width:100%;

        min-width:1200px;  

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
        transition:.25s ease;
    }

    .students-table tbody tr:hover{
        background:#eef6ff;
        transform:scale(1.002);
    }

    .students-table tbody tr:hover td{
        background:#f7fbff;
    }

    .student-cell{
    display:flex;
    align-items:center;
    gap:12px;
    }
    .avatar{
    width:54px;
    height:54px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    font-weight:700;
    overflow:hidden;
    }

    .student-photo{
    width:54px;
    height:54px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #fff;
    box-shadow:0 3px 12px rgba(0,0,0,.18);
    }
    .gender-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 14px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
    }
    .gender-male{
    background:#d9f3ff;
    color:#0c7abf;
    }
    .gender-female{
    background:#ffe0ef;
    color:#d63384;
    }
    .btn-delete-student{
    border-radius:30px;
    padding:10px 22px;
    font-size:13px;
    font-weight:600;
    border:none;
    background:#fdeaea;
    color:#c40530;
    transition:.2s ease;
    display:inline-flex;
    }
    .btn-delete-student:hover{
    background:#c40530;
    color:#fff;
    transform:translateY(-1px);
    }
    .empty-state{
    padding:70px 20px;
    text-align:center;
    }
    .empty-state i{
    font-size:75px;
    color:#d8d8d8;
    }
    /* Modal styling to match theme */
    #addStudentModal .modal-content{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 20px 50px rgba(17,24,39,.25);
    height:95vh;
    }

    #addStudentModal .modal-body{
    padding:0;
    overflow:hidden;
    }
    #addStudentModal .modal-header{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:#fff;
    border:none;
    padding:18px 25px;
    }
    #addStudentModal .modal-title{
    font-weight:700;
    letter-spacing:.2px;
    display:flex;
    align-items:center;
    gap:10px;
    }
    #addStudentModal .btn-close{
    filter:brightness(0) invert(1);
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
    min-width:850px;
    }
    }
    </style>

    <div class="students-container">

    <div class="students-header">
    <div class="header-left">
    <div class="header-icon">
    <i class="bi bi-people-fill"></i>
    </div>
    <div>
    <h2 class="header-title">Manage Students</h2>
    <p class="header-subtitle">
    <?= $total_students ?> student<?= $total_students==1?'':'s' ?> in the system
    </p>
    </div>
    </div>
    <div class="header-right">
    <span class="subject-badge">
    <i class="bi bi-mortarboard-fill me-1"></i>
    Total : <?= $total_students ?>
    </span>
    <button type="button" class="btn-add-student" data-bs-toggle="modal" data-bs-target="#addStudentModal">
    <i class="bi bi-person-plus"></i>
    Add Student
    </button>
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
    <th>Gender</th>
    <th>DOB</th>
    <th>Enroll Date</th>
    <!-- <th>Enrolled By</th> -->
    <th width="120">Action</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 1;
    while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
    <td>
    <strong><?= $i ?></strong>
    </td>
    <td>
    <div class="student-cell">
    <?php if(!empty($row['image_path'])): ?>

    <img
    src="Student_dashboard/<?= htmlspecialchars($row['image_path']) ?>"
    class="student-photo"
    alt="Student">

    <?php else: ?>

    <div class="avatar">
    <?= strtoupper(substr($row['name'],0,1)) ?>
    </div>

    <?php endif; ?>
    <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
    </div>
    </td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td><?= htmlspecialchars($row['phone']) ?></td>
    <td>
    <?php if (strtolower($row['gender']) == "male") { ?>
    <span class="gender-badge gender-male">
    <i class="bi bi-gender-male"></i>
    Male
    </span>
    <?php } else { ?>
    <span class="gender-badge gender-female">
    <i class="bi bi-gender-female"></i>
    Female
    </span>
    <?php } ?>
    </td>
    <td><?= htmlspecialchars($row['dob']) ?></td>
    <td>
    <?= !empty($row['enroll_date'])
            ? date("d M Y", strtotime($row['enroll_date']))
            : "-" ?>
    </td>

    <!-- <td>
    <?= ucfirst($row['enrolled_by'] ?? '-') ?>
    </td> -->
    <td>
    <form method="POST" action="delete_student.php" onsubmit="return confirm('Delete this student?');">
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <button type="submit" class="btn-delete-student">
    <i class="bi bi-trash me-1"></i>
    Delete
    </button>
    </form>
    </td>
    </tr>
    <?php
    $i++;
    }
    ?>
    </tbody>
    </table>
    </div>
    </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
    <i class="bi bi-people"></i>
    <h3 class="mt-4">No Students Found</h3>
    <p class="text-muted">Students added to the system will appear here.</p>
    </div>
    <?php endif; ?>

    </div>

    <!-- Add Student Modal: loads the real enrollment form in an isolated iframe -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
            <i class="bi bi-person-plus-fill"></i>
            Enroll New Student
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-0 overflow-hidden">
        <iframe
                id="enrollFrame"
                src="about:blank"
                style="
                width:100%;
                height:calc(95vh - 72px);
                border:0;
                display:block;
                overflow:hidden;
                ">
                </iframe>
        </div>
        </div>
    </div>
    </div>

    <script>
        $('#addStudentModal').on('show.bs.modal', function () {
        document.getElementById('enrollFrame').src = 'teacher_enroll_student.php';
        });
        $('#addStudentModal').on('hidden.bs.modal', function () {
        document.getElementById('enrollFrame').src = 'about:blank';
        });
    </script>