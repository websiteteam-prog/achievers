<?php
declare(strict_types=1);

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];

$left  = $payload['left'] ?? '';
$right = $payload['right'] ?? '';

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>

<style>

.compare-power-wrap{
    margin-bottom:35px;
}

.compare-power-row{
    display:flex;
    align-items:center;
    gap:18px;
    flex-wrap:wrap;
    margin-top: 20px;
}

.compare-power-question{
    font-size:20px;
    font-weight:600;
    color:#111;
}

.compare-power-input{
    width:45px;
    height:45px;
    border:2px solid #9c27ff;
    text-align:center;
    font-size:24px;
    font-weight:700;
    outline:none;
    border-radius:4px;
}

@media(max-width:768px){

    .compare-power-row{
        flex-direction:column;
        align-items:flex-start;
        gap:10px;
    }

    .compare-power-question{
        font-size:18px;
    }

}

</style>

<div class="compare-power-wrap">

    <div class="compare-power-row">

       <span class="compare-power-question">
    <?= ($index + 1) ?>)
</span>

<span class="compare-power-question">
    <?= $h($left) ?>
</span>

<input
    type="text"
    maxlength="1"
    class="compare-power-input"
    name="answer[<?= $id ?>]"
    autocomplete="off"
>

<span class="compare-power-question">
    <?= $h($right) ?>
</span>

    </div>

</div>