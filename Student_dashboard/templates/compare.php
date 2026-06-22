<?php
$data = [];

if (!empty($q['question_payload'])) {
    $data = json_decode($q['question_payload'], true);
}

$text = $q['question_text'] ?? '';

preg_match('/(-?\d+)\s*___\s*(-?\d+)/', $text, $matches);

$num1 = $data[0] ?? ($matches[1] ?? '');
$num2 = $data[1] ?? ($matches[2] ?? '');
?>

<style>
    .question-card {
        background: #fff;
        padding: 16px 20px;
        margin-bottom: 12px;
        /* Only vertical spacing between cards */
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .question-card:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }

    .question-card h6 {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .question-input {
        width: 80px;
        padding: 6px 4px;
        font-size: 16px;
        border: none;
        border-bottom: 2px solid #ccc;
        background: transparent;
        outline: none;
        transition: border-color 0.3s;
        text-align: center;
    }

    .question-input:focus {
        border-bottom-color: #1F669C;
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
    <div class="container-fluid question-card">

        <h6>
            <strong><?= $char++; ?>)</strong>
        </h6>

        <div style="
        display:flex;
        justify-content:start;
        align-items:center;
        gap:20px;
        margin-top:10px;
    ">

            <span style="font-size:18px;">
                <?= htmlspecialchars($num1) ?>
            </span>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>]"
                class="question-input"
                style="width:60px;" />

            <span style="font-size:18px;">
                <?= htmlspecialchars($num2) ?>
            </span>

        </div>

    </div>
</div>