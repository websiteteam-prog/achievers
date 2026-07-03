<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db_config.php";

if (!isset($_SESSION['teacher_id'])) {
    exit("Unauthorized");
}

$teacher_id = (int)$_SESSION['teacher_id'];
$student_id = (int)($_GET['student_id'] ?? 0);

$sql = "
SELECT
    sd.id,
    sd.file_name,
    sd.file_path,
    sd.status,
    sd.uploaded_at,
    sub.subject_name

FROM student_documents sd

INNER JOIN subjects sub
    ON sub.id = sd.subject_id

INNER JOIN teacher_subjects ts
    ON ts.subject_id = sd.subject_id

WHERE
    ts.teacher_id = ?
    AND sd.student_id = ?

ORDER BY sd.uploaded_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<style>

.document-card{
    border:none;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.25s;
}

.document-card:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 25px rgba(0,0,0,.12);
}

.document-icon{
    width:55px;
    height:55px;
    border-radius:14px;
    background:#edf4ff;
    color:#2a5298;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
}

.document-name{
    font-size:16px;
    font-weight:600;
    margin-bottom:2px;
}

.document-meta{
    font-size:13px;
    color:#6c757d;
}

.empty-box{
    padding:60px 20px;
    text-align:center;
}

.empty-box i{
    font-size:80px;
    color:#d7d7d7;
}

</style>

<?php

if($result->num_rows==0){
?>

<div class="empty-box">

    <i class="bi bi-folder2-open"></i>

    <h4 class="mt-4">
        No Documents Uploaded
    </h4>

    <p class="text-muted">
        This student has not uploaded any documents yet.
    </p>

</div>

<?php

$stmt->close();
exit;

}

while($row=$result->fetch_assoc()):

$statusColor = ($row['status']=="approved")
    ? "success"
    : "warning";

?>

<div class="card document-card mb-3">

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-md-6">

                <div class="d-flex align-items-center">

                    <div class="document-icon me-3">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>

                        <div class="document-name">
                            <?= htmlspecialchars($row['file_name']) ?>
                        </div>

                        <div class="document-meta">

                            Subject :
                            <strong><?= htmlspecialchars($row['subject_name']) ?></strong>

                            <br>

                            Uploaded :
                            <?= date("d M Y h:i A", strtotime($row['uploaded_at'])) ?>

                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-3 text-center mt-3 mt-md-0">

                <span class="badge bg-<?= $statusColor ?> px-3 py-2">

                    <?= ucfirst($row['status']) ?>

                </span>

            </div>

            <div class="col-md-3 text-md-end mt-3 mt-md-0">

                <a
                    href="<?= htmlspecialchars($row['file_path']) ?>"
                    target="_blank"
                    class="btn btn-primary">

                    <i class="bi bi-eye-fill"></i>

                    View Document

                </a>

            </div>

        </div>

    </div>

</div>

<?php endwhile;

$stmt->close();

?>