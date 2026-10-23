<?php
declare(strict_types=1);

/*
 * Admin dashboard: the registrations list with a date filter and Excel download.
 */

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';
require APP_ROOT . '/includes/registrations.php';

$admin = require_admin();
$pdo = db();

$filters = registration_filters($_GET);
[$where, $params] = registration_where($filters);
$filterQuery = registration_filter_query($filters);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations r {$where}");
$stmt->execute($params);
$totalMatching = (int) $stmt->fetchColumn();

$perPage = max(1, (int) config('admin.per_page', 25));
$pages = max(1, (int) ceil($totalMatching / $perPage));
$page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(registration_select_sql() . " {$where} ORDER BY r.id DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageUrl = static fn (int $n): string => 'index.php?' . http_build_query($filterQuery + ['page' => $n]);
$exportUrl = 'export.php' . ($filterQuery ? '?' . http_build_query($filterQuery) : '');

$pageTitle = 'Registrations';
require __DIR__ . '/partials/header.php';
?>
<?php foreach ($filters['errors'] as $error): ?>
    <div class="alert alert-warning py-2"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="card mb-4">
    <div class="card-body p-4">
        <form method="get" action="index.php" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-3">
                <label for="date_type" class="form-label small">Filter by</label>
                <select class="form-select" id="date_type" name="date_type">
                    <option value="registration" <?= $filters['date_type'] === 'registration' ? 'selected' : '' ?>>Registration Date</option>
                    <option value="purchase" <?= $filters['date_type'] === 'purchase' ? 'selected' : '' ?>>Purchase Date</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label for="start_date" class="form-label small">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= e($filters['start_date']) ?>">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label for="end_date" class="form-label small">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= e($filters['end_date']) ?>">
            </div>
            <div class="col-12 col-xl-5 d-flex flex-wrap gap-2 justify-content-xl-end">
                <button type="submit" class="btn btn-primary btn-field px-4">Apply Filters</button>
                <a href="index.php" class="btn btn-outline-secondary btn-field px-4">Reset Filters</a>
                <a href="<?= e($exportUrl) ?>" class="btn btn-success btn-field px-4 d-inline-flex align-items-center gap-2">
                    <span class="btn-icon"><?= icon('download') ?></span> Download Excel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 px-4">
        <span class="text-muted">Total registrations: <span class="total-count ms-1"><?= number_format($totalMatching) ?></span></span>
        <?php if ($totalMatching > 0): ?>
            <span class="small text-muted">Showing <?= number_format($offset + 1) ?>-<?= number_format($offset + count($rows)) ?></span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-registrations mb-0">
            <thead>
            <tr>
                <th class="ps-4">Reg. No.</th>
                <th>Name</th>
                <th>Mobile Number</th>
                <th>Email ID</th>
                <th>IMEI Number</th>
                <th>Purchase Date</th>
                <th>Store Name</th>
                <th>Store Code</th>
                <th>City</th>
                <th class="pe-4">Registered On</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="ps-4"><?= e(registration_number((int) $row['id'])) ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['mobile']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['imei']) ?></td>
                    <td><?= e(format_date($row['purchase_date'])) ?></td>
                    <td><?= e($row['store_name']) ?></td>
                    <td><?= e($row['store_code'] ?? '-') ?></td>
                    <td><?= e($row['city_name']) ?></td>
                    <td class="pe-4"><?= e(format_date($row['created_at'], 'd-m-Y h:i A')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="10" class="text-center text-muted py-5">No registrations found for the selected dates.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
        <div class="card-footer py-3">
            <nav aria-label="Registrations pages">
                <ul class="pagination pagination-sm flex-wrap mb-0 justify-content-center">
                    <li class="page-item<?= $page <= 1 ? ' disabled' : '' ?>">
                        <a class="page-link" href="<?= e($pageUrl(max(1, $page - 1))) ?>">Previous</a>
                    </li>
                    <?php
                    $window = array_unique(array_filter(
                        [1, $page - 2, $page - 1, $page, $page + 1, $page + 2, $pages],
                        static fn (int $n): bool => $n >= 1 && $n <= $pages
                    ));
                    sort($window);
                    $previous = 0;
                    foreach ($window as $n):
                        if ($n - $previous > 1): ?>
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        <?php endif; ?>
                        <li class="page-item<?= $n === $page ? ' active' : '' ?>">
                            <a class="page-link" href="<?= e($pageUrl($n)) ?>"><?= $n ?></a>
                        </li>
                        <?php $previous = $n;
                    endforeach; ?>
                    <li class="page-item<?= $page >= $pages ? ' disabled' : '' ?>">
                        <a class="page-link" href="<?= e($pageUrl(min($pages, $page + 1))) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
