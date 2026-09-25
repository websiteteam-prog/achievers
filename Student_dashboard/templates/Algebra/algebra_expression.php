<?php
declare(strict_types=1);

$q = $q ?? [];

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$id = (int)($q['id'] ?? 0);

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];
?>

<style>

.algebra-wrap{
    margin-bottom:35px;
}

.algebra-row{
    display:flex;
    flex-direction:column;
    gap:12px;
}

.algebra-question{
    font-size:20px;
    font-weight:600;
    color:#111;
}

.expression-row{
    display:flex;
    align-items:center;
    gap:15px;
    margin-top:8px;
}

.expression-label{
    font-size:18px;
    font-weight:700;
    white-space:nowrap;
}

.expression-input{
    flex:1;
    border:none;
    border-bottom:2px solid #8a2be2;
    background:transparent;
    outline:none;
    padding:4px 0;
    font-size:18px;
}

.expression-input:focus{
    border-bottom:3px solid #8a2be2;
}

@media(max-width:768px){

    .expression-row{
        flex-direction:row;
        align-items:center;
    }

    .expression-label{
        white-space:nowrap;
    }

    .expression-input{
        flex:1;
        min-width:120px;
    }

}

</style>

<div class="algebra-wrap">

    <div class="algebra-row">

        <div class="algebra-question">
            <?= ($index + 1) ?>)
            <?= $h($payload['sentence'] ?? '') ?>
        </div>

        <div class="expression-row">

    <span class="expression-label">
        Expression:
    </span>

    <input
        type="text"
        class="expression-input"
        name="answer[<?= $id ?>]"
    >

</div>

    </div>

</div>