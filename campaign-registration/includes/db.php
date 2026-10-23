<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/** Shared PDO connection (real prepared statements, exceptions on error). */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c = config('db');
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $c['host'],
        (int) ($c['port'] ?? 3306),
        $c['name'],
        $c['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Keep MySQL date functions in the same timezone as PHP.
    $pdo->exec('SET time_zone = ' . $pdo->quote(now()->format('P')));

    return $pdo;
}

/** True when the exception is a MySQL duplicate-key (unique constraint) error. */
function is_duplicate_key_error(PDOException $e): bool
{
    return ($e->errorInfo[1] ?? null) === 1062;
}
