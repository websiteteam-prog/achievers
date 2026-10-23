<?php
defined('APP_ROOT') || exit;
/**
 * @var array  $admin
 * @var string $pageTitle
 */
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#110f2b">
    <title><?= e($pageTitle) ?> - Campaign Admin</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<nav class="navbar admin-nav sticky-top">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand" href="index.php"><span class="brand-dot"></span>Registrations</a>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="small text-muted d-none d-md-inline me-1"><?= e($admin['email']) ?></span>
            <a class="btn btn-sm btn-outline-secondary" href="change-password.php">Change Password</a>
            <form method="post" action="logout.php" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-primary">Logout</button>
            </form>
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
