<?php
/**
 * Dedicated login page for branch admins.
 * Separate from the root login.php (used by super_admin / general entry point).
 */
require_once __DIR__ . '/auth.php';

// Already logged in as a branch admin? Go straight to the dashboard.
if (is_branch_admin()) {
    header('Location: branch_dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role, branch_id FROM admins WHERE email = ? AND role = 'branch_admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['branch_id'] = $admin['branch_id'];

            header('Location: branch_dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Branch Admin Login | Achiever's Castle</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#1e3c72;
  --navy2:#2a5298;
  --red:#e8063c;
  --ink:#05364d;
}
*{box-sizing:border-box;font-family:'Poppins',sans-serif;}
html,body{height:100%;margin:0;}

/* page background → very light */
body{
  min-height:100vh;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#c9d9f5,#e5ddf7);
  padding:16px;position:relative;overflow:hidden;
}
/* soft subtle bubbles */
body::before,body::after{
  content:"";position:absolute;border-radius:50%;
  background:rgba(30,60,114,.10);      
  animation:float 7s infinite ease-in-out;z-index:0;
}
body::before{width:260px;height:260px;top:-90px;left:-90px;}
body::after{width:220px;height:220px;bottom:-80px;right:-80px;}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(22px)}}

/* card */
.auth-card{
  position:relative;z-index:1;display:flex;width:100%;max-width:880px;min-height:520px;
  background:#fff;border-radius:22px;overflow:hidden;
  box-shadow:
    0 30px 70px rgba(30,60,114,.18),
    0 8px 20px rgba(0,0,0,.05);
  border-top:4px solid var(--red);
  animation:rise .7s ease;
}
@keyframes rise{from{opacity:0;transform:translateY(35px)}to{opacity:1;transform:translateY(0)}}

/* LEFT brand panel → soft light blue, dark text */
.auth-left{
  flex:1;padding:50px 34px;text-align:center;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  position:relative;
    overflow:hidden;
    background:linear-gradient(160deg,#edf5ff 0%,#dcecff 65%,#ffeef3 100%);
  color:#1e3c72;
}
.auth-left::before{
    content:"";
    position:absolute;
    width:320px;
    height:320px;
    border-radius:50%;
    background:rgba(30,60,114,.05);
    top:-120px;
    left:-120px;
}

.auth-left::after{
    content:"";
    position:absolute;
    width:220px;
    height:220px;
    border-radius:50%;
    background:rgba(232,6,60,.05);
    right:-90px;
    bottom:-90px;
}
.auth-left img{
  width:190px;max-width:82%;margin-bottom:18px;
  filter:drop-shadow(0 6px 14px rgba(0,0,0,.12));
   transition:.4s;
}

.auth-left img:hover{
    transform:scale(1.05);
}
.auth-left h2{
  font-family:"Love Ya Like A Sister",cursive;
  font-size:40px;margin:0 0 8px;font-weight:400;color:#234a87;;
}
.auth-left p{color:#4f6d99;font-size:14px;line-height:1.6;margin:0;}
.auth-left .badge-role{
  margin-top:18px;background:#fff;color:var(--navy);border:1px solid #cfe0ff;
  padding:6px 16px;border-radius:30px;font-size:13px;font-weight:600;letter-spacing:.4px;
  box-shadow:0 4px 10px rgba(30,60,114,.10);
}

/* RIGHT form panel */
.auth-right{flex:1;padding:50px 42px;display:flex;flex-direction:column;justify-content:center;}
.auth-right h3{
  font-family:"Love Ya Like A Sister",cursive;font-size:30px;margin:0 0 6px;font-weight:400;
  background:linear-gradient(to right,var(--red),var(--navy));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
}
.auth-right .lead{color:#6b7280;font-size:14px;margin-bottom:26px;}

.form-label{font-weight:600;color:var(--ink);font-size:14px;margin-bottom:6px;}
.input-wrap{position:relative;margin-bottom:16px;}
.input-wrap .l-ic{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--navy);}
.input-wrap .r-ic{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#999;cursor:pointer;}
.form-control{
  height:50px;border-radius:12px;
  padding:10px 42px;background:#f8fbff;font-size:15px;
}
.form-control:focus{border-color:var(--red);box-shadow:0 0 0 .2rem rgba(232,6,60,.15);background:#fff;}

.row-links{display:flex;justify-content:space-between;align-items:center;font-size:13px;margin:2px 0 20px;}
.row-links a{color:var(--red);text-decoration:none;font-weight:600;}
.row-links a:hover{text-decoration:underline;}
.row-links label{color:#6b7280;}

.btn-auth{
  width:100%;height:50px;border:none;border-radius:30px;color:#fff;font-weight:700;font-size:16px;
  background:linear-gradient(135deg,#1e3c72,#2a5298);
  box-shadow:0 10px 22px rgba(30,60,114,.30);transition:.35s;margin-top:6px;
}
.btn-auth:hover{transform:translateY(-3px) scale(1.02);box-shadow:0 18px 35px rgba(30,60,114,.35);background:linear-gradient(135deg,var(--red),#a10329);}

.err{background:#ffe5e9;color:#a10329;padding:10px 14px;border-radius:10px;font-size:14px;text-align:center;margin-bottom:16px;}
.foot{text-align:center;margin-top:22px;font-size:12px;color:#8a94a6;}

/* responsive */
@media(max-width:820px){
  .auth-card{flex-direction:column;max-width:430px;min-height:auto;}
  .auth-left{padding:34px 24px;}
  .auth-left img{width:150px;}
  .auth-left h2{font-size:32px;}
  .auth-right{padding:32px 26px;}
  body::before,body::after{display:none;}
}
</style>
</head>
<body>
<div class="auth-card">
  <div class="auth-left">
    <img src="../images/logo.png" alt="Achiever's Castle">
    <h2>Branch Admin</h2>
    <p>Manage your branch.<br>Everything in one place.</p>
    <span class="badge-role"><i class="bi bi-diagram-3-fill"></i> Branch Portal</span>
  </div>
  <div class="auth-right">
    <h3>Welcome Back</h3>
    <p class="lead">Sign in to manage your branch</p>

    <?php if ($error): ?>
      <div class="err"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label class="form-label">Email Address</label>
      <div class="input-wrap">
        <i class="bi bi-envelope-fill l-ic"></i>
        <input type="email" class="form-control" name="email" placeholder="you@example.com" autocomplete="username" required autofocus>
      </div>

      <label class="form-label">Password</label>
      <div class="input-wrap">
        <i class="bi bi-lock-fill l-ic"></i>
        <input type="password" class="form-control" name="password" id="pwd" placeholder="Enter your password" autocomplete="current-password" required>
        <i class="bi bi-eye-slash r-ic" id="togglePwd"></i>
      </div>

      <button type="submit" class="btn-auth">Login</button>
    </form>

    <div class="foot">© <?php echo date("Y"); ?> Achiever's Castle</div>
  </div>
</div>
<script>
const t=document.getElementById('togglePwd'),p=document.getElementById('pwd');
t.addEventListener('click',()=>{p.type=p.type==='password'?'text':'password';t.classList.toggle('bi-eye');t.classList.toggle('bi-eye-slash');});
</script>
</body>
</html>