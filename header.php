<style>
/* ================= HEADER RESET (ONLY HEADER RELATED) ================= */
.site-header *{
  box-sizing:border-box;
}

/* ================= HEADER – DESKTOP ================= */
.site-header{
  background:#fff;
  position:relative;
  z-index:9999;
}

/* LOGO */
.logo-area{
  text-align:center;
  padding:10px 0;
}
.logo-area img{
  max-height:55px;
}

/* NAVBAR */
.main-nav{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:30px;
  padding:10px 40px;
}

/* MENU */
.nav-menu{
  list-style:none;
  display:flex;
  gap:30px;
  margin:0;
  padding:0;
}

.nav-menu li{
  position:relative;
}

.nav-menu a{
  text-decoration:none;
  color:#000;
  font-weight:600;
  font-size:16px;
  padding:10px 0;
  position:relative;
}

.nav-menu a:hover{
  color:#e60023;
}

/* UNDERLINE */
.nav-menu a::after{
  content:"";
  position:absolute;
  left:0;
  bottom:-6px;
  width:0;
  height:2px;
  background:#e60023;
  transition:.3s;
}
.nav-menu a:hover::after{
  width:100%;
}

/* DROPDOWN */
.has-dropdown:hover .dropdown{
  opacity:1;
  visibility:visible;
}

.dropdown{
  position:absolute;
  top:38px;
  left:50%;
  transform:translateX(-50%);
  background:#fff;
  width:240px;
  padding:18px;
  list-style:none;
  box-shadow:0 10px 30px rgba(0,0,0,.15);
  opacity:0;
  visibility:hidden;
  transition:.25s;
  z-index:99999;
}

.dropdown li{
  margin-bottom:10px;
}
.dropdown li:last-child{
  margin-bottom:0;
}

/* LOGIN BUTTON */
.login-btn{
  background:#e60023;
  color:#fff;
  padding:10px 36px;
  border-radius:25px;
  text-decoration:none;
  font-weight:600;
  white-space:nowrap;
}
.login-btn:hover{
  background:#009fb5;
}

/* SCHEDULE ASSESSMENT BUTTON (NEW) */
.schedule-btn{
  background:#2e9e74;
  color:#fff;
  padding:10px 26px;
  border-radius:25px;
  text-decoration:none;
  font-weight:600;
  white-space:nowrap;
}
.schedule-btn:hover{
  background:#05364d;
}

/* ================= MOBILE HEADER ================= */
.mobile-top-bar{
  display:none;
}

.mobile-sidebar,
.sidebar-overlay{
  display:none;
}

@media(max-width:768px){

  .logo-area,
  .main-nav{
    display:none;
  }

  .mobile-top-bar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 15px;
    background:#fff;
    position:relative;
    z-index:10000;
  }

  .mobile-logo img{
    height:45px;
  }

  .mobile-icons{
    display:flex;
    gap:18px;
  }

  .hamburger{
    font-size:26px;
    cursor:pointer;
  }

  .mobile-sidebar{
    display:block;
    position:fixed;
    top:0;
    left:-280px;
    width:260px;
    height:100vh;
    background:#fff;
    padding:25px 20px;
    transition:.35s;
    z-index:10001;
    box-shadow:2px 0 20px rgba(0,0,0,.2);
  }

  .mobile-sidebar.active{
    left:0;
  }

  .close-btn{
    font-size:30px;
    cursor:pointer;
    text-align:right;
    margin-bottom:25px;
  }

  .mobile-menu{
    list-style:none;
    padding:0;
  }

  .mobile-menu li{
    margin-bottom:15px;
  }

  .mobile-menu a{
    text-decoration:none;
    color:#000;
    font-weight:600;
  }

  .mobile-dropdown ul{
    display:none;
    padding-left:15px;
    margin-top:8px;
  }

  .mobile-dropdown.open ul{
    display:block;
  }

  .sidebar-overlay{
    display:block;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.5);
    z-index:10000;
    opacity:0;
    visibility:hidden;
    transition:.3s;
  }

  .sidebar-overlay.active{
    opacity:1;
    visibility:visible;
  }
}
.mobile-login-btn {
    width: 79px;
    display: block;
    margin-top: 11px;
    text-align: left;
    background: #e60023;
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
}
.mobile-login-btn:hover{
  background:#009fb5;
}

/* MOBILE SCHEDULE BUTTON (NEW) */
.mobile-schedule-btn{
    display:block;
    margin-top:12px;
    text-align:center;
    background:#2e9e74;
    color:#fff;
    padding:8px 14px;
    border-radius:20px;
    text-decoration:none;
    font-weight:600;
}
.mobile-schedule-btn:hover{
  background:#05364d;
}

/* ===== LOGIN MODAL ===== */
.login-modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.55);
  backdrop-filter:blur(4px);
  display:flex;
  justify-content:center;
  align-items:center;
  opacity:0;
  visibility:hidden;
  transition:.3s ease;
  z-index:99999;
}

.login-modal.active{
  opacity:1;
  visibility:visible;
}

.login-card{
  width:100%;
  max-width:340px;
  background:#fff;
  border-radius:26px;
  overflow:hidden;
  box-shadow:0 25px 60px rgba(0,0,0,.3);
  transform:translateY(40px) scale(.95);
  opacity:0;
  transition:.35s ease;
}

.login-modal.active .login-card{
  transform:translateY(0) scale(1);
  opacity:1;
}

.close-login{
  position:absolute;
  top:15px;
  right:15px;
  width:38px;
  height:38px;
  border-radius:50%;
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:20px;
  font-weight:600;
  color:#2f55a4;
  cursor:pointer;
  box-shadow:0 5px 15px rgba(0,0,0,0.15);
  transition:.3s;
  z-index:20;
}

.close-login:hover{
  background:#e02121;
  color:#fff;
  transform:rotate(90deg);
}

/* Top Section */
.login-card .top{
  position:relative;
  height:250px;
  background:#2f55a4;
  display:flex;
  justify-content:center;
  align-items:flex-end;
}

.login-card .top img{
  width:280px;
}

.login-card .content{
  padding:25px;
}

.login-card .content h2{
  font-family:"Love Ya Like A Sister", cursive;
  font-weight:400;
  font-size:20px;
  margin-bottom:6px;
}

.login-card .content p{
  color:#777;
  margin-bottom:18px;
}

.login-card .btn{
  width:100%;
  padding:14px;
  border-radius:16px;
  font-weight:600;
  cursor:pointer;
  margin-bottom:14px;
   display:block;     
  text-align:center;  
  text-decoration:none; /
}

.login-card .student{
  background:#e02121;
  color:#fff;
  border:none;
}

.login-card .teacher{
  background:#fff;
  border:2px solid #2f55a4;
  color:#2f55a4;
}
</style>

<header class="site-header">
  <div class="logo-area">
    <img src="images/logo1.png" alt="Achiever's Castle">
  </div>

  <nav class="main-nav">
    <ul class="nav-menu">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About Us</a></li>
      <li class="has-dropdown">
        <a href="class.php">Programs ▾</a>
        <ul class="dropdown">
          <li><a href="early_learner.php">Early Starters</a></li>
          <li><a href="elementary.php">Elementary</a></li>
          <li><a href="advance_learner.php">Advance Learner</a></li>
        </ul>
      </li>
      <li><a href="blog.php">Blogs</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>

    <!-- NEW: Schedule Appointment Button -->
    <a href="schedule_appointment.php" class="schedule-btn">Schedule Appointment</a>

    <a href="#" class="login-btn" id="openLoginModal">Login</a>
     <a href="./enroll_query.php" style="
    background-color: #d6b125;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 30px;
    display: inline-block;
    font-size: 16px;
}
">
Enroll Now
</a>
  </nav>

  <div class="mobile-top-bar">
    <div class="mobile-logo">
      <img src="images/logo1.png">
    </div>

    <!-- NEW: Mobile Schedule Button -->
    <a href="schedule_appointment.php" style="
    background-color: #2e9e74;
    color: white;
    padding: 8px 14px;
    text-decoration: none;
    border-radius: 30px;
    display: inline-block;
    font-size: 12px;
">
Schedule
</a>

     <a href="https://www.achieverscastle.com/enroll_query.php" style="
    background-color: #e60023;
    color: white;
    padding: 8px 19px;
    text-decoration: none;
    border-radius: 30px;
    display: inline-block;
    font-size: 12px;
}
">
Enroll Query
</a>
    <div class="mobile-icons">
      <span class="hamburger" id="openMenu">☰</span>
    </div>
  </div>
</header>

<div class="mobile-sidebar" id="mobileSidebar">
  <span class="close-btn" id="closeMenu">×</span>

  <ul class="mobile-menu">
    <li><a href="index.php">Home</a></li>
    <li><a href="about.php">About</a></li>
    <li><a href="contact.php">Contact</a></li>
  </ul>

  <!-- NEW: Schedule Button in sidebar -->
  <a href="schedule_appointment.php" class="mobile-schedule-btn">Schedule Appointment</a>

  <!-- LOGIN BUTTON -->
  <a href="#" class="mobile-login-btn" id="openLoginModalMobile">Login</a>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="login-modal" id="loginModal">
  <div class="login-card">
    <span class="close-login" id="closeLoginModal">✕</span>

    <div class="top">
      <img src="images/illustration.png" alt="Illustration">
    </div>

   <div class="content">
  <h2>Welcome to Achiever's Castle</h2>
  <p>Login as a</p>

  <a href="Student_dashboard/student_login.php" class="btn student">Student</a>
  <a href="teacher_login.php" class="btn teacher">Teacher</a>
</div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

  // Sidebar
  const openMenu = document.getElementById("openMenu");
  const closeMenu = document.getElementById("closeMenu");
  const sidebar = document.getElementById("mobileSidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if(openMenu){
    openMenu.onclick = () => {
      sidebar.classList.add("active");
      overlay.classList.add("active");
    };
  }

  if(closeMenu){
    closeMenu.onclick = overlay.onclick = () => {
      sidebar.classList.remove("active");
      overlay.classList.remove("active");
    };
  }
const openLoginMobile = document.getElementById("openLoginModalMobile");

if(openLoginMobile){
  openLoginMobile.addEventListener("click", function(e){
    e.preventDefault();
    sidebar.classList.remove("active");
    overlay.classList.remove("active");

    loginModal.classList.add("active");
    document.body.style.overflow="hidden";
  });
}
  // Login Modal
  const openLogin = document.getElementById("openLoginModal");
  const closeLogin = document.getElementById("closeLoginModal");
  const loginModal = document.getElementById("loginModal");

  if(openLogin){
    openLogin.addEventListener("click", function(e){
      e.preventDefault();
      loginModal.classList.add("active");
      document.body.style.overflow="hidden";
    });
  }

  if(closeLogin){
    closeLogin.addEventListener("click", function(){
      loginModal.classList.remove("active");
      document.body.style.overflow="auto";
    });
  }

  if(loginModal){
    loginModal.addEventListener("click", function(e){
      if(e.target === loginModal){
        loginModal.classList.remove("active");
        document.body.style.overflow="auto";
      }
    });
  }

});
</script>