<?php
$question_id = $real_question_id;

$canvasSize = $payload['canvas_size'] ?? 360;
$radius = ($canvasSize / 2) - 30;
$items  = $payload['items'] ?? [];

$studentAngles = [];
if (!empty($student_angles)) {
    foreach ($student_angles as $v) $studentAngles[] = (int) round(floatval($v));
}
if (empty($studentAngles) || array_sum($studentAngles) <= 0) {
    $n = max(count($items), 1);
    $equal = (int) round(360 / $n);
    $studentAngles = array_fill(0, $n, $equal);
    $studentAngles[$n - 1] += 360 - $equal * $n; 
}

$readonly = isset($is_result_page);

$colors = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f',
           '#edc949','#af7aa1','#ff9da7','#9c755f','#bab0ab'];
?>

<style>
.pie-editor{ margin-top:25px; margin-bottom:30px; text-align:center; }
.pieCanvas{ border:2px solid #ddd; border-radius:50%; cursor:grab; background:#fff; }
.pieCanvas.locked{ cursor:default; }
.pie-note{ margin-top:12px; font-size:14px; color:#666; }
.legendBox{ margin-top:20px; display:flex; justify-content:center; flex-wrap:wrap; gap:10px; }
.legendItem{ display:flex; align-items:center; gap:8px; padding:6px 12px; border-radius:20px; background:#f5f5f5; font-size:14px; }
.legendColor{ width:16px; height:16px; border-radius:4px; }
</style>

<div class="pie-editor">

<canvas
id="pieCanvas<?= $question_id ?>"
class="pieCanvas <?= $readonly?'locked':'' ?>"
width="<?= $canvasSize ?>"
height="<?= $canvasSize ?>">
</canvas>

<div class="pie-note">
Blue handles drag karo — table apne aap fill hogi.
</div>

<div class="legendBox">
<?php foreach($items as $i=>$row): ?>
<div class="legendItem">
<div class="legendColor" style="background:<?= $colors[$i%count($colors)] ?>"></div>
<?= htmlspecialchars($row['label']) ?>
</div>
<?php endforeach; ?>
</div>

</div>

<script>
(function(){

const questionId = <?= $question_id ?>;
const canvas = document.getElementById("pieCanvas"+questionId);
if(!canvas) return;

const ctx = canvas.getContext("2d");
const W = canvas.width, H = canvas.height;
const CX = W/2, CY = H/2;
const R = <?= $radius ?>;
const READONLY = <?= $readonly ? 'true' : 'false' ?>;
const COLORS = <?= json_encode($colors) ?>;

let angles = <?= json_encode(array_map('intval', array_values($studentAngles))) ?>;
let boundaries = [];
let draggingIndex = -1;
const HANDLE_RADIUS = 10;

function deg2rad(d){ return d*Math.PI/180; }
function rad2deg(r){ return r*180/Math.PI; }
function normalizeAngle(a){ while(a<0) a+=360; while(a>=360) a-=360; return a; }

function buildBoundaries(){
    boundaries=[0];
    let sum=0;
    for(let i=0;i<angles.length-1;i++){ sum+=angles[i]; boundaries.push(sum); }
}

function drawCircle(){
    ctx.beginPath(); ctx.arc(CX,CY,R,0,Math.PI*2);
    ctx.fillStyle="#ffffff"; ctx.fill();
    ctx.lineWidth=2; ctx.strokeStyle="#444"; ctx.stroke();
}

function drawSlices(){
    let start=-90;
    for(let i=0;i<angles.length;i++){
        let end=start+angles[i];
        if(angles[i]>0){
            ctx.beginPath();
            ctx.moveTo(CX,CY);
            ctx.arc(CX,CY,R,deg2rad(start),deg2rad(end));
            ctx.closePath();
            ctx.fillStyle=COLORS[i%COLORS.length];
            ctx.globalAlpha=.40; ctx.fill(); ctx.globalAlpha=1;
        }
        start=end;
    }
}

function drawLabels(){
    let start=-90;
    ctx.textAlign="center";
    ctx.fillStyle="#1f2937";
    for(let i=0;i<angles.length;i++){
        let slice=angles[i];
        if(slice>=12){                       
            let mid=deg2rad(start+slice/2);
            let lx=CX+Math.cos(mid)*R*0.6;
            let ly=CY+Math.sin(mid)*R*0.6;
            let pct=Math.round(slice/360*100);
            ctx.font="bold 13px sans-serif";
            ctx.fillText(Math.round(slice)+"°", lx, ly-4);
            ctx.font="bold 12px sans-serif";
            ctx.fillText(pct+"%", lx, ly+13);
        }
        start+=slice;
    }
}

function drawDividers(){
    for(let i=0;i<boundaries.length;i++){
        let a=boundaries[i]-90;
        let x=CX+R*Math.cos(deg2rad(a));
        let y=CY+R*Math.sin(deg2rad(a));
        ctx.beginPath(); ctx.moveTo(CX,CY); ctx.lineTo(x,y);
        ctx.lineWidth=2; ctx.strokeStyle="#222"; ctx.stroke();
        if(i===0 || READONLY) continue;
        ctx.beginPath(); ctx.arc(x,y,HANDLE_RADIUS,0,Math.PI*2);
        ctx.fillStyle="#0d6efd"; ctx.fill();
        ctx.lineWidth=2; ctx.strokeStyle="#fff"; ctx.stroke();
    }
}

function syncMainTable(){
    let total=0;
    for(let i=0;i<angles.length;i++){
        angles[i]=Math.round(angles[i]);
        total+=angles[i];
        let inp=document.querySelector('[name="answer['+questionId+'][angles]['+i+']"]');
        if(inp) inp.value=angles[i];
    }
    let t=document.querySelector('[name="answer['+questionId+'][total_angle]"]');
    if(t) t.value=Math.round(total);
}

function redraw(){
    buildBoundaries();
    ctx.clearRect(0,0,W,H);
    drawCircle();
    drawSlices();
    drawLabels();
    drawDividers();
    // syncMainTable();
}

function getMousePos(e){ const r=canvas.getBoundingClientRect(); return {x:e.clientX-r.left,y:e.clientY-r.top}; }
function getTouchPos(e){ const r=canvas.getBoundingClientRect(); return {x:e.touches[0].clientX-r.left,y:e.touches[0].clientY-r.top}; }
function pointAngle(x,y){ return normalizeAngle(rad2deg(Math.atan2(y-CY,x-CX))+90); }
function handlePosition(i){ let a=boundaries[i]-90; return {x:CX+R*Math.cos(deg2rad(a)),y:CY+R*Math.sin(deg2rad(a))}; }

function detectHandle(x,y){
    for(let i=1;i<boundaries.length;i++){
        let h=handlePosition(i);
        let d=Math.sqrt((x-h.x)*(x-h.x)+(y-h.y)*(y-h.y));
        if(d<=HANDLE_RADIUS+6) return i;
    }
    return -1;
}

function moveBoundary(index,newAngle){
    let prev=boundaries[index-1];
    let next=(index==boundaries.length-1)?360:boundaries[index+1];
    const MIN=5;
    if(newAngle<prev+MIN) newAngle=prev+MIN;
    if(newAngle>next-MIN) newAngle=next-MIN;
    boundaries[index]=newAngle;
    for(let i=0;i<angles.length;i++){
        angles[i]=(i==angles.length-1)?(360-boundaries[i]):(boundaries[i+1]-boundaries[i]);
    }
    redraw();
    syncMainTable(); 
}

if(!READONLY){
    canvas.addEventListener("mousedown",function(e){
        let p=getMousePos(e);
        draggingIndex=detectHandle(p.x,p.y);
        if(draggingIndex!=-1) canvas.style.cursor="grabbing";
    });
    window.addEventListener("mousemove",function(e){
        if(draggingIndex==-1) return;
        let p=getMousePos(e); moveBoundary(draggingIndex,pointAngle(p.x,p.y));
    });
    window.addEventListener("mouseup",function(){ draggingIndex=-1; canvas.style.cursor="grab"; });

    canvas.addEventListener("touchstart",function(e){
        e.preventDefault(); let p=getTouchPos(e); draggingIndex=detectHandle(p.x,p.y);
    });
    canvas.addEventListener("touchmove",function(e){
        if(draggingIndex==-1) return;
        e.preventDefault(); let p=getTouchPos(e); moveBoundary(draggingIndex,pointAngle(p.x,p.y));
    });
    canvas.addEventListener("touchend",function(){ draggingIndex=-1; });
}

canvas.addEventListener("contextmenu",function(e){ e.preventDefault(); });
if(READONLY){ canvas.style.pointerEvents="none"; canvas.style.opacity="0.9"; }
window.addEventListener("resize",function(){ redraw(); });

redraw();  

})();
</script>