<?php
declare(strict_types=1);
$h = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');
$q = $q ?? [];
$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$image = trim($payload['image'] ?? '');
$makeUrl = fn($path) => $path && !preg_match('~^https?://~i',$path)
    ? rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'),'/').'/'.ltrim($path,'/')
    : $path;
$image = $makeUrl($image);

/* ---------------- Resolve instruction_id safely ---------------- */
$instructionId = (int)($q['instruction_id'] ?? 0);

if ($instructionId === 0) {
    $stmtInst = $conn->prepare("SELECT instruction_id FROM quiz_questions WHERE id = ?");
    $stmtInst->bind_param("i", $q['id']);
    $stmtInst->execute();
    $resInst = $stmtInst->get_result();
    if ($rowInst = $resInst->fetch_assoc()) {
        $instructionId = (int)$rowInst['instruction_id'];
    }
    $stmtInst->close();
}

/* ---------------- Fetch sibling questions in this group ---------------- */
$questions = [];
if ($instructionId > 0) {
    $sql = "SELECT id, question_payload, correct_answer
            FROM quiz_questions
            WHERE instruction_id = ?
            ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructionId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();
}

/* Current question's number within the outer loop (1-based) */
$question_number = $question_number ?? null;

/* Only render once, on the first sub-question of the group.
   For the rest, hide the empty duplicate wrapper card the parent page built for them. */
if (empty($questions) || (int)$q['id'] !== (int)$questions[0]['id']) {
    if ($question_number !== null) {
        echo '<style>#q' . (int)$question_number . '{display:none !important;}</style>';
    }
    return;
}

/* We are on the results page only when explanation_page.php has already defined
   $latest_time (the attempt timestamp). quiz.php never sets this. */
$isResultPage = isset($latest_time) && $latest_time !== null;

$answersById = [];
if ($isResultPage) {
    $ids = array_map(fn($qq) => (int)$qq['id'], $questions);
    $idsList = implode(',', $ids);

    if ($idsList !== '') {
        $sqlAns = "SELECT question_id, student_answer, is_correct
                   FROM student_answers
                   WHERE student_id = ? AND quiz_id = ? AND created_at = ? AND question_id IN ($idsList)";
        $stmtAns = $conn->prepare($sqlAns);
        $stmtAns->bind_param("iis", $student_id, $topic_id, $latest_time);
        $stmtAns->execute();
        $resAns = $stmtAns->get_result();
        while ($rowAns = $resAns->fetch_assoc()) {
            $answersById[(int)$rowAns['question_id']] = $rowAns;
        }
        $stmtAns->close();
    }
}
?>
<style>
<?php if ($isResultPage && $question_number !== null): ?>
/* Hide the parent page's own "Your Answer / Not Attempted / Correct Answer" alert
   boxes for THIS card only, since this template shows its own per-line results
   instead. Purely scoped by ID, no changes needed in explanation_page.php. */
#q<?= (int)$question_number ?> > .ps-5 > .alert { display: none !important; }
<?php endif; ?>

.image-panel{
    background:#fff;
    border-radius:18px;
    padding:40px 45px;
    box-shadow:0 2px 10px rgba(0,0,0,.07);
    margin:20px 0;
    max-width:100%;
    box-sizing:border-box;
    overflow:hidden;
}
.image-panel-grid{
    display:flex;
    flex-wrap:wrap;
    gap:45px;
    align-items:center;
}
.image-left{
    flex:1 1 320px;
    max-width:480px;
    text-align:center;
    min-width:0;
}
.image-left img{
    width:100%;
    max-width:480px;
    height:auto;
}
.image-right{
    flex:1 1 380px;
    min-width:0;
    box-sizing:border-box;
}
.question-row{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:14px 16px;
    padding:14px 0;
    border-bottom:1px solid #f0f1f3;
}
.question-row:last-child{
    border-bottom:none;
}
.question-bullet{
    color:#1F669C;
    font-weight:700;
    flex-shrink:0;
}
.question-text{
    font-size:17px;
    color:#2a2a2a;
    line-height:1.5;
    flex:1 1 200px;
    min-width:0;
    word-break:break-word;
}
.answer{
    width:170px;
    max-width:100%;
    flex-shrink:0;
    padding:9px 12px;
    font-size:16px;
    color:#222;
    border:1.5px solid #d7dbe0;
    border-radius:8px;
    background:#fafbfc;
    outline:none;
    box-sizing:border-box;
    transition:border-color .2s, box-shadow .2s, background .2s;
}
.answer:focus{
    border-color:#1F669C;
    background:#fff;
    box-shadow:0 0 0 3px rgba(31,102,156,.12);
}
.answer:disabled{
    color:#222;
    -webkit-text-fill-color:#222;
    opacity:1;
    background:#f4f5f7;
    border-color:#e2e4e8;
}
.result-line{
    flex-basis:100%;
    font-size:13.5px;
    padding-left:22px;
}
.result-correct{ color:#198754; font-weight:600; }
.result-wrong{ color:#dc3545; font-weight:600; }
.result-empty{ color:#8a8f98; font-style:italic; }
.correct-answer-text{ color:#198754; font-weight:600; }

@media(max-width:900px){
.image-panel{ padding:24px; }
.image-panel-grid{ gap:24px; }
.image-left{ flex-basis:100%; max-width:340px; margin:0 auto; }
.image-right{ flex-basis:100%; }
.question-text{ font-size:16px; }
.answer{ width:150px; font-size:15px; }
}
@media(max-width:480px){
.question-row{ gap:10px 12px; }
.answer{ width:130px; padding:8px 10px; }
}
</style>
<div class="image-panel">
<div class="image-panel-grid">
<div class="image-left">
<img src="<?= $h($image) ?>" alt="">
</div>
<div class="image-right">
<?php foreach($questions as $question):
    $p = json_decode($question['question_payload'] ?? '{}', true) ?: [];
    $item = $p['items'][0] ?? [];
    $questionText = $item['question'] ?? '';

    $studentAns = '';
    $isCorrect  = null;
    $attempted  = false;

    if ($isResultPage) {
        $rec = $answersById[(int)$question['id']] ?? null;
        if ($rec !== null) {
            $studentAns = trim((string)($rec['student_answer'] ?? ''));
            if ($studentAns !== '') {
                $attempted = true;
                $isCorrect = $rec['is_correct'];
            }
        }
    }
?>
<div class="question-row">
    <span class="question-bullet">&bull;</span>
    <span class="question-text"><?= $h($questionText) ?></span>
    <input
        type="text"
        class="answer"
        name="answer[<?= (int)$question['id'] ?>]"
        value="<?= $h($studentAns) ?>"
        <?= $isResultPage ? 'disabled' : '' ?>
        autocomplete="off">

    <?php if ($isResultPage): ?>
        <div class="result-line">
            <?php if (!$attempted): ?>
                <span class="result-empty">Not Attempted</span>
            <?php elseif ((int)$isCorrect === 1): ?>
                <span class="result-correct">Correct</span>
            <?php else: ?>
                <span class="result-wrong">Wrong</span>
                — Correct answer: <span class="correct-answer-text"><?= $h($question['correct_answer']) ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
</div>