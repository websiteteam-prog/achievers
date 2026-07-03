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

.exp-wrap{
    margin-bottom:35px;
}

/* =========================
   QUESTION
========================= */

.exp-question{
    font-size:20px;
    font-weight:600;
    line-height:1.6;
    color:#111;
}

/* =========================
   LABEL + INPUT ROW
========================= */

.exp-answer-row{
    display:flex;
    align-items:center;
    gap:12px;
    margin-left:40px;
    margin-top:8px;
}

.exp-label{
    font-size:20px;
    font-weight:700;
    color:#111;
    min-width:65px;
    margin-left: -18px;
}

.exp-input{
    width:260px;
    min-width:260px;

    border:none;
    border-bottom:2px solid #7b2cff;

    background:transparent;
    outline:none;

    font-size:18px;
    text-align:center;

    padding:4px 0;
}

.exp-input:focus{
    border-bottom-width:3px;
}

/* =========================
   INLINE ROW (Q3/Q4)
========================= */

.exp-inline-row{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    margin-top: 20px;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .exp-question{
        font-size:18px;
    }

    .exp-answer-row{
        margin-left:0;
        flex-direction:column;
        align-items:flex-start;
        gap:8px;
    }

    .exp-inline-row{
        flex-direction:column;
        align-items:flex-start;
        gap:8px;
    }

    .exp-label{
        font-size:18px;
    }

    .exp-input{
        width:100%;
        min-width:unset;
        text-align:left;
    }

}

</style>

<div class="exp-wrap">

<?php if($label !== ''): ?>

    <!-- Q1 & Q2 -->

    <div class="exp-question">
        <?= ($index + 1) ?>)
        <?= $h($sentence) ?>
    </div>

    <div class="exp-answer-row">

        <span class="exp-label">
            <?= $h($label) ?>
        </span>

        <input
            type="text"
            class="exp-input"
            name="answer[<?= $id ?>]">

    </div>

<?php else: ?>

    <!-- Q3 & Q4 -->

    <div class="exp-inline-row">

        <span class="exp-question">
            <?= ($index + 1) ?>)
            <?= $h($sentence) ?>
        </span>

        <input
            type="text"
            class="exp-input"
            name="answer[<?= $id ?>]">

    </div>

<?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const form = document.querySelector('form');

    if(!form) return;

    form.addEventListener('submit', function(){

        document.querySelectorAll('.exp-input').forEach(function(input){

            let val = input.value.trim();

            // remove spaces
            val = val.replace(/\s+/g,'');

            // *, x, X => ×
            val = val.replace(/\*/g,'×');
            val = val.replace(/x/g,'×');
            val = val.replace(/X/g,'×');

            // powers
            val = val.replace(/\^0/g,'⁰');
            val = val.replace(/\^1/g,'¹');
            val = val.replace(/\^2/g,'²');
            val = val.replace(/\^3/g,'³');
            val = val.replace(/\^4/g,'⁴');
            val = val.replace(/\^5/g,'⁵');
            val = val.replace(/\^6/g,'⁶');
            val = val.replace(/\^7/g,'⁷');
            val = val.replace(/\^8/g,'⁸');
            val = val.replace(/\^9/g,'⁹');

            input.value = val;

        });

    });

});
</script>