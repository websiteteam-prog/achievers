<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/*
 * Admin authentication: password_hash()/password_verify(), session ID
 * regeneration on login, account lockout after repeated failures and an
 * inactivity timeout.
 */

// Used when the username does not exist so the response time stays the same.
const ADMIN_DUMMY_HASH = '$2y$10$NaHRcxNYdpRKlU8z/mppSuU6FM0Wt9LLKuQRKAGqkMbXTNbItWohW';

/** @return array{0:bool,1:string} [success, error message] */
function admin_attempt_login(string $username, string $password): array
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    $now = now();

    if ($user && $user['locked_until'] !== null && strtotime($user['locked_until']) > $now->getTimestamp()) {
        $minutes = (int) ceil((strtotime($user['locked_until']) - $now->getTimestamp()) / 60);
        return [false, "Too many failed attempts. Please try again after {$minutes} minute(s)."];
    }

    if (!password_verify($password, $user['password_hash'] ?? ADMIN_DUMMY_HASH) || !$user) {
        if ($user) {
            $max = (int) config('admin.max_login_attempts', 5);
            if ((int) $user['failed_attempts'] + 1 >= $max) {
                $lockedUntil = $now->modify('+' . (int) config('admin.lockout_minutes', 15) . ' minutes');
                $pdo->prepare('UPDATE admin_users SET failed_attempts = 0, locked_until = ? WHERE id = ?')
                    ->execute([db_datetime($lockedUntil), $user['id']]);
            } else {
                $pdo->prepare('UPDATE admin_users SET failed_attempts = failed_attempts + 1 WHERE id = ?')
                    ->execute([$user['id']]);
            }
        }
        return [false, 'Invalid username or password.'];
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }
    $pdo->prepare('UPDATE admin_users SET failed_attempts = 0, locked_until = NULL, last_login_at = ? WHERE id = ?')
        ->execute([db_datetime($now), $user['id']]);

    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'                   => (int) $user['id'],
        'username'             => $user['username'],
        'name'                 => $user['full_name'] ?: $user['username'],
        'must_change_password' => (bool) $user['must_change_password'],
    ];
    $_SESSION['admin_last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    return [true, ''];
}

/** Logged-in admin or null (also enforces the inactivity timeout). */
function current_admin(): ?array
{
    if (empty($_SESSION['admin'])) {
        return null;
    }
    $timeout = (int) config('admin.session_timeout_seconds', 1800);
    if (time() - (int) ($_SESSION['admin_last_activity'] ?? 0) > $timeout) {
        admin_logout();
        flash('warning', 'Your session expired. Please log in again.');
        return null;
    }
    $_SESSION['admin_last_activity'] = time();
    return $_SESSION['admin'];
}

/** Use at the top of every admin page. Redirects to the login page when needed. */
function require_admin(bool $passwordChangePage = false): array
{
    $admin = current_admin();
    if ($admin === null) {
        redirect('login.php');
    }
    if ($admin['must_change_password'] && !$passwordChangePage) {
        flash('warning', 'Please set a new password before continuing.');
        redirect('change-password.php');
    }
    return $admin;
}

function admin_logout(): void
{
    unset($_SESSION['admin'], $_SESSION['admin_last_activity']);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
