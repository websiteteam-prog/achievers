<?php
declare(strict_types=1);

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];

$number    = $payload['number'] ?? '';
$underline = $payload['underline'] ?? '';

$displayNumber = htmlspecialchars($number, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

if ($underline !== '') {

    $displayNumber = preg_replace(
        '/' . preg_quote($underline, '/') . '/',
        '<span class="pv-highlight">$0</span>',
        $displayNumber,
        1
    );

}
?>

<style>

.pv-col{
    margin-bottom:16px;
}

/* make the two boxes of a pair TOUCH:
   remove the column gutter on their INNER sides, keep it on the outer sides */
.pv-col:nth-child(odd)  { padding-right:0; }   /* left box  -> right side touches partner */
.pv-col:nth-child(even) { padding-left:0;  }   /* right box -> left side touches partner  */

/* card fills the full column now (no more 88%) */
.pv-card{
    width:100%;
    display:flex;
    flex-direction:column;
    gap:5px;
}

/* ---- NUMBER BOX (top) ---- */
.pv-top{

    background:#fff;
    border:2px solid #111;
    border-radius:10px;

    height:42px;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:16px;
    font-weight:700;
    color:#111;

    box-shadow:0 4px 0 #e0a41a;
}

.pv-highlight{

    color:#ef3d34;

    text-decoration:underline;
    text-decoration-thickness:3px;
    text-underline-offset:3px;
}

/* ---- ANSWER BOX (bottom, empty) ---- */
.pv-bottom{

    background:#fff;
    border:2px solid #111;
    border-radius:10px;

    height:40px;

    display:flex;
    align-items:center;
    justify-content:center;

    padding:0 8px;

    box-shadow:0 4px 0 #e0a41a;

    transition:.15s;
}

.pv-bottom:focus-within{

    transform:translateY(-1px);
    box-shadow:0 5px 0 #e0a41a;
}

.pv-input{

    width:100%;
    height:100%;

    border:none;
    outline:none;

    background:transparent;

    text-align:center;

    font-size:15px;
    font-weight:600;
    color:#111;
}

.pv-input::placeholder{ color:#bcbcbc; }

/* Tablet */
@media(max-width:992px){
    .pv-top{ height:40px; font-size:15px; }
    .pv-bottom{ height:38px; }
    .pv-input{ font-size:14px; }
}

/* Mobile (2 per row -> still one pair per row, touching) */
@media(max-width:768px){
    .pv-col{ margin-bottom:12px; }
    .pv-top{ height:38px; font-size:14px; }
    .pv-bottom{ height:36px; }
    .pv-input{ font-size:13px; }
}

/* Small Mobile */
@media(max-width:480px){
    .pv-top{ height:36px; font-size:13px; border-radius:9px; }
    .pv-bottom{ height:34px; border-radius:9px; }
    .pv-input{ font-size:12px; }
}

</style>

<div class="col-lg-3 col-md-3 col-6 pv-col">

    <div class="pv-card">

        <div class="pv-top">

            <?= $displayNumber ?>

        </div>

        <div class="pv-bottom">

            <input
                type="text"
                class="pv-input"
                name="answer[<?= $id ?>]"
                autocomplete="off">

        </div>

    </div>

</div>