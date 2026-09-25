<?php
$data = json_decode($q['question_payload'], true);
$correct = json_decode($q['correct_answer'] ?? '{}', true);
$exponents = $data['exponents'] ?? [];
$correct_expanded = $correct['expanded_form'] ?? [];
$correct_product = $correct['product'] ?? [];
$student = [];

if (!empty($q['student_answer'])) {
    $tmp = json_decode($q['student_answer'], true);
    if (is_array($tmp)) {
        $student = $tmp;
    }
}
$student_expanded = $student['expanded_form'] ?? [];
$student_product = $student['product'] ?? [];

function fmt_exp($e){
    $parts = explode('^', $e);
    return $parts[0] . '<sup>' . ($parts[1] ?? '') . '</sup>';
}

// Normalize so 5*5*5, 5x5x5, 5 × 5 × 5 all compare equal
function norm($v){
    $v = trim($v);
    $v = str_replace(['×', 'X', 'x', '*', '·'], '*', $v); // unify multiplication signs
    $v = preg_replace('/\s+/', '', $v);                   // remove all spaces
    return strtolower($v);
}
?>
<style>
.percent-table-box{background:#fff;padding:25px;border-radius:14px;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:25px;}
.percent-table{width:100%;border-collapse:collapse;}
.percent-table th,.percent-table td{border:2px solid #ff4d4d;padding:15px;text-align:center;font-size:18px;}
.percent-table th{background:#f7f7f7;}
.percent-input{width:90%;border:none;border-bottom:2px solid #000;text-align:center;font-size:18px;background:transparent;outline:none;}
.correct-cell{background:#d4edda;}
.wrong-cell{background:#f8d7da;}
</style>
<div class="percent-table-box">
<table class="percent-table">
<thead>
<tr><th>S.No</th><th>Exponent Form</th><th>Expanded Form</th><th>Product</th></tr>
</thead>
<tbody>
<?php foreach($exponents as $i=>$exp):
    $s_exp = $student_expanded[$i] ?? '';
    $c_exp = $correct_expanded[$i] ?? '';
    $s_prod = $student_product[$i] ?? '';
    $c_prod = $correct_product[$i] ?? '';

    $exp_ok  = (norm($s_exp) === norm($c_exp));
    $prod_ok = (norm($s_prod) === norm($c_prod));

    $exp_class = $prod_class = '';
    if(isset($is_result_page) && $s_exp !== ''){
        $exp_class = $exp_ok ? "correct-cell" : "wrong-cell";
    }
    if(isset($is_result_page) && $s_prod !== ''){
        $prod_class = $prod_ok ? "correct-cell" : "wrong-cell";
    }
?>
<tr>
<td><?= $i+1 ?>)</td>
<td><?= fmt_exp($exp) ?> =</td>
<td class="<?= $exp_class ?>">
<input type="text" name="answer[<?= $q['id'] ?>][expanded_form][]" value="<?= htmlspecialchars($s_exp) ?>"
<?= isset($is_result_page) ? 'readonly' : '' ?> class="percent-input">
<?php if(isset($is_result_page) && !$exp_ok): ?>
<div style="color:green;font-size:14px;">Correct: <?= htmlspecialchars($c_exp) ?></div>
<?php endif; ?>
</td>
<td class="<?= $prod_class ?>">
<input type="text" name="answer[<?= $q['id'] ?>][product][]" value="<?= htmlspecialchars($s_prod) ?>"
<?= isset($is_result_page) ? 'readonly' : '' ?> class="percent-input">
<?php if(isset($is_result_page) && !$prod_ok): ?>
<div style="color:green;font-size:14px;">Correct: <?= htmlspecialchars($c_prod) ?></div>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){

const form=document.querySelector('form');

if(!form) return;

form.addEventListener('submit',function(){

document.querySelectorAll('.dynamic-input').forEach(function(input){

let val=input.value.trim();

val=val.replace(/\s+/g,'');

// *,x,X ko × bana do
val=val.replace(/\*/g,'×');
val=val.replace(/x/g,'×');
val=val.replace(/X/g,'×');

// powers
val=val.replace(/\^0/g,'⁰');
val=val.replace(/\^1/g,'¹');
val=val.replace(/\^2/g,'²');
val=val.replace(/\^3/g,'³');
val=val.replace(/\^4/g,'⁴');
val=val.replace(/\^5/g,'⁵');
val=val.replace(/\^6/g,'⁶');
val=val.replace(/\^7/g,'⁷');
val=val.replace(/\^8/g,'⁸');
val=val.replace(/\^9/g,'⁹');

input.value=val;

});

});

});
</script>