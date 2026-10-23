<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

$admin = require_admin();
$pdo = db();

$total = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();

$todayStart = now()->setTime(0, 0);
$stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE created_at >= ? AND created_at < ?');
$stmt->execute([db_datetime($todayStart), db_datetime($todayStart->modify('+1 day'))]);
$today = (int) $stmt->fetchColumn();

$byStore = $pdo->query(
    'SELECT s.id, s.name, s.code, COUNT(r.id) AS total
       FROM stores s
       LEFT JOIN registrations r ON r.store_id = s.id
      GROUP BY s.id, s.name, s.code
     HAVING total > 0 OR MAX(s.is_active) = 1
      ORDER BY total DESC, s.name'
)->fetchAll();

$byCity = $pdo->query(
    'SELECT c.id, c.name, COUNT(r.id) AS total
       FROM cities c
       LEFT JOIN registrations r ON r.city_id = c.id
      GROUP BY c.id, c.name
     HAVING total > 0 OR MAX(c.is_active) = 1
      ORDER BY total DESC, c.name'
)->fetchAll();

$settings = campaign_settings();
$state = campaign_state($settings);
$stateLabel = ['open' => ['Active', 'success'], 'upcoming' => ['Starts soon', 'info'], 'inactive' => ['Inactive', 'secondary']][$state];

$share = static fn (int $count): string => $total > 0 ? number_format($count * 100 / $total, 1) . '%' : '0%';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/partials/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h4 mb-0">Dashboard</h1>
    <a class="btn btn-primary btn-sm" href="registrations.php">View Registrations</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Total Registrations</div>
                <div class="stat-value text-primary"><?= number_format($total) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Today's Registrations</div>
                <div class="stat-value text-success"><?= number_format($today) ?></div>
                <div class="small text-muted"><?= e(now()->format('d M Y')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Campaign</div>
                <div class="fw-semibold"><?= e($settings['campaign_name']) ?></div>
                <div class="small text-muted mb-1">
                    <?= e(format_date($settings['start_date'])) ?> to <?= e(format_date($settings['end_date'])) ?>
                </div>
                <span class="badge text-bg-<?= e($stateLabel[1]) ?>"><?= e($stateLabel[0]) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Registrations by Store</div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                    <tr><th class="ps-3">Store Name</th><th>Store Code</th><th class="text-end">Registrations</th><th class="text-end pe-3">Share</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($byStore as $row): ?>
                        <tr>
                            <td class="ps-3"><a href="registrations.php?store_id=<?= (int) $row['id'] ?>" class="text-decoration-none"><?= e($row['name']) ?></a></td>
                            <td><?= e($row['code'] ?? '-') ?></td>
                            <td class="text-end"><?= number_format((int) $row['total']) ?></td>
                            <td class="text-end pe-3"><?= $share((int) $row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$byStore): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">No stores found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">Registrations by City</div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                    <tr><th class="ps-3">City</th><th class="text-end">Registrations</th><th class="text-end pe-3">Share</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($byCity as $row): ?>
                        <tr>
                            <td class="ps-3"><a href="registrations.php?city_id=<?= (int) $row['id'] ?>" class="text-decoration-none"><?= e($row['name']) ?></a></td>
                            <td class="text-end"><?= number_format((int) $row['total']) ?></td>
                            <td class="text-end pe-3"><?= $share((int) $row['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$byCity): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No cities found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
