<?php
declare(strict_types=1);

// =========================================================
// SAFE GUARDS
// =========================================================

$q    = $q ?? [];
$char = $char ?? 1;


// =========================================================
// SAFE ESCAPE
// =========================================================

$h = fn($s) => htmlspecialchars(
    (string)$s,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);


// =========================================================
// READ QUESTION DATA
// =========================================================

$text_template = (string)($q['question_text'] ?? '');

$payload_raw = (string)(
    $q['question_payload'] ?? '{}'
);

$data = json_decode(
    $payload_raw,
    true
);

if (!is_array($data)) {
    $data = [];
}


// =========================================================
// ANSWER VARIABLE
// =========================================================

$var = isset($data['text']) && $data['text'] !== ''
    ? (string)$data['text']
    : 'x';


// =========================================================
// PLACEHOLDER REPLACEMENTS
// =========================================================

$rendered_text = strtr(
    $text_template,
    [

        '{digit}' =>
            '<span class="eq-highlight">' .
            $h($data['digit'] ?? '') .
            '</span>',

        '{text}' =>
            $h($data['text'] ?? ''),

        '{text2}' =>
            $h($data['text2'] ?? ''),

    ]
);


// =========================================================
// QUESTION IMAGE
// =========================================================

$question_image = trim(
    (string)($q['question_image'] ?? '')
);

$image_url = '';

if ($question_image !== '') {

    $image_url =
        '/' .
        ltrim(
            $question_image,
            '/'
        );
}

?>

<style>

/* =========================================================
   MAIN EQUATION CARD
   ========================================================= */

.eq-question-card {

    width: 100%;

    max-width: 1100px;

    margin: 20px auto;

    padding: 30px 40px;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 20px;

    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.07);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


/* Hover */

.eq-question-card:hover {

    transform: translateY(-2px);

    border-color: #cbd8e8;

    box-shadow:
        0 10px 28px rgba(0, 0, 0, 0.10);

}


/* =========================================================
   QUESTION ROW
   ========================================================= */

.eq-question-row {

    display: flex;

    align-items: center;

    gap: 12px;

    width: 100%;

    margin-bottom: 28px;

}


/* =========================================================
   QUESTION NUMBER
   ========================================================= */

.eq-question-number {

    flex-shrink: 0;

    font-size: 23px;

    font-weight: 700;

    color: #172033;

    line-height: 1.4;

    margin-right: 22px;

}


/* =========================================================
   EQUATION
   ========================================================= */

.eq-equation {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    margin: 0;

    padding: 0;

    font-size: 23px;

    line-height: 1.5;

    font-weight: 700;

}


/* =========================================================
   HIGHLIGHT DIGIT
   ========================================================= */

.eq-highlight {

    text-decoration: underline;

    font-weight: 800;

    color: #1f669c;

}


/* =========================================================
   ANSWER AREA
   ========================================================= */

.eq-answer-area {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-top: 10px;

    font-size: 24px;

    font-weight: 700;

    color: #172033;

}


/* =========================================================
   VARIABLE
   ========================================================= */

.eq-variable {

    min-width: 35px;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size: 24px;

    font-weight: 700;

}


/* =========================================================
   ANSWER INPUT
   ========================================================= */

.eq-answer-input {

    width: 230px;

    height: 54px;

    padding: 8px 16px;

    border: 2px solid #d7dfeb;

    border-radius: 12px;

    background: #f8fafc;

    font-size: 20px;

    font-weight: 600;

    outline: none;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;

}


/* Input focus */

.eq-answer-input:focus {

    background: #ffffff;

    border-color: #1f669c;

    box-shadow:
        0 0 0 4px rgba(
            31,
            102,
            156,
            0.12
        );

}


/* Placeholder */

.eq-answer-input::placeholder {

    color: #a8b2c1;

    font-size: 16px;

    font-weight: 400;

}


/* =========================================================
   QUESTION IMAGE
   ========================================================= */

.eq-question-image {

    width: 100%;

    margin-top: 20px;

    margin-bottom: 10px;

}


.eq-question-image img {

    display: block;

    max-width: 100%;

    height: auto;

    border-radius: 10px;

}


/* =========================================================
   TABLET
   ========================================================= */

@media (max-width: 992px) {

    .eq-question-card {

        padding: 26px 30px;

    }


    .eq-question-number {

        font-size: 25px;

    }


    .eq-equation {

        font-size: 27px;

    }


    .eq-answer-area {

        font-size: 22px;

    }


    .eq-answer-input {

        width: 210px;

    }

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 768px) {

    .eq-question-card {

        width: 100%;

        margin: 15px auto;

        padding: 22px 18px;

        border-radius: 16px;

    }


    .eq-question-row {

        gap: 8px;

        align-items: flex-start;

        margin-bottom: 22px;

    }


    .eq-question-number {

        font-size: 22px;

        line-height: 1.5;

    }


    .eq-equation {

        font-size: 23px;

        line-height: 1.5;

    }


    .eq-answer-area {

        gap: 10px;

        font-size: 20px;

    }


    .eq-variable {

        font-size: 21px;

        min-width: 30px;

    }


    .eq-answer-input {

        width: 190px;

        height: 48px;

        font-size: 18px;

    }


    .eq-answer-input::placeholder {

        font-size: 14px;

    }

}


/* =========================================================
   SMALL MOBILE
   ========================================================= */

@media (max-width: 480px) {

    .eq-question-card {

        padding: 18px 14px;

        border-radius: 14px;

    }


    .eq-question-row {

        gap: 6px;

    }


    .eq-question-number {

        font-size: 20px;

    }


    .eq-equation {

        font-size: 20px;

    }


    .eq-answer-area {

        gap: 8px;

        font-size: 18px;

    }


    .eq-variable {

        font-size: 19px;

        min-width: 27px;

    }


    .eq-answer-input {

        width: 155px;

        height: 44px;

        padding: 6px 12px;

        font-size: 17px;

    }

}

</style>


<!-- =========================================================
     EQUATION QUESTION CARD
     ========================================================= -->

<div class="eq-question-card">


    <!-- =====================================================
         QUESTION NUMBER + EQUATION
         ===================================================== -->

    <div class="eq-question-row">


        <!-- Question Number -->

        <div class="eq-question-number">

            <?php
            if (is_numeric($char)) {

                echo (int)$char;

            } else {

                echo $h($char);

            }
            ?>)

        </div>


        <!-- Equation -->

        <div class="eq-equation">

            <?=
                $rendered_text
                ?: $h(
                    $q['question_text'] ?? ''
                )
            ?>

        </div>


    </div>


    <!-- =====================================================
         QUESTION IMAGE
         ===================================================== -->

    <?php if ($image_url): ?>

        <div class="eq-question-image">

            <img
                src="<?= $h($image_url) ?>"
                alt="Question Image"
            >

        </div>

    <?php endif; ?>


    <!-- =====================================================
         ANSWER AREA
         ===================================================== -->

    <div class="eq-answer-area">


        <!-- Variable -->

        <span class="eq-variable">

            <?= $h($var) ?> =

        </span>


        <!-- Answer Input -->

        <input
            type="text"
            class="eq-answer-input"
            name="answer[<?= (int)$q['id'] ?>]"
            placeholder="Enter answer"
            autocomplete="off"
        >


    </div>


</div>


<?php
// =========================================================
// INCREMENT QUESTION NUMBER
// =========================================================

if (is_numeric($char)) {

    $char = (int)$char + 1;

} else {

    $char = chr(
        ord((string)$char) + 1
    );

}
?>