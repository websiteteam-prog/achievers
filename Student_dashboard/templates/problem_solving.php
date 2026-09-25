<?php
declare(strict_types=1);

$q = $q ?? [];
$index = $index ?? 0;

$h = fn($s) => htmlspecialchars(
    (string)($s ?? ''),
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$data = json_decode($q['question_payload'] ?? '{}', true);

$isInstruction = !empty($data['instruction']);
$image = $data['image'] ?? null;
$solution = $data['solution'] ?? null;

$isSubQuestion = preg_match(
    '/^[a-d]\)/i',
    trim($q['question_text'] ?? '')
) === 1;
?>

<style>
.ps-box {
    background: #fff;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.10);
    margin-top: 25px;
}

.ps-question {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.5;
    margin-bottom: 25px;
}

.ps-solution {
    margin-top: 15px;
    color: red;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.5;
}

/* Main content: answer left + image right */
.ps-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 25px;
}

/* LEFT SIDE */
.ps-answer-area {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.ps-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.ps-input {
    border: none;
    border-bottom: 2px solid #ccc;
    width: 180px;
    font-size: 18px;
    outline: none;
    background: transparent;
    padding: 8px 5px;
}

.ps-input:focus {
    border-bottom-color: #007bff;
}

.ps-unit {
    font-weight: 700;
    font-size: 18px;
}

/* RIGHT SIDE */
.ps-image-area {
    flex: 0 0 320px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.ps-image {
    max-width: 300px;
    max-height: 250px;
    width: auto;
    height: auto;
    object-fit: contain;
}

/* Instruction */
.ps-instruction {
    font-size: 18px;
    font-weight: 600;
}

.ps-box:has(.ps-instruction) .ps-content {
    min-height: auto;
    align-items: center;
}

.ps-box:has(.ps-instruction) .ps-image {
    max-width: 300px;
    max-height: 180px;
}

/* Mobile */
@media (max-width: 768px) {

    .ps-box {
        padding: 20px;
    }

    .ps-content {
        flex-direction: column;
        align-items: stretch;
        gap: 20px;
    }

    .ps-image-area {
        flex: none;
        order: 2;
        justify-content: center;
    }

    .ps-answer-area {
        order: 1;
    }

    .ps-image {
        max-width: 250px;
        max-height: 220px;
    }
}
</style>


<div class="ps-box">

    <!-- QUESTION -->
    <div class="ps-question">

        <?php if (!$isSubQuestion): ?>

            Que<?= ($index + 1) ?>.
            <?= $h($q['question_text'] ?? '') ?>

        <?php else: ?>

            <?= $h($q['question_text'] ?? '') ?>

        <?php endif; ?>

    </div>

    <?php if (!empty($solution)): ?>
    
        <div class="ps-solution">
            <?= $h($solution) ?>
        </div>
    
    <?php endif; ?>
    
    <div class="ps-content">

        <!-- =========================
             LEFT SIDE
        ========================== -->
        <div class="ps-answer-area">

            <?php if ($isInstruction): ?>

                <div class="ps-instruction">
                    How far is:
                </div>

            <?php else: ?>

                <div class="ps-input-wrapper">

                   <input
                type="text"
                name="answer[<?= $h($q['id'] ?? '') ?>]"
                id="ans_<?= $h($q['id'] ?? '') ?>"
                class="ps-input"
                placeholder="Enter your answer"
                value=""
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                data-lpignore="true"
                data-form-type="other"
            >

                    <?php if (!empty($q['unit'])): ?>

                        <span class="ps-unit">
                            <?= $h($q['unit']) ?>
                        </span>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>


        <!-- =========================
             RIGHT SIDE IMAGE
        ========================== -->
        <?php if ($image): ?>

            <div class="ps-image-area">

                <img
                    src="<?= $h($image) ?>"
                    class="ps-image"
                    alt="Question Image"
                >

            </div>

        <?php endif; ?>

    </div>

</div>