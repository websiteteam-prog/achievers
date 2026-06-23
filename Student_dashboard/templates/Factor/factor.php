<?php

$data = json_decode($q['question_payload'], true);
$correct = json_decode($q['correct_answer'] ?? '', true) ?? [];

$section_id = $q['instruction_id'] ?? 0;

$mode = $data['mode'] ?? 'pairs';

// OLD TYPE
$number = $data['number'] ?? '';
$pairs_left = $data['pairs_left'] ?? [];
$blankCount = $data['blanks'] ?? 0;

// GRID TYPE
$grid = $data['grid'] ?? [];

// COMMON FACTORS TYPE
$num1 = $data['num1'] ?? null;
$num2 = $data['num2'] ?? null;
$common_blanks = $data['common_blanks'] ?? 0;

?>

<style>

* {
    box-sizing: border-box;
}

.quiz-box {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 25px;
    margin-bottom: 25px;
    width: 100%;
}

/* Titles */
.quiz-box h5 {
    font-size: 22px;
    margin-bottom: 15px;
    line-height: 1.4;
}

/* FACTOR LINES */
.factor-line {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 8px;
}

/* BLANK ROW */
.blank-row {
    display: flex;
    flex-wrap: wrap; /* important */
    gap: 15px;
    margin-top: 15px;
}

.blank-field {
    width: 110px;
    border-bottom: 2px solid #000;
    height: 28px;
    display: inline-block;
}

/* GRID */
.grid-box {
    background: #f05454;
    padding: 20px;
    border-radius: 20px;
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
    gap: 15px;
    width: 100%;
    max-width: 500px;
}

.grid-cell {
    color: #fff;
    font-size: 20px;
    font-weight: bold;
    padding: 15px;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: 0.2s ease;
    user-select: none;
}

.grid-cell:hover {
    background: rgba(255,255,255,0.2);
}

.grid-cell.selected {
    background: #1f75fe !important;
    transform: scale(1.05);
}

/* COMMON FACTORS */
.common-box {
    width: 100%;
}

.common-box label {
    font-size: 18px;
    font-weight: 600;
}

.common-input {
    width: 150px;
    border: none;
    border-bottom: 2px solid #000;
    font-size: 18px;
    margin-bottom: 15px;
    background: transparent;
}

.blank-field-input {
    width: 100px;
    border: none;
    border-bottom: 2px solid #000;
    height: 35px;
    font-size: 18px;
    text-align: center;
    outline: none;
    background: transparent;
}

/* ===== CLEAN COMMON MODE ===== */

.common-numbers {
    font-size: 20px;
    margin-bottom: 15px;
    line-height: 1.8;
}

.common-answer-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
}

.common-text {
    font-size: 20px;
    font-weight: 600;
}

.common-inputs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.common-small-input {
    width: 100px;
    height: 35px;
    border: none;
    border-bottom: 2px solid #333;
    text-align: center;
    font-size: 18px;
    outline: none;
    background: transparent;
}

/* ===== PROFESSIONAL VENN DIAGRAM ===== */

.venn-wrapper {
    margin-top: 30px;
    text-align: center;
}

.venn-title {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: 600;
}

.intersection {
    position: absolute;
    width: 150px;
    height: 200px;
    left: 225px;
    top: 45px;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-content: center;
    gap: 10px;
    pointer-events: auto;
}

.left-circle .circle-label {
    left: 20px;
}

.right-circle .circle-label {
    right: 20px;
}

.venn-input {
    width: 55px;
    height: 35px;
    border: none;
    border-bottom: 2px solid #000;
    text-align: center;
    font-size: 16px;
    background: transparent;
    outline: none;
}

.venn-bottom::before {
    content: "";
    position: absolute;
    left: 50%;
    top: -60px;
    width: 2px;
    height: 50px;
    background: #8a00ff;
}

.venn-bottom::after {
    content: "";
    position: absolute;
    left: calc(50% - 5px);
    top: -15px;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 10px solid #8a00ff;
}
/* ===== RESPONSIVE VENN ONLY ===== */

.venn-wrapper {
    margin-top: 30px;
    text-align: center;
    width: 100%;
}

/* Container scales automatically */
.venn-container {
    position: relative;
    width: 100%;
    max-width: 650px;
    aspect-ratio: 2 / 1;
    margin: 40px auto;
}

/* Circles scale with container */
.circle {
    position: absolute;
    width: 45%;
    aspect-ratio: 1 / 1;
    border: 3px solid #8a00ff;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: transparent;
}

.left-circle {
    left: 10%;
    top: 10%;
}

.right-circle {
    right: 10%;
    top: 10%;
}

/* Labels scale properly */
.circle-label {
    position: absolute;
    top: -35px;
    left: 50%;
    transform: translateX(-50%);
    width: auto;
    white-space: nowrap;
    text-align: center;
}

/* Bottom Section */
.venn-bottom {
    margin-top: 40px;
    font-size: clamp(14px, 2vw, 20px);
    text-align: center;
    position: relative;
}

/* Vertical Line */
.venn-bottom::before {
    content: "";
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -60px;
    width: 2px;
    height: 50px;
    background: #8a00ff;
}

/* Arrow Head */
.venn-bottom::after {
    content: "";
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: -15px;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 10px solid #8a00ff;
}

/* Input responsive */
.venn-small {
    width: clamp(40px, 6vw, 60px);
    border: none;
    border-bottom: 2px solid #000;
    text-align: center;
    font-size: clamp(14px, 2vw, 18px);
    background: transparent;
    margin-left: 6px;
}

.cross-grid {
    position: relative;
}

.cross-number-badge {
    position: absolute;
    top: -15px;
    left: -15px;
    background: #ff4d4d;
    color: #000;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}
.pool-cell.used {
    background: rgba(255,255,255,0.15);
    text-decoration: line-through;
    opacity: 0.6;
}

.pool-cell.used::after {
    content: " \2713";
}

/* ===== CLEAN BOOK STYLE LCM ===== */

.lcm-wrapper {
    margin: 20px 0;
    padding-left: 10px;
}

.lcm-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}

.lcm-line {
    margin-bottom: 18px;
    font-size: 18px;
}

.line-input {
    width: 80px;
    border: none;
    border-bottom: 2px solid #000;
    margin-left: 10px;
    margin-right: 10px;
    text-align: center;
    font-size: 16px;
    background: transparent;
}

.lcm-final {
    width: 120px;
    border: none;
    border-bottom: 2px solid #000;
    margin-left: 10px;
    text-align: center;
    font-size: 18px;
    background: transparent;
}

.activity-wrapper{
margin-top:20px;
}

.activity-header{
display:flex;
align-items:center;
gap:12px;
margin-bottom:15px;
font-size:18px;
font-weight:600;
}

.color-box{
width:35px;
height:25px;
border:2px solid #000;
}

.activity-grid{
display:grid;
grid-template-columns:repeat(10,1fr);
border:2px solid #8a00ff;
max-width:700px;
}

.activity-cell{
border:1px solid #8a00ff;
padding:6px;
font-size:14px;
text-align:center;
cursor:pointer;
background:#fff;
}

.activity-cell.selected{
color:black !important;
}

.venn-left-numbers{
position:absolute;
left:30%;
top:45%;
transform:translate(-50%,-50%);
display:flex;
flex-wrap:wrap;
gap:12px;
max-width:120px;
justify-content:center;
font-size:18px;
}

.venn-right-numbers{
position:absolute;
right:30%;
top:45%;
transform:translate(50%,-50%);
display:flex;
flex-wrap:wrap;
gap:12px;
max-width:120px;
justify-content:center;
font-size:18px;
}

.venn-common-numbers{
position:absolute;
left:50%;
top:55%;
transform:translate(-50%,-50%);
display:flex;
flex-direction:column;
align-items:center;
gap:6px;
font-weight:600;
font-size:18px;
z-index:10;
}

/* ------------------- RESPONSIVE ------------------- */

@media (max-width: 480px) {

    .venn-container {
        aspect-ratio: unset;
        height: 500px;
    }

    .circle {
        position: relative;
        width: 70%;
        margin: 20px auto;
    }

    .left-circle,
    .right-circle {
        left: unset;
        right: unset;
        top: unset;
    }
}

/* Tablet */
@media (max-width: 992px) {

    .quiz-box {
        padding: 20px;
    }

    .quiz-box h5 {
        font-size: 20px;
    }

    .grid-box {
        grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
    }
}

/* Mobile */
@media (max-width: 600px) {

    .quiz-box {
        padding: 15px;
    }

    .quiz-box h5 {
        font-size: 18px;
    }

    .factor-line {
        font-size: 17px;
    }

    .blank-field-input {
        width: 80px;
        font-size: 16px;
        height: 32px;
    }

    .common-input {
        width: 110px;
        font-size: 16px;
    }

    .grid-box {
        grid-template-columns: repeat(auto-fit, minmax(50px, 1fr));
        gap: 10px;
        padding: 15px;
    }

    .grid-cell {
        font-size: 16px;
        padding: 12px;
    }
}
</style>

<div class="quiz-box">

   <h5><strong>

<?php
$display_no = $q['_sub_no'] ?? 1;

if ($mode == "grid") {
    echo $display_no . ") Factors of $number";
}
elseif ($mode == "common") {
    echo $display_no . ")";
}
elseif ($mode == "venn") {
    echo $display_no . ")";
}
elseif ($mode == "gcf_direct") {
    $numbers = $data['numbers'] ?? [];
    echo $display_no . ")" . implode(" , ", $numbers);
}
elseif ($mode == "gcf_venn") {
    echo $display_no . ")";
}
elseif ($mode == "multiples_between") {
    echo $display_no . ") Multiples of "
         . $data['number'] . "( between "
         . $data['start'] . " and "
         . $data['end'].")";
}
elseif ($mode == "multiples_identify") {
    echo $display_no . ")"
         . implode(", ", $data['numbers'])
         . " are multiples of";
}
elseif ($mode == "multiples_first5") {
    echo $display_no . ") Multiples of "
         . $data['number'];
}

elseif ($mode == "true_false_single") {
    // numbering handled inside question row
}
elseif ($mode == "lcm_steps") {
    echo $display_no . ") "
         . $data['num1'] . " and " . $data['num2'];
}
elseif ($mode == "multiples_find_base") {
    echo $display_no . ")";
}
else {
    echo $display_no . ")";
}
?>

</strong></h5>

    <?php if ($mode == "pairs"): ?>

        <!-- OLD TEMPLATE OUTPUT -->
        <?php foreach($pairs_left as $left): ?>
            <div class="factor-line"><?= $left ?> ×</div>
        <?php endforeach; ?>

        <p><strong>Factors of <?= $number ?> are:</strong></p>

        <div class="blank-row">
            <?php for($i = 0; $i < $blankCount; $i++): ?>
              <input type="text" 
                name="answer[<?= $q['id'] ?>][values][]" 
                class="blank-field-input">
            <?php endfor; ?>
        </div>

    <?php elseif ($mode == "grid"): ?>

        <!-- GRID TEMPLATE OUTPUT -->
        <div class="grid-box" data-qid="<?= $q['id']; ?>">
         <?php foreach($grid as $row): ?>
            <?php foreach($row as $cell): ?>
                <div class="grid-cell" 
                    data-value="<?= $cell ?>" 
                    onclick="toggleSelect(this)">
                    <?= $cell ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </div>

        <input type="hidden" name="answer[<?= $q['id'] ?>]" id="selected_<?= $q['id'] ?>">

    <?php elseif ($mode == "common"): ?>

<div class="common-box">

    <!-- Numbers -->
    <div class="common-numbers">
        <div><strong><?= $num1 ?> :</strong></div>
        <div><strong><?= $num2 ?> :</strong></div>
    </div>

    <!-- Common Factors Line With Inputs -->
    <div class="common-answer-row">
        <span class="common-text">
            Common factors of <?= $num1 ?> and <?= $num2 ?> are:
        </span>

        <div class="common-inputs">
            <?php 
            $common = $data['common'] ?? [];
            foreach($common as $value): ?>
                <input type="text"
                    name="answer[<?= $q['id'] ?>][values][]"    
                    class="common-small-input">
            <?php endforeach; ?>
        </div>
    </div>

</div>
<?php elseif ($mode == "venn"): ?>

<div class="venn-wrapper">

    <div class="venn-container">

        <!-- LEFT CIRCLE -->
        <div class="circle left-circle">
            <div class="circle-label">Factors of <?= $num1 ?></div>
        </div>

        <!-- RIGHT CIRCLE -->
        <div class="circle right-circle">
            <div class="circle-label">Factors of <?= $num2 ?></div>
        </div>

    </div>

    <!-- Bottom Common Line (ONLY ONE INPUT) -->
   <div class="venn-bottom">
        Common factors of <?= $num1 ?> and <?= $num2 ?>:

        <?php
        $common = $data['common'] ?? [];
        foreach($common as $value): ?>
            <input type="text"
                name="answer[<?= $q['id'] ?>][values][]"
                class="venn-small">
        <?php endforeach; ?>
    </div>

</div>
<?php elseif ($mode == "gcf_direct"): ?>

<div class="common-box">

    <div class="common-answer-row">
        <span class="common-text">
            GCF =
        </span>

        <input type="text"
               name="answer[<?= $q['id'] ?>]"
               class="common-small-input">
    </div>

</div>

<?php elseif ($mode == "gcf_venn"): ?>

<div class="venn-wrapper">

    <div class="venn-container">

        <!-- LEFT CIRCLE -->
        <div class="circle left-circle">
            <div class="circle-label">Factors of <?= $num1 ?></div>
        </div>

        <!-- RIGHT CIRCLE -->
        <div class="circle right-circle">
            <div class="circle-label">Factors of <?= $num2 ?></div>
        </div>

    </div>

    <!-- Bottom Section -->
    <div class="venn-bottom">

        Common factors of <?= $num1 ?> and <?= $num2 ?> <nbsp>

        <strong>GCF =</strong>

        <input type="text"
            name="answer[<?= $q['id'] ?>]"
            class="venn-small">

    </div>

</div>

<?php elseif ($mode == "multiples_first5"): ?>

<div class="common-box">

    <div class="blank-row">
        <?php for($i=0;$i<5;$i++): ?>
            <input type="text"
                   name="answer[<?= $q['id'] ?>][values][]"
                   class="blank-field-input">
        <?php endfor; ?>
    </div>

</div>

<?php elseif ($mode == "multiples_between"): ?>

<div class="common-box">

    <div class="blank-row">
        <?php $blank_count = $data['blank_count'] ?? 0;
        for($i=0;$i<$blank_count;$i++): ?>
            <input type="text"
                   name="answer[<?= $q['id'] ?>][values][]"
                   class="blank-field-input">
        <?php endfor; ?>
    </div>

</div>

<?php elseif ($mode == "multiples_identify"): ?>

<div class="common-box">

    <input type="text"
           name="answer[<?= $q['id'] ?>]"
           class="common-small-input">

</div>

<?php elseif ($mode == "multiples_cross"): ?>

<div class="common-box">

    <div class="grid-box cross-grid" data-qid="<?= $q['id']; ?>">

    <div class="cross-number-badge">
        <?= $data['number']; ?>
    </div>
        <?php foreach($data['numbers'] as $num): ?>
            <div class="grid-cell"
                 data-value="<?= $num ?>"
                 onclick="toggleSelect(this)">
                <?= $num ?>
            </div>
        <?php endforeach; ?>

    </div>

    <input type="hidden"
           name="answer[<?= $q['id'] ?>]"
           id="selected_<?= $q['id'] ?>">

</div>

<?php elseif ($mode == "lcm_steps"): 

$num1 = $data['num1'];
$num2 = $data['num2'];

$blank_m1 = $data['blank_m1'] ?? 5;
$blank_m2 = $data['blank_m2'] ?? 5;
$blank_common = $data['blank_common'] ?? 2;

$correct_m1 = $correct['m1'] ?? [];
$correct_m2 = $correct['m2'] ?? [];
$correct_common = $correct['common'] ?? [];
$correct_lcm = $correct['lcm'] ?? '';

?>

<div class="lcm-wrapper">

<input type="hidden"
       name="correct_answer[<?= $q['id'] ?>]"
       value='<?= htmlspecialchars(json_encode($correct),ENT_QUOTES) ?>'>

    <div class="lcm-line">
        Multiples of <?= $num1 ?> =
        <?php for($i=0;$i<$blank_m1;$i++): ?>
            <input type="text"
                   name="answer[<?= $q['id'] ?>][m1][]"
                   class="line-input"
                   data-correct="<?= $correct_m1[$i] ?? '' ?>">
        <?php endfor; ?>
    </div>

    <div class="lcm-line">
        Multiples of <?= $num2 ?> =
        <?php for($i=0;$i<$blank_m2;$i++): ?>
            <input type="text"
                   name="answer[<?= $q['id'] ?>][m2][]"
                   class="line-input"
                   data-correct="<?= $correct_m2[$i] ?? '' ?>">
        <?php endfor; ?>
    </div>

    <div class="lcm-line">
        Common Multiples:
        <?php for($i=0;$i<$blank_common;$i++): ?>
            <input type="text"
                   name="answer[<?= $q['id'] ?>][common][]"
                   class="line-input"
                   data-correct="<?= $correct_common[$i] ?? '' ?>">
        <?php endfor; ?>
    </div>

    <div class="lcm-line">
        Lowest Common Multiple (LCM) =
        <input type="text"
               name="answer[<?= $q['id'] ?>][lcm]"
               class="lcm-final"
               data-correct="<?= $correct_lcm ?>">
    </div>

</div>
<?php elseif ($mode == "true_false_single"): ?>

<div class="common-box">

<div class="common-answer-row">

<strong>
<?= $display_no ?>)
</strong>

<span class="common-text">
<?= $data['question'] ?>
</span>

<select name="answer[<?= $q['id'] ?>]" class="common-small-input">
<option value="">Select</option>
<option value="true">True</option>
<option value="false">False</option>
</select>

</div>

</div>
<?php elseif ($mode == "multiples_activity"): ?>

<div class="activity-wrapper">

<div class="activity-header">

<div class="color-box"
style="background:<?= $data['color'] ?>"></div>

<div class="activity-text">
Multiples of <?= $data['number'] ?>
</div>

</div>

<div class="activity-grid"
     data-qid="<?= $q['id'] ?>"
     data-color="<?= $data['color'] ?>">

<?php for($i=1;$i<=100;$i++): ?>

<div class="activity-cell"
     data-value="<?= $i ?>"
     onclick="toggleSelect(this)">
<?= $i ?>
</div>

<?php endfor; ?>

</div>

<input type="hidden"
name="answer[<?= $q['id'] ?>]"
id="selected_<?= $q['id'] ?>">

</div>

<?php elseif ($mode == "multiples_find_base"): ?>

<div class="venn-wrapper">

<div class="venn-container">

<!-- LEFT CIRCLE -->
<div class="circle left-circle">

<div class="circle-label">
Multiples of 
<input type="text"
name="answer[<?= $q['id'] ?>][num1]"
class="venn-small">
</div>

<div class="venn-left-numbers">
<?php foreach($data['left'] as $n): ?>
<span><?= $n ?></span>
<?php endforeach; ?>
</div>

</div>

<!-- RIGHT CIRCLE -->
<div class="circle right-circle">

<div class="circle-label">
Multiples of 
<input type="text"
name="answer[<?= $q['id'] ?>][num2]"
class="venn-small">
</div>

<div class="venn-right-numbers">
<?php foreach($data['right'] as $n): ?>
<span><?= $n ?></span>
<?php endforeach; ?>
</div>

</div>

<!-- CENTER -->
<div class="venn-common-numbers">
<?php foreach($data['common'] as $n): ?>
<span><?= $n ?></span>
<?php endforeach; ?>
</div>

</div>

<div class="venn-bottom">
Common Multiples
</div>

</div>
<?php elseif ($mode == "multiples_venn"):

$left_count   = count($correct['left'] ?? []);
$right_count  = count($correct['right'] ?? []);
$common_count = count($correct['common'] ?? []);
$pool_numbers = $data['numbers'] ?? [];
?>

<div class="venn-wrapper">

<div class="venn-container">

<!-- LEFT -->
<div class="circle left-circle">
<div class="circle-label">
Multiples of <?= $data['num1'] ?>
</div>

<div class="venn-left-numbers">

<?php for($i=0;$i<$left_count;$i++): ?>

<input type="text"
name="answer[<?= $q['id'] ?>][left][]"
class="venn-small">

<?php endfor; ?>

</div>

</div>


<!-- RIGHT -->
<div class="circle right-circle">
<div class="circle-label">
Multiples of <?= $data['num2'] ?>
</div>

<div class="venn-right-numbers">

<?php for($i=0;$i<$right_count;$i++): ?>

<input type="text"
name="answer[<?= $q['id'] ?>][right][]"
class="venn-small">

<?php endfor; ?>

</div>

</div>


<!-- COMMON -->
<div class="venn-common-numbers">

<?php for($i=0;$i<$common_count;$i++): ?>

<input type="text"
name="answer[<?= $q['id'] ?>][common][]"
class="venn-small">

<?php endfor; ?>

</div>

</div>

<div class="venn-bottom">
Common multiples of <?= $data['num1'] ?> and <?= $data['num2'] ?>
</div>


<!-- NUMBER POOL: reference list of available numbers, click to mark as placed -->
<div class="grid-box number-pool" data-qid="<?= $q['id']; ?>">

<?php foreach($pool_numbers as $num): ?>

<div class="grid-cell pool-cell" onclick="this.classList.toggle('used')">
<?= $num ?>
</div>

<?php endforeach; ?>

</div>

</div>

<?php endif; ?>

</div>

<script>
function toggleSelect(el){

    el.classList.toggle("selected");

    let parentBox = el.closest(".quiz-box");
    let hiddenInput = parentBox.querySelector('input[type="hidden"]');

    if(!hiddenInput) return;

    let grid = el.closest(".activity-grid");
    let color = grid ? grid.getAttribute("data-color") : "#1f75fe";

    if(el.classList.contains("selected")){
        el.style.background = color;
        el.style.color = "#fff";
    }else{
        el.style.background = "#fff";
        el.style.color = "#000";
    }

    let selected = [];

    // 🔥 FIX: include BOTH grid-cell + activity-cell
    parentBox.querySelectorAll(".selected").forEach(cell=>{
        selected.push(cell.getAttribute("data-value"));
    });

    hiddenInput.value = JSON.stringify(selected);
}

</script>
