<?php
declare(strict_types=1);

// ==========================================================
// GENERIC "ICON COMPARE" FAMILY TEMPLATE (multi-mode)
// One file, multiple "mode" values inside question_payload.
// Add new modes below as new question styles are needed —
// don't create a new template file for every variant.
//
// Supported modes:
//   "icon_compare" (default, backward-compatible with old
//                    rows that have no "mode" key at all,
//                    including legacy item1/item2 format)
//     -> N icon groups + M text answer blanks (words/ratio/fraction)
//
//   "part_to_part"
//     -> "The ratio of [icon/label] to [icon/label] = ___"
// ==========================================================

$q     = $q     ?? [];
$char  = $char  ?? 'a';
$index = $index ?? null;

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$text_template = (string)($q['question_text'] ?? '');
$payload_raw   = (string)($q['question_payload'] ?? '{}');
$data = json_decode($payload_raw, true);
if (!is_array($data)) { $data = []; }

$qid = (int)($q['id'] ?? 0);

// Default mode = icon_compare, so OLD rows (no "mode" key,
// just item1/item2) keep working without any DB change.
$mode = $data['mode'] ?? 'icon_compare';

// Question number: prefer numeric $index if the parent loop passes it,
// otherwise fall back to $char (matches your other templates' behaviour)
$number_display = ($index !== null) ? ((int)$index + 1) . ')' : $h($char) . '.';
?>

<style>
.icmp-question {
    width: 100%;
    box-sizing: border-box;

    background: #fff;
    border: 1px solid #e3e3e3;
    border-radius: 12px;

    padding: 22px 28px;
    margin: 20px 0 20px 0;

    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.icmp-heading {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 14px;
}

.icmp-number {
    font-size: 18px;
    font-weight: 700;
    color: #1F669C;
}

.icmp-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

@media (max-width: 768px) {
    .icmp-question { padding: 16px; }
}
</style>

<?php if ($mode === 'icon_compare'): ?>

    <?php
    // --- Build items array (dynamic, with legacy fallback) ---
    $items = [];
    if (!empty($data['items']) && is_array($data['items'])) {
        $items = $data['items'];
    } else {
        // legacy item1 / item2 fallback
        if (!empty($data['item1'])) { $items[] = $data['item1']; }
        if (!empty($data['item2'])) { $items[] = $data['item2']; }
    }

    // --- Build answer fields array (dynamic, with default fallback) ---
    $fields = [];
    if (!empty($data['fields']) && is_array($data['fields'])) {
        $fields = $data['fields'];
    } else {
        $fields = [
            ['key' => 'words',    'label' => 'Words',    'placeholder' => ''],
            ['key' => 'ratio',    'label' => 'Ratio',    'placeholder' => ''],
            ['key' => 'fraction', 'label' => 'Fraction', 'placeholder' => ''],
        ];
    }
    ?>

    <style>
    .icmp-content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 30px;
        flex-wrap: wrap;
    }

    .icmp-icons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        flex: 1 1 300px;
        max-width: 380px;
        margin-top: 20px;
    }

    .icmp-icons span {
        font-size: 26px;
        line-height: 1;
    }

    .icmp-answers {
        flex: 1 1 220px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 200px;
        margin-bottom: 30px;
    }

    .icmp-answer-line {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
    }

    .icmp-answer-line label {
        font-weight: bold;
        color: #333;
        min-width: 75px;
    }

    .icmp-input {
        flex: 1;
        min-width: 0;
        border: none;
        border-bottom: 2px solid #1f669c;
        background: transparent;
        outline: none;
        padding: 3px 4px;
        font-size: 15px;
        transition: border-color 0.3s;
    }

    .icmp-input:focus { border-bottom-color: #007bff; }

    @media (max-width: 768px) {
        .icmp-content { gap: 18px; }
        .icmp-icons { max-width: 100%; }
        .icmp-icons span { font-size: 22px; }
        .icmp-answer-line { font-size: 15px; }
    }
    </style>

    <div class="icmp-question">

        <div class="icmp-heading">
            <span class="icmp-number"><?= $number_display ?></span>
            <span class="icmp-title"><?= $h($text_template) ?></span>
        </div>

        <div class="icmp-content">

            <!-- ICON GROUPS (dynamic count of items) -->
            <div class="icmp-icons">
                <?php foreach ($items as $item):
                    $icon  = $h($item['icon']  ?? '❔');
                    $count = (int)($item['count'] ?? 0);
                    for ($i = 0; $i < $count; $i++) {
                        echo '<span>' . $icon . '</span>';
                    }
                endforeach; ?>
            </div>

            <!-- ANSWER BLANKS (dynamic count of fields) -->
            <div class="icmp-answers">
                <?php foreach ($fields as $f):
                    $key         = $h($f['key'] ?? '');
                    $label       = $h($f['label'] ?? ucfirst($key));
                    $placeholder = $h($f['placeholder'] ?? '');
                    $inputId     = 'icmp_' . $key . '_' . $qid;
                ?>
                    <div class="icmp-answer-line">
                        <label for="<?= $inputId ?>"><?= $label ?> :</label>
                        <input
                            type="text"
                            id="<?= $inputId ?>"
                            class="icmp-input"
                            name="answer[<?= $qid ?>][<?= $key ?>]"
                            placeholder="<?= $placeholder ?>"
                            autocomplete="off"
                        >
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

    </div>

<?php endif; ?>

<?php if ($mode === 'part_to_part'): ?>

    <?php
    $items    = $data['items']    ?? [];
    $ratio_of = $data['ratio_of'] ?? ['label' => '', 'icon' => null];
    $ratio_to = $data['ratio_to'] ?? ['label' => '', 'icon' => null];
    ?>

    <style>
    .ptp-icons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 14px 0 22px 0;
    }

    .ptp-icons span {
        font-size: 30px;
        line-height: 1;
    }

    .ptp-sentence {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 17px;
        font-weight: 700;
        color: #222;
    }

    .ptp-sentence .ptp-icon-inline {
        font-size: 24px;
        vertical-align: middle;
    }

    .ptp-input {
        width: 160px;
        min-width: 0;
        border: none;
        border-bottom: 2px solid #1f669c;
        background: transparent;
        outline: none;
        padding: 3px 6px;
        font-size: 16px;
        font-weight: 600;
        transition: border-color 0.3s;
    }

    .ptp-input:focus { border-bottom-color: #007bff; }

    @media (max-width: 768px) {
        .ptp-icons span { font-size: 24px; }
        .ptp-sentence { font-size: 15px; }
        .ptp-input { width: 120px; }
    }
    </style>

    <div class="icmp-question">

        <?php if ($text_template): ?>
            <div class="icmp-heading">
                <span class="icmp-number"><?= $number_display ?></span>
                <span class="icmp-title" style="color:#cc0000;"><?= $h($text_template) ?></span>
            </div>
        <?php endif; ?>

        <div class="ptp-icons">
            <?php foreach ($items as $item):
                $icon  = $h($item['icon'] ?? '❔');
                $count = (int)($item['count'] ?? 0);
                for ($i = 0; $i < $count; $i++) {
                    echo '<span>' . $icon . '</span>';
                }
            endforeach; ?>
        </div>

        <div class="ptp-sentence">
            <span>The ratio of</span>

            <?php if (!empty($ratio_of['icon'])): ?>
                <span class="ptp-icon-inline"><?= $h($ratio_of['icon']) ?></span>
            <?php else: ?>
                <span><?= $h($ratio_of['label'] ?? '') ?></span>
            <?php endif; ?>

            <span>to</span>

            <?php if (!empty($ratio_to['icon'])): ?>
                <span class="ptp-icon-inline"><?= $h($ratio_to['icon']) ?></span>
            <?php else: ?>
                <span><?= $h($ratio_to['label'] ?? '') ?></span>
            <?php endif; ?>

            <span>=</span>

            <input
                type="text"
                class="ptp-input"
                name="answer[<?= $qid ?>][ratio]"
                autocomplete="off"
                placeholder="e.g. 6:1"
            >
        </div>

    </div>

<?php endif; ?>

<?php if ($mode === 'part_to_part_count'): ?>

    <?php
    $items        = $data['items'] ?? [];
    $blank1_label = $data['blank1_label'] ?? '';
    $blank2_label = $data['blank2_label'] ?? '';
    ?>

    <style>
    .ptpc-icons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 14px 0 22px 0;
    }

    .ptpc-icons span {
        font-size: 30px;
        line-height: 1;
    }

    .ptpc-sentence {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 17px;
        font-weight: 600;
        color: #222;
    }

    .ptpc-input {
        width: 70px;
        border: none;
        border-bottom: 2px solid #1f669c;
        background: transparent;
        outline: none;
        padding: 3px 4px;
        font-size: 16px;
        text-align: center;
        transition: border-color 0.3s;
    }

    .ptpc-input:focus { border-bottom-color: #007bff; }

    @media (max-width: 768px) {
        .ptpc-icons span { font-size: 24px; }
        .ptpc-sentence { font-size: 15px; }
    }
    </style>

    <div class="icmp-question">

        <div class="icmp-heading">
            <span class="icmp-number"><?= $number_display ?></span>
            <span class="icmp-title"><?= $h($text_template) ?></span>
        </div>

        <div class="ptpc-icons">
            <?php foreach ($items as $item):
                $icon  = $h($item['icon'] ?? '❔');
                $count = (int)($item['count'] ?? 0);
                for ($i = 0; $i < $count; $i++) {
                    echo '<span>' . $icon . '</span>';
                }
            endforeach; ?>
        </div>

        <div class="ptpc-sentence">

            <span>There are</span>

            <input
                type="text"
                class="ptpc-input"
                name="answer[<?= $qid ?>][part1]"
                autocomplete="off"
            >

            <span><?= $h($blank1_label) ?> and</span>

            <input
                type="text"
                class="ptpc-input"
                name="answer[<?= $qid ?>][part2]"
                autocomplete="off"
            >

            <span><?= $h($blank2_label) ?>.</span>

        </div>

    </div>

<?php endif; ?>

<?php if ($mode === 'share_ratio'): ?>

    <?php
    $person1 = $data['person1'] ?? ['name' => '', 'color' => '#dbe0fb'];
    $person2 = $data['person2'] ?? ['name' => '', 'color' => '#f3c3a8'];
    ?>

    <style>
    .sr-people {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
        margin: 18px 0 22px 0;
        align-items: flex-start;
    }

    .sr-person-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .sr-person-name {
        font-weight: 700;
        color: #333;
        font-size: 16px;
    }

    .sr-draw-box {
        width: 110px;
        height: 90px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .sr-totals {
        display: flex;
        flex-direction: column;
        gap: 10px;
        justify-content: center;
        margin-top: 6px;
    }

    .sr-total-line {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        font-weight: 700;
        color: #333;
    }

    .sr-input {
        width: 80px;
        border: none;
        border-bottom: 2px solid #1f669c;
        background: transparent;
        outline: none;
        padding: 3px 4px;
        font-size: 15px;
        text-align: center;
        font-weight: 600;
    }

    .sr-input:focus { border-bottom-color: #007bff; }

    @media (max-width: 768px) {
        .sr-people { gap: 24px; }
        .sr-draw-box { width: 85px; height: 75px; }
    }
    </style>

    <div class="icmp-question">

        <div class="icmp-heading">
            <span class="icmp-number"><?= $number_display ?></span>
            <span class="icmp-title"><?= $h($text_template) ?></span>
        </div>

        <div class="sr-people">

            <div class="sr-person-block">
                <span class="sr-person-name"><?= $h($person1['name'] ?? '') ?></span>
                <div class="sr-draw-box" style="background: <?= $h($person1['color'] ?? '#dbe0fb') ?>;"></div>
            </div>

            <div class="sr-person-block">
                <span class="sr-person-name"><?= $h($person2['name'] ?? '') ?></span>
                <div class="sr-draw-box" style="background: <?= $h($person2['color'] ?? '#f3c3a8') ?>;"></div>
            </div>

            <div class="sr-totals">

                <div class="sr-total-line">
                    <span>Total for <?= $h($person1['name'] ?? '') ?> :</span>
                    <input
                        type="text"
                        class="sr-input"
                        name="answer[<?= $qid ?>][total1]"
                        autocomplete="off"
                    >
                </div>

                <div class="sr-total-line">
                    <span>Total for <?= $h($person2['name'] ?? '') ?> :</span>
                    <input
                        type="text"
                        class="sr-input"
                        name="answer[<?= $qid ?>][total2]"
                        autocomplete="off"
                    >
                </div>

            </div>

        </div>

    </div>

<?php endif; ?>

<?php if ($mode === 'writing_ratio'): ?>

    <?php
    $quantity  = (float)($data['quantity'] ?? 0);
    $cost      = (float)($data['cost'] ?? 0);
    $item      = $data['item'] ?? '';
    ?>

    <style>
    .wr-content {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 35px;
        margin-top: 18px;
        flex-wrap: wrap;
    }

    .wr-problem-box {
        background: #f3c3b7;
        border-radius: 25px;
        padding: 22px;
        width: 280px;
        min-height: 130px;
        box-sizing: border-box;
    }

    .wr-problem-text {
        font-size: 17px;
        font-weight: 700;
        line-height: 1.5;
        color: #8b4f2f;
        margin-bottom: 18px;
    }

    .wr-answer-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-width: 260px;
        margin-right: 122px;
        margin-top: 20px;
    }

    .wr-answer-line {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 17px;
        font-weight: 600;
    }

    .wr-answer-line label {
        min-width: 85px;
        font-weight: 700;
        color: #333;
    }

    .wr-input {
        width: 180px;
        border: none;
        border-bottom: 2px solid #1f669c;
        background: transparent;
        outline: none;
        padding: 4px 6px;
        font-size: 16px;
        text-align: center;
    }

    .wr-input:focus {
        border-bottom: 3px solid #007bff;
    }

    @media(max-width:768px) {

        .wr-content {
            flex-direction: column;
            gap: 20px;
        }

        .wr-problem-box {
            width: 100%;
            max-width: 300px;
        }

        .wr-answer-group {
            width: 100%;
        }

        .wr-input {
            width: 160px;
        }
    }
    </style>

    <div class="icmp-question">

        <div class="icmp-heading">
            <span class="icmp-number">
                <?= $number_display ?>
            </span>

            <span class="icmp-title">
                <?= $h($text_template) ?>
            </span>
        </div>

        <div class="wr-content">

            <!-- PROBLEM -->
            <div class="wr-problem-box">

                <div class="wr-problem-text">
                    <?= $h($quantity) ?>
                    <?= $h($item) ?>
                    cost $<?= $h($cost) ?>
                </div>

                <div style="font-weight:700;">
                    ratio =
                </div>

                <div style="font-weight:700; margin-top:8px;">
                    unit rate =
                </div>

            </div>

            <!-- ANSWERS -->
            <div class="wr-answer-group">

                <div class="wr-answer-line">

                    <label>
                        Ratio =
                    </label>

                    <input
                        type="text"
                        class="wr-input"
                        name="answer[<?= $qid ?>][ratio]"
                        autocomplete="off"
                    >

                </div>

                <div class="wr-answer-line">

                    <label>
                        Unit rate =
                    </label>

                    <input
                        type="text"
                        class="wr-input"
                        name="answer[<?= $qid ?>][unit_rate]"
                        autocomplete="off"
                    >

                </div>

            </div>

        </div>

    </div>

<?php endif; ?>

<?php $char++; ?>