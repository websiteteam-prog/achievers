<?php
declare(strict_types=1);

/**
 * =========================================================
 * PROPORTION CHAIN TEMPLATE
 * =========================================================
 *
 * Supports:
 *
 * 1. ONE BLANK
 *
 *    Example:
 *    2 : 3 = ___ : 9
 *
 *    POST:
 *    answer[id] = 6
 *
 *
 * 2. MULTIPLE BLANKS - ONE ROW
 *
 *    Example:
 *    1:8 = ___:16 = ___:24 = 4:32 = 40:___ = ___:48
 *
 *    POST:
 *    answer[id][0] = 2
 *    answer[id][1] = 3
 *    answer[id][2] = 5
 *    answer[id][3] = 6
 *
 *    Result:
 *    ["2","3","5","6"]
 *
 *
 * 3. MULTIPLE BLANKS - MULTIPLE ROWS
 *
 *    Row 1:
 *    1:2 = ___:4
 *
 *    Row 2:
 *    2:3 = ___:6
 *
 *    POST:
 *    answer[id][0][0] = 2
 *    answer[id][1][0] = 4
 *
 *    Result:
 *    [["2"],["4"]]
 *
 */


/* =========================================================
   QUESTION
========================================================= */

$q = $q ?? [];


/* =========================================================
   QUESTION PAYLOAD
========================================================= */

$data = json_decode(
    $q['question_payload'] ?? '{}',
    true
);

$data = is_array($data) ? $data : [];


/* =========================================================
   CORRECT ANSWER
========================================================= */

$correct = json_decode(
    $q['correct_answer'] ?? 'null',
    true
);


/* =========================================================
   STUDENT ANSWER
========================================================= */

$student = json_decode(
    $q['student_answer'] ?? 'null',
    true
);


/* =========================================================
   ITEMS
========================================================= */

$items = $data['items'] ?? [];

if (!is_array($items)) {
    $items = [];
}


/* =========================================================
   HTML ESCAPE
========================================================= */

$h = static function ($value): string {

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );

};


/* =========================================================
   GET PARTS FROM ITEM
========================================================= */

$getParts = static function ($item): array {

    if (
        is_array($item)
        && isset($item['parts'])
        && is_array($item['parts'])
    ) {

        return $item['parts'];

    }

    if (is_array($item)) {
        return $item;
    }

    return [];

};


/* =========================================================
   COUNT TOTAL BLANKS
========================================================= */

$totalBlanks = 0;

foreach ($items as $item) {

    $parts = $getParts($item);

    foreach ($parts as $part) {

        $part = (string)$part;

        $totalBlanks += substr_count(
            $part,
            '___'
        );

    }

}


/* =========================================================
   STRUCTURE DETECTION
========================================================= */

/*
 * ONE BLANK
 *
 * answer[id] = 6
 */

$singleBlank = (
    $totalBlanks === 1
);


/*
 * MULTIPLE BLANKS IN ONLY ONE ROW
 *
 * answer[id][0]
 * answer[id][1]
 * answer[id][2]
 *
 * NOT:
 *
 * answer[id][0][0]
 */

$singleRow = (
    count($items) === 1
);


/* =========================================================
   NORMALIZE SINGLE VALUE
========================================================= */

$getSingleValue = static function ($value): string {

    while (is_array($value)) {

        if (count($value) === 0) {
            return '';
        }

        /*
         * If old saved data is:
         *
         * [["6"]]
         *
         * then keep going until scalar.
         */

        $value = reset($value);
    }

    return is_scalar($value)
        ? (string)$value
        : '';

};


/* =========================================================
   NORMALIZE ONE-ROW ARRAY
========================================================
 *
 * Handles both:
 *
 * ["2","3","32","5","6"]
 *
 * and:
 *
 * [["2","3","32","5","6"]]
 *
 * Returns:
 *
 * ["2","3","32","5","6"]
 */

$getOneRowArray = static function ($value): array {

    if (!is_array($value)) {
        return [];
    }


    /*
     * Already flat:
     *
     * ["2","3","32"]
     */

    $isFlat = true;

    foreach ($value as $v) {

        if (is_array($v)) {
            $isFlat = false;
            break;
        }

    }

    if ($isFlat) {
        return $value;
    }


    /*
     * Nested:
     *
     * [["2","3","32"]]
     *
     * Take first row.
     */

    $first = reset($value);

    if (is_array($first)) {
        return $first;
    }

    return [];

};


/* =========================================================
   SINGLE BLANK VALUES
========================================================= */

$singleCorrectValue = '';

$singleStudentValue = '';

if ($singleBlank) {

    $singleCorrectValue =
        $getSingleValue($correct);

    $singleStudentValue =
        $getSingleValue($student);

}


/* =========================================================
   INPUT NAME HELPER
========================================================= */

$getAnswerName = static function (
    int $questionId,
    int $rowIndex,
    int $answerKey,
    bool $singleBlank,
    bool $singleRow
): string {

    /*
     * ONE BLANK
     *
     * answer[414]
     */

    if ($singleBlank) {

        return 'answer['
            . $questionId
            . ']';

    }


    /*
     * MULTIPLE BLANKS - ONE ROW
     *
     * answer[415][0]
     * answer[415][1]
     * answer[415][2]
     */

    if ($singleRow) {

        return 'answer['
            . $questionId
            . ']['
            . $answerKey
            . ']';

    }


    /*
     * MULTIPLE ROWS
     *
     * answer[415][0][0]
     * answer[415][1][0]
     */

    return 'answer['
        . $questionId
        . ']['
        . $rowIndex
        . ']['
        . $answerKey
        . ']';

};


/* =========================================================
   PREPARE ONE-ROW DATA
========================================================= */

$oneRowCorrect = [];

$oneRowStudent = [];

if (!$singleBlank && $singleRow) {

    $oneRowCorrect =
        $getOneRowArray($correct);

    $oneRowStudent =
        $getOneRowArray($student);

}

?>


<style>

/* =========================================================
   PROPORTION CARD
========================================================= */

.proportion-card{

    width:100%;

    box-sizing:border-box;

    background:#fff;

    border-radius:14px;

    box-shadow:0 4px 12px rgba(0,0,0,.08);

    padding:24px 30px;

    margin:15px 0 25px;

    overflow:hidden;

}


/* =========================================================
   PROPORTION LIST
========================================================= */

.proportion-list{

    display:flex;

    flex-direction:column;

    gap:18px;

    width:100%;

}


/* =========================================================
   PROPORTION ROW
========================================================= */

.proportion-row{

    width:100%;

    box-sizing:border-box;

    background:#f8eeee;

    border-radius:10px;

    padding:13px 18px;

    display:flex;

    align-items:center;

    flex-wrap:wrap;

    gap:8px;

    font-size:20px;

    font-weight:600;

    color:#111;

    line-height:1.5;

}


/* =========================================================
   RATIO PART
========================================================= */

.proportion-part{

    display:inline-flex;

    align-items:center;

    white-space:nowrap;

}


/* =========================================================
   SYMBOLS
========================================================= */

.proportion-symbol{

    display:inline-flex;

    align-items:center;

    justify-content:center;

    margin:0 2px;

    font-weight:700;

}


/* =========================================================
   INPUT
========================================================= */

.proportion-input{

    width:72px;

    min-width:72px;

    height:34px;

    box-sizing:border-box;

    border:none;

    border-bottom:2px solid #000;

    background:transparent;

    outline:none;

    text-align:center;

    font-size:19px;

    font-weight:600;

    padding:2px 5px;

    margin:0 2px;

}


/* =========================================================
   INPUT FOCUS
========================================================= */

.proportion-input:focus{

    border-bottom:2px solid #7b2cff;

}


/* =========================================================
   CORRECT
========================================================= */

.proportion-correct{

    background:#d4edda !important;

    border-radius:5px;

}


/* =========================================================
   WRONG
========================================================= */

.proportion-wrong{

    background:#f8d7da !important;

    border-radius:5px;

}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px){

    .proportion-card{

        padding:18px;

    }


    .proportion-row{

        font-size:17px;

        padding:12px;

        gap:6px;

        overflow-x:auto;

        flex-wrap:nowrap;

    }


    .proportion-input{

        width:58px;

        min-width:58px;

        height:31px;

        font-size:17px;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media(max-width:480px){

    .proportion-card{

        padding:14px;

    }


    .proportion-row{

        font-size:16px;

        padding:10px;

    }


    .proportion-input{

        width:52px;

        min-width:52px;

        font-size:16px;

    }

}

</style>


<div class="proportion-card">

    <div class="proportion-list">


        <?php foreach ($items as $rowIndex => $item): ?>


            <?php

            /* =================================================
               GET PARTS
            ================================================= */

            $parts = $getParts($item);


            /* =================================================
               GET CORRECT ANSWERS
            ================================================= */

            $itemAnswers = [];


            /*
             * -------------------------------------------------
             * ONE BLANK
             * -------------------------------------------------
             */

            if ($singleBlank) {

                $itemAnswers = [];

            }


            /*
             * -------------------------------------------------
             * MULTIPLE BLANKS - ONE ROW
             * -------------------------------------------------
             */

            elseif ($singleRow) {

                /*
                 * If answers are stored inside item,
                 * use them first.
                 */

                if (
                    is_array($item)
                    && isset($item['answers'])
                    && is_array($item['answers'])
                ) {

                    $itemAnswers =
                        $getOneRowArray(
                            $item['answers']
                        );

                } else {

                    /*
                     * Use normalized correct answer.
                     *
                     * Handles:
                     *
                     * ["2","3","32","5","6"]
                     *
                     * OR:
                     *
                     * [["2","3","32","5","6"]]
                     */

                    $itemAnswers =
                        $oneRowCorrect;

                }

            }


            /*
             * -------------------------------------------------
             * MULTIPLE BLANKS - MULTIPLE ROWS
             * -------------------------------------------------
             */

            else {

                if (
                    is_array($item)
                    && isset($item['answers'])
                    && is_array($item['answers'])
                ) {

                    $itemAnswers =
                        $item['answers'];

                }

                elseif (
                    isset($correct[$rowIndex])
                    && is_array($correct[$rowIndex])
                ) {

                    $itemAnswers =
                        $correct[$rowIndex];

                }

            }


            /* =================================================
               GET STUDENT ANSWERS
            ================================================= */

            $itemStudent = [];


            /*
             * -------------------------------------------------
             * ONE BLANK
             * -------------------------------------------------
             */

            if ($singleBlank) {

                $itemStudent = [];

            }


            /*
             * -------------------------------------------------
             * MULTIPLE BLANKS - ONE ROW
             * -------------------------------------------------
             */

            elseif ($singleRow) {

                /*
                 * Normalize:
                 *
                 * ["2","3","32","5","6"]
                 *
                 * and:
                 *
                 * [["2","3","32","5","6"]]
                 *
                 * into:
                 *
                 * ["2","3","32","5","6"]
                 */

                $itemStudent =
                    $oneRowStudent;

            }


            /*
             * -------------------------------------------------
             * MULTIPLE ROWS
             * -------------------------------------------------
             */

            else {

                if (
                    isset($student[$rowIndex])
                    && is_array($student[$rowIndex])
                ) {

                    $itemStudent =
                        $student[$rowIndex];

                }

            }

            ?>


            <div class="proportion-row">


                <?php

                /*
                 * Blank index inside current row.
                 */

                $blankIndex = 0;


                foreach (
                    $parts
                    as $partIndex => $part
                ):


                    $part = (string)$part;


                    /* =================================================
                       SPLIT RATIO
                    ================================================= */

                    $ratioPieces = preg_split(
                        '/\s*:\s*/',
                        $part
                    );


                    if (
                        is_array($ratioPieces)
                        && count($ratioPieces) === 2
                    ):


                        $left =
                            trim($ratioPieces[0]);

                        $right =
                            trim($ratioPieces[1]);

                ?>


                        <span class="proportion-part">


                            <!-- =========================================
                                 LEFT SIDE
                            ========================================== -->

                            <?php if ($left === '___'): ?>


                                <?php

                                $answerKey =
                                    $blankIndex;


                                /*
                                 * STUDENT VALUE
                                 */

                                if ($singleBlank) {

                                    $studentValue =
                                        $singleStudentValue;

                                } else {

                                    $studentValue =
                                        $itemStudent[$answerKey] ?? '';

                                }


                                /*
                                 * CORRECT VALUE
                                 */

                                if ($singleBlank) {

                                    $correctValue =
                                        $singleCorrectValue;

                                } else {

                                    $correctValue =
                                        $itemAnswers[$answerKey] ?? '';

                                }


                                /*
                                 * RESULT CLASS
                                 */

                                $inputClass = '';


                                if (
                                    isset($is_result_page)
                                    && $studentValue !== ''
                                ) {

                                    $inputClass =
                                        (
                                            (string)$studentValue ===
                                            (string)$correctValue
                                        )

                                            ? 'proportion-correct'

                                            : 'proportion-wrong';

                                }


                                /*
                                 * INPUT NAME
                                 */

                                $answerName =
                                    $getAnswerName(
                                        (int)($q['id'] ?? 0),
                                        (int)$rowIndex,
                                        (int)$answerKey,
                                        $singleBlank,
                                        $singleRow
                                    );

                                ?>


                                <input
                                    type="text"
                                    class="proportion-input <?= $h($inputClass) ?>"
                                    name="<?= $h($answerName) ?>"
                                    value="<?= $h($studentValue) ?>"
                                    autocomplete="off"
                                    inputmode="numeric"
                                >


                                <?php

                                $blankIndex++;

                                ?>


                            <?php else: ?>


                                <?= $h($left) ?>


                            <?php endif; ?>


                            <!-- =========================================
                                 COLON
                            ========================================== -->

                            <span class="proportion-symbol">
                                :
                            </span>


                            <!-- =========================================
                                 RIGHT SIDE
                            ========================================== -->

                            <?php if ($right === '___'): ?>


                                <?php

                                $answerKey =
                                    $blankIndex;


                                /*
                                 * STUDENT VALUE
                                 */

                                if ($singleBlank) {

                                    $studentValue =
                                        $singleStudentValue;

                                } else {

                                    $studentValue =
                                        $itemStudent[$answerKey] ?? '';

                                }


                                /*
                                 * CORRECT VALUE
                                 */

                                if ($singleBlank) {

                                    $correctValue =
                                        $singleCorrectValue;

                                } else {

                                    $correctValue =
                                        $itemAnswers[$answerKey] ?? '';

                                }


                                /*
                                 * RESULT CLASS
                                 */

                                $inputClass = '';


                                if (
                                    isset($is_result_page)
                                    && $studentValue !== ''
                                ) {

                                    $inputClass =
                                        (
                                            (string)$studentValue ===
                                            (string)$correctValue
                                        )

                                            ? 'proportion-correct'

                                            : 'proportion-wrong';

                                }


                                /*
                                 * INPUT NAME
                                 */

                                $answerName =
                                    $getAnswerName(
                                        (int)($q['id'] ?? 0),
                                        (int)$rowIndex,
                                        (int)$answerKey,
                                        $singleBlank,
                                        $singleRow
                                    );

                                ?>


                                <input
                                    type="text"
                                    class="proportion-input <?= $h($inputClass) ?>"
                                    name="<?= $h($answerName) ?>"
                                    value="<?= $h($studentValue) ?>"
                                    autocomplete="off"
                                    inputmode="numeric"
                                >


                                <?php

                                $blankIndex++;

                                ?>


                            <?php else: ?>


                                <?= $h($right) ?>


                            <?php endif; ?>


                        </span>


                        <!-- =========================================
                             EQUAL SIGN
                        ========================================== -->

                        <?php if (
                            $partIndex <
                            count($parts) - 1
                        ): ?>


                            <span class="proportion-symbol">
                                =
                            </span>


                        <?php endif; ?>


                    <?php else: ?>


                        <?php

                        /* =================================================
                           FALLBACK
                        ================================================= */

                        if (
                            strpos(
                                $part,
                                '___'
                            ) !== false
                        ):


                            $segments =
                                explode(
                                    '___',
                                    $part
                                );


                            foreach (
                                $segments
                                as $segIndex => $segment
                            ):


                                if (
                                    $segment !== ''
                                ) {

                                    echo $h(
                                        $segment
                                    );

                                }


                                if (
                                    $segIndex <
                                    count($segments) - 1
                                ) {

                                    $answerKey =
                                        $blankIndex;


                                    /*
                                     * STUDENT VALUE
                                     */

                                    if ($singleBlank) {

                                        $studentValue =
                                            $singleStudentValue;

                                    } else {

                                        $studentValue =
                                            $itemStudent[$answerKey] ?? '';

                                    }


                                    /*
                                     * CORRECT VALUE
                                     */

                                    if ($singleBlank) {

                                        $correctValue =
                                            $singleCorrectValue;

                                    } else {

                                        $correctValue =
                                            $itemAnswers[$answerKey] ?? '';

                                    }


                                    /*
                                     * RESULT CLASS
                                     */

                                    $inputClass = '';


                                    if (
                                        isset($is_result_page)
                                        && $studentValue !== ''
                                    ) {

                                        $inputClass =
                                            (
                                                (string)$studentValue ===
                                                (string)$correctValue
                                            )

                                                ? 'proportion-correct'

                                                : 'proportion-wrong';

                                    }


                                    /*
                                     * INPUT NAME
                                     */

                                    $answerName =
                                        $getAnswerName(
                                            (int)($q['id'] ?? 0),
                                            (int)$rowIndex,
                                            (int)$answerKey,
                                            $singleBlank,
                                            $singleRow
                                        );

                                    ?>


                                    <input
                                        type="text"
                                        class="proportion-input <?= $h($inputClass) ?>"
                                        name="<?= $h($answerName) ?>"
                                        value="<?= $h($studentValue) ?>"
                                        autocomplete="off"
                                        inputmode="numeric"
                                    >


                                    <?php

                                    $blankIndex++;

                                }


                            endforeach;


                        else:


                            echo $h($part);


                        endif;

                        ?>


                        <?php if (
                            $partIndex <
                            count($parts) - 1
                        ): ?>


                            <span class="proportion-symbol">
                                =
                            </span>


                        <?php endif; ?>


                    <?php endif; ?>


                <?php endforeach; ?>


            </div>


        <?php endforeach; ?>


    </div>

</div>