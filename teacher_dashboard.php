<?php
 session_start();
  date_default_timezone_set('Asia/Kolkata');
  include 'db_config.php';

  if (!isset($_SESSION['teacher_id'])) {
      header("Location: teacher_login.php");
      exit();
  }

  $teacher_id = $_SESSION['teacher_id'];
  $now = date("Y-m-d H:i:s");
  mysqli_query($conn, "UPDATE teachers SET last_activity = '$now' WHERE id = '$teacher_id'");
  ?>

  <!DOCTYPE html>
  <html>
  <head>
  <title>Teacher Dashboard</title>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <style>

body{
margin:0;
font-family:'Segoe UI',sans-serif;
background:#f4f7fb;
min-height:100vh;
overflow-x:hidden;

}
.main-content {
    padding: 44px;
}
/* Sidebar */
.sidebar{
width:260px;
position:fixed;
top:0;
left:0;
height:100vh;
background:linear-gradient(135deg,#c9d9f5,#e5ddf7);
padding:0 15px;
overflow-y:auto;
overflow-x:hidden;
scrollbar-width:none;
transition:0.3s;
z-index:1000;
}

.sidebar::-webkit-scrollbar{
width:0;
display:none;
}

.sidebar-logo{

margin-top:18px;
margin-bottom:-20px;
}

.sidebar-logo img{
width:150px;
height:100px;
margin-bottom: 23px;
}

.sidebar a{
display:flex;
align-items:center;
gap:10px;
color:#2a5298;
padding:9px 15px;
margin:20px 0;
text-decoration:none;
border-radius:12px;
transition:0.3s;
font-size:15px;
font-weight:500;
}

.sidebar a:hover{
background:rgba(255,255,255,0.15);
transform:translateX(5px);
}

.sidebar a.active{
background:white;
color:#2a5298;
font-weight:bold;
}

.sidebar i{
font-size:18px;
width:22px;
}

/* Wrapper */
.main-wrapper{
  width:calc(100% - 260px);
  margin-left:260px;
  transition:0.3s;
}
/* Header */
.header{
background:white;
padding:15px 25px;
display:flex;
justify-content:space-between;
align-items:center;
box-shadow:0 4px 15px rgba(0,0,0,0.05);
}

/* Hamburger icon */
.menu-toggle{
font-size:24px;
cursor:pointer;
display:none;
margin-right:15px;
}

.header-left{
display:flex;
align-items:center;
}

.header h5{
margin:0;
font-weight:600;
}

.teacher-info{
display:flex;
align-items:center;
gap:10px;
}

.teacher-avatar{
width:40px;
height:40px;
border-radius:50%;
background:#2a5298;
color:white;
display:flex;
align-items:center;
justify-content:center;
}

.teacher-avatar i{
font-size:20px;
}

/* Overlay */
.overlay{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.4);
display:none;
z-index:999;
}

/* Close button */
.sidebar-close{
display:none;
font-size:22px;
cursor:pointer;
text-align:right;
padding:10px;
}

/* MOBILE */
@media(max-width:992px){

.sidebar{
left:-260px;
}

.sidebar.show{
left:0;
}
.invoice-table {
    margin-top: 20px;
}
 .main-wrapper{
    width:100%;
    margin-left:0;
  }
.main-content{
  padding:30px 20px;
}
.menu-toggle{
display:block;
}

.sidebar-close{
display:block;
color:white;
}

.overlay.show{
display:block;
}

}

@media(max-width:425px){
  .teacher{
  display:none;
  }
}

</style>

  </head>
  <body>

  <div class="overlay" id="overlay"></div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">

  <div class="sidebar-close">
  <i class="bi bi-x-lg" id="closeSidebar"></i>
  </div>

  <div class="sidebar-logo">
  <img src="images/logo.png">
  </div>

  <a href="#" class="menu-link active" data-page="dashboard_home.php">
  <i class="bi bi-speedometer2"></i>
  Dashboard
  </a>

  <a href="#" class="menu-link" data-page="calendar.php">
  <i class="bi bi-calendar-event"></i>
  Calendar
  </a>

  <a href="#" class="menu-link" data-page="my_students.php">
  <i class="bi bi-people"></i>
  My Students
  </a>

  <a href="#" class="menu-link" data-page="attendance.php">
  <i class="bi bi-clipboard-check"></i>
  Attendance
  </a>

  <a href="#" class="menu-link" data-page="assign_chapter.php">
  <i class="bi bi-book"></i>
  Assign Chapters
  </a>

  <!--<a href="#" class="menu-link" data-page="invoice_system/dashboard/invoice_dashboard.php">-->
  <!--<i class="bi bi-receipt"></i>-->
  <!--Invoice Dashboard-->
  <!--</a>-->

  <!--<a href="#" class="menu-link" data-page="invoice_system/enroll/admin_enroll_student.php">-->
  <!--<i class="bi bi-person-plus"></i>-->
  <!--Enroll Student-->
  <!--</a>-->

  <!--<a href="#" class="menu-link" data-page="invoice_system/enroll/manage_enrollment.php">-->
  <!--<i class="bi bi-pencil-square"></i>-->
  <!--Manage Enrollment-->
  <!--</a>-->
  
  <!-- <a href="#" class="menu-link" data-page="invoice_system/invoice/invoice_list.php">
  <i class="bi bi-file-earmark-text"></i>
  Invoices
  </a> -->

  <!--<a href="#" class="menu-link" data-page="invoice_system/payments/payment_list.php">-->
  <!--<i class="bi bi-cash-coin"></i>-->
  <!--Payments-->
  <!--</a>-->

  <a href="#" class="menu-link" data-page="suggest_course_changes.php">
  <i class="bi bi-lightbulb"></i>
  Suggest Course Change
  </a>

  <a href="#" class="menu-link" data-page="send_email_updates.php">
  <i class="bi bi-envelope-paper"></i>
  Send Email Update
  </a>

  <a href="#" class="menu-link" data-page="teacher_question_pages/assign_assessment.php">
  <i class="bi bi-file-earmark-plus"></i>
  Assign Assessment
  </a>

  <a href="#" class="menu-link" data-page="teacher_question_pages/manage_assessments.php">
  <i class="bi bi-folder-check"></i>
  Manage Assessments
  </a>

  <a href="#" class="menu-link" data-page="teacher_question_pages/manage_questions.php">
  <i class="bi bi-patch-question"></i>
  Manage Questions
  </a>

  <a href="teacher_logout.php">
  <i class="bi bi-box-arrow-right"></i>
  Logout
  </a>

  </div>


  <!-- Main -->
  <div class="main-wrapper">

  <div class="header">

  <div class="header-left">
  <i class="bi bi-list menu-toggle" id="openSidebar"></i>
  <h5>Teacher Dashboard</h5>
  </div>

  <div class="teacher-info">

  <div class="teacher-avatar">
  <i class="bi bi-person-fill"></i>
  </div>

  <span class="teacher">
    <?php echo $_SESSION['teacher_name'] ?? 'Teacher'; ?>
  </span>

  </div>

  </div>


  <div class="main-content">

  <div class="dashboard-card" id="content-area">
  Loading...
  </div>

  </div>

  </div>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>


  <script>

  $(document).ready(function () {

  /* Sidebar toggle */
  $("#openSidebar").click(function(){
  $("#sidebar").addClass("show");
  $("#overlay").addClass("show");
  });

  $("#closeSidebar, #overlay").click(function(){
  $("#sidebar").removeClass("show");
  $("#overlay").removeClass("show");
  });


  function loadPage(page, addToHistory=true){

  if(!page)return;

  $("#content-area").html("<p>Loading...</p>");

  $.ajax({

  url: page,
  type:"GET",

  success:function(data){

  $("#content-area").html(data);

  $(".menu-link").removeClass("active");

  $('.menu-link[data-page="'+page+'"]').addClass("active");

  if(addToHistory){
history.pushState(
    {page:page},
    "",
    "?page="+page
);  }

  if(typeof initCalendar==="function"){
  setTimeout(function(){
  initCalendar();
  },100);
  }

  /* auto close mobile */
  if(window.innerWidth < 992){
  $("#sidebar").removeClass("show");
  $("#overlay").removeClass("show");
  }

  },

  error:function(){
  $("#content-area").html("<p class='text-danger'>❌ Failed to load page</p>");
  }

  });

  }


  // click menu (delegated so links injected later via AJAX, e.g. inside
  // my_students.php or manage_students.php, respond to clicks too)
  $(document).on("click", ".menu-link", function(e){
  e.preventDefault();
  let page=$(this).data("page");
  loadPage(page,true);
  });


  // browser back
  window.onpopstate=function(event){
  if(event.state && event.state.page){
  loadPage(event.state.page,false);
  }
  };


  // initial load
  let params=new URLSearchParams(window.location.search);

  let page=params.get("page");

  if(!page){
  page="dashboard_home.php";
  history.replaceState({page:page},"","?page="+page);
  }

  loadPage(page,false);

  });

  </script>

  </body>
  </html>