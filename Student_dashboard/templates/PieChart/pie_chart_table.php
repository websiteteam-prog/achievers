<?php
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$payload = json_decode($q['question_payload'] ?? '', true) ?: [];
$correct = json_decode($q['correct_answer'] ?? '', true) ?: [];
$student = json_decode($q['student_answer'] ?? '', true) ?: [];

$items = $payload['items'] ?? [];
$real_question_id = $q['id'] ?? 0;

$correct_angles = $correct['angles'] ?? [];
$correct_total_value = $correct['total_value'] ?? array_sum(array_column($items, 'value'));
$correct_total_angle = $correct['total_angle'] ?? 360;

$student_angles = $student['angles'] ?? [];
$student_total_value = $student['total_value'] ?? '';
$student_total_angle = $student['total_angle'] ?? '';

// ✅ image only once
if (!isset($GLOBALS['pie_table_img'])) {
    $GLOBALS['pie_table_img'] = false;
}

$cell_class = function ($student_val, $correct_val) {
    if (!isset($GLOBALS['is_pie_result_page']) || $student_val === '' || $student_val === null) {
        return '';
    }
    return ((string)$student_val === (string)$correct_val) ? 'correct-cell' : 'wrong-cell';
};
if (isset($is_result_page)) {
    $GLOBALS['is_pie_result_page'] = true;
}
?>

<style>
.pie-table-card{
    margin: 15px auto;
    padding: 20px;
    border-radius: 12px;
    background: #fff;
    max-width: 900px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pie-img{
    text-align:center;
    margin-bottom:20px;
}
.pie-img img{
    max-width:600px;
    width:100%;
}

.table-wrap{
    overflow-x:auto;
}

.table-wrap table{
    width:100%;
    border-collapse:collapse;
}

.table-wrap th, .table-wrap td{
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
}

.table-wrap input{
    width:80px;
    border:none;
    border-bottom:2px solid #333;
    text-align:center;
    outline:none;
}

.table-wrap tr.total-row td,
.table-wrap tr.total-row th{
    font-weight:bold;
    background:#f7f7f7;
}

.correct-cell{ background:#d4edda; }
.wrong-cell{ background:#f8d7da; }
</style>

<!-- ✅ IMAGE -->
<?php if(!$GLOBALS['pie_table_img'] && !empty($q['question_image'])): ?>
    <div class="pie-img">
        <img src="<?= $h($q['question_image']) ?>">
    </div>
    <?php $GLOBALS['pie_table_img'] = true; ?>
<?php endif; ?>

<div class="pie-table-card">

    <h6><?= $char ?>. <?= $h($q['question_text']) ?></h6>
    <?php $char++; ?>

    <div class="table-wrap">
        <table>
            <tr>
                <th>Category</th>
                <th>No. of students</th>
                <th>Size of an angle</th>
            </tr>

            <?php foreach($items as $i => $row): ?>
            <tr>
                <td><?= $h($row['label']) ?></td>
                <td><?= $h($row['value']) ?></td>

                <td class="<?= $cell_class($student_angles[$i] ?? '', $correct_angles[$i] ?? '') ?>">
                    <input type="text"
                        name="answer[<?= $real_question_id ?>][angles][<?= $i ?>]"
                        value="<?= $h($student_angles[$i] ?? '') ?>">
                </td>
            </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <th>Total</th>
                <th>
                    <input type="text"
                        name="answer[<?= $real_question_id ?>][total_value]"
                        value="<?= $h($student_total_value) ?>">
                </th>
                <th class="<?= $cell_class($student_total_angle, $correct_total_angle) ?>">
                    <input type="text"
                        name="answer[<?= $real_question_id ?>][total_angle]"
                        value="<?= $h($student_total_angle) ?>">
                </th>
            </tr>
        </table>
    </div>

    <?php if (isset($is_result_page) && (string)$student_total_value !== (string)$correct_total_value): ?>
        <div style="color:#198754;font-size:14px;margin-top:6px;">
            Correct total no. of students: <?= $h($correct_total_value) ?>
        </div>
    <?php endif; ?>

</div>
