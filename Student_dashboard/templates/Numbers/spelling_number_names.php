<?php
declare(strict_types=1);

$spnH = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$q = $q ?? [];

$spnId      = (int)($q['id'] ?? 0);
$spnPayload = json_decode($q['question_payload'] ?? '{}', true) ?: [];

$spnNumber = (string)($spnPayload['number'] ?? '');
$spnWrong  = (string)($spnPayload['wrong'] ?? '');

/* letter label a, b, c ... from the loop index */
$spnIdx    = isset($index) ? (int)$index : (isset($num) ? ((int)$num - 1) : 0);
$spnLetter = ($spnIdx >= 0 && $spnIdx < 26)
    ? chr(ord('a') + $spnIdx)
    : (string)($spnIdx + 1);

$spnIsResult = isset($latest_time) && $latest_time !== null;

/* student's own submission (result page only — NO grading here) */
$spnStudent = '';
if ($spnIsResult) {
    $spnRaw = $q['student_answer'] ?? '';
    if (is_string($spnRaw)) {
        $spnStudent = trim($spnRaw);
    }
}

/* wrong text: escape, then colour any "?" marker red (like the worksheet) */
$spnWrongHtml = str_replace('?', '<span class="spn-q">?</span>', $spnH($spnWrong));
?>

<style>
/* no card / no background — renders normally on the page */
.spn-wrap{
    background:transparent;
    margin-left: 6px;
    margin-top: 20px;
    font-family:'Segoe UI',system-ui,sans-serif;
    color:#111;
}

.spn-line1{
    font-size:16px;
    font-weight:700;
    line-height:1.7;
    margin-bottom:14px;
}
.spn-no{ margin-right:2px; }
.spn-num{ color:#1b3fae; font-weight:800; }
.spn-wrong{ font-weight:700; color:#111; }
.spn-q{ color:#e11; font-weight:900; }

.spn-line2{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    font-size:16px;
}
.spn-arrow{ color:#111; font-weight:900; }

.spn-blank{
    flex:1;
    min-width:240px;
    max-width:100%;
    border:none;
    border-bottom:2px solid #111;
    font-size:16px;
    font-weight:600;
    outline:none;
    background:transparent;
    padding:4px 6px;
}
.spn-blank:focus{ border-bottom-color:#1b3fae; }
.spn-blank:disabled{ color:#1b3fae; }

@media(max-width:768px){
    .spn-line1{ font-size:15px; }
    .spn-line2{ font-size:15px; }
    .spn-blank{ min-width:150px; }
}
</style>

<div class="spn-wrap">

    <div class="spn-line1">
        <span class="spn-no"><?= $spnH($spnLetter) ?>)</span>
        <span class="spn-num"><?= $spnH($spnNumber) ?></span> =
        <span class="spn-wrong"><?= $spnWrongHtml ?></span>
    </div>

    <div class="spn-line2">
        <span class="spn-arrow">&#10148;</span>
        The right answer is :
        <input type="text" class="spn-blank"
               name="answer[<?= $spnId ?>]"
               value="<?= $spnH($spnStudent) ?>"
               autocomplete="off" <?= $spnIsResult ? 'disabled' : '' ?>>
    </div>

</div>