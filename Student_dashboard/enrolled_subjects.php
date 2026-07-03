<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: ../student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

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
    background: linear-gradient(135deg, #f8f9ff, #e0e7ff);
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


/* CARD */
.card {

    border-radius: 12px;
    border: none;

}


/* IMAGE */
.course-img{
    width:280px;
    height:158px;
    object-fit:cover;
    border-radius:10px;
    display:block;
}

.table th:first-child,
.table td:first-child{
    width:320px;
}

/* TABLE RESPONSIVE */
.table-responsive {

    overflow-x: auto;
    -webkit-overflow-scrolling: touch;

}


/* TABLE DEFAULT */
.table {

    width: 100%;
    white-space: nowrap;

}

.container-fluid h3{
   font-size: 42px;
   font-weight: 400;
   margin-bottom: 6px !important;
   background: linear-gradient(to right, #e02121, #2f55a4);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   font-family: "Love Ya Like A Sister", cursive;
   margin-left: 8px;
}

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

        <h3 class="mb-4">
            My Enrolled Courses
        </h3>


        <?php if(mysqli_num_rows($result) > 0): ?>

        <div class="card shadow-sm">

            <div class="card-body">

                <!-- TABLE SCROLL WRAPPER -->
                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>

                                <th>Image</th>
                                <th>Subject</th>
                                <th>Grade</th>
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

                                <?= htmlspecialchars($row['grade']); ?>

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