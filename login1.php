<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Educora Login</title>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
}

body{
  background:#eeeeee;
  display:flex;
  justify-content:center;
  align-items:center;
  min-height:100dvh; /* better than 100vh for mobile */
  font-family:'Poppins',sans-serif;
  padding:15px;
}

/* ===== CARD ===== */
.card{
  width:100%;
  max-width:340px;   /* responsive width */
  background:#fff;
  border-radius:26px;
  overflow:hidden;
  box-shadow:0 15px 30px rgba(0,0,0,0.12);
}

/* ===== TOP SECTION ===== */
.top{
  position:relative;
  height:260px;   /* slightly smaller for mobile */
  background:#2f55a4;
  display:flex;
  justify-content:center;
  align-items:flex-end;
}

/* Illustration */
.top img{
  width:95%;
  max-width:310px;
  position:relative;
  bottom:5px;
  z-index:2;
}

/* Wave */
.top svg{
  position:absolute;
  bottom:-1px;
  left:0;
  width:100%;
}

/* ===== CONTENT ===== */
.content{
  padding:25px 22px 35px;
}

.content h2{
  font-size:20px;
  font-weight:400;
  margin-bottom:6px;
  font-family: "Love Ya Like A Sister", cursive;
}

.content p{
  color:#777;
  margin-bottom:18px;
  font-size:14px;
}

/* Buttons */
.btn{
  width:100%;
  padding:14px;
  border-radius:16px;
  font-size:15px;
  font-weight:600;
  cursor:pointer;
  margin-bottom:14px;
  transition:0.3s;
}

/* Red Button */
.student{
  background:#e02121;
  color:#fff;
  border:none;
}

.student:active{
  transform:scale(0.98);
}

/* Blue Border Button */
.teacher{
  background:#fff;
  border:2px solid #2f55a4;
  color:#2f55a4;
}

.teacher:active{
  transform:scale(0.98);
}

/* ===== EXTRA SMALL DEVICES ===== */
@media (max-width:380px){

  .top{
    height:230px;
  }

  .content{
    padding:20px;
  }

  .content h2{
    font-size:18px;
  }

  .btn{
    padding:12px;
  }

}
</style>
</head>
<body>

<div class="card">

  <div class="top">
      <img src="images/illustration.png" alt="Illustration">

      <svg viewBox="0 0 500 150" preserveAspectRatio="none">
        <path d="M0,100 C150,160 350,20 500,100 L500,150 L0,150 Z" fill="#ffffff"></path>
      </svg>
  </div>

  <div class="content">
      <h2>Welcome to Achiever's Castle</h2>
      <p>Login as a</p>

      <button class="btn student">Student</button>
      <button class="btn teacher">Teacher</button>
  </div>

</div>

</body>
</html>