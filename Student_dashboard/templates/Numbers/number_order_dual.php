<?php
declare(strict_types=1);

$q = $q ?? [];

$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$correct = json_decode($q['correct_answer'] ?? '{}', true) ?: [];
$student = json_decode($q['student_answer'] ?? '{}', true) ?: [];

$numbers = $payload['numbers'] ?? [];

$studentAsc = $student['asc'] ?? [];
$studentDesc = $student['desc'] ?? [];
?>

<style>

.order-card{

background:#fff;
padding:28px;
border-radius:16px;
box-shadow:0 4px 12px rgba(0,0,0,.08);
margin-bottom:25px;

}

.number-bank{

display:flex;
gap:15px;
flex-wrap:wrap;
margin-bottom:30px;

}

.number-item{

background:#d48ac6;
color:#fff;
padding:10px 18px;
border-radius:6px;
font-size:22px;
font-weight:bold;

}

.answer-row{

display:flex;
align-items:center;
flex-wrap:wrap;
gap:10px;
margin:20px 0;

}

.answer-label{

width:210px;
font-size:22px;
font-weight:bold;

}

.answer-input{

width:90px;
border:none;
border-bottom:3px solid #66b8f6;
background:transparent;
text-align:center;
font-size:18px;
font-weight:bold;
outline:none;

}

.sign{

font-size:22px;
font-weight:bold;

}

@media(max-width:768px){

.answer-label{

width:100%;
margin-bottom:10px;

}

.answer-input{

width:70px;
font-size:16px;

}

.number-item{

font-size:18px;

}

}

</style>

<div class="order-card" id="order-card-<?= $q['id'] ?>">

<div class="number-bank">

<?php foreach($numbers as $num): ?>

<div class="number-item">

<?= htmlspecialchars($num) ?>

</div>

<?php endforeach; ?>

</div>


<div class="answer-row">

<div class="answer-label">

Ascending order :

</div>

<?php for($i=0;$i<count($numbers);$i++): ?>

<input
class="answer-input"
type="text"
name="asc[]"
value="<?= htmlspecialchars($studentAsc[$i] ?? '') ?>">

<?php endfor; ?>

</div>


<div class="answer-row">

<div class="answer-label">

Descending order :

</div>

<?php for($i=0;$i<count($numbers);$i++): ?>

<input
class="answer-input"
type="text"
name="desc[]"
value="<?= htmlspecialchars($studentDesc[$i] ?? '') ?>">

<?php endfor; ?>

</div>

<input
type="hidden"
name="answer[<?= $q['id'] ?>]"
id="final_<?= $q['id'] ?>">

</div>

<script>

(function(){

const qid = <?= $q['id'] ?>;

const card = document.getElementById("order-card-"+qid);

const ascInputs = card.querySelectorAll('input[name="asc[]"]');

const descInputs = card.querySelectorAll('input[name="desc[]"]');

const hidden = document.getElementById("final_"+qid);

function update(){

    let asc = [];
    let desc = [];

    ascInputs.forEach(function(e){
        asc.push(e.value.trim());
    });

    descInputs.forEach(function(e){
        desc.push(e.value.trim());
    });

    hidden.value = JSON.stringify({
        asc: asc,
        desc: desc
    });

}

ascInputs.forEach(function(e){
    e.addEventListener('input', update);
});

descInputs.forEach(function(e){
    e.addEventListener('input', update);
});

update();

})();

</script>