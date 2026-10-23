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
require __DIR__ . '/partials/header.php';

$passwordField = static function (string $id, string $label, string $autocomplete, string $help = ''): void { ?>
    <div class="mb-3">
        <label for="<?= e($id) ?>" class="form-label"><?= e($label) ?> <span class="req">*</span></label>
        <div class="input-group">
            <input type="password" class="form-control" id="<?= e($id) ?>" name="<?= e($id) ?>" autocomplete="<?= e($autocomplete) ?>" required>
            <button class="btn btn-outline-secondary" type="button" data-toggle-password="<?= e($id) ?>" aria-label="Show password">
                <span class="btn-icon icon-show"><?= icon('eye') ?></span>
                <span class="btn-icon icon-hide d-none"><?= icon('eye-off') ?></span>
            </button>
        </div>
        <?php if ($help !== ''): ?><div class="form-text"><?= e($help) ?></div><?php endif; ?>
    </div>
<?php };
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <h1 class="h4 fw-bold text-white mb-1">Change Password</h1>
                <p class="text-muted small mb-4"><?= e($admin['email']) ?></p>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
                <?php endforeach; ?>
                <form method="post" action="change-password.php" novalidate>
                    <?= csrf_field() ?>
                    <?php
                    $passwordField('current_password', 'Current Password', 'current-password');
                    $passwordField('new_password', 'New Password', 'new-password', 'At least 8 characters, with letters and numbers.');
                    $passwordField('confirm_password', 'Confirm New Password', 'new-password');
                    ?>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Update Password</button>
                        <?php if (!$admin['must_change_password']): ?>
                            <a href="index.php" class="btn btn-outline-secondary px-4">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
