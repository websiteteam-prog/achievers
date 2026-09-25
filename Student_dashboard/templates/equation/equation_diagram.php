<?php
declare(strict_types=1);

$q    = $q    ?? [];
$char = $char ?? 'a';

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$text_template = (string)($q['question_text'] ?? '');
$payload_raw   = (string)($q['question_payload'] ?? '{}');

$data = json_decode($payload_raw, true);

if (!is_array($data)) {
    $data = [];
}

$image_path = $q['question_image'] ?? '';

$protocol = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
) ? "https" : "http";

$domain = $_SERVER['HTTP_HOST'];
$base_path = "/Student_dashboard/";

$final_image_path =
    $protocol . "://" .
    $domain .
    $base_path .
    ltrim($image_path, '/');

$sides = $data['sides'] ?? [0, 0, 0];

$perimeter = array_sum($sides);

$unit = $q['unit'] ?? ($data['unit'] ?? 'cm');

$var = 'p';

$rendered_text = strtr($text_template, [
    '{digit}' =>
        '<span class="highlight-digit">' .
        $h($data['digit'] ?? '') .
        '</span>',

    '{text}' =>
        $h($data['text'] ?? ''),

    '{text2}' =>
        $h($data['text2'] ?? '')
]);
?>

<style>

.perimeter-question {
    width: 100%;
    box-sizing: border-box;

    background: #fff;
    border: 1px solid #e3e3e3;
    border-radius: 12px;

    padding: 22px 28px;
    margin: 0 0 20px 0;

    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    margin-top: 25px;
}


/* Question row */
.perimeter-content {
    display: flex;
    align-items: center;
    justify-content: flex-start;

    width: 100%;
    gap: 45px;
}


/* Question number */
.perimeter-number {
    font-size: 20px;
    font-weight: 700;

    min-width: 30px;

    color: #222;

    align-self: flex-start;

    padding-top: 10px;
}


/* Image */
.perimeter-image {
    width: 230px;
    height: 190px;

    display: flex;
    align-items: center;
    justify-content: center;

    flex-shrink: 0;

    background: #fff;
}

.perimeter-image img {
    width: 100%;
    height: 100%;

    object-fit: contain;
    object-position: center;

    display: block;
}


/* Answer area */
.perimeter-answer {
    display: flex;
    align-items: center;

    gap: 8px;

    white-space: nowrap;

    font-size: 19px;
}


/* p = */
.perimeter-answer-label {
    font-weight: 700;
    color: #222;
}


/* Blank */
.perimeter-input {
    width: 145px;
    height: 38px;

    border: none;
    border-bottom: 2px solid #1f669c;

    background: transparent;

    outline: none;

    padding: 3px 6px;

    font-size: 18px;

    text-align: center;

    font-weight: 600;
}

.perimeter-input:focus {
    border-bottom-color: #007bff;
}


/* Unit */
.perimeter-unit {
    font-size: 18px;
    font-weight: 600;
    color: #444;
}


/* Mobile */
@media (max-width: 768px) {

    .perimeter-question {
        padding: 16px;
    }

    .perimeter-content {
        gap: 18px;
        align-items: center;
    }

    .perimeter-image {
        width: 190px;
        height: 160px;
    }

    .perimeter-answer {
        font-size: 17px;
    }

    .perimeter-input {
        width: 110px;
    }

    .perimeter-unit {
        font-size: 16px;
    }
}

</style>

<div class="perimeter-question">

    <div class="perimeter-content">

        <!-- QUESTION NUMBER -->
        <div class="perimeter-number">
            <?= ($index + 1) ?>)
        </div>


        <!-- SHAPE IMAGE -->
        <?php if ($image_path): ?>

            <div class="perimeter-image">

                <img
                    src="<?= $h($final_image_path) ?>"
                    alt="Perimeter Shape"
                >

            </div>

        <?php endif; ?>


        <!-- ANSWER -->
        <div class="perimeter-answer">

            <span class="perimeter-answer-label">
                p =
            </span>

            <input
                type="text"
                name="answer[<?= (int)$q['id'] ?>]"
                class="perimeter-input"
                autocomplete="off"
                inputmode="decimal"
            >

            <span class="perimeter-unit">
                <?= $h($unit) ?>
            </span>

        </div>

    </div>

</div>

<?php $char++; ?>
