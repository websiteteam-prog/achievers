<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

$admin = require_admin(true);
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $current = (string) ($_POST['current_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $stmt = db()->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
    $stmt->execute([$admin['id']]);
    $hash = (string) $stmt->fetchColumn();

    if (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif (!password_verify($current, $hash)) {
        $errors[] = 'Current password is incorrect.';
    } elseif (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
        $errors[] = 'New password must be at least 8 characters and contain letters and numbers.';
    } elseif ($new !== $confirm) {
        $errors[] = 'New password and confirmation do not match.';
    } elseif (password_verify($new, $hash)) {
        $errors[] = 'New password must be different from the current password.';
    } else {
        db()->prepare('UPDATE admin_users SET password_hash = ?, must_change_password = 0 WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        session_regenerate_id(true);
        $_SESSION['admin']['must_change_password'] = false;
        flash('success', 'Password updated successfully.');
        redirect('index.php');
    }
}

$pageTitle = 'Change Password';
$activeNav = 'password';
require __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h5 mb-3">Change Password</h1>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post" action="change-password.php" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="req">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="req">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
                        <div class="form-text">At least 8 characters, with letters and numbers.</div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password <span class="req">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
