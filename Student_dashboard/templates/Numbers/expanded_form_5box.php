<?php
declare(strict_types=1);

$h = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');

$q=$q??[];

$id=(int)($q['id']??0);

$payload=json_decode($q['question_payload']??'{}',true)?:[];

$sentence=$payload['sentence']??'';

$isResultPage=isset($latest_time)&&$latest_time!==null;

/* Number label 1) 2) 3) ... */
$label = ($index + 1) . ')';

/* ---------------- student answers ---------------- */

$correct=json_decode($q['correct_answer']??'[]',true)?:[];

$boxCount=count($correct);

$student=array_fill(0,$boxCount,'');

if($isResultPage){

$stmt=$conn->prepare("
SELECT student_answer
FROM student_answers
WHERE student_id=?
AND quiz_id=?
AND question_id=?
AND created_at=?
LIMIT 1
");

$stmt->bind_param("iiis",$student_id,$topic_id,$id,$latest_time);

$stmt->execute();

$row=$stmt->get_result()->fetch_assoc();

$stmt->close();

if($row){

$tmp=json_decode($row['student_answer'],true);

if(is_array($tmp)){

for($i=0;$i<$boxCount;$i++){

$student[$i]=$tmp[$i]??'';

}

}

}

}
?>
<style>
.ef-card{
background:#fff;
padding:12px 20px;
margin-bottom:10px;
border-radius:12px;
box-shadow:0 2px 8px rgba(0,0,0,.08);
}
.ef-card:hover{
box-shadow:0 5px 15px rgba(0,0,0,.12);
}
.ef-row{
display:flex;
align-items:center;
flex-wrap:wrap;
gap:10px;
}
.ef-label{
font-size:20px;
font-weight:600;
color:#111;
min-width:26px;
}
.ef-sentence{
font-size:20px;
font-weight:600;
color:#111;
}
.ef-eq{
font-size:20px;
font-weight:600;
color:#111;
margin-right:2px;
}
.ef-input{
width:90px;
height:38px;
border:2px solid #9c27ff;
border-radius:6px;
text-align:center;
font-size:16px;
outline:none;
}
.ef-plus{
font-size:20px;
font-weight:bold;
color:#111;
}
@media(max-width:768px){
.ef-sentence,.ef-label,.ef-eq{ font-size:16px; }
.ef-input{ width:70px; height:34px; font-size:14px; }
}
</style>
<div class="ef-card">
<div class="ef-row">
<span class="ef-label"><?= $h($label) ?></span>
<span class="ef-sentence"><?= $h($sentence) ?></span>
<?php for($i=0;$i<$boxCount;$i++): ?>
<input
type="text"
class="ef-input"
name="answer[<?= $id ?>][]"
value="<?= $h($student[$i]) ?>"
<?= $isResultPage?'disabled':'' ?>
>
<?php if($i<$boxCount-1): ?>
<span class="ef-plus">+</span>
<?php endif; ?>
<?php endfor; ?>
</div>
</div>