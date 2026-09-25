<?php
declare(strict_types=1);

$h = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');

$q = $q ?? [];

$payloadRaw = (string)($q['question_payload'] ?? '[]');
$items = json_decode($payloadRaw,true);

if(!is_array($items)){
    $items = [];
}

$qid = (int)$q['id'];

?>

<style>

.coord-wrap{
    width:100%;
    padding:20px;
}

.coord-title{
    font-size:22px;
    font-weight:700;
    margin-bottom:20px;
}

.letter-picker{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom:20px;
}

.letter-btn{
    border:2px solid #1976d2;
    background:#fff;
    color:#1976d2;
    border-radius:8px;
    padding:8px 14px;
    font-weight:bold;
    cursor:pointer;
}

.coord-list-box{
    width:900px;
    margin:25px auto 0;
    background:#ff5b5b;
    color:#fff;
    border-radius:30px;
    padding:25px;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
    font-size:24px;
    font-weight:bold;
}

.coord-msg{
    background:#fff3cd;
    color:#856404;
    border:1px solid #ffeeba;
    padding:12px 16px;
    border-radius:8px;
    margin-bottom:15px;
    font-size:16px;
    font-weight:600;
}

.coord-item{
    text-align:left;
}

.letter-btn.active{
    background:#1976d2;
    color:#fff;
}

.canvas-container{
    width:100%;
    max-width:900px;
    aspect-ratio:1/1;
    margin:auto;
    border:1px solid #ddd;
    border-radius:12px;
    background:#fff;
    overflow:hidden;
}

#coordCanvas<?= $qid ?>{
    width:100%;
    height:100%;
    display:block;
}

.clear-btn{
    margin-top:15px;
    padding:10px 18px;
    border:none;
    background:#d32f2f;
    color:#fff;
    border-radius:8px;
    cursor:pointer;
}

</style>


<div class="coord-wrap">

<div class="coord-title">
<?= $h($q['question_text'] ?? '') ?>
</div>


<div class="letter-picker">

<?php foreach($items as $item):

$coord = $item['coord'] ?? '';

preg_match('/^([A-Z])/', $coord,$m);

$letter = $m[1] ?? '';

if($letter=='') continue;
?>

<button
type="button"
class="letter-btn"
data-letter="<?= $letter ?>">
<?= $letter ?>
</button>

<?php endforeach; ?>

</div>

<div class="coord-msg">
    <strong>Note:</strong> Please choose a letter first, then click on the graph to mark its point.
</div>

<div class="canvas-container">

<canvas id="coordCanvas<?= $qid ?>"></canvas>

</div>

<button
type="button"
class="clear-btn"
onclick="clearCanvas<?= $qid ?>()">
Clear
</button>

<div class="coord-list-box">
<?php foreach($items as $index => $item):

$coord = $item['coord'] ?? '';
preg_match('/^([A-Z])/', $coord,$m);
$letter = $m[1] ?? '';

?>
<div class="coord-item">
    <?= ($index+1) ?>)
    <?= $h($coord) ?>

    <input
        type="hidden"
        id="answer_<?= $qid ?>_<?= $letter ?>"
        name="answer[<?= $qid ?>][<?= $letter ?>]"
        value=""
    >
</div>
<?php endforeach; ?>
</div>
</div>

<script>

let ACTIVE_LETTER_<?= $qid ?> = null;

document.querySelectorAll('.letter-btn').forEach(btn=>{

btn.addEventListener('click',()=>{

document.querySelectorAll('.letter-btn').forEach(b=>{
b.classList.remove('active');
});

btn.classList.add('active');

ACTIVE_LETTER_<?= $qid ?> = btn.dataset.letter;

});

});


const canvas<?= $qid ?> =
document.getElementById('coordCanvas<?= $qid ?>');

const ctx<?= $qid ?> =
canvas<?= $qid ?>.getContext('2d');


canvas<?= $qid ?>.width=900;
canvas<?= $qid ?>.height=900;


const gridSize<?= $qid ?>=35;

const centerX<?= $qid ?>=
canvas<?= $qid ?>.width/2;

const centerY<?= $qid ?>=
canvas<?= $qid ?>.height/2;



function drawGrid<?= $qid ?>(){

ctx<?= $qid ?>.clearRect(
0,
0,
canvas<?= $qid ?>.width,
canvas<?= $qid ?>.height
);

ctx<?= $qid ?>.strokeStyle="#9a9a9a";
ctx<?= $qid ?>.lineWidth=1;


// vertical right
for(let x=centerX<?= $qid ?>;
x<canvas<?= $qid ?>.width;
x+=gridSize<?= $qid ?>){

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(x,0);
ctx<?= $qid ?>.lineTo(x,900);
ctx<?= $qid ?>.stroke();

}

// vertical left

for(let x=centerX<?= $qid ?>;
x>0;
x-=gridSize<?= $qid ?>){

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(x,0);
ctx<?= $qid ?>.lineTo(x,900);
ctx<?= $qid ?>.stroke();

}


// horizontal down

for(let y=centerY<?= $qid ?>;
y<900;
y+=gridSize<?= $qid ?>){

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(0,y);
ctx<?= $qid ?>.lineTo(900,y);
ctx<?= $qid ?>.stroke();

}


// horizontal up

for(let y=centerY<?= $qid ?>;
y>0;
y-=gridSize<?= $qid ?>){

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(0,y);
ctx<?= $qid ?>.lineTo(900,y);
ctx<?= $qid ?>.stroke();

}


// x-axis

ctx<?= $qid ?>.strokeStyle="#000";
ctx<?= $qid ?>.lineWidth=2;

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(0,centerY<?= $qid ?>);
ctx<?= $qid ?>.lineTo(900,centerY<?= $qid ?>);
ctx<?= $qid ?>.stroke();

// right arrow
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(900,centerY<?= $qid ?>);
ctx<?= $qid ?>.lineTo(880,centerY<?= $qid ?>-12);
ctx<?= $qid ?>.moveTo(900,centerY<?= $qid ?>);
ctx<?= $qid ?>.lineTo(880,centerY<?= $qid ?>+12);
ctx<?= $qid ?>.stroke();

// left arrow
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(0,centerY<?= $qid ?>);
ctx<?= $qid ?>.lineTo(15,centerY<?= $qid ?>-10);
ctx<?= $qid ?>.moveTo(0,centerY<?= $qid ?>);
ctx<?= $qid ?>.lineTo(15,centerY<?= $qid ?>+10);
ctx<?= $qid ?>.stroke();

// y-axis

ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(centerX<?= $qid ?>,0);
ctx<?= $qid ?>.lineTo(centerX<?= $qid ?>,900);
ctx<?= $qid ?>.stroke();

// top arrow
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(centerX<?= $qid ?>,0);
ctx<?= $qid ?>.lineTo(centerX<?= $qid ?>-10,15);
ctx<?= $qid ?>.moveTo(centerX<?= $qid ?>,0);
ctx<?= $qid ?>.lineTo(centerX<?= $qid ?>+10,15);
ctx<?= $qid ?>.stroke();

// bottom arrow
ctx<?= $qid ?>.beginPath();
ctx<?= $qid ?>.moveTo(centerX<?= $qid ?>,900);
ctx<?= $qid ?>.lineTo(centerX<?= $qid ?>-10,885);
ctx<?= $qid ?>.moveTo(centerX<?= $qid ?>,900);
ctx<?= $qid ?>.lineTo(centerX<?= $qid ?>+10,885);
ctx<?= $qid ?>.stroke();

ctx<?= $qid ?>.font="bold 14px Arial";
ctx<?= $qid ?>.fillStyle="#000";

for(let i=-10;i<=10;i++){

if(i!=0){

let x=centerX<?= $qid ?>+(i*gridSize<?= $qid ?>);

let y=centerY<?= $qid ?>-(i*gridSize<?= $qid ?>);

ctx<?= $qid ?>.fillText(i,x-8,centerY<?= $qid ?>+20);

ctx<?= $qid ?>.fillText(i,centerX<?= $qid ?>+8,y+5);

}

}

// origin 0
ctx<?= $qid ?>.font="bold 14px Arial";
ctx<?= $qid ?>.fillStyle="#000";

ctx<?= $qid ?>.fillText(
0,
centerX<?= $qid ?>+6,
centerY<?= $qid ?>+18
);

ctx<?= $qid ?>.font="bold 18px Arial";

ctx<?= $qid ?>.font="bold 18px Arial";

ctx<?= $qid ?>.fillText(
"x - axis",
835,
centerY<?= $qid ?>+45
);

ctx<?= $qid ?>.fillText(
"y - axis",
centerX<?= $qid ?>+25,
20
);

}


drawGrid<?= $qid ?>();

canvas<?= $qid ?>.addEventListener('click',function(e){

        if(!ACTIVE_LETTER_<?= $qid ?>){
        alert('Please choose an alphabet first.');
        return;
    }

    const rect=this.getBoundingClientRect();

    let mouseX=e.clientX-rect.left;
    let mouseY=e.clientY-rect.top;

    let x=Math.round((mouseX-centerX<?= $qid ?>)/gridSize<?= $qid ?>);
    let y=Math.round((centerY<?= $qid ?>-mouseY)/gridSize<?= $qid ?>);

    let drawX=centerX<?= $qid ?>+(x*gridSize<?= $qid ?>);
    let drawY=centerY<?= $qid ?>-(y*gridSize<?= $qid ?>);

    // outer circle
    ctx<?= $qid ?>.strokeStyle="red";
    ctx<?= $qid ?>.beginPath();
    ctx<?= $qid ?>.arc(drawX,drawY,8,0,Math.PI*2);
    ctx<?= $qid ?>.stroke();
    
    ctx<?= $qid ?>.fillStyle="red";
    ctx<?= $qid ?>.beginPath();
    ctx<?= $qid ?>.arc(drawX,drawY,3,0,Math.PI*2);
    ctx<?= $qid ?>.fill();
    
    ctx<?= $qid ?>.font="bold 16px Arial";
    ctx<?= $qid ?>.fillStyle="red";
    
    ctx<?= $qid ?>.fillText(
        ACTIVE_LETTER_<?= $qid ?>,
        drawX+10,
        drawY-10
    );
    document.getElementById(
    'answer_<?= $qid ?>_'+ACTIVE_LETTER_<?= $qid ?>
    ).value='('+x+','+y+')';
    document.querySelectorAll('.letter-btn').forEach(b=>{
    b.classList.remove('active');
});

ACTIVE_LETTER_<?= $qid ?> = null;
    });

    function clearCanvas<?= $qid ?>(){
    
    drawGrid<?= $qid ?>();
    
    }

</script>