<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && csrf_valid()) {
    admin_logout();
    flash('success', 'You have been logged out.');
    redirect('login.php');
}

redirect('index.php');
