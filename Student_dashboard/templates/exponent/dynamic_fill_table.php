<?php

$data = json_decode($q['question_payload'], true);
$correct = json_decode($q['correct_answer'] ?? '{}', true);
$student = json_decode($q['student_answer'] ?? '{}', true);

$headers = $data['headers'] ?? [];
$rows = $data['rows'] ?? [];

function normalize_answer($val)
{
    $val = trim((string)$val);

    // remove spaces
    $val = preg_replace('/\s+/u', '', $val);

    // treat *,x,X same as ×
    $val = str_replace(['*','x','X'], '×', $val);

    return mb_strtolower($val);
}

?>

<style>

.dynamic-table-box{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    margin-bottom:25px;
}

.dynamic-table{
    width:100%;
    border-collapse:collapse;
}

.dynamic-table th,
.dynamic-table td{
    border:2px solid #ff4d4d;
    padding:15px;
    text-align:center;
    font-size:18px;
    vertical-align:middle;
}

.dynamic-table th{
    background:#f7f7f7;
}

.dynamic-input{
    width:180px;
    border:none;
    border-bottom:2px solid #000;
    background:transparent;
    outline:none;
    text-align:center;
    font-size:18px;
}

.correct-cell{
    background:#d4edda;
}

.wrong-cell{
    background:#f8d7da;
}

.correct-answer{
    color:green;
    font-size:14px;
    margin-top:5px;
}

@media(max-width:768px){

.dynamic-table th,
.dynamic-table td{
    font-size:16px;
    padding:10px;
}

.dynamic-input{
    width:120px;
}

}

</style>


<div class="dynamic-table-box">

<table class="dynamic-table">

<thead>
<tr>

<?php foreach($headers as $head): ?>
<th><?= $head ?></th>
<?php endforeach; ?>

</tr>
</thead>

<tbody>

<?php foreach($rows as $i=>$row): ?>

<tr>

<td><?= $i+1 ?></td>

<?php foreach($row['fixed'] ?? [] as $fixed): ?>
<td><?= $fixed ?></td>
<?php endforeach; ?>


<?php foreach($row['inputs'] ?? [] as $field):

$student_val = $student[$field][$i] ?? '';
$correct_val = $correct[$field][$i] ?? '';

$class='';

$student_compare = normalize_answer($student_val);
$correct_compare = normalize_answer($correct_val);

if(isset($is_result_page) && $student_val !== '')
{
    $class = ($student_compare == $correct_compare)
        ? 'correct-cell'
        : 'wrong-cell';
}

?>

<td class="<?= $class ?>">

<input
type="text"
class="dynamic-input"
name="answer[<?= $q['id'] ?>][<?= $field ?>][]"
value="<?= htmlspecialchars($student_val) ?>"
>

<?php if(isset($is_result_page) && $student_val !== '' && $student_compare != $correct_compare): ?>

<div class="correct-answer">
Correct: <?= $correct_val ?>
</div>

<?php endif; ?>

</td>

<?php endforeach; ?>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>


<script>
document.addEventListener('DOMContentLoaded', function(){

    const form = document.querySelector('form');

    if(!form) return;

    form.addEventListener('submit', function(){

        document.querySelectorAll('.dynamic-input').forEach(function(input){

            let val = input.value.trim();

            // remove spaces
            val = val.replace(/\s+/g,'');

            // * x X => ×
            val = val.replace(/\*/g,'×');
            val = val.replace(/x/g,'×');
            val = val.replace(/X/g,'×');

            // powers
            val = val.replace(/\^0/g,'⁰');
            val = val.replace(/\^1/g,'¹');
            val = val.replace(/\^2/g,'²');
            val = val.replace(/\^3/g,'³');
            val = val.replace(/\^4/g,'⁴');
            val = val.replace(/\^5/g,'⁵');
            val = val.replace(/\^6/g,'⁶');
            val = val.replace(/\^7/g,'⁷');
            val = val.replace(/\^8/g,'⁸');
            val = val.replace(/\^9/g,'⁹');

            input.value = val;

        });

    });

});
</script>