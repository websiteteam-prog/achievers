<?php

$payload = json_decode($q['question_payload'], true);
$headers = $payload['headers'] ?? [];

if (!isset($GLOBALS['place_value_header_shown'])) {
    $GLOBALS['place_value_header_shown'] = false;
}

?>

<style>

.place-value-wrapper{
    margin:15px 0;
}

.place-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}

.question-text{
    width:380px;
    flex-shrink:0;
    font-size:18px;
    font-weight:500;
    line-height:1.4;
}

.question-text.first-row{
    padding-top:40px;
}

.table-side{
    display:flex;
    flex-direction:column;
}

.place-header{
    display:flex;
    gap:8px;
    margin-top:18px;
    margin-bottom:6px;
}

.place-head{
    width:40px;
    height:22px;
    background:#f3d4bf;
    border-radius:3px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    font-size:13px;
}

.place-table{
    display:flex;
    gap:8px;
}

.place-cell input{
    width:40px;
    height:32px;
    border:1px solid #bbb;
    border-radius:5px;
    text-align:center;
    font-size:18px;
}

.place-cell input:focus{
    outline:none;
    border-color:#1F669C;
    box-shadow:0 0 0 2px rgba(31,102,156,.15);
}

</style>

<div class="place-value-wrapper">

    <div class="place-row">

        <div class="question-text <?= !$GLOBALS['place_value_header_shown'] ? 'first-row' : '' ?>">
            <?= $char.'. '.htmlspecialchars($q['question_text']) ?>
        </div>

        <div class="table-side">

            <?php if(!$GLOBALS['place_value_header_shown']){ ?>

                <div class="place-header">

                    <?php foreach($headers as $head){ ?>

                        <div class="place-head">
                            <?= htmlspecialchars($head) ?>
                        </div>

                    <?php } ?>

                </div>

                <?php
                $GLOBALS['place_value_header_shown'] = true;
                ?>

            <?php } ?>

            <div class="place-table">

                <?php foreach($headers as $head){ ?>

                    <div class="place-cell">

                        <input
                            type="text"
                            maxlength="1"
                            name="answer[<?= $q['id'] ?>][]">

                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

    <?php $char++; ?>

</div>