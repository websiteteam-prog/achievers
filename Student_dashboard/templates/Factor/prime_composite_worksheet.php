<?php

$data=json_decode($q['question_payload'],true);

$mode=$data['mode'];

?>

<style>

* {
    box-sizing: border-box;
}

.pc-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    padding: 25px 30px;
    width: 100%;
    margin-top: 30px;
    margin-bottom: 25px;
}

.pc-title {
    font-size: 19px;
    line-height: 1.6;
    margin-bottom: 18px;
}

.pc-row {
    display: flex;
    align-items: baseline;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 18px;
    color: #000;
    margin-bottom: 16px;
}

.pc-row span {
    flex-shrink: 0;
}

.pc-input,
.pc-small,
.pc-long,
.pc-select {
    border: none;
    border-bottom: 2px solid #000;
    background: transparent;
    outline: none;
    font-size: 17px;
    color: #000;
    padding: 2px 4px;
}

.pc-input {
    flex: 1;
    min-width: 200px;
}

.pc-small {
    width: 130px;
    text-align: center;
}

.pc-long {
    width: 100%;
    margin-top: 8px;
}

.pc-select {
    width: 160px;
    cursor: pointer;
    background: #fff;
}

/* =====================================================
   PRIME / COMPOSITE SORT
===================================================== */

.prime-composite-wrapper {
    margin-top: 25px;
}

.sort-columns {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-bottom: 25px;
}

.sort-column {
    width: 260px;
    min-height: 330px;
    border: 2px solid #222;
    border-radius: 15px;
    background: #fff;
    padding: 0;
}

.sort-column-title {
    text-align: center;
    font-weight: 700;
    font-size: 17px;
    padding: 12px 8px;
    border-bottom: 2px solid #222;
    background: #fff;
    border-radius: 13px 13px 0 0;
}

.sort-drop-zone {
    min-height: 275px;
    padding: 15px;
    display: flex;
    flex-wrap: wrap;
    align-content: flex-start;
    justify-content: center;
    gap: 10px;
    transition: .2s;
}

.sort-drop-zone.drag-over {
    background: #f3f6ff;
}

.sort-number-pool {
    border: 2px solid #222;
    padding: 15px;
    border-radius: 12px;
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 10px;
    background: #fff;
    max-width: 700px;
    margin: auto;
}

.sort-number {
    width: 42px;
    height: 38px;
    border: 1px solid #222;
    border-radius: 8px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    font-weight: 700;
    cursor: grab;
    user-select: none;
    transition: .2s;
}

.sort-number:hover {
    transform: scale(1.05);
}

.sort-number.dragging {
    opacity: .45;
}

.sort-number.source-used {
    opacity: .25;
    pointer-events: none;
}

.sort-number.correct-sort {
    border-color: #22c55e;
}

.sort-number.wrong-sort {
    border-color: #ef4444;
}

@media(max-width:768px) {

    .sort-columns {
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .sort-column {
        width: 100%;
        max-width: 350px;
    }

    .sort-drop-zone {
        min-height: 220px;
    }

    .sort-number-pool {
        grid-template-columns: repeat(5, 1fr);
    }

    .sort-number {
        width: 40px;
        height: 36px;
        font-size: 16px;
    }

}

@media (max-width: 768px) {

    .pc-card {
        padding: 18px 20px;
        border-radius: 12px;
    }

    .pc-title {
        font-size: 17px;
    }

    .pc-row {
        font-size: 16px;
        flex-direction: column;
        align-items: flex-start;
    }

    .pc-input,
    .pc-small,
    .pc-select {
        width: 100%;
        min-width: unset;
    }
}

@media (max-width: 480px) {

    .pc-card {
        padding: 15px;
    }

    .pc-title {
        font-size: 16px;
    }

    .pc-row {
        font-size: 15px;
    }
}

</style>

<?php if($mode=="sum_even_odd"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.
What is the sum of any two

(a) Odd numbers?

(b) Even numbers?

</div>

<?php $opts = $data['options'] ?? ['Even','Odd']; ?>

<div class="pc-row">
<span>(a)</span>
<select name="answer[<?= $q['id']?>][]" class="pc-select">
<option value="">Select</option>
<?php foreach($opts as $o): ?>
<option value="<?= $o ?>"><?= $o ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="pc-row">
<span>(b)</span>
<select name="answer[<?= $q['id']?>][]" class="pc-select">
<option value="">Select</option>
<?php foreach($opts as $o): ?>
<option value="<?= $o ?>"><?= $o ?></option>
<?php endforeach; ?>
</select>
</div>

</div>

<?php endif; ?>
<?php if($mode=="true_false"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.
State whether the following statements are True or False

</div>

<?php foreach($data['questions'] as $k=>$text): ?>

<div class="pc-row">
<span>
(<?= chr(97+$k) ?>) <?= $text ?>
</span>

<select name="answer[<?= $q['id']?>][]" class="pc-select">
<option value="">Select</option>
<option value="True">True</option>
<option value="False">False</option>
</select>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="prime_pairs"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

The numbers 13 and 31 are prime numbers.

Both these numbers have same digit 1 and 3.

Find such pairs of prime numbers upto 100.

</div>

<input
type="text"
class="pc-long"
name="answer[<?= $q['id']?>]">

</div>

<?php endif; ?>
<?php if($mode=="prime_composite_less20"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Write down separately the prime and composite numbers less than 20.

</div>

<div class="pc-row">
<span>Prime</span>
<input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
</div>

<div class="pc-row">
<span>Composite</span>
<input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
</div>

</div>

<?php endif; ?>
<?php if($mode=="greatest_prime"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

What is the greatest prime number between 1 and 10?

</div>

<?php $opts = $data['options'] ?? []; ?>

<?php if(!empty($opts)): ?>
<select name="answer[<?= $q['id']?>]" class="pc-select">
<option value="">Select</option>
<?php foreach($opts as $o): ?>
<option value="<?= $o ?>"><?= $o ?></option>
<?php endforeach; ?>
</select>
<?php else: ?>
<input type="text" class="pc-long" name="answer[<?= $q['id']?>]">
<?php endif; ?>

</div>

<?php endif; ?>
<?php if($mode=="sum_two_odd_primes"): ?>

<div class="pc-card">

<div class="pc-title">
<?= ($index+1) ?>.
Express the following as the sum of two odd primes.
</div>

<?php foreach($data['numbers'] as $num): ?>
<div class="pc-row">
<span><?= $num ?></span>
<input type="text" class="pc-long" name="answer[<?= $q['id']?>][]">
</div>
<?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="twin_primes"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Give three pairs of prime numbers whose difference is 2.

</div>

<?php for($i=0;$i<3;$i++): ?>
<div class="pc-row">
<input type="text" class="pc-long" name="answer[<?= $q['id']?>][]">
</div>
<?php endfor; ?>

</div>

<?php endif; ?>
<?php if($mode=="identify_prime"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Which of the following numbers are prime?

</div>

<?php $opts = $data['options'] ?? ['Prime','Not Prime']; ?>

<?php foreach($data['numbers'] as $k=>$num): ?>

<div class="pc-row">
<span><?= chr(97+$k) ?>) <?= $num ?></span>

<select name="answer[<?= $q['id']?>][]" class="pc-select">
<option value="">Select</option>
<?php foreach($opts as $o): ?>
<option value="<?= $o ?>"><?= $o ?></option>
<?php endforeach; ?>
</select>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="seven_composite"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Write seven consecutive composite numbers less than 100.

</div>

<input type="text" class="pc-long" name="answer[<?= $q['id']?>]">

</div>

<?php endif; ?>
<?php if($mode=="three_odd_primes"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Express each of the following numbers as the sum of three odd primes.

</div>

<?php foreach($data['numbers'] as $num): ?>
<div class="pc-row">
<span><?= $num ?></span>
<input type="text" class="pc-long" name="answer[<?= $q['id']?>][]">
</div>
<?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="pairs_sum_divisible_5"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Write five pairs of prime numbers less than 20 whose sum is divisible by 5.

</div>

<?php for($i=0;$i<5;$i++): ?>
<div class="pc-row">
<input type="text" class="pc-long" name="answer[<?= $q['id']?>][]">
</div>
<?php endfor; ?>

</div>

<?php endif; ?>
<?php if($mode=="fraction_type_select"): ?>

<style>

/* =====================================================
   FRACTION TYPE SELECT
===================================================== */

.fraction-type-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);

    padding: 24px 32px;

    margin-bottom: 24px;
    margin-top: 20px;
    width: 100%;
}


/* =====================================================
   QUESTION ROW
===================================================== */

.fraction-type-item {

    display: grid;

    grid-template-columns: 45px 110px 1fr;

    align-items: center;

    min-height: 75px;

    width: 100%;

}


/* =====================================================
   QUESTION NUMBER
===================================================== */

.fraction-question-number {

    font-size: 20px;

    font-weight: 700;

    color: #111;

}


/* =====================================================
   FRACTION
===================================================== */

.fraction-display {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 80px;

}


/* Mixed number whole */

.fraction-whole {

    font-size: 21px;

    margin-right: 7px;

    line-height: 1;

}


/* Fraction part */

.fraction-part {

    display: inline-flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    line-height: 1;

    min-width: 38px;

}


/* Numerator */

.fraction-numerator {

    font-size: 20px;

    line-height: 1;

    padding: 0 8px 5px;

    border-bottom: 2px solid #000;

}


/* Denominator */

.fraction-denominator {

    font-size: 20px;

    line-height: 1;

    padding: 5px 8px 0;

}


/* =====================================================
   OPTIONS
===================================================== */

.fraction-options {

    display: flex;

    align-items: center;

    justify-content: flex-start;

    gap: 30px;

    margin-left: 15px;

}


/* Option */

.fraction-option {

    position: relative;

    cursor: pointer;

    margin: 0;

}


/* Hide radio */

.fraction-option input {

    position: absolute;

    opacity: 0;

    pointer-events: none;

}


/* Peach button */

.fraction-option-box {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 125px;

    min-height: 48px;

    padding: 8px 15px;

    background: #e7bea8;

    border-radius: 9px;

    color: #111;

    font-family: Georgia, "Times New Roman", serif;

    font-size: 15px;

    font-weight: 600;

    text-align: center;

    line-height: 1.15;

    transition: all .2s ease;

}


/* Hover */

.fraction-option:hover .fraction-option-box {

    transform: translateY(-2px);

}


/* Selected */

.fraction-option input:checked + .fraction-option-box {

    background: #d89e83;

    box-shadow: 0 0 0 3px rgba(120,75,55,.25);

    transform: scale(1.03);

}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width: 768px) {

    .fraction-type-card {

        padding: 20px;

    }


    .fraction-type-item {

        grid-template-columns: 30px 75px 1fr;

        min-height: 70px;

    }


    .fraction-question-number {

        font-size: 17px;

    }


    .fraction-whole,

    .fraction-numerator,

    .fraction-denominator {

        font-size: 18px;

    }


    .fraction-options {

        gap: 8px;

        margin-left: 5px;

    }


    .fraction-option-box {

        min-width: 80px;

        min-height: 42px;

        padding: 6px 7px;

        font-size: 11px;

    }

}


@media(max-width: 480px) {

    .fraction-type-card {

        padding: 15px;

    }


    .fraction-type-item {

        grid-template-columns: 25px 60px 1fr;

    }


    .fraction-options {

        gap: 5px;

    }


    .fraction-option-box {

        min-width: 65px;

        min-height: 38px;

        padding: 5px;

        font-size: 10px;

    }

}

</style>


<div class="fraction-type-card">

    <div class="fraction-type-item">


        <!-- ==========================================
             QUESTION NUMBER
        =========================================== -->

        <div class="fraction-question-number">

            <?= ($index + 1) ?>.

        </div>


        <!-- ==========================================
             FRACTION
        =========================================== -->

        <div class="fraction-display">

            <?php if(isset($data['whole']) && $data['whole'] !== ''): ?>

                <span class="fraction-whole">

                    <?= htmlspecialchars($data['whole']) ?>

                </span>

            <?php endif; ?>


            <span class="fraction-part">

                <span class="fraction-numerator">

                    <?= htmlspecialchars($data['numerator']) ?>

                </span>

                <span class="fraction-denominator">

                    <?= htmlspecialchars($data['denominator']) ?>

                </span>

            </span>

        </div>


        <!-- ==========================================
             OPTIONS
        =========================================== -->

        <div class="fraction-options">

            <?php foreach(($data['options'] ?? [
                'PROPER',
                'IMPROPER',
                'MIXED NUMBER'
            ]) as $option): ?>

                <label class="fraction-option">

                    <input
                        type="radio"
                        name="answer[<?= $q['id'] ?>]"
                        value="<?= htmlspecialchars($option) ?>"
                    >

                    <span class="fraction-option-box">

                        <?= htmlspecialchars($option) ?>

                    </span>

                </label>

            <?php endforeach; ?>

        </div>


    </div>

</div>


<?php endif; ?>
<?php if($mode=="fill_blank"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Fill in the blanks.

</div>

<?php foreach($data['questions'] as $k=>$question): ?>

<?php
    // backward compatible: agar purana simple string format hai
    if(is_array($question)){
        $qtext   = $question['text'] ?? '';
        $blanks  = $question['blanks'] ?? 1;
        $opts    = $question['options'] ?? [];
    } else {
        $qtext   = $question;
        $blanks  = 1;
        $opts    = $data['options'][$k] ?? null;
    }
?>

<div class="pc-row">

<span><?= chr(97+$k) ?>) <?= $qtext ?></span>

<?php for($b=0; $b<$blanks; $b++): ?>

    <?php if(!empty($opts)): ?>
        <select name="answer[<?= $q['id']?>][<?= $k ?>][]" class="pc-select">
        <option value="">Select</option>
        <?php foreach($opts as $o): ?>
            <option value="<?= $o ?>"><?= $o ?></option>
        <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input type="text" class="pc-input" name="answer[<?= $q['id']?>][<?= $k ?>][]">
    <?php endif; ?>

<?php endfor; ?>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="find_multiples_upto"): ?>

<div class="pc-card">

<div class="pc-title">

<?= ($index+1) ?>.

Find all the multiples of <?= $data['number'] ?> upto <?= $data['limit'] ?>.

</div>

<input
type="text"
class="pc-long"
name="answer[<?= $q['id']?>]">

</div>

<?php endif; ?>
<?php if($mode=="smallest_greatest_digit_divisible_3"): ?>

<div class="pc-card">

    <?php foreach($data['questions'] as $k=>$question): ?>

        <div class="pc-row">

            <span>
                (<?= chr(97+$k) ?>)
            </span>

            <span><?= $question['prefix'] ?></span>

            <span>____</span>

            <span><?= $question['suffix'] ?></span>

        </div>

        <div class="pc-row">

            <span style="margin-left:28px;">
                Smallest digit:
            </span>

            <input
                type="text"
                maxlength="1"
                class="pc-small"
                name="answer[<?= $q['id'] ?>][<?= $k ?>][]"
            >

            <span>
                Greatest digit:
            </span>

            <input
                type="text"
                maxlength="1"
                class="pc-small"
                name="answer[<?= $q['id'] ?>][<?= $k ?>][]"
            >

        </div>

    <?php endforeach; ?>

</div>

<?php endif; ?>
<?php if($mode=="time_table"): ?>

<style>

/* =====================================================
   TIME TABLE
===================================================== */

.time-table-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    padding:25px 30px;
    margin-bottom:25px;
    width:100%;
}

.time-table-title{
    font-size:19px;
    font-weight:700;
    margin-bottom:20px;
    color:#000;
}


/* =====================================================
   TABLE
===================================================== */

.time-table-wrapper{
    width:100%;
    overflow-x:auto;
}

.time-table{
    border-collapse:separate;
    border-spacing:8px;
    width:100%;
    min-width:650px;
}

.time-table th,
.time-table td{
    text-align:center;
    vertical-align:middle;
    height:48px;
    font-size:18px;
}


/* LEFT LABEL */

.time-table-label{
    width:125px;
    min-width:125px;
    background:#f8c9c9;
    font-weight:700;
}


/* NORMAL CELL */

.time-table-cell{
    background:#f8eeee;
    min-width:52px;
    height:48px;
}


/* FRACTION DISPLAY */

.time-table-fraction{
    display:inline-flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;

    font-size:17px;
    font-weight:600;
    line-height:1;
}

.time-table-num{
    border-bottom:2px solid #000;
    padding:0 5px 3px;
}

.time-table-den{
    padding:3px 5px 0;
}


/* ANSWER INPUT */

.time-table-input{
    width:100%;
    height:100%;

    border:none;
    outline:none;

    background:transparent;

    text-align:center;
    font-size:17px;
    font-weight:600;

    padding:5px;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:768px){

    .time-table-card{
        padding:18px;
    }

    .time-table{
        min-width:650px;
    }

    .time-table th,
    .time-table td{
        font-size:16px;
    }

}

</style>


<div class="time-table-card">

    <!-- =================================================
         TITLE
    ================================================== -->

    <div class="time-table-title">

        <?= ($index + 1) ?>.
        Complete the table.

    </div>


    <div class="time-table-wrapper">

        <table class="time-table">

            <!-- =================================================
                 MINUTES ROW
            ================================================== -->

            <tr>

                <th class="time-table-label">
                    MINUTES
                </th>


                <?php foreach(($data['minutes'] ?? []) as $i => $minute): ?>

                    <td class="time-table-cell">

                        <?php if($minute === '' || $minute === null): ?>

                            <!-- Answer for missing minutes -->

                            <input
                                type="text"
                                class="time-table-input"
                                name="answer[<?= $q['id'] ?>][<?= $i ?>]"
                                autocomplete="off"
                            >

                        <?php else: ?>

                            <?= htmlspecialchars($minute) ?>

                        <?php endif; ?>

                    </td>

                <?php endforeach; ?>

            </tr>


            <!-- =================================================
                 HOUR ROW
            ================================================== -->

            <tr>

                <th class="time-table-label">
                    HOUR
                </th>


                <?php foreach(($data['hours'] ?? []) as $i => $hour): ?>

                    <td class="time-table-cell">

                        <?php if($hour === '' || $hour === null): ?>

                            <!-- Answer for missing hour fraction -->

                            <input
                                type="text"
                                class="time-table-input"
                                name="answer[<?= $q['id'] ?>][<?= $i ?>]"
                                autocomplete="off"
                            >

                        <?php else: ?>

                            <?php

                                $parts =
                                    explode('/', (string)$hour, 2);

                                if(count($parts) === 2):

                            ?>

                                <span class="time-table-fraction">

                                    <span class="time-table-num">
                                        <?= htmlspecialchars($parts[0]) ?>
                                    </span>

                                    <span class="time-table-den">
                                        <?= htmlspecialchars($parts[1]) ?>
                                    </span>

                                </span>

                            <?php else: ?>

                                <?= htmlspecialchars($hour) ?>

                            <?php endif; ?>

                        <?php endif; ?>

                    </td>

                <?php endforeach; ?>

            </tr>

        </table>

    </div>

</div>

<?php endif; ?>
<?php if($mode=="ratio_table"): ?>

<style>

.ratio-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    padding:25px 30px;
    margin-bottom:25px;
    width:100%;
    margin-top: 20px;
}

.ratio-title{
    font-size:19px;
    font-weight:700;
    color:#000;
    margin-bottom:12px;
    line-height:1.5;
}

.ratio-desc{
    font-size:16px;
    font-weight:600;
    color:#111;
    margin-bottom:14px;
}

.ratio-table-wrap{
    display:flex;
    flex-direction:column;
    gap:8px;
    max-width:520px;
}

.ratio-row{
    display:flex;
    gap:8px;
}

.ratio-label{
    width:110px;
    min-width:110px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    font-size:14px;
    border-radius:6px;
    padding:12px 6px;
    text-align:center;
}

.ratio-cell{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:6px;
    min-height:42px;
    font-size:16px;
    font-weight:600;
}

.ratio-input{
    width:100%;
    height:100%;
    border:none;
    outline:none;
    background:transparent;
    text-align:center;
    font-size:16px;
    font-weight:600;
    padding:6px;
}

@media(max-width:768px){

    .ratio-card{padding:18px;}

    .ratio-label{
        width:85px;
        min-width:85px;
        font-size:13px;
    }

}

</style>

<div class="ratio-card">

    <div class="ratio-title">
        <?= ($index + 1) ?>.
        <?= htmlspecialchars($data['title'] ?? 'Use the information to fill in the missing values in the table.') ?>
    </div>

    <div class="ratio-desc">
        <?= htmlspecialchars($data['description'] ?? '') ?>
    </div>

    <div class="ratio-table-wrap">

        <?php foreach(($data['rows'] ?? []) as $rIndex => $row): ?>

            <div class="ratio-row">

                <div class="ratio-label"
                     style="background:<?= htmlspecialchars($row['color'] ?? '#dde3f7') ?>;">
                    <?= htmlspecialchars($row['label'] ?? '') ?>
                </div>

                <?php foreach(($row['values'] ?? []) as $vIndex => $val): ?>

                    <div class="ratio-cell"
                         style="background:<?= htmlspecialchars($row['cellColor'] ?? '#eef1fb') ?>;">

                        <?php if($val === '' || $val === null): ?>

                            <input
                                type="text"
                                class="ratio-input"
                                name="answer[<?= $q['id'] ?>][]"
                                autocomplete="off"
                            >

                        <?php else: ?>

                            <?= htmlspecialchars($val) ?>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>
<?php if($mode=="equivalent_fractions"): ?>

<style>

/* =====================================================
   EQUIVALENT FRACTIONS
===================================================== */

.eq-frac-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);

    padding:25px 30px;

    margin-bottom:25px;

    width:100%;
}

.eq-frac-title{
    font-size:19px;
    font-weight:700;

    color:#000;

    line-height:1.5;

    margin-bottom:28px;
}


/* =====================================================
   2 COLUMN GRID
===================================================== */

.eq-frac-grid{

    display:grid;

    grid-template-columns:repeat(2, 1fr);

    column-gap:80px;

    row-gap:35px;

    width:100%;
}


/* =====================================================
   FRACTION CHAIN
===================================================== */

.eq-frac-chain{

    display:flex;

    align-items:center;

    justify-content:flex-start;

    gap:16px;

    font-size:20px;

    font-weight:600;

    white-space:nowrap;
}


/* =====================================================
   EQUAL SIGN
===================================================== */

.eq-frac-equal{

    font-size:22px;

    font-weight:600;

    line-height:1;
}


/* =====================================================
   FRACTION
===================================================== */

.eq-frac{

    display:inline-flex;

    flex-direction:column;

    align-items:center;

    justify-content:center;

    min-width:48px;

    line-height:1;

    font-size:20px;

    font-weight:600;
}


/* =====================================================
   NUMERATOR
===================================================== */

.eq-frac-num{

    min-width:42px;

    min-height:30px;

    display:flex;

    align-items:center;

    justify-content:center;

    border-bottom:2px solid #000;

    padding:0 5px 5px;
}


/* =====================================================
   DENOMINATOR
===================================================== */

.eq-frac-den{

    min-width:42px;

    min-height:30px;

    display:flex;

    align-items:center;

    justify-content:center;

    padding:5px 5px 0;
}


/* =====================================================
   NUMERATOR INPUT
===================================================== */

.eq-frac-input{

    width:42px;

    height:28px;

    border:none;

    outline:none;

    background:transparent;

    text-align:center;

    font-size:19px;

    font-weight:600;

    padding:0;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:900px){

    .eq-frac-grid{

        grid-template-columns:1fr;

        row-gap:30px;

    }

    .eq-frac-chain{

        justify-content:center;

    }

}


@media(max-width:600px){

    .eq-frac-card{

        padding:18px 15px;

    }

    .eq-frac-title{

        font-size:17px;

    }

    .eq-frac-chain{

        gap:10px;

        font-size:18px;

    }

    .eq-frac{

        font-size:18px;

        min-width:42px;

    }

    .eq-frac-input{

        width:38px;

        font-size:17px;

    }

}

</style>


<div class="eq-frac-card">

    <!-- =================================================
         TITLE
    ================================================== -->

    <div class="eq-frac-title">

        <?= ($index + 1) ?>.
        <?= htmlspecialchars(
            $data['title']
            ?? 'Complete the following so each chain of fractions are equal.'
        ) ?>

    </div>


    <!-- =================================================
         FRACTION GRID
    ================================================== -->

    <div class="eq-frac-grid">

        <?php foreach(($data['items'] ?? []) as $rowIndex => $row): ?>

            <div class="eq-frac-chain">

                <?php foreach($row as $position => $fraction): ?>

                    <?php

                        $numerator =
                            (string)($fraction['numerator'] ?? '');

                        $denominator =
                            (string)($fraction['denominator'] ?? '');

                    ?>


                    <!-- =================================
                         FRACTION
                    ================================== -->

                    <span class="eq-frac">

                        <!-- NUMERATOR -->

                        <span class="eq-frac-num">

                            <?php if($numerator === ''): ?>

                                <?php
                                    /*
                                     * Stable unique answer key.
                                     *
                                     * Example:
                                     * row_0_blank_1
                                     * row_0_blank_2
                                     * row_2_blank_1
                                     */
                                    $answerKey =
                                        'row_' . $rowIndex .
                                        '_blank_' . $position;
                                ?>

                                <input
                                    type="text"
                                    class="eq-frac-input"

                                    name="answer[<?= $q['id'] ?>][<?= $answerKey ?>]"

                                    autocomplete="off"

                                    inputmode="numeric"

                                    data-answer-key="<?= htmlspecialchars($answerKey) ?>"
                                >

                            <?php else: ?>

                                <?= htmlspecialchars($numerator) ?>

                            <?php endif; ?>

                        </span>


                        <!-- DENOMINATOR -->

                        <span class="eq-frac-den">

                            <?= htmlspecialchars($denominator) ?>

                        </span>

                    </span>


                    <!-- =================================
                         EQUAL SIGN
                    ================================== -->

                    <?php if($position < count($row) - 1): ?>

                        <span class="eq-frac-equal">
                            =
                        </span>

                    <?php endif; ?>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php endif; ?>
<?php if($mode=="time_fractions"): ?>

<style>

/* =====================================================
   TIME AS FRACTIONS
===================================================== */

.time-fraction-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    padding:25px 30px;
    margin-bottom:30px;
    width:100%;
    margin-top: 30px;
}

.time-fraction-title{
    font-size:19px;
    font-weight:700;
    color:#000;
    margin-bottom:25px;
    line-height:1.5;
}


/* =====================================================
   PROPER FRACTION
===================================================== */

.time-frac{
    display:inline-flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    vertical-align:middle;

    min-width:28px;

    font-size:19px;
    font-weight:600;
    line-height:1;
}

.time-frac-num{
    border-bottom:2px solid #000;
    padding:0 6px 4px;
}

.time-frac-den{
    padding:4px 6px 0;
}


/* =====================================================
   SECTION A
===================================================== */

.time-a-list{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:28px 45px;
}

.time-a-item{
    display:flex;
    align-items:center;
    gap:9px;
    font-size:19px;
    white-space:nowrap;
}

.time-fraction-input{
    width:85px;
    height:34px;

    border:none;
    border-bottom:2px solid #000;

    background:transparent;
    outline:none;

    text-align:center;
    font-size:18px;
}


/* =====================================================
   SECTION B
===================================================== */

.time-b-list{
    display:flex;
    flex-direction:column;
    gap:24px;
}

.time-b-item{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;

    font-size:18px;
    line-height:2;
}

.time-b-input{
    width:55px;
    height:32px;

    border:none;
    border-bottom:2px solid #000;

    background:transparent;
    outline:none;

    text-align:center;
    font-size:18px;
}


/* Fraction blank after "because" */

.time-b-fraction-input{
    width:190px;
    height:38px;

    border:1px solid #b97d68;
    border-radius:8px;

    background:#fff;

    outline:none;

    text-align:center;
    font-size:18px;

    box-shadow:
        0 3px 0 #e6b5a5;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:900px){

    .time-a-list{
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:600px){

    .time-fraction-card{
        padding:18px;
    }

    .time-a-list{
        grid-template-columns:1fr;
        gap:20px;
    }

    .time-a-item{
        font-size:17px;
    }

    .time-b-item{
        font-size:16px;
    }

    .time-b-fraction-input{
        width:150px;
    }

}

</style>


<div class="time-fraction-card">

    <!-- =================================================
         TITLE
    ================================================== -->

    <div class="time-fraction-title">

        <?= ($index + 1) ?>.
        <?= htmlspecialchars($data['title'] ?? '') ?>

    </div>


    <!-- =================================================
         SECTION A
    ================================================== -->

    <?php if(($data['section'] ?? '') === 'A'): ?>

        <div class="time-a-list">

            <?php foreach(($data['items'] ?? []) as $item): ?>

                <?php

                    $fraction =
                        (string)($item['fraction'] ?? '');

                    $fractionParts =
                        explode('/', $fraction, 2);

                    $numerator =
                        $fractionParts[0] ?? '';

                    $denominator =
                        $fractionParts[1] ?? '';

                ?>

                <div class="time-a-item">

                    <!-- FRACTION -->

                    <span class="time-frac">

                        <span class="time-frac-num">
                            <?= htmlspecialchars($numerator) ?>
                        </span>

                        <span class="time-frac-den">
                            <?= htmlspecialchars($denominator) ?>
                        </span>

                    </span>


                    <span>
                        of 60 =
                    </span>


                    <!-- ANSWER -->

                    <input
                        type="text"
                        class="time-fraction-input"
                        name="answer[<?= $q['id'] ?>][]"
                        autocomplete="off"
                    >

                </div>

            <?php endforeach; ?>

        </div>


    <!-- =================================================
         SECTION B
    ================================================== -->

    <?php elseif(($data['section'] ?? '') === 'B'): ?>

        <div class="time-b-list">

            <?php foreach(($data['items'] ?? []) as $item): ?>

                <?php

                    $fraction =
                        (string)($item['fraction'] ?? '');

                    $fractionParts =
                        explode('/', $fraction, 2);

                    $numerator =
                        $fractionParts[0] ?? '';

                    $denominator =
                        $fractionParts[1] ?? '';

                    $result =
                        (string)($item['result'] ?? '');

                ?>

                <div class="time-b-item">

                    <!-- FIRST FRACTION -->

                    <span class="time-frac">

                        <span class="time-frac-num">
                            <?= htmlspecialchars($numerator) ?>
                        </span>

                        <span class="time-frac-den">
                            <?= htmlspecialchars($denominator) ?>
                        </span>

                    </span>


                    <span>
                        of an hour is
                    </span>


                    <!-- MINUTES BLANK -->

                    <input
                        type="text"
                        class="time-b-input"
                        name="answer[<?= $q['id'] ?>][]"
                        autocomplete="off"
                    >


                    <span>
                        minutes because
                    </span>


                    <!-- FRACTION ANSWER BLANK -->

                    <input
                        type="text"
                        class="time-b-fraction-input"
                        name="answer[<?= $q['id'] ?>][]"
                        autocomplete="off"
                    >


                        <?php if(isset($item['tail'])): ?>
                        <span><?= htmlspecialchars($item['tail']) ?></span>
                    <?php elseif($result !== ''): ?>
                        <span>of 60 is <?= htmlspecialchars($result) ?>.</span>
                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    </div>
    
    <?php endif; ?>
    <?php if($mode=="short_answer"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    <?= $data['question'] ?>
    
    </div>
    
    <input
    type="text"
    class="pc-long"
    name="answer[<?= $q['id']?>]">
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="number_prime_factors"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    <?= $data['question'] ?>
    
    </div>
    
    <div class="pc-row">
    <span><?= ucfirst($data['type']) ?> <?= $data['digits'] ?>-digit number:</span>
    <input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
    </div>
    
    <div class="pc-row">
    <span>Prime factors:</span>
    <input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
    </div>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="prime_factors_relation"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    Find all the prime factors of <?= $data['number'] ?> and arrange them in ascending order.
    Now state the relation, if any, between two consecutive prime factors.
    
    </div>
    
    <div class="pc-row">
    <span>Prime factors:</span>
    <input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
    </div>
    
    <div class="pc-row">
    <span>Relation:</span>
    <input type="text" class="pc-long" name="answer[<?= $q['id']?>][]">
    </div>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="verify_statement"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    <?= $data['statement'] ?>
    
    </div>
    
    <textarea
    class="pc-long"
    rows="3"
    name="answer[<?= $q['id']?>]"
    style="border:2px solid #000; border-radius:6px; padding:8px; resize:vertical;"
    ></textarea>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="identify_prime_factorisation"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    In which of the following expressions, prime factorisation has been done?
    
    </div>
    
    <?php $opts = $data['options'] ?? ['Yes','No']; ?>
    
    <?php foreach($data['expressions'] as $k=>$exp): ?>
    
    <div class="pc-row">
    <span><?= $exp['label'] ?>) <?= $exp['text'] ?></span>
    
    <select name="answer[<?= $q['id']?>][]" class="pc-select">
    <option value="">Select</option>
    <?php foreach($opts as $o): ?>
    <option value="<?= $o ?>"><?= $o ?></option>
    <?php endforeach; ?>
    </select>
    
    </div>
    
    <?php endforeach; ?>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="divisibility_check"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    Determine if <?= $data['number'] ?> is divisible by <?= $data['divisor'] ?>.
    <?php if(!empty($data['hint'])): ?>
    <br><span style="color:#0d6efd; font-weight:400; font-size:15px;">[Hint: <?= $data['hint'] ?>]</span>
    <?php endif; ?>
    
    </div>
    
    <?php $opts = $data['options'] ?? ['Yes','No']; ?>
    
    <div class="pc-row">
    <span>Answer:</span>
    <select name="answer[<?= $q['id']?>][]" class="pc-select">
    <option value="">Select</option>
    <?php foreach($opts as $o): ?>
    <option value="<?= $o ?>"><?= $o ?></option>
    <?php endforeach; ?>
    </select>
    </div>
    
    <div class="pc-row">
    <span>Reason:</span>
    <input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
    </div>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="verify_lcm_statement"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    18 is divisible by both 2 and 3. It is also divisible by 2 x 3 = 6.
    Similarly, a number is divisible by both 4 and 6.
    Can we say that the number must also be divisible by 4 x 6 = 24? If not, give an example to justify your answer.
    
    </div>
    
    <?php $opts = $data['options'] ?? ['Yes','No']; ?>
    
    <div class="pc-row">
    <span>Answer:</span>
    <select name="answer[<?= $q['id']?>][]" class="pc-select">
    <option value="">Select</option>
    <?php foreach($opts as $o): ?>
    <option value="<?= $o ?>"><?= $o ?></option>
    <?php endforeach; ?>
    </select>
    </div>
    
    <div class="pc-row">
    <span>Example:</span>
    <input type="text" class="pc-input" name="answer[<?= $q['id']?>][]">
    </div>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="smallest_four_prime_factors"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    I am the smallest number, having four different prime factors. Can you find me?
    
    </div>
    
    <input
    type="text"
    class="pc-long"
    name="answer[<?= $q['id']?>]">
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="select_numbers"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    <?= $data['question'] ?>
    
    </div>
    
    <div class="pc-row">
    <?php foreach($data['numbers'] as $num): ?>
    <label style="display:flex; align-items:center; gap:6px; font-size:17px;">
    <input type="checkbox" name="answer[<?= $q['id']?>][]" value="<?= $num ?>">
    <?= $num ?>
    </label>
    <?php endforeach; ?>
    </div>
    
    </div>
    
    <?php endif; ?>
    <?php if($mode=="missing_multiples"): ?>
    
    <div class="pc-card">
    
    <div class="pc-title">
    
    <?= ($index+1) ?>.
    Fill in the missing multiples:
    
    </div>
    
    <div class="pc-row">
    <?php foreach($data['sequence'] as $val): ?>
        <?php if($val === ""): ?>
            <input type="text" class="pc-small" style="width:70px;" name="answer[<?= $q['id']?>][]">
        <?php else: ?>
            <span><?= $val ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
    
    </div>
    
    <?php endif; ?>
<?php if($mode=="missing_number"): ?>

<div class="pc-card">

    <div class="pc-row">

        <!-- QUESTION NUMBER -->
        <span class="missing-number-label">
            <?= ($index + 1) ?>.
        </span>


        <!-- =================================================
             NEW TEXT / MULTI-BLANK MODE
             ================================================= -->

        <?php if(isset($data['parts']) && is_array($data['parts'])): ?>

            <?php foreach($data['parts'] as $part): ?>

                <?php if(is_array($part) && !empty($part['blank'])): ?>

                    <input
                        type="text"
                        class="pc-small"
                        style="width:130px;"
                        name="answer[<?= $q['id'] ?>][]"
                        autocomplete="off"
                    >

                <?php else: ?>

                    <span><?= $part ?></span>

                <?php endif; ?>

            <?php endforeach; ?>


        <!-- =================================================
             YOUR EXISTING missing_number CODE
             NOTHING CHANGED
             ================================================= -->

        <?php else: ?>


            <?php if(!empty($data['result_first'])): ?>

                <span><?= htmlspecialchars($data['result']) ?></span>
                <span>=</span>

            <?php endif; ?>

            <?php foreach($data['terms'] as $i => $term): ?>

                <?php if($term === ""): ?>

                    <input
                        type="text"
                        class="pc-small"
                        style="width:130px;"
                        name="answer[<?= $q['id'] ?>]"
                        autocomplete="off"
                    >

                <?php else: ?>

                    <span><?= htmlspecialchars($term) ?></span>

                <?php endif; ?>

                <?php if($i < count($data['terms']) - 1): ?>
                    <span>+</span>
                <?php endif; ?>

            <?php endforeach; ?>

            <?php if(empty($data['result_first'])): ?>

                <span>=</span>
                <span><?= htmlspecialchars($data['result']) ?></span>

            <?php endif; ?>


        <?php endif; ?>

    </div>

</div>

<?php endif; ?>
<?php if($mode=="ratio_detailed"): ?>

<style>
.ratio-detailed-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    padding:25px 30px;
    margin-bottom:25px;
    width:100%;
}

.ratio-detailed-title{
    font-size:19px;
    line-height:1.45;
    margin-bottom:25px;
}

.ratio-detailed-row{
    font-size:18px;
    font-weight:600;
    line-height:1.7;
    margin-bottom:3px;
}

.ratio-detailed-input{
    border:none;
    border-bottom:2px solid #222;
    outline:none;
    background:transparent;
    height:25px;
    font-size:18px;
    padding:0 3px;
    margin-left:5px;
    text-align: center;
}

.ratio-detailed-q17{
    margin-top:22px;
}

.ratio-detailed-answer{
    font-size:18px;
    line-height:1.7;
    margin-top:22px;
}

@media(max-width:768px){
    .ratio-detailed-card{
        padding:18px;
    }

    .ratio-detailed-title,
    .ratio-detailed-row,
    .ratio-detailed-answer{
        font-size:16px;
    }

    .ratio-detailed-input{
        font-size:16px;
    }
}
</style>

<div class="ratio-detailed-card">

    <div class="ratio-detailed-title">
        <?= ($index + 1) ?>.
        <?= htmlspecialchars($q['question_text'] ?? '') ?>
    </div>

    <?php if(($data['type'] ?? '') === 'q16'): ?>

        <?php foreach(($data['parts'] ?? []) as $part): ?>

            <div class="ratio-detailed-row">
                <?= htmlspecialchars($part['label']) ?>
                <?= htmlspecialchars($part['text']) ?>
                &nbsp;&nbsp;<?= htmlspecialchars($part['answer_label']) ?>

                <input
                    type="text"
                    class="ratio-detailed-input"
                    style="width:<?= htmlspecialchars($part['blank_width'] ?? '70px') ?>;"
                    name="answer[<?= $q['id'] ?>][]"
                    autocomplete="off"
                >
            </div>

        <?php endforeach; ?>

    <?php elseif(($data['type'] ?? '') === 'q17'): ?>

        <?php foreach(($data['parts'] ?? []) as $part): ?>

            <div class="ratio-detailed-answer">
                <?= htmlspecialchars($part['label']) ?>

                <input
                    type="text"
                    class="ratio-detailed-input"
                    style="width:<?= htmlspecialchars($part['blank_width'] ?? '110px') ?>;"
                    name="answer[<?= $q['id'] ?>][]"
                    autocomplete="off"
                >

                <?= htmlspecialchars($part['suffix'] ?? '') ?>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php endif; ?>
<?php if($mode=="shade_fraction"): ?>
<style>
.shade-card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:25px 30px;margin:20px 0;}
.shade-title{font-size:19px;font-weight:700;margin-bottom:20px;color:#000;}
.shade-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:26px 40px;}
.shade-item{display:flex;align-items:center;gap:16px;}
.shade-circle{width:104px;height:104px;flex-shrink:0;}
.shade-circle .wedge{fill:#fff;stroke:#222;stroke-width:1.5;cursor:pointer;transition:fill .15s;}
.shade-circle .wedge.on{fill:#7c9cff;}
.shade-frac{display:inline-flex;flex-direction:column;align-items:center;font-size:22px;font-weight:600;}
.shade-frac .top{border-bottom:2px solid #000;padding:0 8px 3px;}
.shade-frac .bot{padding:3px 8px 0;}
@media(max-width:600px){.shade-grid{grid-template-columns:1fr;}}
</style>

<div class="shade-card">
  <div class="shade-title"><?= ($index+1) ?>. <?= htmlspecialchars($data['title'] ?? 'Shade the fraction.') ?></div>
  <div class="shade-grid">
    <?php foreach(($data['items'] ?? []) as $it):
        $f = explode('/', (string)($it['fraction'] ?? ''), 2);
        $num = (int)($f[0] ?? 0); $den = (int)($f[1] ?? 1);
    ?>
      <div class="shade-item">
        <svg class="shade-circle" viewBox="0 0 100 100" data-parts="<?= $den ?>"></svg>
        <span class="shade-frac">
          <span class="top"><?= $num ?></span>
          <span class="bot"><?= $den ?></span>
        </span>
        <input type="hidden" name="answer[<?= $q['id'] ?>][]" value="" class="shade-hidden">
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
(function(){
  function polar(cx,cy,r,a){return [cx+r*Math.cos(a),cy+r*Math.sin(a)];}
  document.querySelectorAll('.shade-circle').forEach(function(svg){
    if(svg.dataset.done) return; svg.dataset.done="1";
    var ns="http://www.w3.org/2000/svg",cx=50,cy=50,r=46;
    var parts=parseInt(svg.dataset.parts,10)||1;
    var hidden=svg.parentNode.querySelector('.shade-hidden');
    var wedges=[];
    if(parts===1){
      var c=document.createElementNS(ns,"circle");
      c.setAttribute("cx",cx);c.setAttribute("cy",cy);c.setAttribute("r",r);
      c.setAttribute("class","wedge");svg.appendChild(c);wedges.push(c);
    } else {
      for(var i=0;i<parts;i++){
        var a1=(i/parts)*2*Math.PI-Math.PI/2, a2=((i+1)/parts)*2*Math.PI-Math.PI/2;
        var p1=polar(cx,cy,r,a1),p2=polar(cx,cy,r,a2), large=(a2-a1)>Math.PI?1:0;
        var d="M"+cx+","+cy+" L"+p1[0].toFixed(2)+","+p1[1].toFixed(2)+
              " A"+r+","+r+" 0 "+large+" 1 "+p2[0].toFixed(2)+","+p2[1].toFixed(2)+" Z";
        var path=document.createElementNS(ns,"path");
        path.setAttribute("d",d);path.setAttribute("class","wedge");
        svg.appendChild(path);wedges.push(path);
      }
    }
    function update(){
      if(!hidden) return;
      var onCount = svg.querySelectorAll('.wedge.on').length;
      hidden.value = onCount > 0 ? String(onCount) : "";
    }
    wedges.forEach(function(w){ w.addEventListener('click',function(){ this.classList.toggle('on'); update(); }); });
  });
})();
</script>
<?php endif; ?>
<?php if($mode=="compare_fractions"): ?>
<style>
.cmp-card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:25px 30px;margin:20px 0;}
.cmp-title{font-size:19px;font-weight:700;margin-bottom:20px;color:#000;}
.cmp-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:26px 30px;}
.cmp-item{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;}
.cmp-pie{width:66px;height:66px;flex-shrink:0;}
.cmp-pie .wedge{fill:#fff;stroke:#222;stroke-width:1.3;}
.cmp-pie .wedge.on{fill:#c9d4ff;}
.cmp-frac{display:inline-flex;flex-direction:column;align-items:center;font-size:20px;font-weight:600;}
.cmp-frac .top{border-bottom:2px solid #000;padding:0 7px 3px;}
.cmp-frac .bot{padding:3px 7px 0;}
.cmp-select{width:60px;height:40px;text-align:center;font-size:20px;border:1px solid #7c9cff;border-radius:8px;background:#eef2ff;cursor:pointer;}
@media(max-width:600px){.cmp-grid{grid-template-columns:1fr;}}
</style>

<div class="cmp-card">
  <div class="cmp-title"><?= ($index+1) ?>. <?= htmlspecialchars($data['title'] ?? 'Compare the fractions using the symbols: <, > or =') ?></div>
  <div class="cmp-grid">
    <?php foreach(($data['items'] ?? []) as $it):
        $L=explode('/',(string)($it['left']  ?? ''),2); $Ln=(int)($L[0]??0); $Ld=(int)($L[1]??1);
        $R=explode('/',(string)($it['right'] ?? ''),2); $Rn=(int)($R[0]??0); $Rd=(int)($R[1]??1);
    ?>
      <div class="cmp-item">
        <svg class="cmp-pie" viewBox="0 0 100 100" data-parts="<?= $Ld ?>" data-shade="<?= $Ln ?>"></svg>
        <span class="cmp-frac"><span class="top"><?= $Ln ?></span><span class="bot"><?= $Ld ?></span></span>

        <select name="answer[<?= $q['id'] ?>][]" class="cmp-select">
          <option value=""></option>
          <option value="&lt;">&lt;</option>
          <option value="&gt;">&gt;</option>
          <option value="=">=</option>
        </select>

        <span class="cmp-frac"><span class="top"><?= $Rn ?></span><span class="bot"><?= $Rd ?></span></span>
        <svg class="cmp-pie" viewBox="0 0 100 100" data-parts="<?= $Rd ?>" data-shade="<?= $Rn ?>"></svg>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
(function(){
  function polar(cx,cy,r,a){return [cx+r*Math.cos(a),cy+r*Math.sin(a)];}
  document.querySelectorAll('.cmp-pie').forEach(function(svg){
    if(svg.dataset.done) return; svg.dataset.done="1";
    var ns="http://www.w3.org/2000/svg",cx=50,cy=50,r=46;
    var parts=parseInt(svg.dataset.parts,10)||1, shade=parseInt(svg.dataset.shade,10)||0;
    if(parts===1){
      var c=document.createElementNS(ns,"circle");
      c.setAttribute("cx",cx);c.setAttribute("cy",cy);c.setAttribute("r",r);
      c.setAttribute("class","wedge"+(shade>0?" on":""));svg.appendChild(c);return;
    }
    for(var i=0;i<parts;i++){
      var a1=(i/parts)*2*Math.PI-Math.PI/2, a2=((i+1)/parts)*2*Math.PI-Math.PI/2;
      var p1=polar(cx,cy,r,a1),p2=polar(cx,cy,r,a2), large=(a2-a1)>Math.PI?1:0;
      var d="M"+cx+","+cy+" L"+p1[0].toFixed(2)+","+p1[1].toFixed(2)+
            " A"+r+","+r+" 0 "+large+" 1 "+p2[0].toFixed(2)+","+p2[1].toFixed(2)+" Z";
      var path=document.createElementNS(ns,"path");
      path.setAttribute("d",d);path.setAttribute("class","wedge"+(i<shade?" on":""));
      svg.appendChild(path);
    }
  });
})();
</script>
<?php endif; ?>
<?php if($mode=="prime_composite_sort"): ?>

<?php
$sortNumbers = $data['numbers'] ?? [];
?>

<div class="pc-card">

    <div class="pc-title">

        <?= ($index + 1) ?>.

        <?= htmlspecialchars(
            $q['question_text']
            ?? 'Drag and drop the numbers into the correct columns to identify prime and composite numbers.'
        ) ?>

    </div>


    <div class="prime-composite-wrapper"
         data-qid="<?= $q['id'] ?>">

        <!-- =================================================
             PRIME / COMPOSITE COLUMNS
        ================================================== -->

        <div class="sort-columns">

            <!-- PRIME -->
            <div class="sort-column">

                <div class="sort-column-title">
                    PRIME NUMBER
                </div>

                <div
                    class="sort-drop-zone"
                    data-sort-type="prime"
                ></div>

            </div>


            <!-- COMPOSITE -->
            <div class="sort-column">

                <div class="sort-column-title">
                    COMPOSITE NUMBER
                </div>

                <div
                    class="sort-drop-zone"
                    data-sort-type="composite"
                ></div>

            </div>

        </div>


        <!-- =================================================
             NUMBER POOL
        ================================================== -->

        <div class="sort-number-pool">

            <?php foreach($sortNumbers as $number): ?>

                <div
                    class="sort-number"
                    draggable="true"
                    data-number="<?= htmlspecialchars($number) ?>"
                >
                    <?= htmlspecialchars($number) ?>
                </div>

            <?php endforeach; ?>

        </div>


        <!-- =================================================
             HIDDEN ANSWER
        ================================================== -->

        <input
            type="hidden"
            name="answer[<?= $q['id'] ?>]"
            id="primeCompositeAnswer_<?= $q['id'] ?>"
            value=""
        >

    </div>

</div>

<?php endif; ?>
<?php if($mode=="proportion_or_not"): ?>

<?php

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$ratio1 = $data['ratio1'] ?? '';
$ratio2 = $data['ratio2'] ?? '';

$options = $data['options'] ?? [
    'PROPORTIONAL',
    'NOT PROPORTIONAL'
];


/* =========================================================
   STUDENT ANSWER
========================================================= */

$studentAnswer = '';

if (
    isset($q['student_answer'])
    && $q['student_answer'] !== ''
    && $q['student_answer'] !== null
) {

    $decodedStudent = json_decode(
        $q['student_answer'],
        true
    );

    if (is_scalar($decodedStudent)) {

        $studentAnswer = (string)$decodedStudent;

    }

}


/* =========================================================
   CORRECT ANSWER
========================================================= */

$correctAnswer = '';

if (
    isset($q['correct_answer'])
    && $q['correct_answer'] !== ''
    && $q['correct_answer'] !== null
) {

    $decodedCorrect = json_decode(
        $q['correct_answer'],
        true
    );

    if (is_scalar($decodedCorrect)) {

        $correctAnswer = (string)$decodedCorrect;

    }

}


/* =========================================================
   RESULT CLASS
========================================================= */

$answerClass = '';

if (
    isset($is_result_page)
    && $studentAnswer !== ''
) {

    if (
        strtoupper(trim($studentAnswer))
        ===
        strtoupper(trim($correctAnswer))
    ) {

        $answerClass = 'proportion-answer-correct';

    } else {

        $answerClass = 'proportion-answer-wrong';

    }

}

?>

<style>

/* =========================================================
   PROPORTION OR NOT - MAIN
========================================================= */

.proportion-not-wrapper{

    width:100%;

    box-sizing:border-box;

    padding:0 5px;

}


/* =========================================================
   ONE QUESTION
========================================================= */

.proportion-not-col{

    width:100%;

    margin-bottom:34px;

}


/* =========================================================
   QUESTION ROW
========================================================= */

.proportion-not-row{

    width:100%;

    display:flex;

    align-items:flex-start;

    justify-content:left;

    gap:16px;

    box-sizing:border-box;
    
    margin-top: 40px;

}


/* =========================================================
   QUESTION NUMBER
========================================================= */

.proportion-not-number{

    width:32px;

    min-width:32px;

    font-size:20px;

    font-weight:700;

    color:#111;

    text-align:left;

}


/* =========================================================
   RATIO BOX
========================================================= */

.proportion-not-card{

    width:250px;

    min-width:250px;

    height:62px;

    box-sizing:border-box;

    background:#fff;

    border:2px solid #222;

    border-radius:10px;

    box-shadow:0 6px 0 #222;

    display:flex;

    align-items:center;

    justify-content:center;

    padding:0 12px;

    font-size:20px;

    font-weight:700;

    color:#111;

    white-space:nowrap;

}


/* =========================================================
   "and"
========================================================= */

.proportion-not-and{

    margin:0 7px;

}


/* =========================================================
   ANSWER BOX
========================================================= */

.proportion-not-select{

    width:250px;

    min-width:250px;

    height:62px;

    box-sizing:border-box;

    background:#fff;

    border:2px solid #222;

    border-radius:10px;

    box-shadow:0 6px 0 #222;

    padding:0 15px;

    font-size:17px;

    font-weight:700;

    color:#111;

    text-align:center;

    text-align-last:center;

    outline:none;

    cursor:pointer;

}


/* =========================================================
   SELECT FOCUS
========================================================= */

.proportion-not-select:focus{

    border-color:#222;

    outline:none;

}


/* =========================================================
   RESULT - CORRECT
========================================================= */

.proportion-not-select.proportion-answer-correct{

    background:#d4edda !important;

    border-color:#198754;

    color:#0f5132;

}


/* =========================================================
   RESULT - WRONG
========================================================= */

.proportion-not-select.proportion-answer-wrong{

    background:#f8d7da !important;

    border-color:#dc3545;

    color:#842029;

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .proportion-not-col{

        margin-bottom:25px;

    }


    .proportion-not-row{

        justify-content:center;

        gap:10px;

    }


    .proportion-not-number{

        width:24px;

        min-width:24px;

        font-size:17px;

    }


    .proportion-not-card{

        width:calc(50% - 25px);

        min-width:0;

        height:52px;

        font-size:16px;

        padding:0 7px;

        box-shadow:0 5px 0 #222;

    }


    .proportion-not-select{

        width:calc(50% - 25px);

        min-width:0;

        height:52px;

        font-size:14px;

        box-shadow:0 5px 0 #222;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media(max-width:480px){

    .proportion-not-wrapper{

        padding:0;

    }


    .proportion-not-row{

        gap:7px;

    }


    .proportion-not-number{

        width:20px;

        min-width:20px;

        font-size:15px;

    }


    .proportion-not-card{

        width:calc(50% - 14px);

        min-width:0;

        height:48px;

        font-size:14px;

        border-radius:9px;

        box-shadow:0 5px 0 #222;

    }


    .proportion-not-select{

        width:calc(50% - 14px);

        min-width:0;

        height:48px;

        font-size:13px;

        border-radius:9px;

        box-shadow:0 5px 0 #222;

    }

}

</style>


<div class="proportion-not-wrapper">


    <!-- =====================================================
         ONE QUESTION / ONE LINE
    ====================================================== -->

    <div class="proportion-not-col">

        <div class="proportion-not-row">


            <!-- QUESTION NUMBER -->

            <span class="proportion-not-number">

                <?= ($index + 1) ?>.

            </span>


            <!-- RATIO -->

            <div class="proportion-not-card">

                <?= htmlspecialchars(
                    $ratio1,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>

                <span class="proportion-not-and">
                    and
                </span>

                <?= htmlspecialchars(
                    $ratio2,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) ?>

            </div>


            <!-- ANSWER -->

            <select
                name="answer[<?= $id ?>]"
                class="proportion-not-select <?= $answerClass ?>"
            >

                <option value="">
                    Select
                </option>


                <?php foreach($options as $option): ?>

                    <?php

                    $option = (string)$option;

                    $selected =
                        (
                            strtoupper(trim($studentAnswer))
                            ===
                            strtoupper(trim($option))
                        )
                        ? 'selected'
                        : '';

                    ?>

                    <option
                        value="<?= htmlspecialchars(
                            $option,
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ) ?>"
                        <?= $selected ?>
                    >

                        <?= htmlspecialchars(
                            $option,
                            ENT_QUOTES | ENT_SUBSTITUTE,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>


        </div>

    </div>

</div>

<?php endif; ?>

<?php if ($mode == "metric_unit_conversion"): ?>

<div class="pc-card">

    <div class="pc-row">

        <span class="pc-question">
            <?= ($index + 1) ?>)
            <?= htmlspecialchars($data['value']) ?>
        </span>

        <span>=</span>

        <input
            type="text"
            class="pc-small"
            name="answer[<?= $q['id'] ?>]"
            autocomplete="off"
            inputmode="decimal"
        >

        <span>
            <?= htmlspecialchars($data['answer_unit']) ?>
        </span>

    </div>

</div>

<?php endif; ?>
<?php if($mode=="metric_section_a"): ?>

<style>
.msa-outer{margin:22px 0 10px;}
.msa-wrap{display:flex;flex-wrap:nowrap;justify-content:center;align-items:stretch;gap:16px;}
.msa-blob{
    flex:1 1 0;min-width:0;max-width:430px;min-height:280px;
    background-repeat:no-repeat;background-position:center;background-size:100% 100%;
    filter:drop-shadow(0 4px 10px rgba(0,0,0,.12));
    display:flex;flex-direction:column;justify-content:flex-start;
    padding:40px 24px;
}
.msa-blob-title{text-align:center;font-size:20px;font-weight:800;letter-spacing:1px;color:#1c1c1c;margin-bottom:14px;}
.msa-line{display:flex;flex-wrap:nowrap;white-space:nowrap;align-items:center;justify-content:center;gap:6px;font-size:16px;font-weight:600;color:#1c1c1c;margin:9px 0;}
.msa-input{width:56px;border:none;border-bottom:2px solid #1c1c1c;background:transparent;outline:none;text-align:center;font-size:15px;font-weight:700;color:#1c1c1c;padding:2px 4px;}
@media(max-width:768px){
    .msa-wrap{flex-wrap:wrap;}
    .msa-blob{flex:1 1 100%;max-width:340px;min-height:240px;}
}
</style>

<?php
$msaPath = 'M100,10 C135,10 160,25 168,55 C174,78 172,92 178,115 C185,148 162,180 122,185 C100,187 92,182 68,184 C32,187 14,160 18,122 C20,100 26,90 20,66 C13,34 45,12 82,12 C88,11 94,11 100,10 Z';
?>

<div class="msa-outer">
    <div class="msa-wrap">
        <?php foreach(($data['groups'] ?? []) as $gi => $group): ?>
            <?php
                $fill = $group['color'] ?? '#cfe3d0';
                $svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" preserveAspectRatio="none"><path d="'.$msaPath.'" fill="'.$fill.'"/></svg>';
                $bg   = 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
            ?>
            <div class="msa-blob" style="background-image:url('<?= $bg ?>');">
                <div class="msa-blob-title"><?= htmlspecialchars($group['label'] ?? '') ?></div>
                <?php foreach(($group['lines'] ?? []) as $line): ?>
                    <?php $before = trim((string)($line['before'] ?? '')); $after = trim((string)($line['after'] ?? '')); ?>
                    <div class="msa-line">
                        <?php if($before !== ''): ?><span><?= htmlspecialchars($before) ?></span><?php endif; ?>
                        <input type="text" class="msa-input" name="answer[<?= $q['id'] ?>][<?= $gi ?>][]" autocomplete="off">
                        <?php if($after !== ''): ?><span><?= htmlspecialchars($after) ?></span><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>
<?php if($mode=="metric_section_b"): ?>

<style>
.msb-wrap{display:flex;flex-wrap:wrap;justify-content:center;gap:22px;margin:22px 0;}
.msb-panel{flex:1 1 250px;max-width:320px;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.08);}
.msb-title{text-align:center;font-size:18px;font-weight:800;letter-spacing:1px;padding:12px;color:#1c1c1c;}
.msb-body{padding:16px 18px 22px;}
.msb-row{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:18px;font-weight:700;color:#1c1c1c;margin:14px 0;}
.msb-val{flex:1;text-align:center;white-space:nowrap;}
.msb-sym{width:52px;text-align:center;font-size:20px;font-weight:800;}
.msb-select{width:60px;height:38px;text-align:center;text-align-last:center;font-size:18px;font-weight:800;border:2px solid #1c1c1c;border-radius:8px;background:#fff;cursor:pointer;outline:none;}
.msb-length .msb-title{background:#bcd9bf;}  .msb-length .msb-body{background:#dcecdd;}
.msb-capacity .msb-title{background:#e9b9b9;}  .msb-capacity .msb-body{background:#f4d9d9;}
.msb-mass .msb-title{background:#e0cdaa;}  .msb-mass .msb-body{background:#ece1cb;}
@media(max-width:768px){.msb-panel{flex:1 1 100%;max-width:360px;}}
</style>

<div class="msb-wrap">
    <?php foreach(($data['groups'] ?? []) as $gi => $group): ?>
        <?php $type = strtolower((string)($group['type'] ?? '')); ?>
        <div class="msb-panel msb-<?= htmlspecialchars($type) ?>">
            <div class="msb-title"><?= htmlspecialchars(strtoupper($type)) ?></div>
            <div class="msb-body">
                <div class="msb-row">
                    <span class="msb-val"><?= htmlspecialchars($group['done_left']   ?? '') ?></span>
                    <span class="msb-sym"><?= htmlspecialchars($group['done_symbol'] ?? '') ?></span>
                    <span class="msb-val"><?= htmlspecialchars($group['done_right']  ?? '') ?></span>
                </div>
                <?php foreach(($group['questions'] ?? []) as $row): ?>
                    <div class="msb-row">
                        <span class="msb-val"><?= htmlspecialchars($row['left'] ?? '') ?></span>
                        <select class="msb-select" name="answer[<?= $q['id'] ?>][<?= $gi ?>][]">
                            <option value=""></option>
                            <option value="&gt;">&gt;</option>
                            <option value="&lt;">&lt;</option>
                            <option value="=">=</option>
                        </select>
                        <span class="msb-val"><?= htmlspecialchars($row['right'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>
<?php if($mode=="metric_fill_sum"): ?>

<style>
.mfs-card{background:#fff;border-radius:14px;box-shadow:0 4px 12px rgba(0,0,0,.08);padding:22px 28px;margin:16px 0;}
.mfs-item{display:flex;align-items:center;flex-wrap:wrap;gap:8px;font-size:18px;font-weight:600;color:#1c1c1c;}
.mfs-num{font-weight:700;margin-right:2px;}
.mfs-input{width:90px;border:none;border-bottom:2px solid #1c1c1c;background:transparent;outline:none;text-align:center;font-size:17px;font-weight:700;color:#1c1c1c;padding:2px 4px;}
@media(max-width:768px){.mfs-card{padding:16px 18px;}.mfs-item{font-size:16px;}}
</style>

<div class="mfs-card">
    <div class="mfs-item">
        <span class="mfs-num"><?= ($index + 1) ?>)</span>
        <?php foreach((array)($data['parts'] ?? []) as $part): ?>
            <?php if(is_array($part) && !empty($part['blank'])): ?>
                <input type="text" class="mfs-input" name="answer[<?= $q['id'] ?>][]" autocomplete="off">
            <?php else: ?>
                <span><?= htmlspecialchars(is_array($part) ? '' : (string)$part) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<?php endif; ?>
<?php if($mode=="largest_amount"): ?>

<style>

/* =====================================================
   LARGEST AMOUNT - EXACT WORKSHEET STYLE
===================================================== */

.largest-amount-card{
    background:#fff;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
    padding:25px 30px;
    margin-bottom:48px;
    width:100%;
    margin-top: 30px;
}

.largest-amount-heading{
    font-size:19px;
    line-height:1.5;
    font-weight:700;
    color:#8f4f3f;
    margin-bottom:22px;
}

/* 4 BOXES */

.largest-amount-grid{
    display:grid;
    grid-template-columns:repeat(4, 1fr);
    gap:12px;
    width:100%;
}

/* INDIVIDUAL COLORED BOX */

.largest-amount-box{
    min-height:155px;
    border-radius:28px;

    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;

    padding:15px 10px;
}

/* ALTERNATING COLORS */

.largest-amount-box:nth-child(odd){
    background:#f4bcb2;
}

.largest-amount-box:nth-child(even){
    background:#bfc9f7;
}

/* OPTION */

.largest-amount-choice{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;

    width:100%;
    margin:0;
    cursor:pointer;
}

/* HIDE RADIO */

.largest-amount-choice input{
    position:absolute;
    opacity:0;
    width:0;
    height:0;
    pointer-events:none;
}

/* TEXT */

.largest-amount-text{
    position:relative;

    display:flex;
    align-items:center;
    justify-content:center;

    min-height:38px;
    padding:3px 14px;

    font-family:Georgia, "Times New Roman", serif;
    font-size:17px;
    font-weight:700;
    color:#111;

    white-space:nowrap;
}

/* CIRCLE AROUND SELECTED ANSWER */

.largest-amount-choice input:checked + .largest-amount-text::after{
    content:"";

    position:absolute;

    left:50%;
    top:50%;

    width:92px;
    height:42px;

    transform:translate(-50%,-50%);

    border:3px solid #fff;
    border-radius:50%;

    pointer-events:none;
}


/* =====================================================
   MOBILE
===================================================== */

@media(max-width:900px){

    .largest-amount-grid{
        grid-template-columns:repeat(2, 1fr);
        gap:15px;
    }

}

@media(max-width:600px){

    .largest-amount-card{
        padding:18px;
    }

    .largest-amount-heading{
        font-size:17px;
    }

    .largest-amount-grid{
        grid-template-columns:1fr 1fr;
        gap:10px;
    }

    .largest-amount-box{
        min-height:145px;
        border-radius:24px;
    }

    .largest-amount-text{
        font-size:15px;
    }

    .largest-amount-choice input:checked + .largest-amount-text::after{
        width:82px;
        height:38px;
    }

}

@media(max-width:420px){

    .largest-amount-grid{
        grid-template-columns:1fr;
    }

}

</style>


<div class="largest-amount-card">

    <!-- HEADING -->

    <div class="largest-amount-heading">

        <?= htmlspecialchars(
            $data['question']
            ?? ''
        ) ?>

    </div>


    <!-- 4 BOXES -->

    <div class="largest-amount-grid">

        <?php foreach(($data['boxes'] ?? []) as $boxIndex => $box): ?>

            <div class="largest-amount-box">

                <?php foreach(($box['options'] ?? []) as $optionIndex => $option): ?>

                    <label class="largest-amount-choice">

                        <input
                            type="radio"
                            name="answer[<?= $q['id'] ?>][<?= $boxIndex ?>]"
                            value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="off"
                        >

                        <span class="largest-amount-text">
                            <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                        </span>

                    </label>

                <?php endforeach; ?>

            </div>

        <?php endforeach; ?>

    </div>

</div>


<?php endif; ?>
<script>

/* =====================================================
   PRIME / COMPOSITE SORT
===================================================== */

document.querySelectorAll('.prime-composite-wrapper').forEach(function(wrapper) {

    const qid = wrapper.dataset.qid;

    const hiddenInput =
        wrapper.querySelector('#primeCompositeAnswer_' + qid);

    const pool =
        wrapper.querySelector('.sort-number-pool');

    const dropZones =
        wrapper.querySelectorAll('.sort-drop-zone');


    /* =====================================================
       DRAG EVENTS
    ===================================================== */

    function addDragEvents(item) {

        item.addEventListener('dragstart', function(e) {

            e.dataTransfer.setData(
                'text/plain',
                this.dataset.number
            );

            e.dataTransfer.effectAllowed = 'move';

            this.classList.add('dragging');

        });


        item.addEventListener('dragend', function() {

            this.classList.remove('dragging');

        });

    }


    /* Original pool numbers */

    wrapper.querySelectorAll('.sort-number').forEach(function(item) {

        addDragEvents(item);

    });


    /* =====================================================
       DROP ZONES
    ===================================================== */

    dropZones.forEach(function(zone) {


        zone.addEventListener('dragover', function(e) {

            e.preventDefault();

            e.dataTransfer.dropEffect = 'move';

            this.classList.add('drag-over');

        });


        zone.addEventListener('dragleave', function() {

            this.classList.remove('drag-over');

        });


        zone.addEventListener('drop', function(e) {

            e.preventDefault();

            this.classList.remove('drag-over');


            const number =
                e.dataTransfer.getData('text/plain');

            if(!number) return;


            /*
             * Check if number is already
             * inside Prime or Composite.
             */

            const existing =
                wrapper.querySelector(
                    '.sort-drop-zone .sort-number[data-number="' +
                    number +
                    '"]'
                );


            /*
             * If already placed,
             * move it to the new column.
             */

            if(existing) {

                this.appendChild(existing);

                updateAnswer();

                return;

            }


            /*
             * Find original number from pool.
             */

            const original =
                pool.querySelector(
                    '.sort-number[data-number="' +
                    number +
                    '"]'
                );


            if(!original) return;


            /*
             * Clone number into selected column.
             */

            const item =
                original.cloneNode(true);

            item.classList.remove('source-used');

            item.setAttribute('draggable', 'true');

            addDragEvents(item);


            /*
             * Add to selected column.
             */

            this.appendChild(item);


            /*
             * Mark original pool number as used.
             */

            original.classList.add('source-used');


            /*
             * Update answer.
             */

            updateAnswer();

        });

    });


    /* =====================================================
       UPDATE HIDDEN ANSWER
    ===================================================== */

    function updateAnswer() {

        const result = {
            prime: [],
            composite: []
        };


        /*
         * PRIME NUMBERS
         */

        wrapper
            .querySelectorAll(
                '.sort-drop-zone[data-sort-type="prime"] .sort-number'
            )
            .forEach(function(item) {

                result.prime.push(
                    item.dataset.number
                );

            });


        /*
         * COMPOSITE NUMBERS
         */

        wrapper
            .querySelectorAll(
                '.sort-drop-zone[data-sort-type="composite"] .sort-number'
            )
            .forEach(function(item) {

                result.composite.push(
                    item.dataset.number
                );

            });


        /*
         * Save JSON
         */

        hiddenInput.value =
            JSON.stringify(result);

    }

});

</script>