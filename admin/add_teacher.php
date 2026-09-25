<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name       = mysqli_real_escape_string($conn, $_POST['name']);
  $email      = mysqli_real_escape_string($conn, $_POST['email']);
  $subject    = mysqli_real_escape_string($conn, $_POST['subject']);
  $status     = mysqli_real_escape_string($conn, $_POST['status']);
  $branch     = mysqli_real_escape_string($conn, $_POST['branch']);
  $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
  $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Profile photo upload (optional at creation)
  $profile_photo = '';
  if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {

      $maxSize = 5 * 1024 * 1024; // 5MB
      $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

      if ($_FILES['profile_photo']['size'] > $maxSize) {
          $error = "Photo must be under 5MB.";
      } elseif (!in_array($_FILES['profile_photo']['type'], $allowedTypes)) {
          $error = "Only JPG, PNG, or WEBP images are allowed.";
      } else {
          $uploadDir = '../uploads/teachers/';
          if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
          $fileName = 'teacher_' . time() . '_' . uniqid() . '.' . $ext;
          $targetPath = $uploadDir . $fileName;

          if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
              $profile_photo = 'uploads/teachers/' . $fileName;
          }
      }
  }

  if (empty($error)) {

      $sql = "INSERT INTO teachers 
                (name, email, subject, status, password, branch, contact_no, profile_photo, created_at)
              VALUES 
                ('$name', '$email', '$subject', '$status', '$password', '$branch', '$contact_no', '$profile_photo', NOW())";

      if (mysqli_query($conn, $sql)) {
        echo "<script>
          if (typeof $ !== 'undefined') {
            $('.menu-link[data-page=\"manage_teachers.php\"]').trigger('click');
          } else {
            window.location.href = 'dashboard.php?page=manage_teachers.php';
          }
        </script>";
        exit;
      } else {
        $error = "Error: " . mysqli_error($conn);
      }

  }
}
?>

<style>

.form-container{
padding:5px;
}

.form-header{
display:flex;
align-items:center;
gap:15px;
margin-bottom:25px;
}

.header-icon{
width:55px;
height:55px;
border-radius:16px;
background:linear-gradient(135deg,#11998e,#38ef7d);
display:flex;
align-items:center;
justify-content:center;
color:#fff;
font-size:24px;
box-shadow:0 8px 20px rgba(17,153,142,.25);
}

.header-title{
margin:0;
font-size:24px;
font-weight:700;
color:#1e3c72;
}

.header-subtitle{
margin:0;
font-size:14px;
color:#6b7280;
}

.card{
border:none;
border-radius:0px;
overflow:hidden;
box-shadow:0 10px 30px rgba(17,24,39,.08);
animation:fadeInUp .4s ease;
max-width:700px;
}

@keyframes fadeInUp{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

.form-control, .form-select {
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid #e0e6ed;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 2px rgba(30, 136, 229, .15);
    border-color: #42a5f5;
}

.btn-submit{
display:inline-flex;
align-items:center;
gap:6px;
background:linear-gradient(135deg,#11998e,#38ef7d);
color:#fff;
border:none;
padding:10px 22px;
border-radius:30px;
font-weight:600;
font-size:13.5px;
box-shadow:0 5px 15px rgba(17,153,142,.25);
transition:.2s;
}

.btn-submit:hover{
transform:translateY(-2px);
color:#fff;
box-shadow:0 8px 18px rgba(17,153,142,.35);
}

.btn-back{
display:inline-flex;
align-items:center;
gap:6px;
background:#fff;
color:#374151;
border:none;
padding:10px 22px;
border-radius:30px;
font-weight:600;
font-size:13.5px;
box-shadow:0 4px 12px rgba(0,0,0,.06);
transition:.2s;
text-decoration:none;
}

.btn-back:hover{
transform:translateY(-2px);
color:#374151;
}

.form-note{
font-size:12px;
color:#9ca3af;
margin-top:4px;
}

@media(max-width:768px){
.header-title{ font-size:20px; }
}

</style>

<div class="form-container">

<div class="form-header">

<div class="header-icon">
<i class="bi bi-person-plus"></i>
</div>

<div>
<h2 class="header-title">Add New Teacher</h2>
<p class="header-subtitle">Create a new teacher account</p>
</div>

</div>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card">

<div class="card-body p-4">

<form method="POST" action="add_teacher.php" enctype="multipart/form-data" autocomplete="off">

<div class="mb-3">
<label class="form-label">Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label"> Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label"> Create Password</label>
<div class="position-relative">
<input type="password" name="password" id="teacherPassword" class="form-control pe-5" required minlength="6" autocomplete="new-password">
<i class="bi bi-eye toggle-password" id="togglePassword" style="position:absolute; right:14px; top:50%; transform:translateY(-50%); cursor:pointer; color:#6b7280;"></i>
</div>
<p class="form-note">Minimum 6 characters. Teacher can change this later from their profile.</p>
</div>

<div class="mb-3">
<label class="form-label">Subject</label>
<input type="text" name="subject" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Branch</label>
<input type="text" name="branch" class="form-control" placeholder="e.g. Main Branch">
</div>

<div class="mb-3">
<label class="form-label">Contact No.</label>
<input type="text" name="contact_no" class="form-control" maxlength="10" pattern="[0-9]{10}" placeholder="10-digit mobile number">
</div>

<div class="mb-3">
<label class="form-label">Profile Photo</label>
<input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
<p class="form-note">JPG, PNG or WEBP. Max size 5MB.</p>
</div>

<div class="mb-4">
<label class="form-label">Status</label>
<select name="status" class="form-select" required>
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>
</div>

<div class="d-flex gap-2 flex-wrap">

<button type="submit" class="btn-submit">
<i class="bi bi-plus-circle"></i> Add Teacher
</button>

<a href="javascript:void(0)" class="btn-back menu-link" data-page="manage_teachers.php">
<i class="bi bi-arrow-left"></i> Back
</a>

</div>

</form>

</div>

</div>

</div>

<script>
$(document).off('click', '#togglePassword').on('click', '#togglePassword', function() {
    let input = $('#teacherPassword');
    let type = input.attr('type') === 'password' ? 'text' : 'password';
    input.attr('type', type);
    $(this).toggleClass('bi-eye bi-eye-slash');
});
</script>