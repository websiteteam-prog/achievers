<?php
declare(strict_types=1);
$q = $q ?? [];
$id = (int)($q['id'] ?? 0);
$payload = json_decode($q['question_payload'] ?? '{}', true) ?: [];
$number    = $payload['number'] ?? '';
$underline = $payload['underline'] ?? '';
$rounded   = $payload['rounded'] ?? '';
$displayNumber = htmlspecialchars($number, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
if($underline !== ''){
    $displayNumber = preg_replace(
        '/' . preg_quote($underline,'/') . '/',
        '<span class="round-highlight">$0</span>',
        $displayNumber,
        1
    );
}
?>
<style>
.round-col{
    margin-bottom:18px;
}
.round-row{
    display:flex;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
}
.round-number{
    font-size:20px;
    font-weight:700;
    color:#111;
    min-width:28px;
}
.round-card{
    background:#fff;
    border:2px solid #222;
    border-radius:10px;
    height:48px;
    padding:0 16px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    font-weight:700;
    box-shadow:0 3px 0 #d5d5d5;
    white-space:nowrap;
}
.round-highlight{
    color:#ef3d34;
    text-decoration:underline;
    text-decoration-thickness:3px;
    text-underline-offset:3px;
}
.round-arrow{
    margin:0 10px;
    font-size:24px;
    font-weight:bold;
}
.round-select{
    width:160px;
    height:48px;
    border:2px solid #111;
    border-radius:8px;
    font-size:16px;
    font-weight:600;
    text-align:center;
    outline:none;
}
.round-select:focus{
    border-color:#0d6efd;
}
@media(max-width:768px){
.round-number{
    font-size:16px;
}
.round-card{
    font-size:16px;
    height:42px;
}
.round-arrow{
    font-size:20px;
}
.round-select{
    width:130px;
    height:38px;
    font-size:14px;
}
}
</style>
<div class="col-lg-6 col-md-6 col-12 round-col">
    <div class="round-row">
        <span class="round-number"><?= ($index + 1) ?>)</span>
        <div class="round-card">
            <?= $displayNumber ?>
            <span class="round-arrow">→</span>
            <?= htmlspecialchars($rounded) ?>
        </div>
        <select
            class="round-select"
            name="answer[<?= $id ?>]">
            <option value="">Select</option>
            <option value="correct">✓ Correct</option>
            <option value="wrong">✗ Wrong</option>
        </select>
    </div>
</div>