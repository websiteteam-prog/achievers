<?php
declare(strict_types=1);

$h = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');

$q = $q ?? [];

$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];

/* ---------- background image ---------- */
$image = trim($payload['image'] ?? 'images/train.png');

$makeUrl = fn($path)=>$path && !preg_match('~^https?://~i',$path)
    ? rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'),'/').'/'.ltrim($path,'/')
    : $path;

$image = $makeUrl($image);

/* ---------- instruction id ---------- */
$instructionId = (int)($q['instruction_id'] ?? 0);

if($instructionId===0){
    $stmt=$conn->prepare("SELECT instruction_id FROM quiz_questions WHERE id=?");
    $stmt->bind_param("i",$q['id']);
    $stmt->execute();
    $r=$stmt->get_result()->fetch_assoc();
    $instructionId=(int)($r['instruction_id'] ?? 0);
    $stmt->close();
}

/* ---------- all questions in this group ---------- */
$questions=[];
if($instructionId>0){
    $stmt=$conn->prepare("
        SELECT id,question_payload,correct_answer
        FROM quiz_questions
        WHERE instruction_id=?
        ORDER BY id
    ");
    $stmt->bind_param("i",$instructionId);
    $stmt->execute();
    $res=$stmt->get_result();
    while($row=$res->fetch_assoc()){
        $questions[]=$row;
    }
    $stmt->close();
}

/* current question number in the outer loop (1-based) */
$question_number = $question_number ?? null;

/* render only once, on the first sub-question of the group.
   for the rest, hide the empty duplicate card the parent built. */
if(empty($questions) || (int)$q['id'] !== (int)$questions[0]['id']){
    if($question_number !== null){
        echo '<style>#q'.(int)$question_number.'{display:none!important;}</style>';
    }
    return;
}

/* results page only when explanation_page.php has already defined $latest_time.
   quiz.php never sets this. */
$isResultPage = isset($latest_time) && $latest_time !== null;

/* ---------- fetch student answers for this attempt ---------- */
$answersById = [];
if($isResultPage){
    $ids = array_map(fn($qq)=>(int)$qq['id'], $questions);
    $idsList = implode(',', $ids);

    if($idsList !== ''){
        $sqlAns = "SELECT question_id, student_answer, is_correct
                   FROM student_answers
                   WHERE student_id = ? AND quiz_id = ? AND created_at = ? AND question_id IN ($idsList)";
        $stmtAns = $conn->prepare($sqlAns);
        $stmtAns->bind_param("iis", $student_id, $topic_id, $latest_time);
        $stmtAns->execute();
        $resAns = $stmtAns->get_result();
        while($rowAns = $resAns->fetch_assoc()){
            $answersById[(int)$rowAns['question_id']] = $rowAns;
        }
        $stmtAns->close();
    }
}
?>

<style>
<?php if ($isResultPage && $question_number !== null): ?>
/* hide the parent page's own Your Answer / Correct Answer alert boxes for THIS card,
   since this template shows its own inline result state. */
#q<?= (int)$question_number ?> > .ps-5 > .alert { display:none !important; }
<?php endif; ?>

.train-panel{
    background:#fff;
    border-radius:20px;
    padding:30px!important;
    box-shadow:0 3px 10px rgba(0,0,0,.08);
    overflow:auto;
    margin-bottom: 25px;
    margin-top: 20px;
}

.train-wrapper{
    position:relative;
    max-width:1200px;
    width:100%;
    margin:auto;
}

.train-image{
    width:100%;
    display:block;
    position:relative;
    z-index:1;
    pointer-events:none;
}

/* slot holds the input (and, on results, the correct-answer badge) */
.coach-slot{
    position:absolute;
    width:8.2%;
    height:4.8%;
    z-index:9999;
}

.coach-input{
    width:100%;
    height:100%;
    border:none;
    outline:none;
    background:transparent;
    text-align:center;
    font-size:15px;
    font-weight:700;
    font-family:Arial,Helvetica,sans-serif;
    color:#000;
    z-index:9999;
    padding:0;
    line-height:1;
    box-sizing:border-box;
}

/* ---- result states (only added on the results page) ---- */
.coach-slot.res .coach-input{
    background:#fff;
    border:2px solid #ced4da;
    border-radius:6px;
}
.coach-slot.res.correct .coach-input{ border-color:#198754; color:#198754; }
.coach-slot.res.wrong   .coach-input{ border-color:#dc3545; color:#dc3545; }
.coach-slot.res.empty   .coach-input{ border-color:#adb5bd; }
.coach-slot.res.given   .coach-input{ border-color:#ced4da; color:#000; }

/* correct-answer badge shown under a wrong / not-attempted coach */
.coach-correct{
    position:absolute;
    top:106%;
    left:50%;
    transform:translateX(-50%);
    white-space:nowrap;
    background:#198754;
    color:#fff;
    font-size:11px;
    font-weight:700;
    font-family:Arial,Helvetica,sans-serif;
    padding:2px 6px;
    border-radius:5px;
    z-index:10000;
    pointer-events:none;
}

/* positions (applied to the slot now) */
.c1{ left:22.2%; top:50.7%; }
.c2{ left:36.2%; top:50.7%; }
.c3{ left:49.6%; top:50.7%; }
.c4{ left:62.7%; top:50.7%; }
.c5{ left:75.6%; top:50.7%; }
.c6{ left:96.6%; top:50.7%; transform:translateX(-100%); }
</style>

<div class="train-panel">
<div class="train-wrapper">

    <img src="<?= $h($image) ?>" class="train-image">

    <?php foreach($questions as $index=>$question):

        $p       = json_decode($question['question_payload'] ?? '{}', true) ?: [];
        $isFixed = !empty($p['fixed']);
        $slot    = 'c'.($index+1);

        $studentAns = '';
        $isCorrect  = null;
        $attempted  = false;
        $resClass   = '';

        if($isResultPage){
            if($isFixed){
                $resClass = 'res given';
            }else{
                $rec = $answersById[(int)$question['id']] ?? null;
                if($rec !== null){
                    $studentAns = trim((string)($rec['student_answer'] ?? ''));
                    if($studentAns !== ''){
                        $attempted = true;
                        $isCorrect = (int)$rec['is_correct'];
                    }
                }
                $resClass = 'res '.($attempted ? ($isCorrect === 1 ? 'correct' : 'wrong') : 'empty');
            }
        }
    ?>

    <div class="coach-slot <?= $slot ?> <?= $resClass ?>">

        <?php if($isFixed): ?>

            <input
                type="text"
                class="coach-input"
                name="answer[<?= (int)$question['id'] ?>]"
                value="<?= $h($p['fixed']) ?>"
                readonly
                tabindex="-1">

        <?php elseif($isResultPage): ?>

            <input
                type="text"
                class="coach-input"
                name="answer[<?= (int)$question['id'] ?>]"
                value="<?= $h($studentAns) ?>"
                disabled
                autocomplete="off">

            <?php if(!$attempted || $isCorrect !== 1): ?>
                <div class="coach-correct"><?= $h($question['correct_answer']) ?></div>
            <?php endif; ?>

        <?php else: ?>

            <input
                type="text"
                class="coach-input"
                name="answer[<?= (int)$question['id'] ?>]"
                autocomplete="off">

        <?php endif; ?>

    </div>

    <?php endforeach; ?>

</div>
</div>