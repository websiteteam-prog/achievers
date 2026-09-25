<?php
declare(strict_types=1);

$q = $q ?? [];
$index = $index ?? 0;

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

$questionText = (string)($q['question_text'] ?? '');
$unit = (string)($q['unit'] ?? '');

/*
|--------------------------------------------------------------------------
| Question Payload
|--------------------------------------------------------------------------
| Optional payload for future use / checking / diagram generation.
*/
$payload = [];

if (!empty($q['question_payload'])) {
    $decoded = json_decode(
        (string)$q['question_payload'],
        true
    );

    if (is_array($decoded)) {
        $payload = $decoded;
    }
}
?>

<style>

/* ============================================================
   PERIMETER WORD PROBLEM CARD
   ============================================================ */

.perimeter-word-card {
    width: 100%;
    box-sizing: border-box;

    background: #ffffff;

    border: 1px solid #e2e2e2;
    border-radius: 14px;

    padding: 24px 28px;
    margin: 0 0 22px 0;

    box-shadow: 0 2px 7px rgba(0,0,0,0.06);

    transition: box-shadow 0.2s ease,
                transform 0.2s ease;
}

.perimeter-word-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.09);
}


/* ============================================================
   QUESTION
   ============================================================ */

.perimeter-word-question {
    display: flex;
    align-items: flex-start;

    gap: 12px;

    font-size: 19px;
    line-height: 1.65;

    color: #222;

    margin-bottom: 24px;
}


/* Question number */

.perimeter-word-number {
    flex-shrink: 0;

    font-weight: 700;

    color: #222;

    min-width: 28px;
}


/* Question text */

.perimeter-word-text {
    flex: 1;

    font-weight: 500;
}


/* ============================================================
   BOTTOM AREA
   ============================================================ */

.perimeter-word-bottom {
    display: flex;

    align-items: center;

    gap: 18px;

    margin-left: 40px;
}


/* ============================================================
   DRAWING BOX
   ============================================================ */

.perimeter-draw-box {
    width: 300px;
    height: 150px;

    border: 2px dashed #b8b8b8;
    border-radius: 10px;

    background: #fff;

    position: relative;

    flex-shrink: 0;

    overflow: hidden;
}

.drawing-canvas {
    width: 100%;
    height: 100%;

    display: block;

    cursor: crosshair;

    touch-action: none;
}

.clear-drawing {
    position: absolute;

    right: 8px;
    bottom: 8px;

    border: none;

    background: #dc3545;
    color: #fff;

    padding: 5px 10px;

    border-radius: 5px;

    font-size: 12px;

    cursor: pointer;
}

.clear-drawing:hover {
    background: #b02a37;
}


/* ============================================================
   ANSWER AREA
   ============================================================ */

.perimeter-word-answer {
    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 19px;

    white-space: nowrap;
}


.perimeter-answer-label {
    font-weight: 700;

    color: #222;
}


/* ============================================================
   INPUT
   ============================================================ */

.perimeter-word-input {
    width: 150px;
    height: 38px;

    border: none;

    border-bottom: 2px solid #1f669c;

    background: transparent;

    outline: none;

    padding: 3px 6px;

    text-align: center;

    font-size: 18px;

    font-weight: 600;

    box-sizing: border-box;
}


.perimeter-word-input:focus {
    border-bottom-color: #007bff;
}


/* ============================================================
   UNIT
   ============================================================ */

.perimeter-word-unit {
    font-size: 18px;

    font-weight: 600;

    color: #444;
}


/* ============================================================
   MOBILE
   ============================================================ */

@media (max-width: 768px) {

    .perimeter-word-card {
        padding: 18px;
    }

    .perimeter-word-question {
        font-size: 17px;

        line-height: 1.55;
    }

    .perimeter-word-bottom {
        margin-left: 0;

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .perimeter-draw-box {
        width: 160px;
        height: 80px;
    }

    .perimeter-word-answer {
        font-size: 17px;
    }

    .perimeter-word-input {
        width: 120px;
    }

    .perimeter-word-unit {
        font-size: 16px;
    }
}

</style>


<!-- ============================================================
     WORD PROBLEM CARD
     ============================================================ -->

<div class="perimeter-word-card">

    <!-- QUESTION -->

    <div class="perimeter-word-question">

        <div class="perimeter-word-number">
            <?= ($index + 1) ?>)
        </div>

        <div class="perimeter-word-text">
            <?= nl2br($h($questionText)) ?>
        </div>

    </div>


    <!-- DRAW + ANSWER -->

    <div class="perimeter-word-bottom">

        <!-- DRAWING AREA -->

       <div class="perimeter-draw-box">

    <canvas
        class="drawing-canvas"
        id="drawingCanvas<?= (int)$q['id'] ?>"
        width="300"
        height="150"
    ></canvas>

    <button
        type="button"
        class="clear-drawing"
        onclick="clearDrawing<?= (int)$q['id'] ?>()"
    >
        Clear
    </button>

    <input
        type="hidden"
        name="drawing[<?= (int)$q['id'] ?>]"
        id="drawingData<?= (int)$q['id'] ?>"
    >

</div>


        <!-- ANSWER -->

        <div class="perimeter-word-answer">

            <span class="perimeter-answer-label">
                Answer =
            </span>

            <input
                type="text"
                name="answer[<?= (int)$q['id'] ?>]"
                class="perimeter-word-input"
                autocomplete="off"
                inputmode="decimal"
            >

            <span class="perimeter-word-unit">
                <?= $h($unit) ?>
            </span>

        </div>

    </div>

</div>
<script>

(function () {

    const canvas = document.getElementById(
        'drawingCanvas<?= (int)$q['id'] ?>'
    );

    const hiddenInput = document.getElementById(
        'drawingData<?= (int)$q['id'] ?>'
    );

    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    let drawing = false;

    /*
    |--------------------------------------------------------------------------
    | Canvas setup
    |--------------------------------------------------------------------------
    */

    ctx.lineWidth = 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#222';


    /*
    |--------------------------------------------------------------------------
    | Get mouse/touch position
    |--------------------------------------------------------------------------
    */

    function getPosition(e) {

        const rect = canvas.getBoundingClientRect();

        let clientX;
        let clientY;

        if (e.touches && e.touches.length > 0) {

            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;

        } else {

            clientX = e.clientX;
            clientY = e.clientY;
        }

        return {
            x: (clientX - rect.left) *
               (canvas.width / rect.width),

            y: (clientY - rect.top) *
               (canvas.height / rect.height)
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Start drawing
    |--------------------------------------------------------------------------
    */

    function startDrawing(e) {

        e.preventDefault();

        drawing = true;

        const pos = getPosition(e);

        ctx.beginPath();

        ctx.moveTo(
            pos.x,
            pos.y
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Draw
    |--------------------------------------------------------------------------
    */

    function draw(e) {

        if (!drawing) return;

        e.preventDefault();

        const pos = getPosition(e);

        ctx.lineTo(
            pos.x,
            pos.y
        );

        ctx.stroke();
    }


    /*
    |--------------------------------------------------------------------------
    | Stop drawing
    |--------------------------------------------------------------------------
    */

    function stopDrawing(e) {

        if (!drawing) return;

        e.preventDefault();

        drawing = false;

        ctx.closePath();

        saveDrawing();
    }


    /*
    |--------------------------------------------------------------------------
    | Save drawing into hidden input
    |--------------------------------------------------------------------------
    */

    function saveDrawing() {

        hiddenInput.value =
            canvas.toDataURL('image/png');
    }


    /*
    |--------------------------------------------------------------------------
    | Mouse events
    |--------------------------------------------------------------------------
    */

    canvas.addEventListener(
        'mousedown',
        startDrawing
    );

    canvas.addEventListener(
        'mousemove',
        draw
    );

    canvas.addEventListener(
        'mouseup',
        stopDrawing
    );

    canvas.addEventListener(
        'mouseleave',
        stopDrawing
    );


    /*
    |--------------------------------------------------------------------------
    | Touch events
    |--------------------------------------------------------------------------
    */

    canvas.addEventListener(
        'touchstart',
        startDrawing,
        { passive: false }
    );

    canvas.addEventListener(
        'touchmove',
        draw,
        { passive: false }
    );

    canvas.addEventListener(
        'touchend',
        stopDrawing,
        { passive: false }
    );


    /*
    |--------------------------------------------------------------------------
    | Clear drawing
    |--------------------------------------------------------------------------
    */

    window[
        'clearDrawing<?= (int)$q['id'] ?>'
    ] = function () {

        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );

        hiddenInput.value = '';

    };

})();

</script>