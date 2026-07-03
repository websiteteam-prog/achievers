<?php
$data = json_decode($q['question_payload'], true);

$expression = $data['expression'] ?? '';
$start = $data['start'] ?? -10;
$end = $data['end'] ?? 10;

$qid = $q['id'];
?>
<style>

.numberline-box{
    margin-top:20px;
}

.expression-title{
    font-size:24px;
    /*font-weight:bold;*/
    margin-bottom:25px;
    display:flex;
    gap:10px;
    align-items:center;
}

.line-wrapper{
    position:relative;
    width:100%;
    overflow-x:auto;
    padding-bottom:30px;
}

#lineCanvas<?= $qid ?>{
    background:transparent;
    cursor:pointer;
}

.answer-box{
    margin-top:20px;
    font-size:20px;
    color:#343a40;
    /*font-weight:bold;*/
}

.answer-input{
    border:none;
    border-bottom:2px solid #000;
    width:100px;
    text-align:center;
    font-size:20px;
    outline:none;
    background: transparent;
}
</style>

<div class="numberline-box">

<div class="expression-title">
<strong><?= isset($q['_sub_no']) ? $q['_sub_no'].')' : '' ?></strong>
<?= $expression ?>
</div>

<div class="line-wrapper">
<canvas id="lineCanvas<?= $qid ?>"></canvas>
</div>

<div class="answer-box">
Answer :
<input
class="answer-input"
type="text"
id="answer_display_<?= $qid ?>"
readonly>

<input
type="hidden"
name="answer[<?= $qid ?>]"
id="answer_hidden_<?= $qid ?>">
</div>

</div>

<script>

const canvas<?= $qid ?>=
document.getElementById("lineCanvas<?= $qid ?>");

const ctx<?= $qid ?>=
canvas<?= $qid ?>.getContext("2d");

const start<?= $qid ?>=<?=$start?>;
const end<?= $qid ?>=<?=$end?>;

const total<?= $qid ?> = end<?= $qid ?> - start<?= $qid ?>;

canvas<?= $qid ?>.width = 900;
canvas<?= $qid ?>.height = 120;

const padding<?= $qid ?> = 40;

const step<?= $qid ?> =
(canvas<?= $qid ?>.width - (padding<?= $qid ?> * 2))
/
total<?= $qid ?>;

const y<?= $qid ?> = 50;

ctx<?= $qid ?>.lineWidth=2;

// main line
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(padding<?= $qid ?>,y<?= $qid ?>);
ctx<?= $qid ?>.lineTo(canvas<?= $qid ?>.width-padding<?= $qid ?>,y<?= $qid ?>);
ctx<?= $qid ?>.stroke();

// arrows
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(20,y<?= $qid ?>);
ctx<?= $qid ?>.lineTo(30,43);
ctx<?= $qid ?>.moveTo(20,y<?= $qid ?>);
ctx<?= $qid ?>.lineTo(30,57);
ctx<?= $qid ?>.stroke();

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(canvas<?= $qid ?>.width-20,y<?= $qid ?>);
ctx<?= $qid ?>.lineTo(canvas<?= $qid ?>.width-30,43);
ctx<?= $qid ?>.moveTo(canvas<?= $qid ?>.width-20,y<?= $qid ?>);
ctx<?= $qid ?>.lineTo(canvas<?= $qid ?>.width-30,57);
ctx<?= $qid ?>.stroke();

for(let i=start<?= $qid ?>;i<=end<?= $qid ?>;i++)
{
    let x=
    padding<?= $qid ?>+
    (i-start<?= $qid ?>)*step<?= $qid ?>;

    ctx<?= $qid ?>.beginPath();
    ctx<?= $qid ?>.moveTo(x,42);
    ctx<?= $qid ?>.lineTo(x,58);
    ctx<?= $qid ?>.stroke();

    ctx<?= $qid ?>.font="18px Arial";
    ctx<?= $qid ?>.fillText(i,x-10,80);
}

canvas<?= $qid ?>.addEventListener("click",function(e){

let rect=this.getBoundingClientRect();

let mouseX=e.clientX-rect.left;

let index=Math.round(
(mouseX-padding<?= $qid ?>)
/
step<?= $qid ?>
);

let value=start<?= $qid ?>+index;

let pointX=
padding<?= $qid ?>+
index*step<?= $qid ?>;

ctx<?= $qid ?>.clearRect(0,90,canvas<?= $qid ?>.width,30);

// redraw red point
ctx<?= $qid ?>.fillStyle="red";
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.arc(pointX,50,7,0,Math.PI*2);
ctx<?= $qid ?>.fill();

document.getElementById(
"answer_display_<?= $qid ?>"
).value=value;

document.getElementById(
"answer_hidden_<?= $qid ?>"
).value=value;

});

</script>