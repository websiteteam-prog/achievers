<?php
session_start();
include 'db_config.php';

if (!isset($_SESSION['teacher_id'])) {
    exit('<div class="alert alert-danger">Session expired</div>');
}

$teacher_id = $_SESSION['teacher_id'];

$stmt = $conn->prepare("SELECT * FROM teachers WHERE id=?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if (!$teacher) {
    exit('<div class="alert alert-danger">Teacher record not found</div>');
}

$teacherPhoto = $teacher['profile_photo'] ?? '';
$msg = "";
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

/*
|--------------------------------------------------------------------------
| UPDATE PROFILE (name, contact, branch)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    $name        = trim($_POST['name'] ?? '');
    $contact_no  = trim($_POST['contact_no'] ?? '');

    $stmt = $conn->prepare("UPDATE teachers SET name=?, contact_no=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $contact_no, $teacher_id);

   if ($stmt->execute()) {

    $_SESSION['teacher_name'] = $name;
    $teacher['name'] = $name;
    $teacher['contact_no'] = $contact_no;

    if($isAjax){

        echo json_encode([
            "success"=>true,
            "message"=>"Profile updated successfully",
            "name"=>$name,
            "contact"=>$contact_no
        ]);

        exit;
    }

    $msg='<div class="alert alert-success">Profile updated successfully</div>';

}else{

    if($isAjax){

        echo json_encode([
            "success"=>false,
            "message"=>"Update failed"
        ]);

        exit;
    }

    $msg='<div class="alert alert-danger">Update failed</div>';
}
}

/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {

    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $teacher['password']) && $current !== $teacher['password']) {
        $msg = '<div class="alert alert-danger">Current password is incorrect</div>';
    } elseif ($new !== $confirm) {
        $msg = '<div class="alert alert-danger">New passwords do not match</div>';
    } elseif (strlen($new) < 6) {
        $msg = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE teachers SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $teacher_id);

        if ($stmt->execute()) {
           if($isAjax){

    echo json_encode([
        "success"=>true,
        "message"=>"Password updated successfully"
    ]);

    exit;
}
        } else {
            $msg = '<div class="alert alert-danger">Failed to update password</div>';
        }
    }
}

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

.settings-container{ padding:5px; }

.settings-header{
display:flex; align-items:center; gap:15px; margin-bottom:25px;
}

.settings-header-icon{
width:55px; height:55px; border-radius:16px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
display:flex; align-items:center; justify-content:center;
color:#fff; font-size:24px;
box-shadow:0 8px 20px rgba(30,60,114,.25);
}

.settings-title{ margin:0; font-size:24px; font-weight:700; color:#1e3c72; }
.settings-subtitle{ margin:0; font-size:14px; color:#6b7280; }

.settings-card{
background:#fff; border-radius:18px; padding:30px;
box-shadow:0 10px 30px rgba(17,24,39,.08); margin-bottom:25px;
}

.avatar-upload-wrap{
position:relative; width:130px; height:130px; margin:0 auto 15px;
}

.profile-img{
width:130px; height:130px; border-radius:50%; object-fit:cover;
border:4px solid #fff; box-shadow:0 0 0 6px rgba(30,60,114,.12);
background:#2a5298; display:flex; align-items:center; justify-content:center;
color:#fff; font-size:48px;
}

.avatar-edit-btn{
position:absolute; bottom:2px; right:2px;
width:40px; height:40px; border-radius:50%;
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff; display:flex; align-items:center; justify-content:center;
box-shadow:0 3px 10px rgba(0,0,0,.25); cursor:pointer;
border:3px solid #fff; font-size:15px; transition:.2s;
}

.avatar-edit-btn:hover{ transform:scale(1.08); }

.nav-tabs{ border-bottom:1px solid #e5e7eb; margin-bottom:22px; }

.nav-tabs .nav-link{
color:#6b7280; font-weight:600; border:none; padding:10px 18px;
}

.nav-tabs .nav-link.active{
color:#1e3c72; background:transparent; border:none;
border-bottom:3px solid #2a5298; font-weight:700;
}

.form-label{ font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; font-weight:600; margin-bottom:6px; }

.form-control{
border-radius:10px; border:1px solid #e5e7eb; font-size:14px;
padding:11px 14px; background:#f8fbff;
}

.form-control:focus{
border-color:#2a5298; box-shadow:0 0 0 .2rem rgba(42,82,152,.15); background:#fff;
}

.form-control[readonly]{ background:#eef1f7; color:#6b7280; }

.btn-save{
display:inline-flex; align-items:center; gap:8px;
background:linear-gradient(135deg,#1e3c72,#2a5298); color:#fff; border:none;
padding:11px 28px; border-radius:40px; font-weight:600; font-size:14px; transition:.25s;
}

.btn-save:hover{ transform:translateY(-2px); box-shadow:0 12px 26px rgba(30,60,114,.3); color:#fff; }

.btn-danger-custom{
display:inline-flex; align-items:center; gap:8px;
background:linear-gradient(135deg,#e8063c,#c40530); color:#fff; border:none;
padding:11px 28px; border-radius:40px; font-weight:600; font-size:14px; transition:.25s;
}

.btn-danger-custom:hover{ transform:translateY(-2px); box-shadow:0 12px 26px rgba(232,6,60,.32); color:#fff; }

.alert{ border:none; border-radius:12px; padding:14px 18px; font-size:14px; font-weight:500; }
.alert-success{ background:#dcfce7; color:#15803d; }
.alert-danger{ background:#fee2e2; color:#b91c1c; }

.toggle-password{ background:#f8fbff; border:1px solid #e5e7eb; border-left:0; cursor:pointer; }

@media(max-width:768px){
.settings-card{ padding:20px; }
.settings-title{ font-size:20px; }
}

</style>

<div class="settings-container">

<div class="settings-header">
  <div class="settings-header-icon"><i class="bi bi-gear-fill"></i></div>
  <div>
    <h2 class="settings-title">Settings</h2>
    <p class="settings-subtitle">Manage your profile photo, details and password</p>
  </div>
</div>

<div id="settings-message">
    <?= $msg ?>
</div>

<div class="row">

  <!-- Profile Photo -->
  <div class="col-lg-4 mb-4">
    <div class="settings-card text-center h-100">

      <div class="avatar-upload-wrap">
        <?php
        $teacherPhotoPath = __DIR__ . '/' . $teacherPhoto;
        ?>

        <?php if (!empty($teacherPhoto) && file_exists($teacherPhotoPath)): ?>
          <img
            src="<?= $baseUrl . '/' . htmlspecialchars($teacherPhoto) ?>?v=<?= time() ?>"
            class="profile-img"
            id="avatarImg">
        <?php else: ?>
          <div class="profile-img" id="avatarPlaceholder"><i class="bi bi-person-fill"></i></div>
        <?php endif; ?>

        <label for="dpInput" class="avatar-edit-btn" title="Change photo">
          <i class="bi bi-camera-fill"></i>
        </label>
        <input type="file" id="dpInput" accept="image/png, image/jpeg, image/webp" hidden>
      </div>

      <h5 class="fw-bold mb-1"><?= htmlspecialchars($teacher['name'] ?? 'Teacher') ?></h5>
      <p class="text-muted mb-0"><?= htmlspecialchars($teacher['email'] ?? '') ?></p>

    </div>
  </div>

  <!-- Tabs -->
  <div class="col-lg-8">
    <div class="settings-card">

      <ul class="nav nav-tabs">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profileTab">
            <i class="bi bi-person-fill me-1"></i> Profile
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#securityTab">
            <i class="bi bi-shield-lock-fill me-1"></i> Security
          </button>
        </li>
      </ul>

      <div class="tab-content">

        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profileTab">
          <form id="profileForm" method="POST">
            <input type="hidden" name="update_profile" value="1">

            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control"
                     value="<?= htmlspecialchars($teacher['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control"
                     value="<?= htmlspecialchars($teacher['email'] ?? '') ?>" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="text" name="contact_no" class="form-control"
                     value="<?= htmlspecialchars($teacher['contact_no'] ?? '') ?>" maxlength="10">
            </div>

            <div class="mb-3">
              <label class="form-label">Branch</label>
              <input type="text" class="form-control"
                     value="<?= htmlspecialchars($teacher['branch'] ?? '') ?>" readonly>
            </div>

            <button type="submit" class="btn-save">
              <i class="bi bi-check-circle"></i> Save Changes
            </button>
          </form>
        </div>

        <!-- Security Tab -->
        <div class="tab-pane fade" id="securityTab">
          <form id="passwordForm" method="POST">
            <input type="hidden" name="change_password" value="1">

            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <div class="input-group">
                <input type="password" name="current_password" class="form-control password-field" required>
                <span class="input-group-text toggle-password"><i class="bi bi-eye"></i></span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">New Password</label>
              <div class="input-group">
                <input type="password" name="new_password" class="form-control password-field" required>
                <span class="input-group-text toggle-password"><i class="bi bi-eye"></i></span>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">Confirm New Password</label>
              <div class="input-group">
                <input type="password" name="confirm_password" class="form-control password-field" required>
                <span class="input-group-text toggle-password"><i class="bi bi-eye"></i></span>
              </div>
            </div>

            <button type="submit" class="btn-danger-custom">
              <i class="bi bi-shield-lock"></i> Update Password
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>
</div>

<script>
(function(){

  // Show/hide password
  document.querySelectorAll(".toggle-password").forEach(function(btn){
    btn.addEventListener("click", function(){
      const input = this.previousElementSibling;
      const icon = this.querySelector("i");
      if(input.type === "password"){
        input.type = "text";
        icon.classList.replace("bi-eye","bi-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("bi-eye-slash","bi-eye");
      }
    });
  });

  // Avatar upload (reuses same api/update_teacher_photo.php)
  const dpInput = document.getElementById("dpInput");

  if (dpInput) {
    dpInput.addEventListener("change", function(){

      const file = this.files[0];
      if (!file) return;

      const allowed = ["image/jpeg","image/png","image/webp"];
      if (!allowed.includes(file.type)) {
        alert("Only JPG, PNG or WEBP images allowed");
        return;
      }
      if (file.size > 5 * 1024 * 1024) {
        alert("Max file size is 5MB");
        return;
      }

      const formData = new FormData();
      formData.append("profile_photo", file);

      $.ajax({
        url: "api/update_teacher_photo.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(res){
          if (res.success) {
            let newSrc = "<?= $baseUrl ?>/" + res.image_url + "?v=" + Date.now();
            let img = document.getElementById("avatarImg");
            if (img) {
              img.src = newSrc;
            } else {
              document.getElementById("avatarPlaceholder").outerHTML =
                '<img src="'+newSrc+'" class="profile-img" id="avatarImg" alt="DP">';
            }
          } else {
            alert(res.message || "Upload failed");
          }
        },
        error: function(){
          alert("Something went wrong while uploading");
        }
      });
    });
  }

})();

$("#profileForm").submit(function(e){

    e.preventDefault();

    $.ajax({

        url:"teacher_settings.php",

        type:"POST",

        data:$(this).serialize(),

        headers:{
            "X-Requested-With":"XMLHttpRequest"
        },

        dataType:"json",

        success:function(res){

            if(res.success){

                $("#settings-message").html(
                '<div class="alert alert-success">'+res.message+'</div>'
            );

            setTimeout(function () {
                $("#settings-message .alert").fadeOut(500, function () {
                    $(this).remove();
                });
            }, 3000);

                // Update profile card
                $(".fw-bold.mb-1").text(res.name);

                // Update dashboard header
                $(".teacher").text(res.name);

                $(".teacher-name").text(res.name);

            }else{

                $("#settings-message").html(
                '<div class="alert alert-danger">'+res.message+'</div>'
            );

            setTimeout(function () {
                $("#settings-message .alert").fadeOut(500, function () {
                    $(this).remove();
                });
            }, 3000);
            }

        }

    });

});

$("#passwordForm").submit(function(e){

    e.preventDefault();

    $.ajax({

        url:"teacher_settings.php",

        type:"POST",

        data:$(this).serialize(),

        headers:{
            "X-Requested-With":"XMLHttpRequest"
        },

        dataType:"json",

        success:function(res){

            if(res.success){

                $("#settings-message").html(
                '<div class="alert alert-success">'+res.message+'</div>'
            );

            setTimeout(function () {
                $("#settings-message .alert").fadeOut(500, function () {
                    $(this).remove();
                });
            }, 3000);

                $("#passwordForm")[0].reset();

            }else{

                $("#settings-message").html(
                '<div class="alert alert-danger">'+res.message+'</div>'
            );

            setTimeout(function () {
                $("#settings-message .alert").fadeOut(500, function () {
                    $(this).remove();
                });
            }, 3000);
            }

        }

    });

});
</script>