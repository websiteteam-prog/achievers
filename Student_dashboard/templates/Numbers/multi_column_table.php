<?php
declare(strict_types=1);

$q = $q ?? [];

$data = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$correct = json_decode($q['correct_answer'] ?? '{}', true) ?: [];
$student = json_decode($q['student_answer'] ?? '{}', true) ?: [];

$headers = $data['headers'] ?? [];
$rows = $data['rows'] ?? [];
$prefill = $data['prefill'] ?? [];

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

?>

<style>

.multi-table-box{

    background:#fff;
    padding:22px;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    margin-bottom:25px;
    overflow-x:auto;

}

.multi-table{

    width:100%;
    border-collapse:collapse;
    min-width:850px;

}

.multi-table th,
.multi-table td{

    border:2px solid #333;
    padding:12px;
    text-align:center;
    vertical-align:middle;

}

.multi-table th{

    background:#f7f7f7;
    font-size:18px;
    font-weight:700;

}

.multi-number{

    font-size:22px;
    font-weight:700;

}

.multi-highlight{

    color:#ef3d34;
    text-decoration:underline;
    text-decoration-thickness:3px;
    text-underline-offset:4px;

}

.multi-input{

    width:120px;
    border:none;
    border-bottom:2px solid #7b2cff;
    outline:none;
    background:transparent;
    text-align:center;
    font-size:17px;
    padding:4px;

}

.multi-prefill{

    font-size:18px;
    font-weight:700;
    color:#222;

}

.correct-cell{

    background:#d4edda;

}

.wrong-cell{

    background:#f8d7da;

}

/* Tablet */

@media(max-width:992px){

.multi-table{

    min-width:760px;

}

.multi-number{

    font-size:20px;

}

.multi-input{

    width:100px;

}

}

/* Mobile */

@media(max-width:768px){

.multi-table th,
.multi-table td{

    padding:8px;
    font-size:15px;

}

.multi-number{

    font-size:18px;

}

.multi-input{

    width:80px;
    font-size:15px;

}

.multi-prefill{

    font-size:16px;

}

}

</style>

<div class="multi-table-box">

<table class="multi-table">

<thead>

<tr>

<?php foreach($headers as $head): ?>

<th><?= $h($head) ?></th>

<?php endforeach; ?>

</tr>

</thead>

<tbody>

<?php foreach($rows as $i=>$row): ?>

<?php

$display = $h($row['display'] ?? '');

if(!empty($row['underline'])){

    $underline = $h($row['underline']);

    $display = preg_replace(
        '/' . preg_quote($underline,'/') . '/',
        '<span class="multi-highlight">'.$underline.'</span>',
        $display,
        1
    );

}

?>

<tr>

<td class="multi-number">
<?php if($display !== ''): ?>
    <?= $display ?>
<?php else: ?>
    <input
        type="text"
        class="multi-input"
        name="answer[<?= $q['id'] ?>][number][<?= $i ?>]"
        value="<?= $h($student['number'][$i] ?? '') ?>">
    <?php if(isset($is_result_page) && ($student['number'][$i] ?? '') !== '' && ($student['number'][$i] ?? '') != ($correct['number'][$i] ?? '')): ?>
        <div class="text-success small mt-1">
            <?= $h($correct['number'][$i] ?? '') ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</td>

<?php

for($c=1;$c<count($headers);$c++):

    $key = strtolower($headers[$c]);

    $key = str_replace(
        [' ','-'],
        ['_','_'],
        $key
    );

    $correctValue = $correct[$key][$i] ?? '';

    $studentValue = $student[$key][$i] ?? '';

    $prefillValue = $prefill[$key][$i] ?? '';

    $cls='';

    if($studentValue!==''){

        $cls = ($studentValue==$correctValue)
            ? 'correct-cell'
            : 'wrong-cell';

    }

?>

<td class="<?= $cls ?>">

<?php if($prefillValue !== ''): ?>

<div class="multi-prefill">
    <?= $h($prefillValue) ?>
</div>

<input
    type="hidden"
    name="answer[<?= $q['id'] ?>][<?= $key ?>][<?= $i ?>]"
    value="<?= $h($prefillValue) ?>">

<?php else: ?>

        <input
        type="text"
        class="multi-input"
        name="answer[<?= $q['id'] ?>][<?= $key ?>][<?= $i ?>]"
        value="<?= $h($studentValue) ?>">

    <?php if(isset($is_result_page) && $studentValue!=='' && $studentValue!=$correctValue): ?>

        <div class="text-success small mt-1">

            <?= $h($correctValue) ?>

        </div>

    <?php endif; ?>

<?php endif; ?>

</td>

<?php endfor; ?>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>