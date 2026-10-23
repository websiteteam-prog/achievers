<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/** Read a config value using dot notation, e.g. config('otp.length'). */
function config(string $key, mixed $default = null): mixed
{
    $value = $GLOBALS['__app_config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('CAMPAIGNSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data: blob:; media-src 'self' blob:; connect-src 'self'; object-src 'none'; "
        . "base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
}

/* ---------------------------------------------------------------- CSRF */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* ----------------------------------------------------------- Responses */

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
    exit;
}

/** Common checks for every JSON endpoint: POST only + valid CSRF token. */
function api_guard(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
    }
    if (!csrf_valid()) {
        json_response([
            'ok'              => false,
            'message'         => 'Your session has expired. Please refresh the page and try again.',
            'session_expired' => true,
        ], 403);
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** @return list<array{type:string,message:string}> */
function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/* ---------------------------------------------------------- Utilities */

/**
 * Visitor IP used for OTP rate limits. Behind Cloudflare / a load balancer set
 * app.client_ip_header (e.g. 'HTTP_CF_CONNECTING_IP'); otherwise REMOTE_ADDR is used.
 */
function client_ip(): string
{
    $header = (string) config('app.client_ip_header', '');
    $candidates = [];
    if ($header !== '' && !empty($_SERVER[$header])) {
        $candidates[] = trim(explode(',', (string) $_SERVER[$header])[0]);
    }
    $candidates[] = $_SERVER['REMOTE_ADDR'] ?? '';
    foreach ($candidates as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return '0.0.0.0';
}

function now(): DateTimeImmutable
{
    return new DateTimeImmutable('now');
}

function db_datetime(DateTimeImmutable $date): string
{
    return $date->format('Y-m-d H:i:s');
}

/** Parse a strict Y-m-d date string; null when invalid. */
function parse_date(mixed $value): ?DateTimeImmutable
{
    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return ($date && $date->format('Y-m-d') === $value) ? $date : null;
}

function format_date(?string $value, string $format = 'd-m-Y'): string
{
    if ($value === null || $value === '') {
        return '';
    }
    return (new DateTimeImmutable($value))->format($format);
}

/** Returns a 10-digit Indian mobile number or null. Accepts +91 / 0 prefixes. */
function normalize_mobile(mixed $value): ?string
{
    $value = trim((string) $value);
    if ($value === '' || preg_match('/[^\d\s+\-]/', $value)) {
        return null;
    }
    $digits = preg_replace('/\D+/', '', $value);
    if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
        $digits = substr($digits, 2);
    } elseif (strlen($digits) === 11 && $digits[0] === '0') {
        $digits = substr($digits, 1);
    }
    return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
}

/** 15 digits, not all the same digit, and (optionally) a valid Luhn check digit. */
function valid_imei(string $imei, bool $luhnCheck): bool
{
    if (!preg_match('/^\d{15}$/', $imei) || preg_match('/^(\d)\1{14}$/', $imei)) {
        return false;
    }
    if (!$luhnCheck) {
        return true;
    }
    $sum = 0;
    for ($i = 0; $i < 15; $i++) {
        $digit = (int) $imei[$i];
        if ($i % 2 === 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    return $sum % 10 === 0;
}

function mask_mobile(string $mobile): string
{
    return substr($mobile, 0, 2) . str_repeat('X', max(0, strlen($mobile) - 4)) . substr($mobile, -2);
}

function app_log(string $channel, string $message): void
{
    $file = APP_ROOT . '/storage/logs/' . preg_replace('/[^a-z0-9_-]/i', '', $channel) . '.log';
    @file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/** Inline SVG icons (stroke style, inherit the text colour). */
function icon(string $name): string
{
    $paths = [
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'lock'      => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'eye'       => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'   => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>'
                     . '<path d="M6.61 6.61A13.53 13.53 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/>',
        'barcode'   => '<path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8v8M10 8v8M13 8v8M17 8v8"/>',
        'download'  => '<path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" '
        . 'stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
}
