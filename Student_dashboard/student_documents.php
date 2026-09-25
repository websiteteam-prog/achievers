<?php
session_start();
include "../db_config.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch Student Details
$sqlStudent = "SELECT first_name, last_name FROM students WHERE id=?";
$stmt = $conn->prepare($sqlStudent);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Subjects
$sqlSubjects = "SELECT sub.id, sub.subject_name 
                FROM student_subjects ss 
                INNER JOIN subjects sub ON sub.id = ss.subject_id 
                WHERE ss.student_id = ? 
                ORDER BY sub.subject_name";
$stmt = $conn->prepare($sqlSubjects);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$subjects = $stmt->get_result();

// Fetch Documents
$sqlDocs = "SELECT
        sd.id,
        sd.title,
        sd.upload_type,
        sd.file_name,
        sd.file_path,
        sd.uploaded_at,
        sd.status,
        sd.remark,
        sd.action_role,
        sd.action_date,

        sub.subject_name

    FROM student_documents sd

    LEFT JOIN subjects sub
    ON sub.id = sd.subject_id

    WHERE sd.student_id=?

    ORDER BY sd.uploaded_at DESC";
$stmt = $conn->prepare($sqlDocs);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$documents = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Documents</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Love+Ya+Like+A+Sister&display=swap" rel="stylesheet">
  
<link href="student.css" rel="stylesheet">

</head>

<body>

<?php include 'student_sidebar.php'; ?>
<!-- Mobile Sidebar Toggle -->
<button
    class="btn btn-primary d-lg-none position-fixed"
    id="sidebarToggle"
    style="top:15px;left:15px;z-index:1200;border-radius:50%;width:48px;height:48px;">

    <i class="bi bi-list fs-4"></i>

</button>
<!-- Main Content -->
<div class="documents-page">
    <div class="documents-wrapper">
        
        <!-- Header -->
        <div class="documents-header">
            <div class="documents-left">
    <i class="bi bi-folder2-open icon-style"></i>

    <div>
        <h2 class="documents-title">My Documents</h2>
        <div class="documents-subtitle">
            Upload assignments, worksheets and study documents.
        </div>
    </div>
</div>
            <div class="upload-badge">
                <?= $documents->num_rows ?> Documents Uploaded
            </div>
        </div>

        <?php if(isset($_GET['uploaded'])): ?>
            <div id="flashMessage" class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i>
                Document uploaded successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
        <div id="flashMessage" class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            <?php
            switch($_GET['error']) {
                case "subject": echo "Invalid Subject Selected."; break;
                case "upload": echo "Unable to upload document."; break;
                case "size": echo "Maximum file size is 40 MB."; break;
                case "type": echo "Invalid file type."; break;
                default: echo "Something went wrong.";
            }
            ?>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Upload -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>
                            Upload Document
                        </h4>
                        <form
                        action="upload_student_document.php"
                        method="POST"
                        enctype="multipart/form-data">

                        <div class="mb-3">

                        <label class="form-label">

                        Upload Type

                        </label>

                        <select
                        class="form-select"
                        name="upload_type"
                        id="upload_type"
                        required>

                        <option value="">

                        Select Upload Type

                        </option>

                        <option value="personal">

                        📄 Personal Document

                        </option>

                        <option value="assignment">

                        📝 Assignment

                        </option>

                        <option value="homework">

                        📚 Homework

                        </option>

                        <option value="project">

                        💻 Project

                        </option>

                        </select>

                        </div>



                        <div class="mb-3">

                        <label class="form-label">

                        Document Title

                        </label>

                        <input

                        type="text"

                        name="title"

                        class="form-control"

                        placeholder="Example : Maths Assignment 1"

                        required>

                        </div>



                        <div
                        class="mb-3"
                        id="subjectWrapper"
                        style="display:none;">

                        <label class="form-label">

                        Subject

                        </label>

                        <select
                        class="form-select"
                        name="subject_id">

                        <option value="">

                        Select Subject

                        </option>

                        <?php
                        mysqli_data_seek($subjects,0);

                        while($sub=$subjects->fetch_assoc()){
                        ?>

                        <option value="<?= $sub['id']?>">

                        <?= htmlspecialchars($sub['subject_name'])?>

                        </option>

                        <?php } ?>

                        </select>

                        </div>



                        <div class="mb-3">

                        <label class="form-label">

                        Choose File

                        </label>

                        <input
                        type="file"
                        class="form-control"
                        name="document"
                        required>

                        </div>



                        <button
                        class="btn btn-primary w-100">

                        <i class="bi bi-upload"></i>

                        Upload

                        </button>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Uploaded Documents -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h4 class="fw-bold mb-4">
                            <i class="bi bi-folder2-open text-primary me-2"></i>
                            Uploaded Documents
                        </h4>

                        <?php if($documents->num_rows > 0): ?>
                            <?php mysqli_data_seek($documents, 0); 
                            while($doc = $documents->fetch_assoc()): ?>
                              <div class="card document-card mb-3">
    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="d-flex">

                    <div class="document-icon me-3">
                        <i class="bi bi-file-earmark-text-fill"></i>
                    </div>

                    <div>

                        <h6 class="fw-bold mb-1">
                            <?= htmlspecialchars($doc['title']) ?>
                        </h6>

                        <div class="small text-muted">
                            <?= htmlspecialchars($doc['file_name']) ?>
                        </div>

                        <?php if(!empty($doc['subject_name'])): ?>

                        <span class="badge bg-light text-primary border mt-1">

                        <?= htmlspecialchars($doc['subject_name']) ?>

                        </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

         <div class="col-md-4 text-end">

<?php

$statusClass=[
    "Pending"=>"warning",
    "Approved"=>"success",
    "Rejected"=>"danger"
];

?>

<div class="mb-3">

<?php

$icons=[
    "Approved"=>"bi-check-circle-fill",
    "Pending"=>"bi-clock-fill",
    "Rejected"=>"bi-x-circle-fill"
];

?>

<span style="font-size:13px;" class="badge rounded-pill  bg-<?= $statusClass[$doc['status']] ?? 'secondary' ?>  px-2 py-1">

<i class="bi <?= $icons[$doc['status']] ?? 'bi-info-circle-fill' ?> me-1"></i>

<?= ucfirst($doc['status']) ?>

</span>

</div>

<div class="document-actions">

<a href="../<?= htmlspecialchars($doc['file_path']) ?>"
target="_blank"
class="btn btn-view">

<i class="bi bi-eye-fill"></i>

<span>View</span>

</a>

<button
type="button"
class="btn btn-details detailsBtn"

data-title="<?= htmlspecialchars($doc['title']) ?>"
data-file="<?= htmlspecialchars($doc['file_name']) ?>"
data-subject="<?= htmlspecialchars($doc['subject_name']) ?>"
data-uploadtype="<?= ucfirst($doc['upload_type']) ?>"
data-status="<?= ucfirst($doc['status']) ?>"
data-uploaded="<?= date("d M Y h:i A",strtotime($doc['uploaded_at'])) ?>"
data-role="<?= htmlspecialchars($doc['action_role']) ?>"
data-date="<?= !empty($doc['action_date']) ? date("d M Y h:i A",strtotime($doc['action_date'])) : '' ?>"
data-remark="<?= htmlspecialchars($doc['remark']) ?>">

<i class="bi bi-info-circle-fill"></i>

<span>Details</span>

</button>

</div>

</div>

        </div>

    </div>
</div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-folder2-open"></i>
                                <h4 class="mt-4">No Documents Uploaded</h4>
                                <p class="text-muted">Upload your assignments, worksheets or study material.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.documents-page{

    margin-left:270px;

    min-height:100vh;

    background:#f5f7fb;

    padding:30px;

    box-sizing:border-box;

}

 .documents-wrapper{
    width:100%;
}

/* Rest of your styles (same as before) */
.documents-header{

display:flex;

justify-content:space-between;

align-items:center;

flex-wrap:wrap;

gap:20px;

margin-bottom:35px;

}
.documents-left { display: flex; align-items: center; gap: 18px; }

.documents-title { 
    font-size: 42px;
    font-weight: 400;
    margin-bottom: 20px !important;
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
    font-family: "Love Ya Like A Sister", cursive;
    margin-left: 8px;
}
.icon-style { 
    font-size: 42px;
    font-weight: 400;
    margin-bottom: 29px !important;
    background: linear-gradient(to right, #e02121, #2f55a4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    color: transparent;
    font-family: "Love Ya Like A Sister", cursive;
}
.documents-subtitle { color: #6b7280; margin-top: -22px; margin-bottom: 20px; }

.document-icon{

    width:58px;
    height:58px;

    display:flex;

    align-items:center;

    justify-content:center;

    flex-shrink:0;

    background:linear-gradient(135deg,#eef4ff,#dbeafe);

    border-radius:16px;

    color:#2563eb;

    font-size:26px;

}

.upload-badge{

background:#eef4ff;

color:#2563eb;

padding:10px 18px;

border-radius:30px;

font-size:13px;

font-weight:700;

}

.card{

border:none;

border-radius:20px;

box-shadow:0 12px 35px rgba(15,23,42,.08);

}

h4.fw-bold{
color:var(--primary);
}
.document-card {
    border:none;
    border-radius:18px;
    padding:8px;
    margin-bottom:18px;

    transition:.25s;

    box-shadow: 0 12px 35px rgba(15,23,42,.08);
}
.document-card:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(15,23,42,.12); }

.document-card h6{

    /* font-size:24px; */

    font-weight:700;

    margin-bottom:6px;

}

.small.text-muted{

    font-size:15px;

    color:#6b7280 !important;

}

.document-actions{

display:flex;

justify-content:flex-end;

gap:10px;

flex-wrap:wrap;

}

.col-md-4.text-end{

    display:flex;

    flex-direction:column;

    align-items:flex-end;

    justify-content:center;

}

.btn-view,
.btn-details{

    display:flex;

    align-items:center;

    justify-content:center;

    gap:6px;

    width:110px;

    height:42px;

    border-radius:10px;

    text-decoration:none;

}

.btn-view{

    background:#eff6ff;

    border:1px solid #bfdbfe;

    color:#2563eb;

}

.btn-view:hover{

    background:#2563eb;

    color:#fff;

    border-color:#2563eb;

}

.btn-details{

    background:#f8fafc;

    border:1px solid #d8dee8;

    color:#475569;

}

.btn-details:hover{

    background:#f8fafc;
    color:#2563eb;
    border-color:#2563eb;
    transform:translateY(-2px);

}
.form-label { font-weight: 600; color: #1f2937; margin-bottom: 8px; }
.form-select, .form-control { height: 52px; border-radius: 14px; border: 1px solid #dbe4ef; }
.form-control:focus, .form-select:focus { border-color: #2f55a4; box-shadow: 0 0 0 0.2rem rgba(47,85,164,.15); }
.btn-primary{

background:linear-gradient(135deg,#2563eb,#1d4ed8);

border:none;

border-radius:12px;

height:50px;

font-weight:600;

}

.empty-state { padding: 70px 20px; text-align: center; }
.empty-state i { font-size: 75px; color: #c8d4ea; }

.modal-dialog{

max-width:750px;

}

.modal-body{

overflow-x:auto;

}

.table{

min-width:650px;

}

@media(max-width:992px){

.documents-page{

margin-left:0;

padding:80px 18px 20px;

}

.row.g-4{

row-gap:20px;

}

.col-lg-5,
.col-lg-7{

width:100%;

}

.documents-header{

flex-direction:column;

align-items:flex-start;

}

.upload-badge{

width:100%;

text-align:center;

}

}

@media(max-width:576px){

.documents-page{

padding:75px 12px 15px;

}

.documents-title{

font-size:24px;
margin-top: 36px;
}

.documents-subtitle{

font-size:13px;

}

.documents-icon{
    width:42px;
    height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;

    background:transparent;
    border:none;
    border-radius:0;

    font-size:32px;
    color:#2563eb;
}

.documents-icon i{
    color:#2563eb;
    line-height:1;
}

.card-body{

padding:24px;

}

.form-control,
.form-select{

height:46px;

font-size:14px;

}

.document-card .row{

flex-direction:column;

}

.col-md-8,
.col-md-4{

width:100%;

text-align:left !important;

}

.col-md-4.text-end{

align-items:flex-start;

margin-top:15px;

}

.document-actions{

width:100%;

display:flex;

flex-direction:column;

}

.btn-view,
.btn-details{

width:100%;

}

}
</style>


<div class="modal fade" id="documentDetailsModal">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">

Document Details

</h5>

<button
class="btn-close"
data-bs-dismiss="modal">
</button>

</div>

<div
class="modal-body"
id="documentDetailsBody">

</div>

</div>

</div>

</div>
<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const uploadType = document.getElementById("upload_type");

    const subjectWrapper = document.getElementById("subjectWrapper");

    const subject = document.querySelector("select[name='subject_id']");

    function toggleSubject(){

        if(
            uploadType.value === "assignment" ||
            uploadType.value === "homework" ||
            uploadType.value === "project"
        ){

            subjectWrapper.style.display="block";

            subject.required=true;

        }else{

            subjectWrapper.style.display="none";

            subject.required=false;

            subject.value="";

        }

    }

    uploadType.addEventListener("change",toggleSubject);

    toggleSubject();

});

document.addEventListener("DOMContentLoaded", function () {

    const flash = document.getElementById("flashMessage");

    if (flash) {

        setTimeout(function () {

            const bsAlert = bootstrap.Alert.getOrCreateInstance(flash);
            bsAlert.close();

        }, 3000);

        if (window.history.replaceState) {
            const url = window.location.pathname;
            window.history.replaceState({}, document.title, url);
        }
    }

});

document.querySelectorAll(".detailsBtn").forEach(function(btn){

btn.addEventListener("click",function(){

document.getElementById("documentDetailsBody").innerHTML=`

<table class="table table-bordered">

<tr>

<th width="35%">Title</th>

<td>${this.dataset.title}</td>

</tr>

<tr>

<th>File Name</th>

<td>${this.dataset.file}</td>

</tr>

<tr>

<th>Subject</th>

<td>${this.dataset.subject || '-'}</td>

</tr>

<tr>

<th>Upload Type</th>

<td>${this.dataset.uploadtype}</td>

</tr>

<tr>

<th>Status</th>

<td>${this.dataset.status}</td>

</tr>

<tr>

<th>Uploaded</th>

<td>${this.dataset.uploaded}</td>

</tr>

<tr>

<th>Reviewed By</th>

<td>${this.dataset.role || '-'}</td>

</tr>

<tr>

<th>Review Date</th>

<td>${this.dataset.date || '-'}</td>

</tr>

<tr>

<th>Remark</th>

<td>${this.dataset.remark || 'No Remark'}</td>

</tr>

</table>

`;

new bootstrap.Modal(
document.getElementById("documentDetailsModal")
).show();

});

});

const sidebar=document.getElementById("studentSidebar");

document.getElementById("sidebarToggle")?.addEventListener("click",function(){

sidebar.classList.toggle("show");

});

</script>

</body>
</html>