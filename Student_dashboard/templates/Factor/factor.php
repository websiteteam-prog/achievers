<?php

$data = json_decode($q['question_payload'], true);
$correct = json_decode($q['correct_answer'] ?? '', true) ?? [];

$section_id = $q['instruction_id'] ?? 0;

$mode = $data['mode'] ?? 'pairs';

// OLD TYPE
$number = $data['number'] ?? '';
$pairs_left = $data['pairs_left'] ?? [];
$pairs_right = $data['pairs_right'] ?? [];
$blankCount = $data['blanks'] ?? 0;

// GRID TYPE
$grid = $data['grid'] ?? [];
// MATCH TEXT
$leftItems  = $data['left'] ?? [];
$rightItems = $data['right'] ?? [];

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
    overflow-x:auto;
}

/* Titles */
.quiz-box h5 {
    font-size: 22px;
    margin-bottom: 15px;
    line-height: 1.4;
}

/* FACTOR LINES */
.factor-line{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:20px;
    font-weight:700;
    margin-bottom:15px;
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
    max-width: 100%;
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
    font-weight: 700;
}

.common-input {
    width: 150px;
    border: none;
    border-bottom: 2px solid #000;
    font-size: 18px;
    margin-bottom: 15px;
    background: transparent;
}

input{
    max-width:100%;
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
    font-weight: 700;
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

.common-small-input,
.blank-field-input,
.line-input,
.lcm-final,
.pair-input{
    width:clamp(60px,10vw,120px);
}

/* ===== PROFESSIONAL VENN DIAGRAM ===== */

.venn-wrapper {
    margin-top: 30px;
    text-align: center;
}

.venn-title {
    font-size: 20px;
    margin-bottom: 20px;
    font-weight: 700;
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
    width: 50%;
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
    /*width: clamp(40px, 6vw, 60px);*/
    border: none;
    border-bottom: 2px solid #000;
    text-align: center;
    /*font-size: clamp(14px, 2vw, 18px);*/
    background: transparent;
    margin-left: 6px;
    width:46px !important;
    height:26px !important;
    font-size:14px !important;
    margin:0 !important;
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
    font-weight: 700;
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
font-weight:700;
}

.color-box{
width:35px;
height:25px;
border:2px solid #000;
}

.activity-grid{
    display:grid;
    grid-template-columns:repeat(6, minmax(70px, 1fr));
    gap:10px;
    border:2px solid #8a00ff;
    width:100%;
    max-width:700px;
    padding:10px;
}

.activity-wrapper{
    overflow-x:auto;
}

.activity-grid{
    min-width:500px;
}

select.common-small-input{
    width:120px;
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
font-weight:700;
font-size:18px;
z-index:10;
}

.pair-input{
    width:90px;
    height:35px;
    border:none;
    border-bottom:2px solid #000;
    background:transparent;
    text-align:center;
    font-size:18px;
    margin-left:10px;
    outline:none;
}

.factor-answer-line{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:8px;
    margin-top:15px;
    font-size:20px;
}

.factor-small-input{
    width:clamp(35px,6vw,60px);
    border:none;
    border-bottom:2px solid #000;
    background:transparent;
    text-align:center;
    outline:none;
    font-size:18px;
}

/* ================= MATCH TEXT ================= */

.match-wrapper{
    position:relative;
    display:flex;
    justify-content:space-between;
    gap:80px;
    margin-top:20px;
}

.match-column{
    width:45%;
}

.match-heading{
    font-size:20px;
    font-weight:bold;
    margin-bottom:20px;
}

.match-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px solid #ddd;
    border-radius:10px;
    padding:14px 16px;
    margin-bottom:18px;
    background:#fff;
    cursor:pointer;
    transition:.2s;
}

.match-item:hover{
    background:#f7f7f7;
}

.match-item.active{
    border:2px solid #1f75fe;
}

.match-dot{
    width:18px;
    height:18px;
    border-radius:50%;
    background:#1f75fe;
    flex-shrink:0;
}

.match-wrapper svg{
    position:absolute;
    left:0;
    top:0;
    width:100%;
    height:100%;
    pointer-events:none;
    overflow:visible;
}

@media(max-width:768px){

.match-wrapper{
    flex-direction:column;
    gap:20px;
}

.match-column{
    width:100%;
}

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

    .common-text,
    .common-numbers,
    .factor-line,
    .lcm-line{
        font-size:18px;
    }

    .grid-cell{
        font-size:18px;
        padding:12px;
    }

    .activity-grid{
        grid-template-columns:repeat(6, minmax(70px, 1fr));
    }

    .grid-box {
        grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
    }
}


@media (max-width:768px){

    .quiz-box{
        padding:15px;
    }

    .quiz-box h5{
        font-size:18px;
    }

    .common-answer-row{
        flex-direction:column;
        align-items:flex-start;
    }

    .common-inputs{
        width:100%;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }

    .factor-answer-line{
        align-items:flex-start;
    }

    .factor-answer-line strong{
        width:100%;
    }

    .grid-box{
        max-width:100%;
        grid-template-columns:repeat(auto-fit,minmax(45px,1fr));
        gap:8px;
        padding:12px;
    }

    .grid-cell{
        font-size:15px;
        padding:10px;
    }

    .activity-grid{
        overflow-x:auto;
        display:grid;
        min-width:500px;
    }

    .lcm-line{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        line-height:2;
    }

    .line-input{
        margin:0;
    }

       .venn-container{
        height:auto;
        aspect-ratio:auto;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:25px;
        max-width:720px;
        aspect-ratio:2 / 1;
    }

    .circle{
        position:relative;
        width:260px;
        max-width:90%;
        left:auto !important;
        right:auto !important;
        top:auto !important;
    }

    .circle-label{
        position:static;
        transform:none;
        margin-bottom:10px;
        white-space:normal;
    }

    .venn-left-numbers,
    .venn-right-numbers{
        display:flex;
        flex-wrap:wrap;
        gap:6px;
        max-width:120px;
        max-height:78%;
        overflow:auto;
        justify-content:center;
        align-content:center;
    }
    .venn-left-numbers{  left:32%; }
    .venn-right-numbers{ right:32%; }
    
    /* CENTER lens: vertical stack, compact, lens ke andar */
    .venn-common-numbers{
        flex-direction:column;
        gap:4px;
        max-width:60px;
        max-height:88%;
        overflow:auto;
    }

    .venn-bottom{
        margin-top:20px;
    }

    .venn-bottom::before,
    .venn-bottom::after{
        display:none;
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

/* =====================================
   TABLET
===================================== */

@media (max-width:992px){

    .quiz-box h5{
        font-size:20px;
    }

    .common-text,
    .common-numbers,
    .factor-line,
    .lcm-line{
        font-size:18px;
    }

    .grid-cell{
        font-size:18px;
        padding:12px;
    }

    .activity-grid{
        grid-template-columns:repeat(6, minmax(70px, 1fr));
    }
}
</style>

<div class="quiz-box">

   <h5><strong>

<?php
$display_no = $q['_sub_no'] ?? 1;

if ($mode == "grid") {

    // Only for Puzzle Time
    if (!empty($data['show_question'])) {

        echo nl2br(htmlspecialchars($q['question_text']));

    } else {

        // Existing behaviour (unchanged)
        echo $display_no . ") Factors of " . htmlspecialchars($number);

    }

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
elseif ($mode == "factor_list") {

    echo $display_no . ") "
         . ($q['question_text'] ?? '');

}
elseif ($mode == "match_text") {

    echo $display_no . ") " . ($q['question_text'] ?? '');

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
    echo $display_no . ") " . ($q['question_text'] ?? '');
}
?>

</strong></h5>

    <?php if ($mode == "pairs"): ?>

        <!-- OLD TEMPLATE OUTPUT -->
       <?php foreach($pairs_left as $i => $left): ?>

        <div class="factor-line">
            <?= $left ?> &times;
        
            <input type="text"
                   name="answer[<?= $q['id'] ?>][pairs][]"
                   class="pair-input">
        </div>
        
        <?php endforeach; ?>

        <div class="factor-answer-line">

        <strong>Factors of <?= $number ?> are:</strong>
    
        <?php for($i=0;$i<$blankCount;$i++): ?>
    
            <input type="text"
                   name="answer[<?= $q['id'] ?>][values][]"
                   class="factor-small-input">
    
            <?php if($i < $blankCount-1): ?>
                <span>,</span>
            <?php endif; ?>
    
        <?php endfor; ?>
    
        <span>.</span>
    
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
<?php elseif ($mode == "common_full"): ?>

<?php
$factors1 = $data['factors1'] ?? [];
$factors2 = $data['factors2'] ?? [];
$common   = $data['common'] ?? [];
?>

<div class="common-box">

    <!-- Number 1 -->
    <div class="factor-answer-line">

        <strong><?= $num1 ?> :</strong>

        <?php foreach($factors1 as $i=>$v): ?>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>][factors1][]"
                class="factor-small-input">

            <?php if($i < count($factors1)-1): ?>
                ,
            <?php endif; ?>

        <?php endforeach; ?>

    </div>

    <br>

    <!-- Number 2 -->

    <div class="factor-answer-line">

        <strong><?= $num2 ?> :</strong>

        <?php foreach($factors2 as $i=>$v): ?>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>][factors2][]"
                class="factor-small-input">

            <?php if($i < count($factors2)-1): ?>
                ,
            <?php endif; ?>

        <?php endforeach; ?>

    </div>

    <br>

    <!-- Common -->

    <div class="factor-answer-line">

        <strong>

            Common factors of <?= $num1 ?> and <?= $num2 ?> are:

        </strong>

        <?php foreach($common as $i=>$v): ?>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>][common][]"
                class="factor-small-input">

            <?php if($i < count($common)-1): ?>
                ,
            <?php endif; ?>

        <?php endforeach; ?>

    </div>

</div>

<?php elseif ($mode == "venn"):

$factors1 = $data['factors1'] ?? [];
$factors2 = $data['factors2'] ?? [];
$common   = $data['common'] ?? [];

// left = sirf num1 ke, right = sirf num2 ke (common nikaal ke)
$left_only  = array_values(array_diff($factors1, $common));
$right_only = array_values(array_diff($factors2, $common));
?>

<div class="venn-wrapper">

    <div class="venn-container">

        <!-- LEFT CIRCLE -->
        <div class="circle left-circle">
            <div class="circle-label">Factors of <?= $num1 ?></div>

            <div class="venn-left-numbers">
                <?php
                    $left_count  = $data['left_blanks']  ?? max(1, count($left_only));
                    
                    for($i=0;$i<$left_count;$i++):
                    ?>
                    <input type="text"
                        name="answer[<?= $q['id'] ?>][left][]"
                        class="venn-small">
               <?php endfor; ?>
            </div>
        </div>

        <!-- RIGHT CIRCLE -->
        <div class="circle right-circle">
            <div class="circle-label">Factors of <?= $num2 ?></div>

            <div class="venn-right-numbers">
                <?php
            $right_count = $data['right_blanks'] ?? max(1, count($right_only));

            for($i=0;$i<$right_count;$i++):
            ?>
                    <input type="text"
                        name="answer[<?= $q['id'] ?>][right][]"
                        class="venn-small">
                <?php endfor; ?>
            </div>
        </div>

        <!-- COMMON (overlap / lens) -->
        <div class="venn-common-numbers">
            <?php foreach($common as $v): ?>
                <input type="text"
                    name="answer[<?= $q['id'] ?>][common][]"
                    class="venn-small">
            <?php endforeach; ?>
        </div>

    </div>

    <div class="venn-bottom">
        Common factors of <?= $num1 ?> and <?= $num2 ?>
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

<?php elseif ($mode == "gcf_venn"):

$factors1 = $data['factors1'] ?? [];
$factors2 = $data['factors2'] ?? [];
$common   = $data['common'] ?? [];

$left_only  = array_values(array_diff($factors1, $common));
$right_only = array_values(array_diff($factors2, $common));
?>

<div class="venn-wrapper">

    <div class="venn-container">

        <!-- LEFT CIRCLE -->
        <div class="circle left-circle">
            <div class="circle-label">Factors of <?= $num1 ?></div>

            <div class="venn-left-numbers">
                <?php foreach($left_only as $v): ?>
                    <input type="text"
                        name="answer[<?= $q['id'] ?>][left][]"
                        class="venn-small">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT CIRCLE -->
        <div class="circle right-circle">
            <div class="circle-label">Factors of <?= $num2 ?></div>

            <div class="venn-right-numbers">
                <?php foreach($right_only as $v): ?>
                    <input type="text"
                        name="answer[<?= $q['id'] ?>][right][]"
                        class="venn-small">
                <?php endforeach; ?>
            </div>
        </div>

        <!-- COMMON (overlap / lens) -->
        <div class="venn-common-numbers">
            <?php foreach($common as $v): ?>
                <input type="text"
                    name="answer[<?= $q['id'] ?>][common][]"
                    class="venn-small">
            <?php endforeach; ?>
        </div>

    </div>

    <!-- Bottom: common line + GCF input -->
    <div class="venn-bottom">
        Common factors of <?= $num1 ?> and <?= $num2 ?>

        <div style="margin-top:15px;font-weight:700;">
            G.C.F =
            <input type="text"
                name="answer[<?= $q['id'] ?>][gcf]"
                class="common-small-input">
        </div>
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

<?php elseif ($mode == "factor_list"): ?>

<div class="common-box">

    <div class="factor-answer-line">

        <strong>
            Factors of <?= $data['number'] ?> are:
        </strong>

        <?php
        $count = $data['blank_count'] ?? 0;

        for($i=0;$i<$count;$i++):
        ?>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>][values][]"
                class="factor-small-input">

            <?php if($i < $count-1): ?>
                ,
            <?php endif; ?>

        <?php endfor; ?>

    </div>

</div>
<?php elseif ($mode == "match_text"): ?>

<?php

$roman=["i","ii","iii","iv","v","vi","vii","viii","ix","x"];

?>

<div class="match-wrapper" id="matchWrap<?= $q['id']?>">

<svg id="matchSvg<?= $q['id']?>"></svg>

<div class="match-column">

<div class="match-heading">
Column 1
</div>

<?php foreach($leftItems as $k=>$item): ?>

<div class="match-item match-left"
     data-value="<?= $item ?>">

<div>
<?= $roman[$k] ?>) <?= $item ?>
</div>

<div class="match-dot"></div>

</div>

<?php endforeach; ?>

</div>



<div class="match-column">

<div class="match-heading">
Column 2
</div>

<?php foreach($rightItems as $item): ?>

<div class="match-item match-right"
     data-label="<?= $item['label'] ?>">

<div>
<?= $item['label'] ?>) <?= $item['text'] ?>
</div>

<div class="match-dot"></div>

</div>

<?php endforeach; ?>

</div>

</div>

<div id="matchAnswers<?= $q['id']?>"></div>
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
style="background:<?= htmlspecialchars($data['color'] ?? '#1f75fe') ?>"></div>

<div class="activity-text">

<?php if (isset($data['numbers'])): ?>

    Follow the boxes with prime numbers

<?php else: ?>

    Multiples of <?= htmlspecialchars((string)($data['number'] ?? '')) ?>

<?php endif; ?>

</div>

</div>


<div class="activity-grid"
     data-qid="<?= $q['id'] ?>"
     data-color="<?= htmlspecialchars($data['color'] ?? '#1f75fe') ?>">

<?php if (isset($data['numbers'])): ?>

    <?php foreach($data['numbers'] as $value): ?>

        <div class="activity-cell"
             data-value="<?= htmlspecialchars((string)$value) ?>"
             onclick="toggleSelect(this)">
            <?= htmlspecialchars((string)$value) ?>
        </div>

    <?php endforeach; ?>

<?php else: ?>

    <?php for($i=1;$i<=100;$i++): ?>

        <div class="activity-cell"
             data-value="<?= $i ?>"
             onclick="toggleSelect(this)">
            <?= $i ?>
        </div>

    <?php endfor; ?>

<?php endif; ?>

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

    //  FIX: include BOTH grid-cell + activity-cell
    parentBox.querySelectorAll(".selected").forEach(cell=>{
        selected.push(cell.getAttribute("data-value"));
    });

    hiddenInput.value = JSON.stringify(selected);
}

document.querySelectorAll(".match-wrapper").forEach(function(wrapper){

    let selectedLeft = null;

    const svg = wrapper.querySelector("svg");

    function drawLine(left,right){

        const wrapRect = wrapper.getBoundingClientRect();

        const a = left.querySelector(".match-dot").getBoundingClientRect();
        const b = right.querySelector(".match-dot").getBoundingClientRect();

        const line = document.createElementNS("http://www.w3.org/2000/svg","line");

        line.setAttribute("x1",a.left+a.width/2-wrapRect.left);

        line.setAttribute("y1",a.top+a.height/2-wrapRect.top);

        line.setAttribute("x2",b.left+b.width/2-wrapRect.left);

        line.setAttribute("y2",b.top+b.height/2-wrapRect.top);

        line.setAttribute("stroke","#1f75fe");

        line.setAttribute("stroke-width","3");

        svg.appendChild(line);

    }

    wrapper.querySelectorAll(".match-left").forEach(function(left){

        left.onclick=function(){

            wrapper.querySelectorAll(".match-left").forEach(function(x){

                x.classList.remove("active");

            });

            selectedLeft=this;

            this.classList.add("active");

        };

    });

    wrapper.querySelectorAll(".match-right").forEach(function(right){

        right.onclick=function(){

            if(!selectedLeft) return;

            drawLine(selectedLeft,this);

            let hidden=document.createElement("input");

            hidden.type="hidden";

            hidden.name="answer[<?= $q['id']?>]["+selectedLeft.dataset.value+"]";

            hidden.value=this.dataset.label;

            wrapper.parentNode.querySelector("#matchAnswers<?= $q['id']?>").appendChild(hidden);

            selectedLeft.classList.remove("active");

            selectedLeft=null;

        };

    });

});

</script>
