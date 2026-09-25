<?php
declare(strict_types=1);

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];

$sentence = $payload['sentence'] ?? '';
$label    = trim($payload['label'] ?? '');

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>

<style>

.alg-wrap{
    margin-bottom:35px;
}

.alg-question{
    font-size:20px;
    font-weight:600;
    margin-bottom:8px;
}

.alg-answer-row{
    display:flex;
    align-items:center;
    gap:12px;
    margin-left:40px;
}

.alg-label{
    font-size:18px;
    font-weight:700;
    min-width:60px;
    margin-left: -22px;
}

.alg-input{
    width:260px;
    border:none;
    border-bottom:2px solid #7b2cff;
    background:transparent;
    outline:none;
    font-size:18px;
    text-align:center;
    padding:4px 0;
}

@media(max-width:768px){

    .alg-answer-row{
        margin-left:0;
        flex-direction:column;
        align-items:flex-start;
    }

    .alg-input{
        width:100%;
    }
}

</style>

<div class="alg-wrap">

    <div class="alg-question">
        <?= ($index + 1) ?>)
        <?= $h($sentence) ?>
    </div>

    <div class="alg-answer-row">

        <?php if($label !== ''): ?>
            <span class="alg-label">
                <?= $h($label) ?>
            </span>
        <?php endif; ?>

        <input
            type="text"
            class="alg-input"
            name="answer[<?= $id ?>]">

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const form = document.querySelector('form');

    if(!form) return;

    form.addEventListener('submit', function(){

        document.querySelectorAll('.alg-input').forEach(function(input){

            let val = input.value.trim();

            val = val.replace(/\s+/g,'');

            input.value = val;

        });

    });

});
</script>