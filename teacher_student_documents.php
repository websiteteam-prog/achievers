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
    sd.title,
    sd.upload_type,
    sd.file_name,
    sd.file_path,
    sd.status,
    sd.remark,
    sd.uploaded_at,

    sub.subject_name

FROM student_documents sd

LEFT JOIN subjects sub
    ON sub.id = sd.subject_id

INNER JOIN teacher_subjects ts
    ON ts.subject_id = sd.subject_id

WHERE
    ts.teacher_id=?
    AND sd.student_id=?

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
    border-radius:18px;
    box-shadow:0 10px 30px rgba(17,24,39,.08);
    transition:.25s;
    overflow:hidden;
}

.document-card:hover{
    transform:translateY(-2px);
    box-shadow:0 14px 35px rgba(17,24,39,.12);
}

/* .document-icon{
    width:60px;
    height:60px;
    border-radius:16px;
    background:#edf4ff;
    color:#2a5298;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:26px;
    flex-shrink:0;
} */

.document-name{
    font-size:18px;
    font-weight:700;
    color:#1f2937;
}

.document-meta{
    font-size:13px;
    color:#6b7280;
    line-height:1.7;
}

.document-actions{
    display:flex;
    flex-direction:column;
    gap:12px;
    align-items:flex-end;
}

.document-actions .btn{
    min-width:115px;
    height:42px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    font-weight:600;
}

.status,
.remark{
    border-radius:10px;
}

.empty-box{
    padding:70px 20px;
    text-align:center;
}

.empty-box i{
    font-size:75px;
    color:#d7d7d7;
}

/* Mobile */

@media(max-width:991px){

.document-card .row>div{
    margin-bottom:18px;
}

.document-actions{
    flex-direction:row;
    justify-content:flex-start;
    align-items:center;
}

.document-actions .btn{
    flex:1;
    min-width:0;
}

}

@media(max-width:576px){

.document-card .d-flex{
    flex-direction:column;
    align-items:flex-start !important;
}

/* .document-icon{
    margin-bottom:15px;
} */

.document-actions{
    flex-direction:column;
    width:100%;
}

.document-actions .btn{
    width:100%;
}

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
                        <!-- <i class="bi bi-file-earmark-text"></i> -->
                    </div>

                    <div>

                       <div class="document-name">
                        <?= htmlspecialchars($row['title']) ?>
                    </div>

                    <div class="text-muted small">
                        <?= htmlspecialchars($row['file_name']) ?>
                    </div>

                    <div class="mt-1">

                    <?php

                    $typeClass=[
                        "personal"=>"secondary",
                        "assignment"=>"primary",
                        "homework"=>"warning",
                        "project"=>"success"
                    ];

                    ?>

                    <span class="badge bg-<?= $typeClass[$row['upload_type']] ?? "secondary" ?>">
                        <?= ucfirst($row['upload_type']) ?>
                    </span>

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

            <div class="col-md-3">

            <label class="form-label fw-semibold">

            Status

            </label>

            <select
            class="form-select form-select-sm status"
            data-id="<?= $row['id'] ?>">

            <option value="Pending"
            <?= $row['status']=="Pending"?"selected":"" ?>>

            Pending

            </option>

            <option value="Approved"
            <?= $row['status']=="Approved"?"selected":"" ?>>

            Approved

            </option>

            <option value="Rejected"
            <?= $row['status']=="Rejected"?"selected":"" ?>>

            Rejected

            </option>

            </select>

            <div class="mt-2">

            <textarea

            class="form-control form-control-sm remark"

            rows="2"

            data-id="<?= $row['id'] ?>"

            placeholder="Teacher Remark"><?= htmlspecialchars($row['remark']) ?></textarea>

            </div>

            </div>

            <div class="col-md-3">

    <div class="document-actions">

        <button
            class="btn btn-success save-document"
            data-id="<?= $row['id'] ?>">

            <i class="bi bi-check2-circle"></i>
            Save

        </button>

        <a
            href="<?= htmlspecialchars($row['file_path']) ?>"
            target="_blank"
            class="btn btn-primary">

            <i class="bi bi-eye-fill"></i>
            View

        </a>

    </div>

</div>

        </div>

    </div>

</div>

<?php endwhile;

$stmt->close();

?>

<script>

$(document)
    .off("click", ".save-document")
    .on("click", ".save-document", function () {

    let id=$(this).data("id");

    let status=$(".status[data-id='"+id+"']").val();

    let remark=$(".remark[data-id='"+id+"']").val();

    $.ajax({

        url:"save_document_status.php",

        type:"POST",

        data:{
            document_id:id,
            status:status,
            remark:remark
        },

       success:function(res){

    if(res.trim()=="Document updated successfully."){

       $("#documentsBody .alert-success").remove();

        $('<div class="alert alert-success mt-3">' +
            '<i class="bi bi-check-circle-fill me-2"></i>' +
            'Document updated successfully.' +
        '</div>')
        .prependTo("#documentsBody")
        .delay(2000)
        .fadeOut(function () {
            $(this).remove();
        });

    }else{

        alert(res);

    }

}

        });

});

</script>