<?php
include '../db_config.php';

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $id");
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Teacher not found");
}

$updateError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name       = mysqli_real_escape_string($conn, $_POST['name']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $subject    = mysqli_real_escape_string($conn, $_POST['subject']);
    $branch     = mysqli_real_escape_string($conn, $_POST['branch']);
    $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);

    $photoSql = '';
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {

        $maxSize = 5 * 1024 * 1024;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if ($_FILES['profile_photo']['size'] > $maxSize) {
            $updateError = "Photo must be under 5MB.";
        } elseif (!in_array($_FILES['profile_photo']['type'], $allowedTypes)) {
            $updateError = "Only JPG, PNG, or WEBP images are allowed.";
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
                $photoSql = ", profile_photo='$profile_photo'";
            }
        }
    }

    if (empty($updateError)) {

        $update = "UPDATE teachers 
                   SET name='$name', email='$email', subject='$subject', 
                       branch='$branch', contact_no='$contact_no' 
                       $photoSql
                   WHERE id=$id";

        if (mysqli_query($conn, $update)) {
            echo "<script>
            if (typeof $ !== 'undefined') {
              $('.menu-link[data-page=\"manage_teachers.php\"]').trigger('click');
            } else {
              window.location.href = 'dashboard.php?page=manage_teachers.php';
            }
            </script>";
            exit;
        } else {
            $updateError = "Error: " . mysqli_error($conn);
        }

    }

    $result = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $id");
    $data = mysqli_fetch_assoc($result);
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

.form-control {
    border-radius: 10px;
    padding: 10px 12px;
    border: 1px solid #e0e6ed;
}

.form-control:focus {
    box-shadow: 0 0 0 2px rgba(30, 136, 229, .15);
    border-color: #42a5f5;
}

.current-photo{
width:70px;
height:70px;
border-radius:50%;
object-fit:cover;
margin-bottom:10px;
border:2px solid #e0e6ed;
}

.btn-submit{
display:inline-flex;
align-items:center;
gap:6px;
background:linear-gradient(135deg,#1e3c72,#2a5298);
color:#fff;
border:none;
padding:10px 22px;
border-radius:30px;
font-weight:600;
font-size:13.5px;
box-shadow:0 5px 15px rgba(30,60,114,.25);
transition:.2s;
}

.btn-submit:hover{
transform:translateY(-2px);
color:#fff;
box-shadow:0 8px 18px rgba(30,60,114,.35);
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
<i class="bi bi-pencil-square"></i>
</div>

<div>
<h2 class="header-title">Edit Teacher</h2>
<p class="header-subtitle">Update teacher information</p>
</div>

</div>

<?php if (!empty($updateError)): ?>
<div class="alert alert-danger"><?= $updateError ?></div>
<?php endif; ?>

<div class="card">

<div class="card-body p-4">

<form method="POST" action="edit_teacher.php?id=<?= $id ?>" enctype="multipart/form-data">

<?php if (!empty($data['profile_photo'])): ?>
<div class="mb-3">
<img src="../<?= htmlspecialchars($data['profile_photo']) ?>" class="current-photo" alt="Current photo">
</div>
<?php endif; ?>

<div class="mb-3">
<label class="form-label"><i class="bi bi-person"></i> Name</label>
<input type="text" name="name" class="form-control"
    value="<?= htmlspecialchars($data['name']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label"><i class="bi bi-envelope"></i> Email</label>
<input type="email" name="email" class="form-control"
    value="<?= htmlspecialchars($data['email']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label"><i class="bi bi-book"></i> Subject</label>
<input type="text" name="subject" class="form-control"
    value="<?= htmlspecialchars($data['subject']) ?>" required>
</div>

<div class="mb-3">
<label class="form-label"><i class="bi bi-diagram-3"></i> Branch</label>
<input type="text" name="branch" class="form-control"
    value="<?= htmlspecialchars($data['branch'] ?? '') ?>">
</div>

<div class="mb-3">
<label class="form-label"><i class="bi bi-telephone"></i> Contact No.</label>
<input type="text" name="contact_no" class="form-control" maxlength="10" pattern="[0-9]{10}"
    value="<?= htmlspecialchars($data['contact_no'] ?? '') ?>">
</div>

<div class="mb-4">
<label class="form-label"><i class="bi bi-image"></i> Update Profile Photo</label>
<input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
<p class="form-note">Leave empty to keep the current photo. Max size 5MB.</p>
</div>

<div class="d-flex gap-2 flex-wrap">

<button type="submit" class="btn-submit">
<i class="bi bi-check-circle"></i> Update Teacher
</button>

<a href="javascript:void(0)" class="btn-back menu-link" data-page="manage_teachers.php">
<i class="bi bi-arrow-left"></i> Back
</a>

</div>

</form>

</div>

</div>

</div>