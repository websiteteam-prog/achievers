<?php
declare(strict_types=1);

/*
 * Loaded first by every page and API endpoint.
 * Pages that answer with JSON define('IS_API', true) before including this file.
 */

define('APP_ROOT', dirname(__DIR__));

if (!is_file(APP_ROOT . '/config/config.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Setup required: copy config/config.sample.php to config/config.php and fill in your settings.\n");
}
$GLOBALS['__app_config'] = require APP_ROOT . '/config/config.php';

require APP_ROOT . '/includes/helpers.php';
require APP_ROOT . '/includes/db.php';
require APP_ROOT . '/includes/settings.php';

date_default_timezone_set((string) config('app.timezone', 'Asia/Kolkata'));

error_reporting(E_ALL);
ini_set('display_errors', config('app.env') === 'local' ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/php-errors.log');

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (defined('IS_API')) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(['ok' => false, 'message' => 'Something went wrong. Please try again in a few minutes.']);
        return;
    }
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>'
        . '<div style="font-family:sans-serif;max-width:480px;margin:15vh auto;text-align:center">'
        . '<h1 style="font-size:1.4rem">Something went wrong</h1>'
        . '<p>Please try again in a few minutes.</p></div>';
    if (config('app.env') === 'local') {
        echo '<pre style="white-space:pre-wrap">' . e((string) $e) . '</pre>';
    }
});

send_security_headers();
start_secure_session();
