<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

if (current_admin() !== null) {
    redirect('index.php');
}

$error = '';
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        [$ok, $error] = admin_attempt_login($email, $password);
        if ($ok) {
            redirect('index.php');
        }
    }
}
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#110f2b">
    <title>Admin Login - Campaign Admin</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<main class="center-screen">
    <div class="card login-card">
        <div class="card-body">
            <div class="icon-badge icon-badge-sm"><?= icon('lock') ?></div>
            <h1 class="h3 fw-bold text-center text-white mb-1">Admin Login</h1>
            <p class="text-muted text-center mb-4">Sign in to view registrations</p>

            <?php foreach (take_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> py-2 small"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= icon('mail') ?></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($email) ?>"
                               placeholder="admin@example.com" autocomplete="username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter password" autocomplete="current-password" required>
                        <button class="btn btn-outline-secondary" type="button" data-toggle-password="password" aria-label="Show password">
                            <span class="btn-icon icon-show"><?= icon('eye') ?></span>
                            <span class="btn-icon icon-hide d-none"><?= icon('eye-off') ?></span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
            </form>
        </div>
    </div>
</main>
<script src="../assets/js/admin.js"></script>
</body>
</html>
