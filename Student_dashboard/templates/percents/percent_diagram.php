    <?php

    $data = json_decode($q['question_payload'], true);
    $image = $q['question_image'] ?? '';
    $p = $data['percent'] ?? '';
    $n = $data['number'] ?? '';

    $section_id = $q['instruction_id'] ?? 0;

    if (!isset($GLOBALS['section_question'][$section_id])) {
        $GLOBALS['section_question'][$section_id] = 1;
    }

    ?>

    <style>

    .percent-box{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
    margin-bottom:25px;
    }

    .percent-main{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:20px;
        flex-wrap:wrap;
    }

    .percent-left{
        flex:1;
        min-width:250px;
    }

    .percent-right{
        width:180px; 
        flex-shrink:0;
    }

    .percent-right img{
        width:100%;
        height:auto;
        border-radius:10px;
    }

    .percent-row{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    font-size:22px;
    }

    .percent-input{
    width:120px;
    border:none;
    border-bottom:2px solid #000;
    text-align:center;
    font-size:20px;
    outline:none;
    background:transparent;
    }

    .percent-image{
        text-align:center;
        margin-bottom:15px;
    }

    .percent-image img{
        max-width:100%;
        height:auto;
        border-radius:10px;
    }

</style>

<div class="percent-box">

    <div class="percent-main">

        <!-- LEFT SIDE -->
        <div class="percent-left">
            <div class="percent-row">
                <strong><?= $GLOBALS['section_question'][$section_id]++ ?>)</strong>
                <?= $p ?>% of <?= $n ?> =
                <input type="text"
                name="answer[<?= $q['id'] ?>]"
                class="percent-input">
            </div>
        </div>

        <!-- RIGHT SIDE IMAGE -->
        <?php if(!empty($image)) { ?>
            <div class="percent-right">
                <img src="<?= $image ?>" alt="question image">
            </div>
        <?php } ?>

    </div>

</div>