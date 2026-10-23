<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

if (current_admin() !== null) {
    redirect('index.php');
}

$error = '';
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($username === '' || $password === '') {
        $error = 'Please enter your username and password.';
    } else {
        [$ok, $error] = admin_attempt_login($username, $password);
        if ($ok) {
            redirect('index.php');
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login - Campaign Admin</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="../assets/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body class="admin-page">
<div class="container login-wrap d-flex align-items-center justify-content-center py-5">
    <div class="card border-0 shadow-sm w-100 login-card">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-1 text-center">Campaign Admin</h1>
            <p class="text-muted small text-center mb-4">Sign in to manage registrations</p>

            <?php foreach (take_flashes() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> py-2 small"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= e($username) ?>"
                           autocomplete="username" required autofocus>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                           autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
