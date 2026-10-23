<?php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
 * Excel (.xlsx) export of ALL registrations matching the current date filter
 * (same filter as the admin list, no pagination). Admins only.
 */

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';
require APP_ROOT . '/includes/registrations.php';

require_admin();

if (!is_file(APP_ROOT . '/vendor/autoload.php')) {
    flash('danger', 'Excel export needs PhpSpreadsheet. Run "composer install" in the project folder (see README).');
    redirect('index.php');
}
require APP_ROOT . '/vendor/autoload.php';

@set_time_limit(300);
@ini_set('memory_limit', '512M');

$filters = registration_filters($_GET);
[$where, $params] = registration_where($filters);
$stmt = db()->prepare(registration_select_sql() . " {$where} ORDER BY r.id ASC");
$stmt->execute($params);

// [header, width, type]  type: text | int | date | datetime
$columns = [
    ['S. No.',             8,  'int'],
    ['Registration No.',   18, 'text'],
    ['Customer Name',      28, 'text'],
    ['Mobile Number',      16, 'text'],
    ['Email ID',           34, 'text'],
    ['IMEI Number',        20, 'text'],
    ['Date of Purchase',   16, 'date'],
    ['Store Name',         34, 'text'],
    ['Store Code',         16, 'text'],
    ['City',               18, 'text'],
    ['Registration Date & Time', 24, 'datetime'],
];
$lastCol = chr(ord('A') + count($columns) - 1);

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('Campaign Admin')
    ->setTitle(campaign_settings()['campaign_name'] . ' - Registrations')
    ->setSubject('Campaign registrations export');
$spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registrations');

foreach ($columns as $i => [$header, $width]) {
    $col = chr(ord('A') + $i);
    $sheet->setCellValueExplicit($col . '1', $header, DataType::TYPE_STRING);
    $sheet->getColumnDimension($col)->setWidth($width);
}

$rowNum = 1;
while ($r = $stmt->fetch()) {
    $rowNum++;
    $values = [
        $rowNum - 1,
        registration_number((int) $r['id']),
        $r['name'],
        $r['mobile'],
        $r['email'],
        $r['imei'],
        $r['purchase_date'],
        $r['store_name'],
        (string) ($r['store_code'] ?? ''),
        $r['city_name'],
        $r['created_at'],
    ];
    foreach ($columns as $i => [, , $type]) {
        $cell = chr(ord('A') + $i) . $rowNum;
        $value = $values[$i];
        match ($type) {
            'int'   => $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_NUMERIC),
            // Real Excel dates so they sort and filter correctly
            'date', 'datetime' => $sheet->setCellValue($cell, ExcelDate::PHPToExcel(new DateTimeImmutable($value))),
            // Explicit strings: keeps mobile/IMEI as text (no 4.9E+14) and stops formula injection
            default => $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING),
        };
    }
}
$lastRow = max(1, $rowNum);

// Header styling
$sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1F4E79']]],
]);
$sheet->getRowDimension(1)->setRowHeight(30);

if ($lastRow > 1) {
    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
    ]);
    foreach ($columns as $i => [, , $type]) {
        $range = chr(ord('A') + $i) . "2:" . chr(ord('A') + $i) . $lastRow;
        $style = $sheet->getStyle($range);
        match ($type) {
            'int'      => $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER),
            'date'     => $style->getNumberFormat()->setFormatCode('dd-mm-yyyy'),
            'datetime' => $style->getNumberFormat()->setFormatCode('dd-mm-yyyy hh:mm AM/PM'),
            default    => $style->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT),
        };
        if ($type === 'date' || $type === 'datetime') {
            $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
    }
    // Light zebra striping for readability
    for ($row = 3; $row <= $lastRow; $row += 2) {
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F6FB');
    }
}

$sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
$sheet->freezePane('A2');
$sheet->setSelectedCell('A2');

// Printing: landscape, all columns on one page width, header row on every page
$sheet->getPageSetup()
    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
    ->setPaperSize(PageSetup::PAPERSIZE_A4)
    ->setFitToWidth(1)
    ->setFitToHeight(0)
    ->setRowsToRepeatAtTopByStartAndEnd(1, 1);

$filename = 'campaign-registrations-' . now()->format('Y-m-d-His') . '.xlsx';

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, max-age=0');

(new Xlsx($spreadsheet))->save('php://output');
exit;
