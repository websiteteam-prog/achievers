<?php
$data = json_decode($q['question_payload'], true);
?>

<style>

    .question-card{
        background:#fff;
        padding:10px 20px;
        margin-bottom:10px;
        border-radius:12px;
        box-shadow:0 2px 8px rgba(0,0,0,.08);
    }

    .question-card .row{
        min-height:40px;
    }

    .question-card h6{
        margin:0;
        font-size:18px;
        font-weight:600;
        line-height:1.2;
    }

    .question-input{
        width:55px;
        padding:2px 0;
        font-size:24px;
        font-weight:700;
        text-align:center;
        border:none;
        border-bottom:2px solid #cfcfcf;
        background:transparent;
        outline:none;
    }

    .question-input:focus{
        border-bottom-color:#1F669C;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .question-card {
            padding: 14px 16px;
            border-radius: 10px;
        }
        .question-input {
            width: 70px;
            font-size: 15px;
        }
        .question-card h6 {
            font-size: 15px;
        }
    }

    @media (max-width: 576px) {
        .question-card {
            padding: 12px 14px;
        }
        .question-input {
            width: 60px;
        }
    }
</style>

<div class="container-fluid question-card">

    <div class="row align-items-center">

        <div class="col-5">
            <h6><strong><?= $char++; ?>)</strong> <?= htmlspecialchars($data['num1']) ?></h6>
        </div>

        <div class="col-2 text-center">
            <input
                type="text"
                maxlength="1"
                name="answer[<?= $q['id'] ?>]"
                class="question-input">
        </div>

        <div class="col-5 text-end">
            <h6><?= htmlspecialchars($data['num2']) ?></h6>
        </div>

    </div>

</div>
