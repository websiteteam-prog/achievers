<?php
/* Fraction renderer — guarded so it doesn't redeclare when the template
   is included once per question. */
if (!function_exists('render_fraction_set')) {

    function frac_html($num, $den) {
        return '<span class="frac">'
             . '<span class="num">'.htmlspecialchars($num).'</span>'
             . '<span class="den">'.htmlspecialchars($den).'</span>'
             . '</span>';
    }

    function render_one_fraction($token) {
        $token = trim($token);
        // Mixed number: "7 3/4"
        if (preg_match('~^(\d+)\s+(\d+)\s*/\s*(\d+)$~', $token, $m)) {
            return '<span class="mixed"><span class="whole">'.htmlspecialchars($m[1]).'</span>'
                 . frac_html($m[2], $m[3]).'</span>';
        }
        // Simple fraction: "12/9"
        if (preg_match('~^(\d+)\s*/\s*(\d+)$~', $token, $m)) {
            return frac_html($m[1], $m[2]);
        }
        // Anything else: show as-is
        return '<span class="plain">'.htmlspecialchars($token).'</span>';
    }

    function render_fraction_set($text) {
        $parts = preg_split('/\s*,\s*/', trim((string)$text));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            $out[] = render_one_fraction($p);
        }
        return implode('<span class="frac-sep">,</span>', $out);
    }
}
?>
<style>
.container-fluid {
    margin-left: 0;
    margin-bottom: 20px;
    padding: 18px 25px;
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    background-color: #ffffff;
    transition: box-shadow 0.3s, transform 0.2s;
    width: 100%;
    max-width: 100%;
    margin-top: 10px;
}
.container-fluid:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}
.container-fluid h6 {
    font-weight: 600;
    margin-bottom: 14px;
    color: #333;
    font-size: 17px;
    line-height: 2;         /* room for stacked fractions */
    /* REMOVED white-space:nowrap and overflow-x:auto — they created the ▲▼ scrollbar */
}

/* ---- proper stacked fractions (scoped to the heading) ---- */
.container-fluid h6 .frac {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    line-height: 1;
    margin: 0 3px;
    font-size: 15px;
}
.container-fluid h6 .frac .num { padding: 0 5px 1px; border-bottom: 2px solid currentColor; }
.container-fluid h6 .frac .den { padding: 1px 5px 0; }
.container-fluid h6 .mixed {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    vertical-align: middle;
}
.container-fluid h6 .mixed .whole { font-size: 18px; font-weight: 700; }
.container-fluid h6 .frac-sep { margin: 0 8px 0 3px; }

.arrange-container {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 14px;
}
.arrange-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.arrange-input {
    width: 80px;
    text-align: center;
    font-size: 16px;
    padding: 6px 4px 4px;
    border: none;
    border-bottom: 2px solid #ccc;
    background: transparent;
    transition: border-color 0.3s;
}
/* insurance: kill spinner arrows if any input is ever type=number */
.arrange-input::-webkit-outer-spin-button,
.arrange-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.arrange-input[type=number] { -moz-appearance: textfield; appearance: textfield; }

.arrange-input::placeholder { color: transparent; }
.arrange-input:focus { border-bottom-color: #007bff; outline: none; }
.less-symbol { font-size: 18px; font-weight: 600; color: #555; }

@media (max-width:768px) {
    .container-fluid { width: 100%; padding: 14px 16px; }
    .arrange-container { justify-content: center; }
}
</style>

<div class="container-fluid col-lg-12 col-md-12 col-sm-12">
    <h6><?= htmlspecialchars((string)$char) ?>. <?= render_fraction_set($q['question_text'] ?? '') ?></h6>
    <?php $char++; ?>

    <div class="arrange-container">
        <input type="text" class="arrange-input" name="answer[<?= $q['id'] ?>][]" placeholder="______">
        <span class="less-symbol">&lt;</span>
        <input type="text" class="arrange-input" name="answer[<?= $q['id'] ?>][]" placeholder="______">
        <span class="less-symbol">&lt;</span>
        <input type="text" class="arrange-input" name="answer[<?= $q['id'] ?>][]" placeholder="______">
        <span class="less-symbol">&lt;</span>
        <input type="text" class="arrange-input" name="answer[<?= $q['id'] ?>][]" placeholder="______">
    </div>
</div>