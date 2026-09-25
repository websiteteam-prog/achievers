<?php

$q = $q ?? [];

$id = (int)($q['id'] ?? 0);

$payload = json_decode(
    $q['question_payload'] ?? '{}',
    true
) ?: [];

$area = $payload['area'] ?? '';

$image = $q['question_image'] ?? '';

?>
<?php if ($index == 0): ?>

<div class="square-header">

    <div></div>

    <div class="header-square">
        Squares:
    </div>

    <div class="header-area">
        Area of square
    </div>

    <div class="header-side">
        side length ( in cm )
    </div>

</div>

<?php endif; ?>
<style>

.side-row{
    display:grid;
    grid-template-columns:60px 220px 250px 250px;
    align-items:center;
    margin-bottom:45px;
}

.side-no{
    font-size:24px;
}

.side-img img{
    width:120px;
    height:auto;
    margin-top:25px;
    margin-left: 54px;
}

.side-area{
    text-align:center;
    font-size:32px;
}

.side-answer{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:24px;
    justify-content:center;
}

.side-input{
    width:90px;
    border:none;
    border-bottom:2px solid #000;
    background:transparent;
    text-align:center;
    font-size:24px;
    outline:none;
}

.square-header{
    display:grid;
    grid-template-columns:60px 220px 250px 250px;
    align-items:center;
    margin:30px 0 40px;
    font-weight:700;
    font-size:18px;
}

.header-square,
.header-area,
.header-side{
    text-align:center;
}

@media(max-width:768px){

    .side-row{
        grid-template-columns:1fr;
        gap:15px;
        text-align:center;
    }

    .side-answer{
        justify-content:center;
    }
}

</style>

<div class="side-row">

    <div class="side-no">
        <?= ($index + 1) ?>)
    </div>

    <div class="side-img">
        <img src="<?= htmlspecialchars($image) ?>" alt="">
    </div>

    <div class="side-area">
        <?= $area ?>cm<sup>2</sup>
    </div>

    <div class="side-answer">

        <input
            type="text"
            class="side-input"
            name="answer[<?= $id ?>]"
            autocomplete="off"
        >

        <span>cm</span>

    </div>

</div>