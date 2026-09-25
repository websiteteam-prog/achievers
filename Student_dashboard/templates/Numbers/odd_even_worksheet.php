<?php
declare(strict_types=1);

$h = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];

$mode = $payload['mode'] ?? '';

$bank = trim($payload['bank'] ?? '');

$isResultPage = isset($latest_time) && $latest_time !== null;

/* ---------------- Student Answer ---------------- */

$studentAnswer = [];

if($isResultPage){

$stmt = $conn->prepare("
SELECT student_answer
FROM student_answers
WHERE student_id=?
AND quiz_id=?
AND question_id=?
AND created_at=?
LIMIT 1
");

$stmt->bind_param(
"iiis",
$student_id,
$topic_id,
$id,
$latest_time
);

$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

$stmt->close();

if($row){

$tmp = json_decode($row['student_answer'],true);

if(is_array($tmp)){

$studentAnswer = $tmp;

}

}

}

/* ---------------- Input Value Helper ---------------- */

$getValue=function($i) use($studentAnswer){

return $studentAnswer[$i] ?? '';

};

?>

<div class="oe-card">

<?php if($mode=='odd_even'): ?>

    <div class="oe-bank">
        <?= nl2br($h($bank)) ?>
    </div>

    <div class="oe-title">
        <?= ($index+1) ?>) Which of the numbers above are:
    </div>

    <div class="oe-row">
        <div class="oe-label">a) odd</div>
        <input
            type="text"
            class="oe-input oe-long"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(0)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>
    </div>

    <div class="oe-row">
        <div class="oe-label">b) even</div>
        <input
            type="text"
            class="oe-input oe-long"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(1)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>
    </div>

<?php elseif($mode=='odd_20_30'): ?>

    <div class="oe-title">
        <?= ($index+1) ?>) Write down all the odd numbers between 20 and 30
    </div>

    <div class="oe-box-row">
        <?php for($i=0;$i<5;$i++): ?>
            <input
                type="text"
                class="oe-input oe-blank"
                name="answer[<?= $id ?>][]"
                value="<?= $h($getValue($i)) ?>"
                <?= $isResultPage ? 'disabled' : '' ?>>
        <?php endfor; ?>
    </div>

<?php elseif($mode=='even_50_60'): ?>

    <div class="oe-title">
        <?= ($index+1) ?>) Write down all the even numbers between 50 and 60
    </div>

    <div class="oe-box-row">
        <?php for($i=0;$i<6;$i++): ?>
            <input
                type="text"
                class="oe-input oe-blank"
                name="answer[<?= $id ?>][]"
                value="<?= $h($getValue($i)) ?>"
                <?= $isResultPage ? 'disabled' : '' ?>>
        <?php endfor; ?>
    </div>

<?php elseif(
        $mode=='odd_plus_odd'
        ||
        $mode=='even_plus_even'
        ||
        $mode=='even_plus_odd'
): ?>

    <div class="oe-title">
        <?= ($index+1) ?>)
        <?php
        if($mode=='odd_plus_odd') echo "Add together two odd numbers. Is the number odd or even?";
        elseif($mode=='even_plus_even') echo "Add together two even numbers. Is the answer odd or even?";
        else echo "Add together 1 even number + 1 odd number. Is the answer odd or even?";
        ?>
    </div>

    <div class="oe-sum-row">
        <span class="oe-letter">a.</span>

        <input
            type="text"
            class="oe-input oe-blank"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(0)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>

        <span class="oe-sign">+</span>

        <input
            type="text"
            class="oe-input oe-blank"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(1)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>

        <span class="oe-sign">=</span>

        <input
            type="text"
            class="oe-input oe-blank"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(2)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>

        <input
            type="text"
            class="oe-input oe-medium"
            placeholder="odd or even"
            name="answer[<?= $id ?>][]"
            value="<?= $h($getValue(3)) ?>"
            <?= $isResultPage ? 'disabled' : '' ?>>
    </div>

<?php endif; ?>

</div>
<style>

.oe-card{
    background:#fff;
    padding:26px 30px;
    border-radius:16px;
    margin-bottom:10px;
    font-family:"Times New Roman", Georgia, serif;
}

/* ---------------- Number Bank ---------------- */
.oe-bank{
    background:#fff;
    border:none;
    border-radius:24px;
    box-shadow:0 10px 22px rgba(0,0,0,.18);
    padding:26px 34px;
    font-size:22px;
    font-weight:700;
    line-height:2.1;
    color:#f0b27a;
    text-align:center;
    letter-spacing:2px;
    margin-bottom:26px;
}

/* ---------------- Title ---------------- */
.oe-title{
    font-size:20px;
    font-weight:700;
    color:#111;
    line-height:1.6;
    margin-bottom:14px;
}

/* ---------------- Normal Row (a/b odd-even) ---------------- */
.oe-row{
    display:flex;
    align-items:center;
    gap:14px;
    margin:10px 0 10px 30px;
}

.oe-label{
    min-width:60px;
    font-size:20px;
    font-weight:700;
    color:#222;
    font-style:italic;
}

/* ---------------- Addition Row ---------------- */
.oe-sum-row{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin:10px 0 4px 30px;
}

.oe-letter{
    font-size:20px;
    font-weight:700;
    margin-right:4px;
}

/* ---------------- Input Boxes (dashed underline style, like image) ---------------- */
.oe-input{
    height:26px;
    min-width:120px;
    border:none;
    border-bottom:2px dashed #e05555;
    background:transparent;
    outline:none;
    text-align:left;
    font-size:20px;
    font-weight:500;
    color:#111;
    font-family:"Times New Roman", Georgia, serif;
}

.oe-input:focus{
    border-bottom-color:#1F669C;
}

.oe-input::placeholder{
    color:#888;
    font-style:italic;
}

/* Long input for a)/b) rows - fills remaining line width */
.oe-long{
    flex:1;
    min-width:260px;
}

/* Small blank box used in fill-in-the-blank rows */
.oe-blank{
    width:90px;
    min-width:90px;
}

/* "odd or even" answer box */
.oe-medium{
    width:130px;
    margin-left:6px;
}

/* ---------------- Signs ---------------- */
.oe-sign{
    font-size:18px;
    font-weight:400;
    color:#111;
}

/* ---------------- Multiple Boxes Row (Q2 / Q3) ---------------- */
.oe-box-row{
    display:flex;
    gap:24px;
    flex-wrap:wrap;
    margin:10px 0 20px 30px;
}

/* ---------------- Disabled ---------------- */
.oe-input:disabled{
    color:#111;
    -webkit-text-fill-color:#111;
    opacity:1;
    background:transparent;
    cursor:default;
}

/* ---------------- Responsive ---------------- */
@media(max-width:768px){

.oe-bank{
    font-size:20px;
    padding:18px;
}

.oe-title{
    font-size:15px;
}

.oe-row, .oe-sum-row, .oe-box-row{
    margin-left:14px;
}

.oe-long{
    min-width:150px;
}

.oe-blank{
    width:70px;
    min-width:70px;
}

.oe-input{
    font-size:13px;
}

.oe-medium{
    width:110px;
}

}

</style>
