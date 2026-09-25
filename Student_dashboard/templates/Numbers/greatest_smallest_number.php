<?php
declare(strict_types=1);

$gsnH = fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');

$q = $q ?? [];

$gsnId = (int)($q['id'] ?? 0);

$gsnPayload = json_decode($q['question_payload'] ?? '{}', true) ?: [];

$gsnNumber = $gsnPayload['number'] ?? '';

/* ---- question letter (a, b, c ...) from the loop index ---- */
$gsnLabelIndex = isset($index) ? (int)$index : (isset($num) ? ((int)$num - 1) : 0);
$gsnLetter = ($gsnLabelIndex >= 0 && $gsnLabelIndex < 26)
    ? chr(ord('a') + $gsnLabelIndex)
    : (string)($gsnLabelIndex + 1);

$gsnIsResultPage = isset($latest_time) && $latest_time !== null;

$gsnStudentGreatest = '';
$gsnStudentSmallest = '';

/* On the result page, just show back what the student typed (no checking) */
if ($gsnIsResultPage) {

    $gsnStmt = $conn->prepare("
        SELECT student_answer
        FROM student_answers
        WHERE student_id=?
        AND quiz_id=?
        AND question_id=?
        AND created_at=?
        LIMIT 1
    ");

    $gsnStmt->bind_param(
        "iiis",
        $student_id,
        $topic_id,
        $gsnId,
        $latest_time
    );

    $gsnStmt->execute();

    $gsnRes = $gsnStmt->get_result()->fetch_assoc();

    if ($gsnRes) {

        $gsnStudentAns = json_decode($gsnRes['student_answer'], true);

        if (is_array($gsnStudentAns)) {

            $gsnStudentGreatest = $gsnStudentAns['greatest'] ?? '';

            $gsnStudentSmallest = $gsnStudentAns['smallest'] ?? '';

        }

    }

    $gsnStmt->close();

}
?>

<style>

.gsn-col{
    margin-bottom:30px;
}

/* one worksheet row: label on left, two boxes on right */
.gsn-item{
    display:flex;
    align-items:center;
    gap:14px;
    padding:8px 4px;
}

/* number + letter on the left */
.gsn-qlabel{
    flex:0 0 128px;
    max-width:128px;
    font-size:17px;
    font-weight:700;
    color:#111;
    white-space:nowrap;
}

/* the two boxes */
.gsn-boxes{
    flex:1;
    display:flex;
    gap:14px;
}

.gsn-box{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:4px;
}

.gsn-cap{
    text-align:center;
    font-size:13px;
    font-weight:700;
    color:#333;
}

.gsn-field{
    background:#fff;
    border:2px solid #111;
    border-radius:16px;

    height:44px;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:0 10px;

    box-shadow:0 4px 0 #e0a41a;

    transition:.15s;
}

.gsn-field:focus-within{
    transform:translateY(-1px);
    box-shadow:0 5px 0 #e0a41a;
}

.gsn-input{
    width:100%;
    height:100%;

    border:none;
    outline:none;
    background:transparent;

    text-align:center;

    font-size:16px;
    font-weight:600;
    color:#111;
}

.gsn-input::placeholder{ color:#c4c4c4; }
.gsn-input:disabled{ color:#111; }

/* Tablet */
@media(max-width:992px){
    .gsn-qlabel{ flex-basis:110px; max-width:110px; font-size:16px; }
    .gsn-field{ height:42px; }
    .gsn-input{ font-size:15px; }
}

/* Mobile */
@media(max-width:768px){
    .gsn-item{ gap:10px; }
    .gsn-qlabel{ flex-basis:92px; max-width:92px; font-size:14px; }
    .gsn-boxes{ gap:10px; }
    .gsn-cap{ font-size:12px; }
    .gsn-field{ height:40px; border-radius:14px; }
    .gsn-input{ font-size:14px; }
}

/* Small mobile */
@media(max-width:480px){
    .gsn-qlabel{ flex-basis:80px; max-width:80px; font-size:13px; }
    .gsn-field{ height:38px; }
    .gsn-input{ font-size:13px; }
}

</style>

<div class="col-lg-6 col-md-6 col-12 gsn-col">

    <div class="gsn-item">

        <div class="gsn-qlabel">
            <?= $gsnH($gsnLetter) ?>) <?= $gsnH($gsnNumber) ?>
        </div>

        <div class="gsn-boxes">

            <!-- Greatest -->
            <div class="gsn-box">

                <div class="gsn-cap">Greatest</div>

                <div class="gsn-field">
                    <input
                        type="text"
                        class="gsn-input"
                        name="answer[<?= $gsnId ?>][greatest]"
                        value="<?= $gsnH($gsnStudentGreatest) ?>"
                        autocomplete="off"
                        <?= $gsnIsResultPage ? 'disabled' : '' ?>>
                </div>

            </div>

            <!-- Smallest -->
            <div class="gsn-box">

                <div class="gsn-cap">Smallest</div>

                <div class="gsn-field">
                    <input
                        type="text"
                        class="gsn-input"
                        name="answer[<?= $gsnId ?>][smallest]"
                        value="<?= $gsnH($gsnStudentSmallest) ?>"
                        autocomplete="off"
                        <?= $gsnIsResultPage ? 'disabled' : '' ?>>
                </div>

            </div>

        </div>

    </div>

</div>