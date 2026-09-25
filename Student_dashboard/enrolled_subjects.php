<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int) $_SESSION['student_id'];   // ✅ YE LINE WAAPAS ADD KARO

$currentMonth = date('Y-m');

/* Current month ki SABSE LATEST (date-wise) invoice utha lo.
   Upgrade hone pe nayi invoice hi latest hogi -> usi ki status decide karegi. */
$stmtInvoice = $conn->prepare("
    SELECT invoice_number, total, status, due_date, invoice_date
    FROM invoices
    WHERE student_id = ?
      AND DATE_FORMAT(invoice_date, '%Y-%m') = ?
      AND status != 'Cancelled'
    ORDER BY invoice_date DESC, id DESC
    LIMIT 1
");
$stmtInvoice->bind_param("is", $student_id, $currentMonth);
$stmtInvoice->execute();
$invoice = $stmtInvoice->get_result()->fetch_assoc();

$canAccessSubjects = false;

// Sirf LATEST invoice paid ho to hi unlock
if ($invoice && strtolower($invoice['status']) === 'paid') {
    $canAccessSubjects = true;
}


$sql = "
SELECT 
    s.id AS subject_id,
    s.subject_name,
    st.grade,
    c.image AS course_image
FROM student_subjects ss
JOIN subjects s 
    ON ss.subject_id = s.id
JOIN students st
    ON ss.student_id = st.id
LEFT JOIN courses c
    ON c.subject_id = s.id
WHERE ss.student_id = ?
GROUP BY s.id
ORDER BY s.subject_name ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>

<title>My Enrolled Courses</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="student.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
  
<style>


/* BODY */
body {
    background: #f5f7fb;
    margin: 0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}


/* MAIN LAYOUT */
.main-layout {
    display: flex;
    min-height: 100vh;
}


/* CONTENT AREA FULL WIDTH FIX */
.content-area {

    margin-left: 260px;
    padding: 30px;

    width: calc(100% - 260px);
    max-width: calc(100% - 260px);

}

.page-title{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:30px;
}

.icon-style{
    font-size:50px;
    line-height:1;
    margin:0 !important;
    background:linear-gradient(to right,#e02121,#2f55a4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    color:transparent;
    flex-shrink:0;
}

.page-title h3{
    margin:0 !important;
    font-size:42px;
    font-weight:400;
    background:linear-gradient(to right,#e02121,#2f55a4);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
    color:transparent;
    font-family:"Love Ya Like A Sister", cursive;
}

/* CARD */
.card {

    background: #fff !important;
    border: 1px solid #e9ecef;
    border-radius: 12px;

}

.payment-lock-icon{
    font-size:70px;
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display:inline-block;
}

.payment-title{
    font-weight:700;
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* IMAGE */
.course-img{
    width:280px;
    height:158px;
    object-fit:cover;
    border-radius:10px;
    display:block;
}

.table{
    table-layout: fixed;
    width:100%;
}

.table th:nth-child(1),
.table td:nth-child(1){
    width:380px;
}

.table th:nth-child(2),
.table td:nth-child(2){
    width:240px;
    text-align:left;
}

.table th:nth-child(3),
.table td:nth-child(3){
    width:120px;
    text-align:center;
}
/* TABLE RESPONSIVE */
.table-responsive {

    overflow-x: auto;
    -webkit-overflow-scrolling: touch;

}


/* TABLE DEFAULT */
.table {

    background: #fff !important;
    margin-bottom: 0;

}

.table thead,
.table thead th {
    background: #fff !important;
    color: var(--primary);
}

.table tbody tr,
.table tbody td {
    background: #fff !important;
}

.table-hover tbody tr:hover td {
    background: #f8f9fc !important;
}

.page-subtitle{
    color:#6b7280;
    margin-top:-6px;
    font-size:16px;
}

/* .container-fluid h3{
   font-size: 42px;
   font-weight: 400;
   margin-bottom: 6px !important;
   background: linear-gradient(to right, #e02121, #2f55a4);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   font-family: "Love Ya Like A Sister", cursive;
   margin-left: 8px;
} */

/* SCROLL ONLY BELOW 1280px */
@media (max-width:1280px) {

    .table {

        min-width: 900px;

    }

}


/* TABLET */
@media (max-width:992px) {

    .content-area {

        margin-left: 0;
        width: 100%;
        max-width: 100%;
        padding: 20px;
        padding-top: 80px;

    }

}


/* MOBILE */
@media (max-width:576px) {

    .content-area {

        padding: 15px;
        padding-top: 75px;

    }

    .course-img {

        width: 100px;
        height: 65px;

    }

        .page-title{
        gap:12px;
        margin-bottom:20px;
    }

    .icon-style{
        font-size:38px;
    }

    .page-title h3{
        font-size:30px;
        margin-top:27px !important;
    }

    .page-subtitle{
    font-size:13px;
    }
}


/* SIDEBAR TOGGLE */
#sidebarToggle {

    top: 15px;
    left: 15px;
    z-index: 1100;
    border-radius: 50%;
    width: 48px;
    height: 48px;

}

@media (max-width:768px){

    .table{
        table-layout:auto;
        min-width:unset;
    }

    .table th:nth-child(1),
    .table td:nth-child(1),
    .table th:nth-child(2),
    .table td:nth-child(2),
    .table th:nth-child(3),
    .table td:nth-child(3){
        width:auto;
    }

    .course-img{
        width:110px;
        height:70px;
    }
}

</style>

</head>

<body>

<div class="main-layout">

    <!-- SIDEBAR -->
    <?php include 'student_sidebar.php'; ?>


    <!-- MOBILE BUTTON -->
    <button class="btn btn-primary d-lg-none position-fixed"
        id="sidebarToggle">

        <i class="bi bi-list fs-4"></i>

    </button>


    <!-- CONTENT -->
    <div class="content-area container-fluid">
    <div class="page-title">

    <i class="bi bi-book icon-style"></i>

    <div>

        <h3 class="mb-0">
            My Enrolled Courses
        </h3>

        <div class="page-subtitle">
            Access your enrolled subjects and continue your learning journey.
        </div>

    </div>

</div>
        <?php if($canAccessSubjects): ?>

    <?php if(mysqli_num_rows($result)>0): ?>

        <div class="card shadow-sm">

            <div class="card-body">

                <!-- TABLE SCROLL WRAPPER -->
                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>Image</th>
                                <th>Subject</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while($row = mysqli_fetch_assoc($result)): ?>

                        <tr>

                            <td>

                                <?php
                                $imgPath = !empty($row['course_image']) 
                                           ? '../' . $row['course_image']
                                           : '../images/default-course.jpg';
                                ?>

                                <img src="<?= htmlspecialchars($imgPath); ?>" 
                                     class="course-img">

                            </td>


                            <td>

                                <?= htmlspecialchars($row['subject_name']); ?>

                            </td>
                            <td>

                                <a href="course_sidebar.php?id=<?= urlencode($row['subject_id']); ?>"
                                   class="btn btn-sm btn-primary">

                                   View

                                </a>

                            </td>

                        </tr>

                        <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

            <?php else: ?>

    <div class="alert alert-info">

        You have not enrolled in any courses yet.

    </div>

    <?php endif; ?>

<?php else: ?>

<div class="card shadow border-0 rounded-4">

    <div class="card-body text-center py-5">

       <i class="bi bi-lock-fill payment-lock-icon"></i>

        <h3 class="mt-4 payment-title">
            Payment Pending
        </h3>

        <p class="text-muted mt-3">

            Your subjects are locked because this month's
            payment has not been completed.

        </p>

        <?php if($invoice): ?>

        <div class="alert alert-warning mt-4">

            <h5>

                Invoice Number :
                <strong><?= htmlspecialchars($invoice['invoice_number']) ?></strong>

            </h5>

            <h4 class="mt-3">

                Amount Payable :
                <span class="text-primary">

                    ₹<?= number_format($invoice['total'],2) ?>

                </span>

            </h4>

            <p class="mb-0 mt-2">

                Due Date :
                <?= date("d M Y",strtotime($invoice['due_date'])) ?>

            </p>

        </div>

        <a href="purchase_history.php"
           class="btn btn-primary btn-lg mt-3">

            <i class="bi bi-credit-card me-2"></i>

            Pay Now

        </a>

        <?php else: ?>

        <div class="alert alert-danger mt-4">

            No invoice generated for this month.

            Please contact the administrator.

        </div>

        <?php endif; ?>

    </div>

</div>

<?php endif; ?>


    </div>

</div>


<script>

/* SIDEBAR TOGGLE */
const sidebar = document.getElementById('studentSidebar');

document.getElementById('sidebarToggle')
.addEventListener('click', function(){

    sidebar.classList.toggle('show');

});

</script>

</body>

</html>