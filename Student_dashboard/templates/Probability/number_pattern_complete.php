<?php
declare(strict_types=1);
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$optBadge = fn($val) => preg_match('/^[A-D]$/', (string)$val) ? '<span class="opt-badge">'.$val.'</span>' : '';

$q = $q ?? [];
$questionImage = trim((string)($q['question_image'] ?? ''));
$payloadRaw = (string)($q['question_payload'] ?? '[]');
$payload = json_decode($payloadRaw, true) ?: [];
$real_question_id = (int)$q['id'];
$renderType = $payload['type'] ?? 'unknown';
$items = $payload['items'] ?? [];
$instruction = $payload['instruction'] ?? '';
$makeUrl = fn($path) => $path && !preg_match('~^https?://~i', $path)
    ? rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/') . '/' . ltrim($path, '/')
    : $path;
$imgUrl = $makeUrl($questionImage);
?>

<style>
.pattern-wrap { margin: 20px 0; padding: 0 15px; font-family: 'Segoe UI', Tahoma, sans-serif; max-width: 900px; }
.pattern-instruction { font-weight: 600; color: #d32f2f; margin-bottom: 20px; font-size: 18px; text-align: center; }
.pattern-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; margin-top: 20px; }
.pattern-item { background: #f9f9f9; padding: 24px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center; }
.pattern-label { font-weight: bold; font-size: 20px; margin-bottom: 16px; color: #1a1a1a; }
.pattern-sequence { font-family: monospace; font-size: 16px; line-height: 2.2; }
.pattern-fill-input {
    display: inline-block;
    border: none;
    border-bottom: 2px solid #1976d2;
    width: 64px;
    margin: 0 -7px;
    line-height: 2.2;
    text-align: left;
    font-family: monospace;
    font-size: 16px;
    background: transparent;
    padding: 1px 0;
    outline: none;
}
.pattern-item {
    position: relative;
    padding-left: 60px; /* space for number on left */
}

.pattern-label {
    position: absolute;
    left: 15px;
    top: 20px;
    font-weight: bold;
    font-size: 20px;
    color: #1976d2;
}

.pattern-type { margin-top: 20px; text-align: left; }
.pattern-type select {
    font-size: 15px;
    padding: 7px 51px;
    border-radius: 10px;
    border: 1px solid #aaa;
    width: 200px;
    background: #fff;
}
.opt-badge {
    display: inline-block;
    min-width: 24px;
    height: 24px;
    line-height: 24px;
    text-align: center;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-weight: 600;
    margin-right: 6px;
    background: #fff;
}
.pattern-wrap {
    text-align: left;
}

.pattern-instruction {
    text-align: left;
}

.pattern-item {
    text-align: left;
}

</style>

<div class="pattern-wrap">
    <?php if ($instruction): ?>
        <div class="pattern-instruction"><?= $h($instruction) ?></div>
    <?php endif; ?>

    <!-- 1. number_pattern_complete -->
    <?php if ($renderType === 'number_pattern_complete'): ?>
        <input type="hidden" name="answer[<?= $real_question_id ?>]" value="{}" id="pattern-hidden-<?= $real_question_id ?>">
        <div class="pattern-grid" id="pattern-grid-<?= $real_question_id ?>">
            <?php foreach ($items as $idx => $item):
                $part = $item['part'] ?? ('q' . ($idx + 1));
                $sequence = $item['sequence'] ?? [];
                $blank_count = (int)($item['blanks'] ?? 2);
                $label = $item['label'] ?? ($idx + 1) . ')';
            ?>
                <div class="pattern-item" data-part="<?= $h($part) ?>">
                    <div class="pattern-label"><?= $h($label) ?></div>
                    <div class="pattern-sequence">
                        <?php
                        foreach ($sequence as $i => $num):
                            echo $h($num);
                            if ($i < count($sequence) - 1 || $blank_count > 0): ?>, <?php endif;
                        endforeach;
                        for ($b = 0; $b < $blank_count; $b++): ?>
                            <?php if ($b > 0): ?>, <?php endif; ?>
                            <input type="text"
                                   class="pattern-fill-input pattern-num-input"
                                   data-qid="<?= $real_question_id ?>"
                                   data-part="<?= $h($part) ?>"
                                   data-index="<?= $b ?>"
                                   maxlength="6"
                                   placeholder="">
                        <?php endfor; ?>
                    </div>
                    <div class="pattern-type">
                        <select class="pattern-type-select"
                                data-qid="<?= $real_question_id ?>"
                                data-part="<?= $h($part) ?>_type">
                            <option value="">Select Pattern</option>
                            <option value="growing">Growing</option>
                            <option value="shrinking">Shrinking</option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const grid   = document.getElementById('pattern-grid-<?= $real_question_id ?>');
    const hidden = document.getElementById('pattern-hidden-<?= $real_question_id ?>');
    if (!grid || !hidden) return;

    function updateAnswer() {
        const answer = {};
        let hasAny = false;

        grid.querySelectorAll('.pattern-item').forEach(item => {
            const part = item.dataset.part;
            const nums = [];
            item.querySelectorAll('.pattern-num-input').forEach(input => {
                const v = input.value.trim();
                nums.push(v);
                if (v !== '') hasAny = true;
            });
            const typeSel = item.querySelector('.pattern-type-select');
            const typeVal = typeSel ? typeSel.value : '';
            if (typeVal !== '') hasAny = true;

            answer[part] = { numbers: nums, type: typeVal };
        });

        hidden.value = hasAny ? JSON.stringify(answer) : '';   // ✅ blank = not attempted
    }

    grid.querySelectorAll('.pattern-num-input, .pattern-type-select').forEach(el => {
        el.addEventListener('input', updateAnswer);
        el.addEventListener('change', updateAnswer);
    });

    updateAnswer();
});
</script>

    <!-- 2. pattern_rule_mcq -->
    <?php elseif ($renderType === 'pattern_rule_mcq'): ?>
       <input type="hidden" name="answer[<?= $real_question_id ?>]" value="" id="pattern-hidden-<?= $real_question_id ?>">
        <?php if ($instruction): ?>
            <div style="font-weight:600; color:#d32f2f; text-align:center; font-size:18px; margin:30px 0;">
                <?= $h($instruction) ?>
            </div>
        <?php endif; ?>
        <?php foreach ($items as $item):
            $sequence = $item['sequence'] ?? [];
            $options = $item['options'] ?? [];
        ?>
            <div style="background:#f9f9f9; padding:30px; border-radius:12px; margin-bottom:30px; text-align:left;">
                <div style="font-family:monospace; font-size:22px; margin-bottom:25px;">
                    <?= implode(', ', array_map($h, $sequence)) ?>
                </div>
                <div style="font-weight:600; color:#d32f2f; font-size:18px; margin-bottom:20px;">
                    Pattern Rule:
                </div>
                <div style="display:flex; flex-direction:column; gap:18px; align-items:flex-start; max-width:700px;">
                    <?php foreach ($options as $opt):
                        $val = $opt['value'] ?? '';
                        $text = $opt['text'] ?? '';
                    ?>
                        <label style="display:flex; align-items:center; gap:12px; font-size:17px;">
                            <input type="radio"
                                   name="rule_<?= $real_question_id ?>"
                                   value="<?= $h($val) ?>"
                                   style="transform:scale(1.4);">
                            <span style="background:#ffebee; padding:10px 16px; border-radius:8px; flex:1; text-align:left;">
                                <?= $optBadge($val) ?> <?= $h($text) ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <script>
        (function() {
            const hidden = document.getElementById('rule-mcq-hidden-<?= $real_question_id ?>');
            if (!hidden) return;
            const radios = document.querySelectorAll('input[name="rule_<?= $real_question_id ?>"]');
            function update() {
                const selected = document.querySelector('input[name="rule_<?= $real_question_id ?>"]:checked');
                hidden.value = selected ? selected.value : '';
            }
            radios.forEach(r => r.addEventListener('change', update));
            update();
        })();
        </script>

    <!-- 3. pattern_extend_rule (UPDATED & FIXED) -->
       <!-- 3. FIXED pattern_extend_rule (Scoped to current question) -->
    <?php elseif ($renderType === 'pattern_extend_rule'): ?>
        <input type="hidden" name="answer[<?= $real_question_id ?>]" value="{}" id="extend-hidden-<?= $real_question_id ?>">
        
        <?php if ($instruction): ?>
            <div style="font-weight:600; color:#d32f2f; text-align:center; font-size:18px; margin:30px 0;">
                <?= $h($instruction) ?>
            </div>
        <?php endif; ?>

        <?php 
        $item = $items[0] ?? [];
        $sequence = $item['sequence'] ?? [];
        $blanks = (int)($item['blanks'] ?? 2);
        ?>

        <div class="extend-question-container" data-qid="<?= $real_question_id ?>">
            <div style="background:#f9f9f9; padding:30px; border-radius:12px; margin-bottom:40px; text-align:left;">
                <div style="font-family:monospace; font-size:22px; line-height:2.2; margin-bottom:30px; text-align:left;">
                    <?php
                    foreach ($sequence as $i => $num):
                        echo $h($num);
                        if ($i < count($sequence)-1 || $blanks > 0): ?>, <?php endif;
                    endforeach;
                    for ($b = 0; $b < $blanks; $b++): ?>
                        <?php if ($b > 0): ?>, <?php endif; ?>
                        <input type="text"
                               class="extend-num-input"
                               style="width:90px; border:none; border-bottom:3px solid #1976d2; font-family:monospace; font-size:22px; text-align:center; background:transparent; outline:none;"
                               maxlength="6"
                               placeholder="">
                    <?php endfor; ?>
                </div>

                <div style="font-weight:600; color:#333; font-size:18px; margin-bottom:12px;">
                    My Pattern Rule:
                </div>
                <div>
                    <input type="text"
       class="extend-rule-text"
       style="width:100%; max-width:600px; padding:12px; font-size:17px; border-radius:8px; border:1px solid #ccc;"
                           placeholder="Write the pattern rule here">
                </div>
            </div>
        </div>

        <script>
        (function() {
            const container = document.querySelector('.extend-question-container[data-qid="<?= $real_question_id ?>"]');
            const hidden = document.getElementById('extend-hidden-<?= $real_question_id ?>');
            if (!container || !hidden) return;

           function updateAnswer() {
    const nums = [];
    container.querySelectorAll('.extend-num-input').forEach(inp => {
        nums.push(inp.value.trim());
    });

    const ruleInput = container.querySelector('.extend-rule-text');
    const rule = ruleInput ? ruleInput.value.trim() : '';

    const hasInput =
        nums.some(n => n !== '') ||
        rule !== '';

    if (!hasInput) {
        hidden.value = '';   // ✅ NOT ATTEMPTED
        return;
    }

    hidden.value =
      '{"numbers":' + JSON.stringify(nums) +
      ',"rule":"' + rule.replace(/"/g, '\\"') + '"}';
}

            container.querySelectorAll('.extend-num-input, .extend-rule-text').forEach(el => {
                el.addEventListener('input', updateAnswer);
                el.addEventListener('blur', updateAnswer);
            });

            updateAnswer();
        })();
        </script>
    <!-- 4. pattern_match_rule -->
    <?php elseif ($renderType === 'pattern_match_rule'): ?>

<input type="hidden"
       name="answer[<?= $real_question_id ?>]"
       id="match-hidden-<?= $real_question_id ?>"
       value="{}">

<?php if ($instruction): ?>
  <div style="font-weight:600;color:#d32f2f;font-size:18px;margin-bottom:20px;">
    <?= $h($instruction) ?>
  </div>
<?php endif; ?>

<?php foreach ($items as $idx => $item): 
  $sequence = $item['sequence'] ?? [];
  $options  = $item['options'] ?? [];
?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:30px;">
    
    <!-- LEFT: sequence -->
    <div class="pattern-item">
      <div style="font-family:monospace;font-size:18px;margin-bottom:12px;">
        <?= implode(', ', array_map($h, $sequence)) ?>
      </div>

      <?php foreach ($options as $opt): ?>
        <label style="margin-right:18px;font-size:16px;">
          <input type="radio"
                 name="match_<?= $real_question_id ?>_<?= $idx ?>"
                 value="<?= $h($opt['value']) ?>"
                 data-row="<?= $idx ?>">
          <?= $optBadge($opt['value']) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <!-- RIGHT: rules (ONLY for this sequence) -->
    <div style="background:#f9f9f9;padding:18px;border-radius:10px;">
      <div style="font-weight:600;margin-bottom:10px;">Pattern Rules</div>
      <?php foreach ($options as $opt): ?>
        <div style="margin-bottom:8px;">
          <?= $optBadge($opt['value']) ?>
          <?= $h($opt['text']) ?>
        </div>
      <?php endforeach; ?>
    </div>

  </div>

<?php endforeach; ?>

<script>
(function(){
  const hidden = document.getElementById('match-hidden-<?= $real_question_id ?>');

function update(){
  const checked = document.querySelector(
    'input[name^="match_<?= $real_question_id ?>_"]:checked'
  );
  hidden.value = checked ? checked.value : '';
}

  document.querySelectorAll(
    'input[name^="match_<?= $real_question_id ?>_"]'
  ).forEach(r => r.addEventListener('change', update));

  update();
})();
</script>

<?php endif; ?>
</div>