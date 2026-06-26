<?php
/**
 * Pie chart image generator for pie_chart_table questions.
 *
 * CLI usage:
 *   php generate_pie_chart.php '[{"label":"Baseball","value":5},{"label":"Soccer","value":30}]' output.png
 *
 * Or include and call generate_pie_chart_image($items, $outPath) from admin code
 * (e.g. teacher_question_pages/add_question.php) right after building $payload['items'],
 * then store the returned path in question_image.
 */

function generate_pie_chart_image(array $items, string $outPath, int $size = 600): string
{
    $colors = [
        [255, 99, 132], [54, 162, 235], [255, 206, 86], [75, 192, 192],
        [153, 102, 255], [255, 159, 64], [199, 199, 199], [83, 102, 255],
    ];

    $total = 0;
    foreach ($items as $row) {
        $total += (float)$row['value'];
    }
    if ($total <= 0) {
        throw new InvalidArgumentException('Pie chart items must have a positive total value.');
    }

    $img = imagecreatetruecolor($size, $size);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);

    $cx = $size / 2;
    $cy = $size / 2;
    $radius = $size / 2 - 40;

    $startAngle = 0;
    $legendY = 10;

    foreach ($items as $i => $row) {
        $value = (float)$row['value'];
        $sweep = ($value / $total) * 360;
        $endAngle = $startAngle + $sweep;

        [$r, $g, $b] = $colors[$i % count($colors)];
        $color = imagecolorallocate($img, $r, $g, $b);

        imagefilledarc(
            $img, $cx, $cy, $radius * 2, $radius * 2,
            (int)round($startAngle), (int)round($endAngle),
            $color, IMG_ARC_PIE
        );

        // legend swatch + label
        imagefilledrectangle($img, $size - 150, $legendY, $size - 135, $legendY + 12, $color);
        imagestring($img, 3, $size - 130, $legendY, $row['label'] . ' (' . $row['value'] . ')', imagecolorallocate($img, 0, 0, 0));
        $legendY += 20;

        $startAngle = $endAngle;
    }

    if (!is_dir(dirname($outPath))) {
        mkdir(dirname($outPath), 0777, true);
    }

    imagepng($img, $outPath);
    imagedestroy($img);

    return $outPath;
}

/**
 * Helper: derive the matching "angles" / totals for correct_answer
 * straight from the same items array, so the chart and the answer key
 * are always generated from one source of truth.
 */
function build_pie_chart_correct_answer(array $items): array
{
    $total_value = 0;
    foreach ($items as $row) {
        $total_value += (float)$row['value'];
    }

    $angles = [];
    foreach ($items as $row) {
        $angles[] = (string)round(((float)$row['value'] / $total_value) * 360);
    }

    return [
        'angles' => $angles,
        'total_value' => (string)$total_value,
        'total_angle' => '360',
    ];
}

if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $items = json_decode($argv[1], true);
    $out = $argv[2] ?? __DIR__ . '/../../../templates/images/pie_chart_' . time() . '.png';
    generate_pie_chart_image($items, $out);
    echo "Saved: $out\n";
    echo "correct_answer: " . json_encode(build_pie_chart_correct_answer($items)) . "\n";
}
