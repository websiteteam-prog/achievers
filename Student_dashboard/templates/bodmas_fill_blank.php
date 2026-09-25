<?php
$data = json_decode($q['question_payload'], true);
$operatorMap = [
    'x' => '&times;',
    '*' => '&times;',
    '/' => '÷',
    '+' => '+',
    '-' => '−'
];
$symbolMathML = $operatorMap[$data['operator'] ?? ''] ?? ($data['operator'] ?? '');
$numbers = [];
foreach ($data as $key => $value) {
    if (strpos($key, 'num') === 0) {
        $numbers[] = $value;
    }
}
$layoutType = $data['layout'] ?? 'inline';
$isVertical = ($layoutType === 'vertical');
$mode = $data['mode'] ?? '';
?>

<?php if ($mode === 'missing_addition'):
    $rows = $data['rows'];
    $numRows = count($rows);
    $maxCols = max(array_map('count', $rows));
?>
<style>
    /* .missing-add-wrap = inner layout only. Width/columns come from the
       Bootstrap grid class (col-md-4) on the div below — 3 per row.          */
    .missing-add-wrap {
        display: flex;
        align-items: flex-start;
        font-size: 18px;
        margin-bottom: 26px;
    }
    .missing-add-wrap strong {
        margin-right: 10px;
        font-weight: 600;
        line-height: 2.4;
    }
    .missing-add-table {
        border-collapse: collapse;
    }
    .missing-add-table td {
        width: 34px;
        height: 40px;
        text-align: center;
        vertical-align: middle;
        font-size: 18px;
        padding: 2px;
    }
    .missing-add-table td.op-cell {
        width: 18px;
        text-align: left;
        font-weight: 600;
        padding-left: 0;
    }
    .missing-add-table tr.sum-row td {
        border-top: 2px solid #000;
        padding-top: 6px;
    }
    .digit-blank {
        width: 28px;
        height: 28px;
        border: 2px solid #2b3fae;
        border-radius: 4px;
        text-align: center;
        font-size: 15px;
        outline: none;
        background: #fff;
        padding: 0;
    }
    .digit-blank:focus {
        border-color: #007bff;
    }
</style>
<!-- col-md-4 = 3 per row. For 2 per row change it to col-md-6 -->
<div class="missing-add-wrap col-md-4">
    <strong><?= chr(97 + $index) ?>)</strong>
    <table class="missing-add-table">
        <?php foreach ($rows as $rIndex => $row):
            $isFirstRow = ($rIndex === 0);
            $isLastRow  = ($rIndex === $numRows - 1);
            $pad = $maxCols - count($row);
            $rowClass = $isLastRow ? 'sum-row' : '';
        ?>
        <tr class="<?= $rowClass ?>">
            <td class="op-cell">
                <?= (!$isFirstRow && !$isLastRow) ? $symbolMathML : '' ?>
            </td>
            <?php for ($p = 0; $p < $pad; $p++): ?>
                <td></td>
            <?php endfor; ?>
            <?php foreach ($row as $cell): ?>
                <td>
                    <?php if ($cell === ''): ?>
                        <input type="text"
                               name="answer[<?= $q['id'] ?>][]"
                               class="digit-blank"
                               maxlength="1"
                               inputmode="numeric"
                               autocomplete="off">
                    <?php else: ?>
                        <?= htmlspecialchars($cell) ?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php elseif ($mode === 'factor_tree'):
    $rows = $data['rows'];
?>
<style>
    /* col-md-6 = 2 per row, matching the worksheet image (1&2 / 3&4). */
    .factor-tree-wrap {
        display: flex;
        align-items: flex-start;
        font-size: 18px;
        margin-bottom: 40px;
        margin-top: 50px;
    }
    .factor-tree-wrap > strong {
        margin-right: 10px;
        font-weight: 600;
        line-height: 2.6;
    }
    .factor-tree-pyramid {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .ft-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .ft-box {
        width: 46px;
        height: 46px;
        background: #f6c6ab;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 17px;
        flex-shrink: 0;
    }
    .ft-op {
        font-weight: 700;
        font-size: 17px;
    }
    .ft-input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        text-align: center;
        font-size: 17px;
        font-weight: 700;
        outline: none;
        padding: 0;
    }
</style>
<div class="factor-tree-wrap col-md-6">
    <strong><?= ($index + 1) ?>)</strong>
    <div class="factor-tree-pyramid">
        <?php foreach ($rows as $row): ?>
            <div class="ft-row">
                <?php foreach ($row as $cIndex => $cell): ?>
                    <?php if ($cIndex > 0): ?><span class="ft-op">x</span><?php endif; ?>
                    <div class="ft-box">
                        <?php if ($cell === ''): ?>
                            <input type="text"
                                   name="answer[<?= $q['id'] ?>][]"
                                   class="ft-input"
                                   maxlength="3"
                                   inputmode="numeric"
                                   autocomplete="off">
                        <?php else: ?>
                            <?= htmlspecialchars($cell) ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php elseif ($mode === 'prime_factor_tree'):
    $rootVal = $data['root'] ?? '';
    $tree = $data['tree'] ?? [];

    $slotWidth = 64;
    $rowHeight = 74;
    $nodeSize  = 42;
    $topPad    = 26;

    if (!function_exists('pft_layout')) {
        function pft_layout(&$node, &$leafCounter, $depth, $slotWidth, $rowHeight, $topPad) {
            $node['y'] = $topPad + $depth * $rowHeight;
            if (empty($node['children'])) {
                $node['x'] = $leafCounter * $slotWidth + $slotWidth / 2;
                $leafCounter++;
            } else {
                foreach ($node['children'] as &$child) {
                    pft_layout($child, $leafCounter, $depth + 1, $slotWidth, $rowHeight, $topPad);
                }
                unset($child);
                $node['x'] = ($node['children'][0]['x'] + $node['children'][1]['x']) / 2;
            }
        }
    }

    if (!function_exists('pft_maxdepth')) {
        function pft_maxdepth($node, $depth, &$maxDepth) {
            $maxDepth = max($maxDepth, $depth);
            if (!empty($node['children'])) {
                foreach ($node['children'] as $c) pft_maxdepth($c, $depth + 1, $maxDepth);
            }
        }
    }

    if (!function_exists('pft_collect')) {
        function pft_collect(&$node, $parentX, $parentY, &$lines, &$allNodes) {
            $lines[] = [$parentX, $parentY, $node['x'], $node['y']];
            $allNodes[] = $node;
            if (!empty($node['children'])) {
                foreach ($node['children'] as &$c) {
                    pft_collect($c, $node['x'], $node['y'], $lines, $allNodes);
                }
                unset($c);
            }
        }
    }

    $leafCounter = 0;
    foreach ($tree as &$childNode) {
        pft_layout($childNode, $leafCounter, 1, $slotWidth, $rowHeight, $topPad);
    }
    unset($childNode);

    $rootX = ($tree[0]['x'] + $tree[1]['x']) / 2;
    $rootY = $topPad;

    $maxDepth = 0;
    foreach ($tree as $c) pft_maxdepth($c, 1, $maxDepth);

    $totalWidth  = max($leafCounter * $slotWidth, 130);
    $totalHeight = $topPad + ($maxDepth + 1) * $rowHeight + 20;

    $lines = [];
    $allNodes = [];
    foreach ($tree as &$childNode) {
        pft_collect($childNode, $rootX, $rootY, $lines, $allNodes);
    }
    unset($childNode);

    $markerId = 'pftArrow' . $q['id'] . '_' . $index;
?>
<style>
    .pft-wrap {
        display: flex;
        align-items: flex-start;
        font-size: 18px;
        margin-bottom: 30px;
        margin-top: 50px;
    }
    .pft-wrap > strong {
        margin-right: 10px;
        font-weight: 600;
        line-height: 2.2;
    }
    .pft-canvas {
        position: relative;
    }
    .pft-svg {
        position: absolute;
        left: 0;
        top: 0;
    }
    .pft-root {
        position: absolute;
        transform: translate(-50%, 0);
        background: #fcd34d;
        color: #7c4a03;
        font-weight: 700;
        font-size: 16px;
        padding: 6px 14px;
        border-radius: 8px;
        white-space: nowrap;
    }
    .pft-node {
        position: absolute;
        transform: translate(-50%, -50%);
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #f6c6ab;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
    }
    .pft-input {
        width: 100%;
        height: 100%;
        border: none;
        background: transparent;
        text-align: center;
        font-size: 15px;
        font-weight: 700;
        outline: none;
        padding: 0;
    }
</style>
<div class="pft-wrap col-md-6">
    <strong><?= ($index + 1) ?>)</strong>
    <div class="pft-canvas" style="width:<?= $totalWidth ?>px;height:<?= $totalHeight ?>px;">
        <svg class="pft-svg" width="<?= $totalWidth ?>" height="<?= $totalHeight ?>">
            <defs>
                <marker id="<?= $markerId ?>" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto">
                    <path d="M0,0 L6,3 L0,6 Z" fill="#4a5568"></path>
                </marker>
            </defs>
            <?php foreach ($lines as $l): ?>
                <line x1="<?= $l[0] ?>" y1="<?= $l[1] + 16 ?>" x2="<?= $l[2] ?>" y2="<?= $l[3] - 22 ?>"
                      stroke="#4a5568" stroke-width="2" marker-end="url(#<?= $markerId ?>)"></line>
            <?php endforeach; ?>
        </svg>
        <div class="pft-root" style="left:<?= $rootX ?>px;top:<?= $rootY - 14 ?>px;">
            <?= htmlspecialchars($rootVal) ?>
        </div>
        <?php foreach ($allNodes as $n): ?>
            <div class="pft-node" style="left:<?= $n['x'] ?>px;top:<?= $n['y'] ?>px;">
                <?php if ($n['value'] === ''): ?>
                    <input type="text"
                           name="answer[<?= $q['id'] ?>][]"
                           class="pft-input"
                           maxlength="4"
                           inputmode="numeric"
                           autocomplete="off">
                <?php else: ?>
                    <?= htmlspecialchars($n['value']) ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif ($mode === 'prime_factor_tree_product'):

    $rootVal = $data['root'] ?? '';
    $tree = $data['tree'] ?? [];

    $productAnswer = $data['product_answer'] ?? '';

    $slotWidth = 70;
    $rowHeight = 74;
    $topPad = 26;

    /*
    |--------------------------------------------------------------------------
    | LAYOUT
    |--------------------------------------------------------------------------
    */

    if (!function_exists('pftp_layout')) {

        function pftp_layout(
            &$node,
            &$leafCounter,
            $depth,
            $slotWidth,
            $rowHeight,
            $topPad
        ) {

            $node['y'] = $topPad + ($depth * $rowHeight);

            /*
             * Leaf = circle
             * Node with children = rectangle
             */

            if (empty($node['children'])) {

                $node['x'] =
                    ($leafCounter * $slotWidth) +
                    ($slotWidth / 2);

                $leafCounter++;

            } else {

                foreach ($node['children'] as &$child) {

                    pftp_layout(
                        $child,
                        $leafCounter,
                        $depth + 1,
                        $slotWidth,
                        $rowHeight,
                        $topPad
                    );
                }

                unset($child);

                $node['x'] =
                    (
                        $node['children'][0]['x'] +
                        $node['children'][1]['x']
                    ) / 2;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MAX DEPTH
    |--------------------------------------------------------------------------
    */

    if (!function_exists('pftp_maxdepth')) {

        function pftp_maxdepth(
            $node,
            $depth,
            &$maxDepth
        ) {

            $maxDepth = max(
                $maxDepth,
                $depth
            );

            if (!empty($node['children'])) {

                foreach ($node['children'] as $c) {

                    pftp_maxdepth(
                        $c,
                        $depth + 1,
                        $maxDepth
                    );
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | COLLECT NODES + LINES
    |--------------------------------------------------------------------------
    */

    if (!function_exists('pftp_collect')) {

        function pftp_collect(
            &$node,
            $parentX,
            $parentY,
            &$lines,
            &$allNodes
        ) {

            $lines[] = [
                $parentX,
                $parentY,
                $node['x'],
                $node['y']
            ];

            $allNodes[] = $node;

            if (!empty($node['children'])) {

                foreach ($node['children'] as &$child) {

                    pftp_collect(
                        $child,
                        $node['x'],
                        $node['y'],
                        $lines,
                        $allNodes
                    );
                }

                unset($child);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE POSITIONS
    |--------------------------------------------------------------------------
    */

    $leafCounter = 0;

    foreach ($tree as &$childNode) {

        pftp_layout(
            $childNode,
            $leafCounter,
            1,
            $slotWidth,
            $rowHeight,
            $topPad
        );
    }

    unset($childNode);


    /*
    |--------------------------------------------------------------------------
    | ROOT POSITION
    |--------------------------------------------------------------------------
    */

    if (!empty($tree)) {

        $rootX =
            ($tree[0]['x'] + $tree[1]['x']) / 2;

    } else {

        $rootX = 100;
    }

    $rootY = $topPad;


    /*
    |--------------------------------------------------------------------------
    | DEPTH
    |--------------------------------------------------------------------------
    */

    $maxDepth = 0;

    foreach ($tree as $c) {

        pftp_maxdepth(
            $c,
            1,
            $maxDepth
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SIZE
    |--------------------------------------------------------------------------
    */

    $totalWidth =
        max(
            $leafCounter * $slotWidth,
            180
        );

    $totalHeight =
        $topPad +
        (($maxDepth + 1) * $rowHeight) +
        20;


    /*
    |--------------------------------------------------------------------------
    | LINES
    |--------------------------------------------------------------------------
    */

    $lines = [];
    $allNodes = [];

    foreach ($tree as &$childNode) {

        pftp_collect(
            $childNode,
            $rootX,
            $rootY,
            $lines,
            $allNodes
        );
    }

    unset($childNode);


    $markerId =
        'pftpArrow' .
        $q['id'] .
        '_' .
        $index;

?>

<style>

    /*
    |--------------------------------------------------------------------------
    | ONE QUESTION PER ROW
    |--------------------------------------------------------------------------
    */

    .pftp-wrap {
        display: flex;
        align-items: flex-start;
        font-size: 18px;
        margin-bottom: 30px;
        margin-top: 50px;
    
        width: 100%;
        float: none;
        clear: both;
    }


    .pftp-wrap > strong {

        margin-right: 12px;

        font-weight: 600;

        line-height: 2.2;

        flex-shrink: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | TREE CANVAS
    |--------------------------------------------------------------------------
    */

    .pftp-tree-area {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    }

    .pftp-canvas {
    position: relative;
    margin-left: 0;
    margin-right: 0;
    }


    .pftp-svg {

        position: absolute;

        left: 0;
        top: 0;

        overflow: visible;
    }


    /*
    |--------------------------------------------------------------------------
    | ROOT = RECTANGLE
    |--------------------------------------------------------------------------
    */

    .pftp-root {

        position: absolute;

        transform: translate(-50%, 0);

        width: 46px;
        height: 46px;

        background: #fff;

        border: 1.5px solid #000;

        border-radius: 4px;

        color: #1717a8;

        display: flex;

        align-items: center;

        justify-content: center;

        font-weight: 700;

        font-size: 16px;

        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMAL INTERNAL NODE = RECTANGLE
    |--------------------------------------------------------------------------
    */

    .pftp-node.pftp-rectangle {

        position: absolute;

        transform: translate(-50%, -50%);

        width: 46px;
        height: 46px;

        background: #fff;

        border: 1.5px solid #000;

        border-radius: 4px;

        display: flex;

        align-items: center;

        justify-content: center;

        color: #1717a8;

        font-weight: 700;

        font-size: 16px;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIME LEAF = CIRCLE
    |--------------------------------------------------------------------------
    */

    .pftp-node.pftp-circle {

        position: absolute;

        transform: translate(-50%, -50%);

        width: 46px;
        height: 46px;

        background: #fff;

        border: 1.5px solid #000;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

        color: #1717a8;

        font-weight: 700;

        font-size: 16px;
    }


    /*
    |--------------------------------------------------------------------------
    | INPUT
    |--------------------------------------------------------------------------
    */

    .pftp-input {

        width: 100%;
        height: 100%;

        border: none;

        background: transparent;

        text-align: center;

        font-size: 16px;

        font-weight: 700;

        color: #1717a8;

        outline: none;

        padding: 0;
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT OF PRIME FACTORS
    |--------------------------------------------------------------------------
    */

    .pftp-product {
    margin-top: 25px;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 15px;
    white-space: nowrap;
    width: 100%;
    }

    .pftp-product-input {

        width: 200px;

        height: 32px;

        border: none;

        border-bottom: 3px solid #1717a8;

        outline: none;

        background: transparent;

        font-size: 17px;

        text-align: center;

        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media(max-width: 768px) {

        .pftp-wrap {

            margin-top: 25px;

            margin-bottom: 35px;
        }

        .pftp-product {

            font-size: 16px;

            gap: 10px;
        }

        .pftp-product-input {

            width: 150px;
        }
    }

</style>


<div class="pftp-wrap">

    <strong>
        <?= ($index + 1) ?>)
    </strong>


    <div class="pftp-tree-area">


        <!-- TREE -->

        <div
            class="pftp-canvas"
            style="
                width:<?= $totalWidth ?>px;
                height:<?= $totalHeight ?>px;
            "
        >


            <!-- CONNECTING LINES -->

            <svg
                class="pftp-svg"
                width="<?= $totalWidth ?>"
                height="<?= $totalHeight ?>"
            >

                <defs>

                    <marker
                        id="<?= $markerId ?>"
                        markerWidth="8"
                        markerHeight="8"
                        refX="6"
                        refY="3"
                        orient="auto"
                    >

                        <path
                            d="M0,0 L6,3 L0,6 Z"
                            fill="#4a5568"
                        ></path>

                    </marker>

                </defs>


                <?php foreach ($lines as $l): ?>

                    <line
                        x1="<?= $l[0] ?>"
                        y1="<?= $l[1] + 20 ?>"
                        x2="<?= $l[2] ?>"
                        y2="<?= $l[3] - 23 ?>"
                        stroke="#4a5568"
                        stroke-width="1.5"
                    ></line>

                <?php endforeach; ?>


            </svg>


            <!-- ROOT RECTANGLE -->

            <div
                class="pftp-root"
                style="
                    left:<?= $rootX ?>px;
                    top:<?= $rootY - 14 ?>px;
                "
            >

                <?= htmlspecialchars($rootVal) ?>

            </div>


            <!-- TREE NODES -->

            <?php foreach ($allNodes as $n): ?>

                <?php

                    /*
                     * Has children = rectangle
                     * No children = circle
                     */

                    $nodeClass =
                        !empty($n['children'])
                        ? 'pftp-rectangle'
                        : 'pftp-circle';

                ?>


                <div
                    class="pftp-node <?= $nodeClass ?>"
                    style="
                        left:<?= $n['x'] ?>px;
                        top:<?= $n['y'] ?>px;
                    "
                >

                    <?php if (($n['value'] ?? '') === ''): ?>

                        <input
                            type="text"
                            name="answer[<?= $q['id'] ?>][]"
                            class="pftp-input"
                            maxlength="4"
                            inputmode="numeric"
                            autocomplete="off"
                        >

                    <?php else: ?>

                        <?= htmlspecialchars($n['value']) ?>

                    <?php endif; ?>

                </div>


            <?php endforeach; ?>


        </div>


        <!-- PRODUCT -->

        <div class="pftp-product">

            <span>
                Product of prime factors
            </span>

            <input
                type="text"
                name="answer[<?= $q['id'] ?>][]"
                class="pftp-product-input"
                autocomplete="off"
            >

        </div>


    </div>

</div>
<?php elseif (
    $mode === 'math_fill_blank' ||
    $mode === 'fraction_of_amount'
):

    /*
     * ============================================================
     * GENERIC MATH FILL BLANK
     *
     * Supports:
     * - fractions
     * - mixed numbers
     * - whole numbers
     * - +
     * - -
     * - ×
     * - ÷
     * - of
     * - or
     *
     * A "blank" can appear as:
     * - {"type":"blank"}                                  (standalone blank)
     * - {"type":"fraction","numerator":"","denominator":"1"}  or  "__blank__"
     * - {"type":"number","value":""}  or  "__blank__"
     *
     * Old fraction_of_amount payload is also supported.
     * ============================================================
     */

    $items = $data['items'] ?? [];

    /*
     * BACKWARD COMPATIBILITY
     *
     * Existing fraction_of_amount SQL:
     * numerator, denominator, operator, amount
     */
    if (empty($items) && isset($data['numerator'])) {

        $items = [
            [
                'type' => 'fraction',
                'numerator' => $data['numerator'] ?? '',
                'denominator' => $data['denominator'] ?? ''
            ],
            [
                'type' => 'operator',
                'value' => $data['operator'] ?? 'of'
            ],
            [
                'type' => 'number',
                'value' => $data['amount'] ?? ''
            ]
        ];
    }

    /*
     * Does this expression already contain a blank somewhere inside
     * (standalone blank / blank fraction part / blank number)?
     * If yes, we do NOT render the template's own trailing "= ___" input,
     * because the blank the student needs to fill is already mid-expression.
     */
    $hasBlank = false;
    foreach ($items as $__it) {
        $__t = $__it['type'] ?? 'number';
        if ($__t === 'blank') {
            $hasBlank = true;
            break;
        }
        if ($__t === 'fraction' || $__t === 'mixed') {
            $__n = (string)($__it['numerator'] ?? '');
            $__d = (string)($__it['denominator'] ?? '');
            if ($__n === '' || $__n === '__blank__' || $__d === '' || $__d === '__blank__') {
                $hasBlank = true;
                break;
            }
        }
        if ($__t === 'number') {
            $__v = (string)($__it['value'] ?? '');
            if ($__v === '' || $__v === '__blank__') {
                $hasBlank = true;
                break;
            }
        }
    }

?>

<style>

.math-fill-blank {
    display: flex;
    align-items: center;

    font-size: 20px;

    /* CARD */
    background: #ffffff;
    border: 1px solid #d9d9d9;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);

    /* CARD SIZE */
    width: 50%;
    max-width: 50%;
    min-height: 95px;

    /* CARD SPACING */
    margin-bottom: 20px;
    margin-top: 10px;

    /* CARD INNER SPACE */
    padding: 18px 20px;

    box-sizing: border-box;
}

.math-fill-blank > strong {
    margin-right: 18px;
    font-weight: 600;
    flex-shrink: 0;
    font-size: 20px;
}


/* ============================================================
   EXPRESSION
   ============================================================ */

.math-expression {
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
    flex: 1;
}


/* ============================================================
   FRACTION
   ============================================================ */

.math-fraction {

    display: inline-flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    min-width: 32px;

    font-size: 20px;

    font-weight: 600;

    line-height: 1;
}

.math-fraction .math-numerator {

    border-bottom: 2px solid #000;

    padding: 0 6px 4px;
}

.math-fraction .math-denominator {

    padding: 4px 6px 0;
}


/* ============================================================
   MIXED NUMBER
   ============================================================ */

.math-mixed {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    font-size: 20px;

    font-weight: 600;
}

.math-mixed-whole {
    display: inline-block;
}


/* ============================================================
   OPERATOR
   ============================================================ */

.math-operator {

    font-size: 20px;

    font-weight: 600;

    white-space: nowrap;
}


/* ============================================================
   NORMAL NUMBER
   ============================================================ */

.math-number {

    font-size: 20px;

    font-weight: 600;

    white-space: nowrap;
}


/* ============================================================
   ANSWER INPUT
   ============================================================ */

.math-fill-answer {

    width: 100px;

    height: 30px;

    border: none;

    border-bottom: 2px solid #000;

    background: transparent;

    text-align: center;

    font-size: 20px;

    font-weight: 600;

    outline: none;

    margin-left: 8px;

    flex-shrink: 0;
}

/* Smaller inline variant used for blanks INSIDE a fraction (numerator/denominator) */
.math-fraction .math-fill-answer {
    width: 40px;
    height: 26px;
    margin-left: 0;
    font-size: 16px;
}


/* ============================================================
   MOBILE
   ============================================================ */

@media(max-width:768px) {

    .math-fill-blank {
        width: 100%;
        max-width: 100%;
        min-height: 85px;

        font-size: 18px;

        margin-top: 10px;
        margin-bottom: 15px;

        padding: 14px 16px;
    }

    .math-fill-blank > strong {
        margin-right: 12px;
        font-size: 18px;
    }

    .math-expression {
        gap: 7px;
    }

    .math-fraction {
        font-size: 18px;
    }

    .math-mixed {
        font-size: 18px;
    }

    .math-operator {
        font-size: 18px;
    }

    .math-number {
        font-size: 18px;
    }

    .math-fill-answer {
        width: 80px;
        font-size: 18px;
    }

    .math-fraction .math-fill-answer {
        width: 32px;
        font-size: 14px;
    }
}

</style>


<div class="math-fill-blank col-md-6">

    <!-- QUESTION NUMBER -->

    <strong>
        <?= ($index + 1) ?>)
    </strong>


    <!-- EXPRESSION -->

    <div class="math-expression">

        <?php foreach ($items as $item): ?>

            <?php

                $itemType = $item['type'] ?? 'number';

            ?>


            <!-- ==================================================
                 FRACTION
                 ================================================== -->

            <?php if ($itemType === 'fraction'):

                $numVal = (string)($item['numerator'] ?? '');
                $denVal = (string)($item['denominator'] ?? '');
                $numBlank = ($numVal === '' || $numVal === '__blank__');
                $denBlank = ($denVal === '' || $denVal === '__blank__');
            ?>

                <div class="math-fraction">

                    <div class="math-numerator">
                        <?php if ($numBlank): ?>
                            <input type="text"
                                   name="answer[<?= $q['id'] ?>]"
                                   class="math-fill-answer"
                                   autocomplete="off">
                        <?php else: ?>
                            <?= htmlspecialchars($numVal) ?>
                        <?php endif; ?>
                    </div>

                    <div class="math-denominator">
                        <?php if ($denBlank): ?>
                            <input type="text"
                                   name="answer[<?= $q['id'] ?>]"
                                   class="math-fill-answer"
                                   autocomplete="off">
                        <?php else: ?>
                            <?= htmlspecialchars($denVal) ?>
                        <?php endif; ?>
                    </div>

                </div>


            <!-- ==================================================
                 MIXED NUMBER
                 ================================================== -->

            <?php elseif ($itemType === 'mixed'):

                $mixedNum = (string)($item['numerator'] ?? '');
                $mixedDen = (string)($item['denominator'] ?? '');
                $mixedNumBlank = ($mixedNum === '' || $mixedNum === '__blank__');
                $mixedDenBlank = ($mixedDen === '' || $mixedDen === '__blank__');
            ?>

                <div class="math-mixed">

                    <span class="math-mixed-whole">

                        <?= htmlspecialchars(
                            (string)($item['whole'] ?? '')
                        ) ?>

                    </span>


                    <div class="math-fraction">

                        <div class="math-numerator">
                            <?php if ($mixedNumBlank): ?>
                                <input type="text"
                                       name="answer[<?= $q['id'] ?>]"
                                       class="math-fill-answer"
                                       autocomplete="off">
                            <?php else: ?>
                                <?= htmlspecialchars($mixedNum) ?>
                            <?php endif; ?>
                        </div>

                        <div class="math-denominator">
                            <?php if ($mixedDenBlank): ?>
                                <input type="text"
                                       name="answer[<?= $q['id'] ?>]"
                                       class="math-fill-answer"
                                       autocomplete="off">
                            <?php else: ?>
                                <?= htmlspecialchars($mixedDen) ?>
                            <?php endif; ?>
                        </div>

                    </div>

                </div>


            <!-- ==================================================
                 OPERATOR
                 ================================================== -->

            <?php elseif ($itemType === 'operator'): ?>

                <?php

                    $operator = (string)($item['value'] ?? '');

                    $operatorMap = [
                        'x'  => '×',
                        '*'  => '×',
                        '/'  => '÷',
                        '+'  => '+',
                        '-'  => '−',
                        '−'  => '−',
                        '×'  => '×',
                        '÷'  => '÷',
                        'of' => 'of',
                        'or' => 'or'
                    ];

                    $displayOperator =
                        $operatorMap[$operator] ?? $operator;

                ?>

                <span class="math-operator">

                    <?= htmlspecialchars($displayOperator) ?>

                </span>


            <!-- ==================================================
                 STANDALONE BLANK
                 ================================================== -->

            <?php elseif ($itemType === 'blank'): ?>

                <input type="text"
                       name="answer[<?= $q['id'] ?>]"
                       class="math-fill-answer"
                       style="margin-left:0;"
                       autocomplete="off">


            <!-- ==================================================
                 WHOLE NUMBER / TEXT
                 ================================================== -->

            <?php else:

                $numberVal = (string)($item['value'] ?? '');
                $numberBlank = ($numberVal === '' || $numberVal === '__blank__');
            ?>

                <?php if ($numberBlank): ?>
                    <input type="text"
                           name="answer[<?= $q['id'] ?>]"
                           class="math-fill-answer"
                           style="margin-left:0;"
                           autocomplete="off">
                <?php else: ?>
                    <span class="math-number">
                        <?= htmlspecialchars($numberVal) ?>
                    </span>
                <?php endif; ?>

            <?php endif; ?>

        <?php endforeach; ?>


        <!-- ======================================================
             EQUAL + FINAL ANSWER
             Only rendered when the blank hasn't already appeared
             mid-expression (otherwise we'd get two blanks / two "=").
             ====================================================== -->

        <?php if (!$hasBlank): ?>

            <span class="math-operator">

                =

            </span>


            <input
                type="text"
                name="answer[<?= $q['id'] ?>]"
                class="math-fill-answer"
                autocomplete="off">

        <?php endif; ?>

    </div>

</div>
<?php else: ?>

<style>
    /* ============================================================
       DEFAULT QUESTION — CARD STYLE
       (matches the .math-fill-blank card look used by other templates)
       ============================================================ */
    .quiz-line {
        display: flex;
        align-items: center;

        font-size: 18px;

        /* CARD */
        background: #ffffff;
        border: 1px solid #d9d9d9;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);

        /* CARD SIZE */
        width: 50%;
        max-width: 50%;
        min-height: 85px;

        /* CARD SPACING */
        margin-bottom: 20px;
        margin-top: 10px;

        /* CARD INNER SPACE */
        padding: 16px 20px;

        box-sizing: border-box;
    }
    .quiz-line strong {
        margin-right: 14px;
        font-weight: 600;
        flex-shrink: 0;
    }
    /* Editable underline box */
    .answer-blank {
        display: inline-block;
        border: none;
        border-bottom: 2px solid #000;
        width: 120px;
        height: 28px;
        margin-left: 10px;
        text-align: center;
        font-size: 18px;
        outline: none;
        background: transparent;
        flex-shrink: 0;
    }
    .answer-blank:focus {
        border-bottom: 2px solid #007bff; /* highlight on focus */
    }
    .math-inline {
        display: inline-block;
        white-space: nowrap;
    }

    /* ============================================================
       MOBILE
       ============================================================ */
    @media(max-width: 768px) {
        .quiz-line {
            width: 100%;
            max-width: 100%;
            min-height: 75px;

            font-size: 16px;

            margin-top: 10px;
            margin-bottom: 15px;

            padding: 12px 16px;
        }
        .quiz-line strong {
            margin-right: 10px;
        }
        .answer-blank {
            width: 90px;
            font-size: 16px;
        }
    }
</style>
<div class="quiz-line col-md-6">
    <strong><?= chr(97 + $index) ?>)</strong>

    <?php if ($isVertical): ?>
        <!-- Vertical layout -->
        <div class="math-inline">
            <math xmlns="http://www.w3.org/1998/Math/MathML" display="block">
                <mtable columnwidth="auto">
                    <mtr>
                        <mtd columnalign="right">
                            <mn><?= htmlspecialchars($numbers[0]) ?></mn>
                        </mtd>
                    </mtr>
                    <mtr>
                        <mtd columnalign="right">
                            <mo><?= $symbolMathML ?></mo>
                            <mn><?= htmlspecialchars($numbers[1]) ?></mn>
                        </mtd>
                    </mtr>
                </mtable>
            </math>
        </div>
        <input type="text" 
               name="answer[<?= $q['id'] ?>]" 
               class="answer-blank" 
               placeholder=""
               autocomplete="off">
    <?php else: ?>
        <!-- Inline layout -->
        <span class="math-inline">
            <?= htmlspecialchars($numbers[0]) ?>
            <?= " " . $symbolMathML . " " ?>
            <?= htmlspecialchars($numbers[1]) ?> =
        </span>
        <input type="text" 
               name="answer[<?= $q['id'] ?>]" 
               class="answer-blank" 
               placeholder=""
               autocomplete="off">
    <?php endif; ?>
</div>

<?php endif; ?>