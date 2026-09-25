<?php
declare(strict_types=1);

$pvdH = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$q = $q ?? [];

$pvdId      = (int)($q['id'] ?? 0);
$pvdPayload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$pvdMode    = $pvdPayload['mode'] ?? 'value_blanks';

/* worksheet number 1,2,3... from the loop index */
$pvdNo = isset($index) ? ((int)$index + 1) : (isset($num) ? (int)$num : 1);

$pvdImg      = trim((string)($q['question_image'] ?? ''));
$pvdIsResult = isset($latest_time) && $latest_time !== null;

/* student's own submission (result page only — NO grading here) */
$pvdStudentArr    = [];
$pvdStudentScalar = '';
if ($pvdIsResult) {
    $pvdRaw = $q['student_answer'] ?? '';
    if (is_string($pvdRaw) && $pvdRaw !== '') {
        $pvdDec = json_decode($pvdRaw, true);
        if (is_array($pvdDec)) {
            $pvdStudentArr = $pvdDec;      // value_blanks -> {"place":..,"value":..}
        } else {
            $pvdStudentScalar = trim($pvdRaw); // mcq -> scalar option text
        }
    }
}

/* highlight helper: escape text first, then bold the given terms */
$pvdHi = function ($text, $terms) use ($pvdH) {
    $out = $pvdH($text);
    foreach ((array)$terms as $t) {
        $t = (string)$t;
        if ($t === '') continue;
        $et = $pvdH($t);
        $out = str_replace($et, '<span class="pvd-hl">' . $et . '</span>', $out);
    }
    return $out;
};

$pvdDigit  = (string)($pvdPayload['digit'] ?? '');
$pvdNumber = (string)($pvdPayload['number'] ?? '');
$pvdQtext  = (string)($pvdPayload['question'] ?? '');
$pvdHls    = $pvdPayload['highlights'] ?? [];
$pvdOpts   = $pvdPayload['options'] ?? [];
?>

<style>
/* ⬇️ no card, no background — renders normally on the page */
.pvd-wrap{
    background:transparent;
    font-family:'Segoe UI',system-ui,sans-serif;
    color:#111;
    margin-left: 6px;
    margin-top: 20px;
    margin-bottom: 26px;
}
.pvd-wrap::after{ content:""; display:block; clear:both; }

.pvd-illus{
    float:right;
    max-width:210px;
    width:35%;
    height:auto;
    margin:0 0 10px 18px;
}

.pvd-qtext{
    font-size:17px;
    font-weight:600;
    line-height:1.7;
    margin-bottom:6px;
}
.pvd-no{ font-weight:700; margin-right:4px; }
.pvd-hl{ color:#1b3fae; font-weight:800; }

/* ---- blanks (value_blanks) ---- */
.pvd-line{
    font-size:16px;
    line-height:2.2;
    margin:10px 0;
}
.pvd-blank{
    border:none;
    border-bottom:2px solid #111;
    min-width:170px;
    max-width:100%;
    font-size:16px;
    font-weight:700;
    text-align:center;
    outline:none;
    background:transparent;
    padding:2px 8px;
    margin:0 6px;
}
.pvd-blank:focus{ border-bottom-color:#1b3fae; }
.pvd-blank:disabled{ color:#1b3fae; }

/* ---- mcq ---- */
.pvd-options{
    display:flex;
    flex-wrap:wrap;
    gap:14px 26px;
    margin-top:12px;
}
.pvd-opt{
    display:inline-flex;
    align-items:center;
    gap:10px;
    cursor:pointer;
    font-size:16px;
    font-weight:700;
    color:#111;
    user-select:none;
}
.pvd-opt input{
    position:absolute;
    opacity:0;
    width:0;
    height:0;
}
.pvd-box{
    width:36px;
    height:27px;
    border:2px solid #111;
    border-radius:7px;
    background:#fff;
    box-shadow:0 3px 0 #e0a41a;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    transition:.12s;
    flex:0 0 auto;
}
.pvd-opt input:checked + .pvd-box{ background:#eafce9; }
.pvd-opt input:checked + .pvd-box::after{
    content:"\2713";        /* ✓ */
    color:#198754;
    font-size:17px;
    font-weight:900;
    line-height:1;
}
.pvd-opt input:focus + .pvd-box{
    box-shadow:0 3px 0 #e0a41a, 0 0 0 3px rgba(27,63,174,.25);
}
.pvd-opt:hover .pvd-box{
    transform:translateY(-1px);
    box-shadow:0 4px 0 #e0a41a;
}

/* responsive */
@media(max-width:768px){
    .pvd-qtext{ font-size:15px; }
    .pvd-line{ font-size:15px; }
    .pvd-blank{ min-width:120px; font-size:15px; }
    .pvd-illus{ max-width:150px; width:40%; }
    .pvd-options{ gap:12px 18px; }
    .pvd-opt{ font-size:15px; }
}
@media(max-width:480px){
    .pvd-illus{ float:none; display:block; margin:0 auto 12px; width:60%; }
    .pvd-blank{ min-width:100px; }
}
</style>

<div class="pvd-wrap">

    <?php if ($pvdImg !== ''): ?>
        <img src="<?= $pvdH($pvdImg) ?>" alt="" class="pvd-illus">
    <?php endif; ?>

    <?php if ($pvdMode === 'value_blanks'): ?>

        <div class="pvd-qtext">
            <span class="pvd-no"><?= $pvdNo ?>.</span>
            What is the value of
            <span class="pvd-hl"><?= $pvdH($pvdDigit) ?></span>
            in
            <span class="pvd-hl"><?= $pvdH($pvdNumber) ?></span> ?
        </div>

        <div class="pvd-line">
            <span class="pvd-hl"><?= $pvdH($pvdDigit) ?></span>
            is in the
            <input type="text" class="pvd-blank"
                   name="answer[<?= $pvdId ?>][place]"
                   value="<?= $pvdH($pvdStudentArr['place'] ?? '') ?>"
                   autocomplete="off" <?= $pvdIsResult ? 'disabled' : '' ?>>
            place
        </div>

        <div class="pvd-line">
            <span class="pvd-hl"><?= $pvdH($pvdDigit) ?></span>
            has a value of
            <input type="text" class="pvd-blank"
                   name="answer[<?= $pvdId ?>][value]"
                   value="<?= $pvdH($pvdStudentArr['value'] ?? '') ?>"
                   autocomplete="off" <?= $pvdIsResult ? 'disabled' : '' ?>>
        </div>

    <?php else: /* ---- mcq ---- */ ?>

        <div class="pvd-qtext">
            <span class="pvd-no"><?= $pvdNo ?>.</span>
            <?= $pvdHi($pvdQtext, $pvdHls) ?>
        </div>

     <div class="pvd-options">

    <?php foreach ((array)$pvdOpts as $index => $opt):

        $optStr = (string)$opt;
        $optionLetter = chr(97 + $index);

        $checked = (
            $pvdIsResult &&
            $pvdStudentScalar !== '' &&
            $pvdStudentScalar === trim($optStr)
        );

    ?>

        <label class="pvd-opt">

            <!-- OPTION TEXT FIRST -->
            <span class="pvd-opt-text">
                <?= $optionLetter ?>) <?= $pvdH($optStr) ?>
            </span>

            <!-- BOX AFTER TEXT -->
            <input
                type="radio"
                name="answer[<?= $pvdId ?>]"
                value="<?= $pvdH($optStr) ?>"
                <?= $checked ? 'checked' : '' ?>
                <?= $pvdIsResult ? 'disabled' : '' ?>
            >

            <span class="pvd-box"></span>

        </label>

    <?php endforeach; ?>

</div>
    <?php endif; ?>

</div>