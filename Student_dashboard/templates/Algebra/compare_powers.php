<?php
declare(strict_types=1);

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];

$left  = $payload['left'] ?? '';
$right = $payload['right'] ?? '';

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

/*
|--------------------------------------------------------------------------
| FRACTION DISPLAY
|--------------------------------------------------------------------------
| Converts "2/3" into a proper stacked fraction.
| If the value is not a fraction, it displays normally.
|--------------------------------------------------------------------------
*/

$renderFraction = function ($value) use ($h) {

    $value = trim((string)$value);

    if (strpos($value, '/') !== false) {

        $parts = explode('/', $value, 2);

        $numerator   = trim($parts[0]);
        $denominator = trim($parts[1]);

        return '
            <span class="fraction">
                <span class="fraction-top">' . $h($numerator) . '</span>
                <span class="fraction-bottom">' . $h($denominator) . '</span>
            </span>
        ';
    }

    return $h($value);
};
?>

<style>

.compare-power-card{
    background:#fff;
    padding:14px 20px !important;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,.08);
    overflow-x:auto;
    margin-top:20px;
    margin-bottom:20px;
}

.compare-power-card:hover{
    box-shadow:0 5px 15px rgba(0,0,0,.12);
}

.compare-power-row{
    display:flex;
    align-items:center;
    white-space:nowrap;
}


/* QUESTION NUMBER */

.q-no{
    width:36px;
    flex:0 0 auto;
    font-size:20px;
    font-weight:700;
}


/* LEFT FRACTION */

.compare-left{
    flex:0 0 auto;
    min-width:70px;
    margin-right:10px;
    font-size:20px;
    font-weight:600;
    text-align:right;
    white-space:nowrap;

    display:flex;
    justify-content:flex-end;
    align-items:center;
}


/* RIGHT FRACTION */

.compare-right{
    flex:0 0 auto;
    margin-left:10px;
    font-size:20px;
    font-weight:600;
    text-align:left;
    white-space:nowrap;

    display:flex;
    justify-content:flex-start;
    align-items:center;
}


/* =========================
   FRACTION
========================= */

.fraction{
    display:inline-flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;

    vertical-align:middle;

    min-width:28px;

    line-height:1;
    font-size:20px;
    font-weight:600;
}

.fraction-top{
    display:block;

    padding:0 5px 3px;

    line-height:1.05;

    border-bottom:1.5px solid #000;
}

.fraction-bottom{
    display:block;

    padding:3px 5px 0;

    line-height:1.05;
}


/* CENTER BOX */

.compare-middle{
    width:56px;
    flex:0 0 auto;

    display:flex;
    justify-content:center;
    align-items:center;
}


/* INPUT */

.compare-power-input{
    width:36px;
    height:30px;

    border:2px solid #9c27ff;
    border-radius:4px;   /* rectangle */

    background:#fff;

    text-align:center;

    font-size:22px;
    font-weight:700;

    outline:none;
    padding:0;

    box-sizing:border-box;
}


/* =========================
   MOBILE
========================= */

@media(max-width:768px){

    .compare-power-card{
        padding:10px 12px;
    }

    .q-no{
        width:28px;
        font-size:16px;
    }

    .compare-left{
        min-width:50px;
        font-size:16px;
    }

    .compare-right{
        font-size:16px;
    }

    .compare-middle{
        width:42px;
    }

     .compare-power-input{
        width:28px;
        height:28px;
        font-size:18px;
        border-radius:4px;
    }

    .fraction{
        font-size:18px;
        min-width:25px;
    }

}


/* =========================
   SMALL MOBILE
========================= */

@media(max-width:480px){

    .q-no{
        width:22px;
        font-size:14px;
    }

    .compare-left{
        min-width:36px;
        font-size:14px;
    }

    .compare-right{
        font-size:14px;
    }

    .compare-middle{
        width:34px;
    }

    .compare-power-input{
        width:24px;
        height:24px;
        font-size:15px;
        border-radius:4px;
    }

    .fraction{
        font-size:15px;
        min-width:22px;
    }

    .fraction-top{
        padding:0 4px 2px;
    }

    .fraction-bottom{
        padding:2px 4px 0;
    }

}

</style>


<div class="compare-power-card">

    <div class="compare-power-row">

        <!-- QUESTION NUMBER -->

        <div class="q-no">
            <?= ($index + 1) ?>)
        </div>


        <!-- LEFT FRACTION -->

        <div class="compare-left">
            <?= $renderFraction($left) ?>
        </div>


        <!-- COMPARISON INPUT -->

        <div class="compare-middle">

            <input
                type="text"
                maxlength="1"
                class="compare-power-input"
                name="answer[<?= $id ?>]"
                autocomplete="off"
            >

        </div>


        <!-- RIGHT FRACTION -->

        <div class="compare-right">
            <?= $renderFraction($right) ?>
        </div>

    </div>

</div>