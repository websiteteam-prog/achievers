<?php
defined('APP_ROOT') || exit;
/**
 * @var array  $admin
 * @var string $pageTitle
 * @var string $activeNav  dashboard | registrations | settings | password
 */
$navItems = [
    'dashboard'     => ['index.php', 'Dashboard'],
    'registrations' => ['registrations.php', 'Registrations'],
    'settings'      => ['settings.php', 'Campaign Settings'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> - Campaign Admin</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="admin-page">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand fw-semibold" href="index.php">Campaign Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
                aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($navItems as $key => [$href, $label]): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $activeNav === $key ? ' active' : '' ?>" href="<?= e($href) ?>"
                           <?= $activeNav === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex flex-wrap align-items-center gap-2 py-2 py-lg-0">
                <span class="navbar-text small me-1">Signed in as <?= e($admin['name']) ?></span>
                <a class="btn btn-sm btn-outline-light<?= $activeNav === 'password' ? ' active' : '' ?>" href="change-password.php">Change Password</a>
                <form method="post" action="logout.php" class="m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-light">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
<main class="container-fluid px-3 px-lg-4 py-4">
    <?php foreach (take_flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
