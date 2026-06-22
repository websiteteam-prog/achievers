<?php
/**
 * Shared bootstrap for every branch_admin page.
 *  - starts the session (once)
 *  - opens the DB connection ($conn)
 *  - provides the branch-admin auth guard, flash messages and small helpers
 *
 * Include this BEFORE printing any HTML so header() redirects keep working.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db_config.php';

/** Is the current visitor a logged-in branch admin? */
function is_branch_admin(): bool
{
    return !empty($_SESSION['admin_logged_in']) && ($_SESSION['role'] ?? '') === 'branch_admin';
}

/** Redirect to the login page unless a branch admin is logged in. */
function require_branch_admin(): void
{
    if (!is_branch_admin()) {
        header('Location: ../login.php');
        exit();
    }
}

/** Queue a one-time flash message rendered by flash_render() on the next page. */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Print and clear any queued flash message as a Bootstrap alert. */
function flash_render(): void
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $allowed = ['success', 'danger', 'warning', 'info'];
    $type    = in_array($flash['type'], $allowed, true) ? $flash['type'] : 'info';
    $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
        . $message
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
        . '</div>';
}

/** Store a flash message and redirect back to the previous same-site page (or a safe fallback). */
function redirect_with_flash(string $type, string $message, string $fallback = 'branch_manage_users.php'): void
{
    flash_set($type, $message);

    $target = $fallback;
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        if (($ref['host'] ?? '') === ($_SERVER['HTTP_HOST'] ?? '')) {
            $target = $_SERVER['HTTP_REFERER'];
        }
    }

    header('Location: ' . $target);
    exit();
}

/** Send a JSON response and stop (for AJAX endpoints). */
function json_response(bool $success, string $message): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

/** Escape a value for safe HTML output. */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
