<?php
declare(strict_types=1);

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

$stores = $pdo->query('SELECT id, name, code FROM stores ORDER BY name')->fetchAll();
$cities = $pdo->query('SELECT id, name FROM cities ORDER BY name')->fetchAll();

$pageUrl = static fn (int $n): string => 'registrations.php?' . http_build_query($filterQuery + ['page' => $n]);
$exportUrl = 'export.php' . ($filterQuery ? '?' . http_build_query($filterQuery) : '');

$pageTitle = 'Registrations';
$activeNav = 'registrations';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Registrations</h1>
    <a class="btn btn-success" href="<?= e($exportUrl) ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="me-1"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
        Download Excel
    </a>
</div>

<?php foreach ($filters['errors'] as $error): ?>
    <div class="alert alert-warning py-2"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="get" action="registrations.php" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-4">
                <label for="q" class="form-label small fw-semibold">Search</label>
                <input type="search" class="form-control" id="q" name="q" value="<?= e($filters['q']) ?>"
                       placeholder="Name, mobile number or IMEI">
            </div>
            <div class="col-6 col-md-3 col-xl-4">
                <label for="store_id" class="form-label small fw-semibold">Store</label>
                <select class="form-select" id="store_id" name="store_id">
                    <option value="">All stores</option>
                    <?php foreach ($stores as $store): ?>
                        <option value="<?= (int) $store['id'] ?>" <?= $filters['store_id'] === (int) $store['id'] ? 'selected' : '' ?>>
                            <?= e($store['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-4">
                <label for="city_id" class="form-label small fw-semibold">City</label>
                <select class="form-select" id="city_id" name="city_id">
                    <option value="">All cities</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= (int) $city['id'] ?>" <?= $filters['city_id'] === (int) $city['id'] ? 'selected' : '' ?>>
                            <?= e($city['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-xl-3">
                <label for="date_type" class="form-label small fw-semibold">Date Filter</label>
                <select class="form-select" id="date_type" name="date_type">
                    <option value="registration" <?= $filters['date_type'] === 'registration' ? 'selected' : '' ?>>Registration Date</option>
                    <option value="purchase" <?= $filters['date_type'] === 'purchase' ? 'selected' : '' ?>>Purchase Date</option>
                </select>
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <label for="start_date" class="form-label small fw-semibold">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= e($filters['start_date']) ?>">
            </div>
            <div class="col-6 col-md-4 col-xl-3">
                <label for="end_date" class="form-label small fw-semibold">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= e($filters['end_date']) ?>">
            </div>
            <div class="col-12 col-xl-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Apply Filters</button>
                <a href="registrations.php" class="btn btn-outline-secondary flex-fill">Reset Filters</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span>Total matching records: <strong><?= number_format($totalMatching) ?></strong></span>
        <?php if ($totalMatching > 0): ?>
            <span class="small text-muted">Showing <?= number_format($offset + 1) ?>-<?= number_format($offset + count($rows)) ?></span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm table-registrations mb-0">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Reg. No.</th>
                <th>Name</th>
                <th>Mobile Number</th>
                <th>Email ID</th>
                <th>IMEI Number</th>
                <th>Purchase Date</th>
                <th>Store Name</th>
                <th>Store Code</th>
                <th>City</th>
                <th class="pe-3">Registered On</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="ps-3"><?= e(registration_number((int) $row['id'])) ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td><?= e($row['mobile']) ?></td>
                    <td><?= e($row['email']) ?></td>
                    <td><?= e($row['imei']) ?></td>
                    <td><?= e(format_date($row['purchase_date'])) ?></td>
                    <td><?= e($row['store_name']) ?></td>
                    <td><?= e($row['store_code'] ?? '-') ?></td>
                    <td><?= e($row['city_name']) ?></td>
                    <td class="pe-3"><?= e(format_date($row['created_at'], 'd-m-Y h:i A')) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="10" class="text-center text-muted py-5">No registrations match the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pages > 1): ?>
        <div class="card-footer bg-white">
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
