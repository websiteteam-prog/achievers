<?php
declare(strict_types=1);
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$q = $q ?? [];
$questionImage = trim((string)($q['question_image'] ?? ''));
$payloadRaw = (string)($q['question_payload'] ?? '[]');
$payload = json_decode($payloadRaw, true) ?: [];
$real_question_id = (int)$q['id'];  
$renderType = $payload['type'] ?? 'unknown';
$items = $payload['items'] ?? [];
$instruction = $payload['instruction'] ?? '';
$treeImage = $payload['tree_image'] ?? '';
$makeUrl = fn($path) => $path && !preg_match('~^https?://~i', $path)
    ? rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/') . '/' . ltrim($path, '/')
    : $path;
$imgUrl = $makeUrl($questionImage);
$treeUrl = $makeUrl($treeImage);
$optBadge = fn($val) => preg_match('/^[A-D]$/', (string)$val) ? '<span class="opt-badge">'.$val.'</span>' : '';
?>
<style>
.probability-wrap { margin: 20px 0; padding: 0 15px; font-family: 'Segoe UI', Tahoma, sans-serif; max-width: 100%; }
.prob-title { font-weight: 600; font-size: 18px; margin: 0 0 16px; color: #1a1a1a; }
.prob-img { display: block; max-width: 100%; margin: 0 auto 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;}
    /* --- Patterning card layout --- */
.pat-card {
    display: flex;
    align-items: center;
    gap: 28px;
    width: 100%;          /* full width */
    margin: 0 0 18px;     /* was: 0 auto 18px  → no more centering cap */
    padding: 24px 28px;
    background: #fff;
    border: 1px solid #e8e8ef;
    border-radius: 16px;
    box-shadow: 0 4px 14px rgba(20,20,50,.06);
}
.pat-media {
    position: relative;
    flex: 0 0 auto;
    padding: 14px 16px;
    background: #f7f8fc;
    border: 1px solid #eceef5;
    border-radius: 12px;
}
.pat-num {
    position: absolute;
    top: -13px; left: -13px;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    background: #1565d8; color: #fff;
    font-weight: 700; font-size: 15px;
    border-radius: 50%;
    box-shadow: 0 2px 6px rgba(21,101,216,.4);
}
.pat-img { display: block; max-width: 210px; height: auto; }
.pat-body { flex: 1 1 auto; min-width: 0; }
.pat-q { font-weight: 700; font-size: 17px; color: #1a1a2e; margin-bottom: 12px; }
.pat-opts { display: flex; flex-direction: column; gap: 10px; }
.pat-opt {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    border: 1.5px solid #e3e5ee;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px; color: #2a2a3a;
    transition: border-color .15s, background .15s;
}
.pat-opt:hover { border-color: #b9c6ff; background: #f5f8ff; }
.pat-opt input { accent-color: #1565d8; transform: scale(1.15); }
.pat-opt:has(input:checked) {
    border-color: #1565d8;
    background: #eef4ff;
    box-shadow: inset 0 0 0 1px #1565d8;
}
@media (max-width: 640px) {
    .pat-card { flex-direction: column; align-items: stretch; gap: 18px; }
    .pat-media { align-self: flex-start; }
    .pat-img { max-width: 100%; }
}
.prob-instruction { font-weight: 500; color: #d32f2f; margin-bottom: 8px; }
.prob-outcome-label { font-weight: 600; margin: 12px 0 6px; color: #1976d2; }
.prob-fill { display: inline-block; border-bottom: 2px solid #1976d2; width: 120px; margin: 0 6px; font-family: monospace; text-align: center; padding: 4px 0; }
.prob-options { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 10px; }
.prob-option { display: flex; align-items: center; gap: 8px; font-size: 16px; }
.prob-option input[type="radio"], .prob-option input[type="checkbox"] { transform: scale(1.2); }
.opt-badge { display: inline-block; min-width: 24px; height: 24px; line-height: 24px; text-align: center; border: 1px solid #ccc; border-radius: 6px; font-weight: 600; margin-right: 6px; }
.prob-tree { margin: 20px 0; text-align: center; }
.prob-outcome-row { display: flex; align-items: flex-start; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
.prob-img-box { flex: 0 0 180px; text-align: center; }
.prob-img-inline { max-width: 160px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.prob-outcome-side { flex: 1; min-width: 200px; }
.prob-inputs { margin-top: 8px; }
</style>

<div class="probability-wrap">
    <?php if (!empty($q['question_text'])): ?>
        <div class="prob-title"><?= $h($q['question_text']) ?></div>
    <?php endif; ?>
    <?php if ($instruction): ?>
        <div class="prob-instruction"><?= $h($instruction) ?></div>
    <?php endif; ?>

    <?php if ($renderType === 'fill_outcomes'): ?>
        <input type="hidden" name="answer[<?= $real_question_id ?>]" value="[]">
        <div class="prob-outcome-row">
            <?php if ($imgUrl): ?>
                <div class="prob-img-box">
                    <img src="<?= $h($imgUrl) ?>" alt="Visual" class="prob-img-inline" loading="lazy">
                </div>
            <?php endif; ?>
            <div class="prob-outcome-side">
                <div class="prob-outcome-label">POSSIBLE OUTCOMES:</div>
                <div class="prob-inputs">
                    <?php foreach ($items as $i => $item): ?>
                        <input type="text"
                               class="prob-fill fill-outcome-input"
                               data-qid="<?= $real_question_id ?>"
                               data-index="<?= $i ?>"
                               placeholder="_"
                               maxlength="30"
                               style="width:<?= (int)($item['width'] ?? 100) ?>px;">
                        <?php if ($i < count($items)-1): ?><span>,</span><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.fill-outcome-input[data-qid="<?= $real_question_id ?>"]');
            const hidden = document.querySelector('input[name="answer[<?= $real_question_id ?>]"]');
            function updateAnswer() {
                const values = Array.from(inputs)
                    .map(inp => inp.value.trim().toUpperCase())
                    .filter(v => v !== '');
                hidden.value = JSON.stringify(values.length > 0 ? values : []);
            }
            inputs.forEach(inp => {
                inp.addEventListener('input', updateAnswer);
                inp.addEventListener('blur', updateAnswer);
            });
            updateAnswer();
        });
        </script>

    <?php elseif ($renderType === 'probability_mc'): ?>
        <input type="hidden" name="answer[<?= $real_question_id ?>]" value="[]" id="prob-mc-hidden-<?= $real_question_id ?>">
        <div class="probability-wrap">
            <?php if ($instruction): ?>
                <div class="prob-instruction"><?= $h($instruction) ?></div>
            <?php endif; ?>
            <?php if ($imgUrl): ?>
                <div style="text-align:center; margin:20px 0;">
                    <img src="<?= $h($imgUrl) ?>" alt="Question Image" class="prob-img" loading="lazy">
                </div>
            <?php endif; ?>
            <div class="prob-outcome-row">
                <div class="prob-outcome-side" style="width:100%;">
                    <?php foreach ($items as $idx => $item):
                        $part = $item['part'] ?? 'q' . ($idx + 1);
                        $label = $item['label'] ?? ('Question ' . ($idx + 1));
                        $options = $item['options'] ?? [];
                    ?>
                        <div class="mb-4 p-3 border rounded bg-light">
                            <strong class="d-block mb-3"><?= $h($label) ?></strong>
                            <div class="prob-options">
                                <?php foreach ($options as $opt):
                                    $value = strtoupper(trim($opt['value'] ?? ''));
                                    $text = $opt['text'] ?? $value;
                                ?>
                                    <label class="prob-option">
                                        <input type="radio"
                                               name="mc_<?= $real_question_id ?>_<?= $part ?>"
                                               value="<?= $h($value) ?>"
                                               class="mc-radio"
                                               data-qid="<?= $real_question_id ?>"
                                               data-part="<?= $part ?>">
                                        <?= $optBadge($value) ?> <?= $h($text) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('.mc-radio[data-qid="<?= $real_question_id ?>"]');
            const hidden = document.getElementById('prob-mc-hidden-<?= $real_question_id ?>');
            function buildAnswer() {
                const answer = [];
                <?php foreach ($items as $idx => $item):
                    $part = $item['part'] ?? 'q' . ($idx + 1); ?>
                const selected_<?= $idx ?> = document.querySelector('input[name="mc_<?= $real_question_id ?>_<?= $part ?>"]:checked');
                answer.push(selected_<?= $idx ?> ? selected_<?= $idx ?>.value : "");
                <?php endforeach; ?>
                hidden.value = JSON.stringify(answer);
            }
            radios.forEach(radio => radio.addEventListener('change', buildAnswer));
            buildAnswer();
        });
        </script>

        <?php elseif ($renderType === 'Patterning'): ?>
        <?php foreach ($items as $idx => $item):
            $part    = $item['part'] ?? ('q'.($idx+1));
            $label   = $item['label'] ?? '';
            $options = $item['options'] ?? [];
            if (!$part) continue;
            $num = preg_replace('/\D+/', '', (string)$part);   // q1 -> 1
            if ($num === '') { $num = (string)($idx + 1); }
        ?>
            <div class="pat-card">
                <div class="pat-media">
                    <span class="pat-num"><?= $h($num) ?></span>
                    <?php if ($imgUrl): ?>
                        <img src="<?= $h($imgUrl) ?>" alt="Pattern <?= $h($num) ?>"
                             class="pat-img" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="pat-body">
                    <?php if ($label): ?><div class="pat-q"><?= $h($label) ?></div><?php endif; ?>
                    <div class="pat-opts">
                        <?php foreach ($options as $opt):
                            $val  = $opt['value'] ?? '';
                            $text = $opt['text'] ?? $val;
                        ?>
                            <label class="pat-opt">
                                <input type="radio"
                                       name="answer[<?= $real_question_id ?>_<?= $h($part) ?>]"
                                       value="<?= $h($val) ?>">
                                <?= $optBadge($val) ?>
                                <span><?= $h($text) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    <?php elseif ($renderType === 'probability_multiple_ch'): ?>
        <div class="prob-mc-grid">
            <?php foreach ($items as $idx => $item):
                $part = $item['part'] ?? ('q'.($idx+1));
                $label = $item['label'] ?? '';
                $options = $item['options'] ?? [];
                if (!$part) continue;
            ?>
                <div>
                    <?php if ($label): ?><strong><?= $h($label) ?></strong><?php endif; ?>
                    <div class="prob-options" style="flex-direction:column; gap:12px; margin-top:8px;">
                        <?php foreach ($options as $opt):
                            $val = $opt['value'] ?? '';
                            $text = $opt['text'] ?? $val;
                        ?>
                            <label class="prob-option" style="align-items:flex-start;">
                                <input type="checkbox" name="answer[<?= $real_question_id ?>_<?= $h($part) ?>][]" value="<?= $h($val) ?>">
                                <?= $optBadge($val) ?><?= $h($text) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($renderType === 'tree_diagram'): ?>
        <input type="hidden" name="answer[<?= $real_question_id ?>]" value="{}">
        <div class="prob-tree" style="text-align:center; margin:30px 0;">
            <?php if (!empty($payload['label'])): ?>
                <div class="prob-outcome-label"><?= $h($payload['label']) ?></div>
            <?php endif; ?>
            <svg width="380" height="280" viewBox="0 0 380 280" xmlns="http://www.w3.org/2000/svg">
                <text x="30" y="90" font-size="18" font-weight="bold">H</text>
                <line x1="55" y1="85" x2="120" y2="65" stroke="#1976d2" stroke-width="2" />
                <circle cx="140" cy="60" r="10" fill="none" stroke="#1976d2" stroke-width="2" />
                <line x1="55" y1="85" x2="120" y2="105" stroke="#1976d2" stroke-width="2" />
                <circle cx="140" cy="110" r="10" fill="none" stroke="#1976d2" stroke-width="2" />
                <rect x="170" y="45" width="60" height="30" rx="4" stroke="#d32f2f" fill="none"/>
                <foreignObject x="170" y="45" width="60" height="30">
                    <input type="text" class="tree-input" data-key="<?= $h($items[0]['part'] ?? 'h1') ?>" style="width:100%;height:100%;text-align:center;border:none;background:transparent;font-size:14px;font-weight:bold;">
                </foreignObject>
                <rect x="170" y="95" width="60" height="30" rx="4" stroke="#d32f2f" fill="none"/>
                <foreignObject x="170" y="95" width="60" height="30">
                    <input type="text" class="tree-input" data-key="<?= $h($items[1]['part'] ?? 'h2') ?>" style="width:100%;height:100%;text-align:center;border:none;background:transparent;font-size:14px;font-weight:bold;">
                </foreignObject>
                <text x="30" y="185" font-size="18" font-weight="bold">T</text>
                <line x1="55" y1="180" x2="120" y2="160" stroke="#1976d2" stroke-width="2" />
                <circle cx="140" cy="155" r="10" fill="none" stroke="#1976d2" stroke-width="2" />
                <line x1="55" y1="180" x2="120" y2="200" stroke="#1976d2" stroke-width="2" />
                <circle cx="140" cy="205" r="10" fill="none" stroke="#1976d2" stroke-width="2" />
                <rect x="170" y="140" width="60" height="30" rx="4" stroke="#d32f2f" fill="none"/>
                <foreignObject x="170" y="140" width="60" height="30">
                    <input type="text" class="tree-input" data-key="<?= $h($items[2]['part'] ?? 't1') ?>" style="width:100%;height:100%;text-align:center;border:none;background:transparent;font-size:14px;font-weight:bold;">
                </foreignObject>
                <rect x="170" y="190" width="60" height="30" rx="4" stroke="#d32f2f" fill="none"/>
                <foreignObject x="170" y="190" width="60" height="30">
                    <input type="text" class="tree-input" data-key="<?= $h($items[3]['part'] ?? 't2') ?>" style="width:100%;height:100%;text-align:center;border:none;background:transparent;font-size:14px;font-weight:bold;">
                </foreignObject>
            </svg>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.tree-input');
            const hidden = document.querySelector('input[name="answer[<?= $real_question_id ?>]"]');
            function update() {
                const obj = {};
                inputs.forEach(inp => {
                    const val = inp.value.trim();
                    if (val) obj[inp.dataset.key] = val.toUpperCase();
                });
                hidden.value = JSON.stringify(obj);
            }
            inputs.forEach(i => i.addEventListener('input', update));
            update();
        });
        </script>

    <?php else: ?>
        <?php foreach ($items ?? [] as $idx => $item):
            $part = $item['part'] ?? ('q'.($idx+1));
            $label = $item['label'] ?? '';
            $options = $item['options'] ?? [];
            if (!$part) continue;
        ?>
            <div>
                <input type="hidden" name="answer[<?= $real_question_id ?>_<?= $part ?>]" value="">
                <?php if ($label): ?><strong><?= $h($label) ?></strong><?php endif; ?>
                <div class="prob-options"00 style="flex-direction:column; gap:12px; margin-top:8px;">
                    <?php foreach ($options as $opt):
                        $val = strtoupper($opt['value'] ?? '');
                        $text = $opt['text'] ?? $val;
                    ?>
                        <label class="prob-option">
                            <input type="<?= $renderType === 'probability_multiple_ch' ? 'checkbox' : 'radio' ?>"
                                   name="answer[<?= $real_question_id ?>_<?= $part ?><?= $renderType === 'probability_multiple_ch' ? '][]' : '' ?>"
                                   value="<?= $h($val) ?>">
                            <?= $optBadge($val) ?><?= $h($text) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>