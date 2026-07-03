<?php
declare(strict_types=1);

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$questionImage = trim((string)($q['question_image'] ?? ''));
$questionText  = trim((string)($q['question_text'] ?? ''));

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];

$showInput = !empty($payload['show_input']);
$suffix    = trim($payload['suffix'] ?? '');

$makeUrl = function (string $path): string {

    if ($path === '') return '';

    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }

    $root = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    $root = $root === '' ? '/' : $root;

    return $root . '/' . ltrim($path, '/');
};

$imgUrl = $makeUrl($questionImage);
?>

<style>

.diagram-full{
    width:100%;
    padding:10px 20px;
}

.diagram-full img{
    width:75%;
    max-width:75%;
    height:auto;
    max-height:90vh;
    object-fit:contain;
    display:block;
    border:none;
    box-shadow:none;
    border-radius:0;
    background:transparent;
}

.diagram-text{
    margin-top:20px;
    font-size:22px;
    font-weight:600;
    color:#111;
    line-height:1.7;
}

/* answer row */

.diagram-answer-row{
    display:flex;
    align-items:center;
    gap:20px;
    margin-top:35px;
    flex-wrap:wrap;
}

.diagram-star{
    color:#ff4d4d;
    font-size:42px;
    line-height:1;
}

.diagram-input{
    width:320px;
    border:none;
    border-bottom:2px solid red;
    background:transparent;
    outline:none;
    font-size:20px;
    text-align:center;
    padding:5px;
}

.diagram-suffix{
    font-size:18px;
    font-weight:600;
    color:#111;
}

@media(max-width:768px){

    .diagram-full img{
        width:100%;
        max-width:100%;
    }

    .diagram-text{
        font-size:18px;
    }

    .diagram-answer-row{
        gap:12px;
    }

    .diagram-input{
        width:100%;
    }

    .diagram-suffix{
        width:100%;
    }

}

</style>


<div class="diagram-full">

    <?php if ($imgUrl !== ''): ?>

        <img
            src="<?= $h($imgUrl) ?>"
            alt=""
            loading="lazy"
        >

    <?php endif; ?>


    <?php if ($questionText !== ''): ?>

        <div class="diagram-text">
            <?= nl2br($h($questionText)) ?>
        </div>

    <?php endif; ?>


    <?php if ($showInput): ?>

        <div class="diagram-answer-row">

            <span class="diagram-star">
                ★
            </span>

            <input
                type="text"
                class="diagram-input"
                name="answer[<?= $id ?>]"
            >

            <?php if ($suffix !== ''): ?>

                <span class="diagram-suffix">
                    <?= $h($suffix) ?>
                </span>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <?php if ($imgUrl === '' && $questionText === ''): ?>

        <div style="
            height:200px;
            display:flex;
            align-items:center;
            color:#999;
            font-size:18px;
        ">
            No Content
        </div>

    <?php endif; ?>

</div>