<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/*
 * uEngage SMS gateway (Airtel DLT) client.
 *
 * The endpoint, HTTP method, authentication and parameter names all come from
 * the 'sms' section of config/config.php and must be copied from the uEngage
 * API document - nothing is hard-coded or guessed here.
 */

/**
 * Send the OTP SMS.
 *
 * @return array{ok:bool, reason:?string, response:string}
 *   reason: null on success, 'not_configured' or 'failed'
 */
function sms_send_otp(string $mobile, string $otp): array
{
    $cfg = config('sms', []);

    if (($cfg['driver'] ?? 'uengage') === 'log') {
        if (config('app.env') !== 'local') {
            app_log('sms', 'Refused: the "log" SMS driver only works when app.env = local.');
            return ['ok' => false, 'reason' => 'not_configured', 'response' => ''];
        }
        app_log('otp-local', "OTP for {$mobile}: {$otp}");
        return ['ok' => true, 'reason' => null, 'response' => 'logged'];
    }

    $missing = sms_missing_config($cfg);
    if ($missing) {
        app_log('sms', 'uEngage SMS is not configured. Missing/invalid: ' . implode(', ', $missing));
        return ['ok' => false, 'reason' => 'not_configured', 'response' => ''];
    }

    $names  = $cfg['param_names'];
    $params = array_merge($cfg['auth_params'] ?? [], $cfg['extra_params'] ?? []);
    $params[$names['mobile']]  = ($cfg['mobile_prefix'] ?? '') . $mobile;
    $params[$names['message']] = sms_build_message($cfg, $otp);
    foreach (['sender_id', 'template_id', 'pe_id'] as $key) {
        if ($names[$key] !== null) {
            $params[$names[$key]] = $cfg[$key];
        }
    }

    [$httpStatus, $body, $curlError] = sms_http_request($cfg, $params);

    $successMatch = (string) ($cfg['success_match'] ?? '');
    $ok = $curlError === ''
        && $httpStatus >= 200 && $httpStatus < 300
        && ($successMatch === '' || stripos($body, $successMatch) !== false);

    // Never write the OTP to logs, even if the provider echoes the message back.
    $safeBody = str_replace($otp, str_repeat('*', strlen($otp)), mb_substr(trim($body), 0, 480));
    app_log('sms', sprintf(
        '%s to=%s http=%d%s response=%s',
        $ok ? 'SENT' : 'FAILED',
        mask_mobile($mobile),
        $httpStatus,
        $curlError !== '' ? ' curl_error="' . $curlError . '"' : '',
        $safeBody
    ));

    return ['ok' => $ok, 'reason' => $ok ? null : 'failed', 'response' => $safeBody];
}

/** @return list<string> config keys that are missing or invalid */
function sms_missing_config(array $cfg): array
{
    $missing = [];

    $url = (string) ($cfg['api_url'] ?? '');
    if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
        $missing[] = 'api_url';
    }
    if (!in_array(strtoupper((string) ($cfg['method'] ?? '')), ['GET', 'POST'], true)) {
        $missing[] = 'method';
    }
    foreach (['sender_id', 'template_id', 'pe_id', 'message_template'] as $key) {
        if (trim((string) ($cfg[$key] ?? '')) === '') {
            $missing[] = $key;
        }
    }

    $names = $cfg['param_names'] ?? [];
    foreach (['mobile', 'message'] as $key) {
        if (trim((string) ($names[$key] ?? '')) === '') {
            $missing[] = "param_names.{$key}";
        }
    }
    foreach (['sender_id', 'template_id', 'pe_id'] as $key) {
        // null = deliberately not sent (bound to the account); '' = not filled in yet
        if (!array_key_exists($key, $names) || ($names[$key] !== null && trim((string) $names[$key]) === '')) {
            $missing[] = "param_names.{$key}";
        }
    }

    $placeholders = preg_match_all('/\{#var#\}/i', (string) ($cfg['message_template'] ?? ''));
    $vars = $cfg['template_vars'] ?? [];
    if ($placeholders === 0 || $placeholders !== count($vars) || !in_array('otp', $vars, true)) {
        $missing[] = 'template_vars (must list one value per {#var#} and include "otp")';
    }

    return $missing;
}

/** Fill the DLT template's {#var#} placeholders in order; fixed text stays untouched. */
function sms_build_message(array $cfg, string $otp): string
{
    $values = array_map(static fn ($var): string => match ($var) {
        'otp'            => $otp,
        'expiry_minutes' => (string) max(1, intdiv((int) config('otp.expiry_seconds', 300), 60)),
        default          => (string) $var,
    }, array_values($cfg['template_vars']));

    $i = 0;
    return preg_replace_callback('/\{#var#\}/i', static function () use (&$i, $values): string {
        return $values[$i++];
    }, (string) $cfg['message_template']);
}

/** @return array{0:int,1:string,2:string} [HTTP status, response body, cURL error] */
function sms_http_request(array $cfg, array $params): array
{
    $ch = curl_init();
    $headers = $cfg['headers'] ?? [];
    $url = (string) $cfg['api_url'];

    if (strtoupper((string) $cfg['method']) === 'POST') {
        if (($cfg['post_format'] ?? 'form') === 'json') {
            $payload = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        } else {
            $payload = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    } else {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => (int) ($cfg['timeout'] ?? 15),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $body   = curl_exec($ch);
    $error  = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    return [$status, is_string($body) ? $body : '', $error];
}
