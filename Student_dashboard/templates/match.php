<?php
$data = json_decode($q['question_payload'], true);

$left = $data['left'] ?? [];
$right = $data['right'] ?? [];

shuffle($right);
?>

<style>
.match-wrapper {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.match-title {
    font-weight: 600;
    font-size: 18px;
    margin-bottom: 20px;
}

/* 2 column layout */
.match-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

/* left side */
.match-left div {
    margin: 10px 0;
    font-weight: 600;
}

/* right side */
.match-right div {
    margin: 10px 0;
    color: #444;
}

/* dropdown row */
.match-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 10px 0;
}

.match-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* mobile */
@media(max-width:600px){
    .match-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="match-wrapper">

    <div class="match-title">
        <?= htmlspecialchars($q['question_text']) ?>
    </div>

    <div class="match-grid">

        <!-- LEFT SIDE -->
        <div class="match-left">
            <?php foreach ($left as $index => $item) { ?>
                <div>
                    <?= chr(97+$index) ?>) <?= htmlspecialchars($item) ?>
                </div>
            <?php } ?>
        </div>

        <!-- RIGHT SIDE (options list) -->
        <div class="match-right">
            <?php foreach ($right as $r) { ?>
                <div><?= htmlspecialchars($r) ?></div>
            <?php } ?>
        </div>

    </div>

    <hr>

    <!-- SELECT AREA -->
    <?php foreach ($left as $index => $item) { ?>
        <div class="match-row">

            <span>
                <?= chr(97+$index) ?>) <?= htmlspecialchars($item) ?>
            </span>

            <select 
                class="match-select"
                name="answer[<?= $q['id'] ?>][<?= htmlspecialchars($item) ?>]"
            >
                <option value="">Select</option>
                <?php foreach ($right as $r) { ?>
                    <option value="<?= htmlspecialchars($r) ?>">
                        <?= htmlspecialchars($r) ?>
                    </option>
                <?php } ?>
            </select>

        </div>
    <?php } ?>

</div>