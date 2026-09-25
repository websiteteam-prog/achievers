<?php
include '../db_config.php';
session_start();

if (!isset($_SESSION['teacher_id'])) {
    exit('Session expired');
}

$id = (int)($_GET['id'] ?? 0);

$q = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE id = $id");

if (!$q || mysqli_num_rows($q) == 0) {
    exit('❌ Question not found.');
}

$data = mysqli_fetch_assoc($q);
$payload = json_decode($data['question_payload'], true);
?>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet"
>

<style>

*{
box-sizing:border-box;
}

.page-container{
padding:5px;
width:100%;
max-width:100%;
}

/* ========================= */
/* HEADER - matches manage_questions.php */
/* ========================= */

.questions-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
flex-wrap:wrap;
gap:15px;
}

.header-left{
display:flex;
align-items:center;
gap:15px;
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
flex-shrink:0;
}

.header-title{
margin:0;
font-size:24px;
font-weight:700;
color:#1e3c72;
letter-spacing:.2px;
word-break:break-word;
}

.header-subtitle{
margin:0;
font-size:14px;
color:#6b7280;
}

.header-right{
display:flex;
align-items:center;
gap:12px;
flex-wrap:wrap;
}

.btn-back{
display:inline-flex;
align-items:center;
gap:8px;
background:#f1f3f7;
color:#374151;
border:none;
padding:10px 22px;
border-radius:40px;
font-weight:600;
font-size:14px;
text-decoration:none;
transition:.25s ease;
cursor:pointer;
}

.btn-back:hover{
background:#e5e7eb;
color:#374151;
transform:translateY(-2px);
}

/* ========================= */
/* CARD - matches manage_questions.php card style */
/* ========================= */

.card{
border:none;
border-radius:18px;
overflow:hidden;
box-shadow:0 10px 30px rgba(17,24,39,.08);
animation:fadeInUp .4s ease;
background:#fff;
}

@keyframes fadeInUp{
from{opacity:0;transform:translateY(10px);}
to{opacity:1;transform:translateY(0);}
}

.card-body-custom{
padding:25px;
width:100%;
max-width:100%;
overflow:hidden;
}

/* Question */
.question-box{
background:#f8fbff;
border:1px solid #edf1f7;
padding:16px;
border-radius:12px;
word-break:break-word;
font-size:15px;
color:#374151;
line-height:1.6;
}

/* Info */
.info-box{
border:1px solid #edf1f7;
padding:14px 16px;
border-radius:12px;
background:#f8fbff;
width:100%;
transition:background .2s ease;
}

.info-box:hover{
background:#f1f6fd;
}

.label{
font-size:12px;
color:#6b7280;
text-transform:uppercase;
letter-spacing:.5px;
font-weight:600;
margin-bottom:4px;
}

.value{
font-weight:600;
word-break:break-word;
color:#1e3c72;
font-size:14.5px;
}

.type-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#d9f3ff;
    color:#0c7abf;
    padding:8px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    max-width:100%;
    min-height:34px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.answer-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#dcfce7;
    color:#15803d;
    padding:8px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

.unit-badge{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#eef1f7;
    color:#374151;
    padding:8px 14px;
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}

/* JSON */
.json-box{
background:#0b1220;
color:#00ff9c;
padding:16px;
border-radius:12px;
font-size:13px;
overflow:auto;
max-height:300px;
white-space:pre-wrap;
word-break:break-word;
}

/* Image */
.question-img{
max-width:100%;
height:auto;
border-radius:12px;
margin-top:10px;
box-shadow:0 8px 20px rgba(17,24,39,.1);
}

.section-block{
margin-bottom:22px;
}

/* ========================= */
/* MOBILE FIX */
/* ========================= */

@media(max-width:768px){

.questions-header{
flex-direction:column;
align-items:flex-start;
}

.header-right{
width:100%;
}

.btn-back{
width:100%;
justify-content:center;
}

.header-title{
font-size:22px;
}

}

@media(max-width:575px){

.info-row{
display:block !important;
}

.info-row > div{
width:100% !important;
max-width:100% !important;
flex:none !important;
margin-bottom:12px;
}

.page-container{
padding:5px;
}

.card-body-custom{
padding:16px;
}

}

@media(max-width:360px){

.header-icon{
width:45px;
height:45px;
font-size:20px;
border-radius:12px;
}

.page-title,
.header-title{
font-size:17px;
}

.question-box{
font-size:14px;
}

.json-box{
font-size:11px;
}

}

@media(max-width:300px){

.card-body-custom{
padding:12px;
}

.question-box{
font-size:13px;
padding:10px;
}

.info-box{
padding:10px;
}

.value{
font-size:13px;
}

.label{
font-size:10.5px;
}

.json-box{
font-size:10px;
padding:8px;
max-height:200px;
}

.btn-back{
font-size:13px;
padding:8px;
}

}

</style>

<div class="page-container">

  <!-- HEADER -->
  <div class="questions-header">

    <div class="header-left">

      <div class="header-icon">
        <i class="bi bi-eye-fill"></i>
      </div>

      <div>
        <h2 class="header-title">View Question #<?= $data['id'] ?></h2>
        <p class="header-subtitle">Full details of the selected question</p>
      </div>

    </div>

    <div class="header-right">
      <button onclick="goBack()" class="btn-back">
        <i class="bi bi-arrow-left"></i>
        Back
      </button>
    </div>

  </div>

  <!-- CARD -->
  <div class="card">
    <div class="card-body-custom">

      <!-- Question -->
      <div class="section-block">
        <div class="label mb-1">Question</div>
        <div class="question-box mt-1">
          <?= nl2br(htmlspecialchars($data['question_text'])) ?>
        </div>
      </div>

      <!-- Info -->
      <div class="row g-3 info-row section-block">

        <div class="col-md-4">
          <div class="info-box">
            <div class="label">Type</div>
            <div class="value">
              <span class="type-badge"><?= htmlspecialchars($data['question_type']) ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="info-box">
            <div class="label">Correct Answer</div>
            <div class="value">
              <span class="answer-badge"><?= htmlspecialchars($data['correct_answer']) ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="info-box">
            <div class="label">Unit</div>
            <div class="value">
              <span class="unit-badge"><?= $data['unit'] ? htmlspecialchars($data['unit']) : '-' ?></span>
            </div>
          </div>
        </div>

      </div>

      <!-- Image -->
      <?php if (!empty($data['question_image'])): ?>
      <div class="section-block">
        <div class="label mb-1">Image</div>
        <img src="https://creativetheka.in/Student_dashboard/<?= htmlspecialchars($data['question_image']) ?>"
             class="img-fluid question-img">
      </div>
      <?php endif; ?>

      <!-- Payload -->
      <div class="section-block mb-0">
        <div class="label mb-1">Payload</div>
        <pre class="json-box mt-1">
<?= json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
        </pre>
      </div>

    </div>
  </div>

</div>

<script>
function goBack() {
    if (document.referrer !== "") {
        window.history.back();
    } else {
        // fallback agar direct open kiya ho
        loadPage('teacher_question_pages/manage_questions.php');
    }
}
</script>