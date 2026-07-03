<?php
$isReview = defined('IS_CHECK_ANSWER');

$payload = json_decode($q['question_payload'], true) ?? [];
$numbers = $payload['numbers'] ?? [];
$mode    = $payload['mode'] ?? 'asc';

$qid  = (int)$q['id'];
$sign = ($mode === 'asc') ? '<' : '>';

?>

<style>

.order-card{
    background:#fff;
    padding:26px 28px;
    margin:25px auto;
    border-radius:16px;
    box-shadow:0 4px 14px rgba(0,0,0,.1);
    max-width:100%;
    display:flex;
    flex-direction:column;
    align-items:left;
}

.number-line{
    display:flex;
    justify-content:left;
    gap:10px;
    flex-wrap:wrap;
    font-size:22px;
    font-weight:bold;
    margin-top: -38px;
    margin-left: 31px;
}

.answer-row{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    margin-top: 30px;
}

.order-label{
    font-size:18px;
    font-weight:bold;
    min-width:55px;
}

.number-input{
    width:60px;
    border:none;
    border-bottom:2px solid #000;
    text-align:center;
    font-size:16px;
    outline:none;
    background:transparent;
}

.sign{
    font-size:16px;
    font-weight:bold;
}

</style>

<div class="order-card">

<div style="font-size:18px;font-weight:bold;margin-bottom:8px;align-self:flex-start;">
<?= $char ?>)
</div>

<?php $char++; ?>

<div class="number-line">

<?php foreach($numbers as $i=>$n): ?>

<span><?= htmlspecialchars($n) ?></span>

<?php if($i<count($numbers)-1): ?>,<?php endif; ?>

<?php endforeach; ?>

</div>

<div class="answer-row">

<div class="order-label">
<?= $mode==='asc' ? 'I.O.' : 'D.O.' ?>
</div>

<?php for($i=0;$i<count($numbers);$i++): ?>

<input
class="number-input"
type="text"
name="answer[<?= $qid ?>][]"

>

<?php if($i<count($numbers)-1): ?>

<span class="sign"><?= $sign ?></span>

<?php endif; ?>

<?php endfor; ?>

</div>

</div>

<input type="hidden" name="answer[<?= $qid ?>]" id="final_answer_<?= $qid ?>">

<script>

document.addEventListener("input",function(){

const inputs=document.querySelectorAll(
'.order-card input[name="answer[<?= $qid ?>][]"]'
);

let values=[];

inputs.forEach(i=>{
if(i.value.trim()!==''){
values.push(i.value.trim());
}
});

if(values.length===inputs.length){
document.getElementById("final_answer_<?= $qid ?>").value=
JSON.stringify(values);
}

});

</script>
