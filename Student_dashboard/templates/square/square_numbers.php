<?php
declare(strict_types=1);

$q = $q ?? [];

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$type    = $q['question_type'] ?? '';
$id      = (int)($q['id'] ?? 0);

$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$GLOBALS['partCounters'] = $GLOBALS['partCounters'] ?? [];
$is_first_missing_digit = $is_first_missing_digit ?? false;
?>

<style>

.square-card{
    width:100%;
    background:#f5f5f5;
    border-radius:12px;
    padding:25px;
    margin-bottom:25px;
    position:relative;
    overflow:visible;
}

.first-missing-card{
    position:relative;
}

.square-hint{
    position:absolute;
    right:-360px;
    top:-20px;
    width:320px;
}

.square-title{
    font-size:20px;
    font-weight:600;
    margin-bottom:25px;
}

.square-row{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:18px;
    flex-wrap:wrap;
}

.square-row span{
    font-size:20px;
    font-weight:600;
}

.square-input{
    width:70px;
    border:none;
    border-bottom:2px solid #000;
    background:transparent;
    text-align:center;
    font-size:18px;
    outline:none;
}

.square-small{
    width:70px;
}

.root-input-group{
    display:inline-flex;
    align-items:flex-start;
}

.root-sign{
    font-size:30px;
    line-height:1.25;
    margin-right:-2px;
    margin-top:6px;
    display:inline-block;
    transform-origin:bottom;
    transform:scaleY(1.55);
}

.root-box{
    border-top:2px solid #000;
    width:60px;
    margin-left:-1px;
}

.root-box-number{
    border-top:2px solid #000;
    min-width:28px;
    text-align:center;
    font-size:18px;
    font-weight:600;
    padding:0 4px;
}

.root-box input{
    width:60px;
    border:none;
    background:transparent;
    text-align:center;
    font-size:18px;
    outline:none;
}

.root-step1{
    padding-top:2px;
}

/* =========================
   MISSING DIGIT
========================= */

.missing-digit-box{
    width:40px;
    height:40px;
    border:2px solid #9c27ff;
    text-align:center;
    font-size:20px;
    font-weight:600;
    outline:none;
}

/* =========================
   MATCH TYPE
========================= */

.match-wrapper{
    display:flex;
    justify-content:space-between;
    gap:40px;
    margin-top:20px;
}

.match-left,
.match-right{
    width:45%;
}

.match-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:25px;
}

.match-question{
    font-size:20px;
    font-weight:600;
}

.match-answer{
    font-size:20px;
    font-weight:600;
}

.match-wrapper-main{
    width:100%;
    display:flex;
    justify-content:space-between;
    padding:20px 60px;
    margin-top:20px;
}

/* =========================
   LEFT SIDE
========================= */

.match-left-side{
    width:40%;
}

.match-left-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:55px;
}

.match-question{
    font-size:20px;
    font-weight:600;
    color:#111;
}

.match-select{
    width:110px;
    height:45px;
    border:1px solid #d6d6d6;
    border-radius:8px;
    background:#fff;
    padding:0 10px;
    font-size:20px;
    outline:none;
    cursor:pointer;
}

/* =========================
   RIGHT SIDE
========================= */

.match-right-side{
    width:30%;
}

.match-right-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:55px;
}

.match-option{
    font-size:20px;
    font-weight:600;
    width:50px;
    color:#000;
}

.match-answer{
    font-size:20px;
    font-weight:600;
    color:#111;
}

/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .match-wrapper-main{
        flex-direction:column;
        gap:20px;
        padding:15px;
    }

    .match-left-side,
    .match-right-side{
        width:100%;
    }

    .match-question{
        font-size:20px;
    }

    .match-answer{
        font-size:20px;
    }

}
</style>

<?php


/* =========================================================
   1. SQUARE COMPLETE TYPE
========================================================= */

if($type === 'square_complete'):

?>

<div class="square-card">

    <div class="square-row">

        <div class="square-question">

            <span>
                <?= $h($q['question_image']) ?><sup>2</sup> =
            </span>

        </div>

        <input
            type="text"
            class="square-input"
            name="answer[<?= $id ?>]"
        >

    </div>

</div>


<?php

/* =========================================================
   2. FILL MISSING DIGIT
========================================================= */

elseif($type === 'square_missing_digit'):
?>

<div class="square-card <?= $is_first_missing_digit ? 'first-missing-card' : '' ?>"
     style="<?= $is_first_missing_digit ? 'position:relative;' : '' ?>">

    <div class="square-row">

        <span><?= ($index + 1) ?>)</span>

        <span>
            <?= $h($payload['question'] ?? '') ?>
        </span>

        <input
            type="text"
            maxlength="1"
            class="missing-digit-box"
            name="answer[<?= $id ?>]"
        >

    </div>
    <?php if ($is_first_missing_digit): ?>
    
    <div style="
    position:absolute;
    right:20px;
    top:-45px;
    width:250px;
    z-index:9999;
    ">
    <img
        src="templates/images/square_hint.png"
        style="width:100%;display:block;"
    >
</div>

<?php endif; ?>
</div>


<?php
elseif($type === 'perfect_square_root'):

$number = $payload['number'] ?? '';

?>

<div class="square-card">

    <div class="square-row">
     <!--<span><?= ($index + 1) ?>)</span>-->
   <div class="root-input-group root-first">

    <span class="root-sign">√</span>

    <div class="root-box-number">
        <?= $h($number) ?>
    </div>

</div>

<span>=</span>

<div class="root-input-group">

    <span class="root-sign">√</span>

    <div class="root-box">
    <input
        type="text"
        class="root-step1"
        name="answer[<?= $id ?>][step1]"
        autocomplete="off"
    >
</div>

</div>

<span>=</span>

<input
    type="text"
    class="square-input square-small"
    name="answer[<?= $id ?>][step2]"
    autocomplete="off"
>

    </div>

</div>

<?php
/* =========================================================
   3. MATCH THE CORRECT ANSWER
========================================================= */

    elseif($type === 'square_match'):
    $part = $payload['part'] ?? '1';
    
    $GLOBALS['partCounters'] =
    $GLOBALS['partCounters'] ?? [];
    
    $GLOBALS['printedParts'] =
    $GLOBALS['printedParts'] ?? [];

    if(!isset($GLOBALS['printedParts'][$part])){
    
        echo '
            <div style="
                width:100%;
                font-size:20px;
                font-weight:600;
                margin:40px 0 20px;
                color:#111;
            ">
                '.$part.')
            </div>
            ';
    
        $GLOBALS['printedParts'][$part] = true;
    }
    $GLOBALS['match_option_counter'] =
    $GLOBALS['match_option_counter'] ?? 0;
    ?>

<div class="match-wrapper-main">

    <!-- =========================
         LEFT COLUMN
    ========================== -->

    <div class="match-left-side">

        <div class="match-left-row">

            <div class="match-question">
                <?= $h($q['question_image'] ?? '') ?>
            </div>

            <select
                class="match-select"
                name="answer[<?= $id ?>]"
            >
                <option value="">Select</option>

                <!-- ALWAYS A TO D -->

                <option value="a">a</option>
                <option value="b">b</option>
                <option value="c">c</option>
                <option value="d">d</option>

            </select>

        </div>

    </div>

    <!-- =========================
         RIGHT COLUMN
    ========================== -->

    <div class="match-right-side">

        <div class="match-right-row">

          <div class="match-option">
        <?php
        
        $part = $payload['part'] ?? '1';
        
        if(!isset($GLOBALS['partCounters'][$part])){
            $GLOBALS['partCounters'][$part] = 0;
        }
        
        echo chr(97 + $GLOBALS['partCounters'][$part]) . ')';
        
        $GLOBALS['partCounters'][$part]++;
        
        ?>
            
            </div>
            <div class="match-answer">
                <?= $h($payload['match_value'] ?? '') ?>
            </div>

        </div>

    </div>

</div>

<?php endif; ?>


<script>
document.addEventListener('DOMContentLoaded', function(){

    const form = document.querySelector('form');

    if(!form) return;

    form.addEventListener('submit', function(){

        document
        .querySelectorAll('input[name*="[step1]"]')
        .forEach(function(input){

            let val = input.value.trim();

            const match = val.match(/^(\d+)\^(\d+)$/);

            if(match){

                const base = match[1];
                const power = match[2];

                if(base === power){
                    val = base + '²';
                }
            }

            input.value = val;

            console.log('AFTER =>', input.value);

        });

    });

});
</script>