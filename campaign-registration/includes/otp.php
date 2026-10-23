<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

require_once APP_ROOT . '/includes/sms.php';

/*
 * OTP flow
 *  - otp_send():   rate limits -> new OTP row -> SMS via uEngage
 *  - otp_verify(): latest OTP for the mobile, expiry + attempt limit, marks verified
 *                  and remembers the verified mobile in the session
 *  - otp_verified_id(): used by the registration endpoint; the verified mobile
 *                  must equal the submitted mobile and must not be used yet
 *
 * Each function returns ['status' => HTTP code, 'body' => JSON payload].
 */

function otp_hash(string $mobile, string $otp): string
{
    $secret = (string) config('app.secret', '');
    if (strlen($secret) < 32) {
        throw new RuntimeException('Set APP_SECRET in .env to a random string of at least 32 characters.');
    }
    return hash_hmac('sha256', $mobile . '|' . $otp, $secret);
}

/**
 * DEV OTP mode: no SMS is sent and the fixed DEV_OTP from .env works.
 * OTP_MODE=auto (default) uses it only while SMS_API_URL is empty, so it switches
 * off by itself as soon as the live uEngage API is configured.
 */
function otp_dev_mode(): bool
{
    return match (config('otp.mode', 'auto')) {
        'dev'   => true,
        'live'  => false,
        default => trim((string) config('sms.api_url', '')) === '',
    };
}

function otp_dev_code(): string
{
    $code = (string) config('otp.dev_otp', '123456');
    $length = (int) config('otp.length', 6);
    if (!preg_match('/^\d{' . $length . '}$/', $code)) {
        throw new RuntimeException("DEV_OTP in .env must be exactly {$length} digits.");
    }
    return $code;
}

function otp_send(string $mobile): array
{
    $pdo = db();
    $cfg = config('otp');
    $now = now();
    $hourAgo = db_datetime($now->modify('-1 hour'));
    $ip = client_ip();

    // A new OTP request always cancels any earlier verification in this session.
    unset($_SESSION['otp_verified']);

    // Serialise requests for the same mobile so the cooldown cannot be raced.
    $lock = 'campaign_otp_' . $mobile;
    $stmt = $pdo->prepare('SELECT GET_LOCK(?, 10)');
    $stmt->execute([$lock]);
    if ((int) $stmt->fetchColumn() !== 1) {
        return otp_result(429, false, 'Please wait a moment and try again.');
    }

    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM otp_verifications WHERE ip_address = ? AND created_at >= ?');
        $stmt->execute([$ip, $hourAgo]);
        if ((int) $stmt->fetchColumn() >= (int) $cfg['max_sends_per_ip_per_hour']) {
            return otp_result(429, false, 'Too many OTP requests. Please try again after some time.');
        }

        $stmt = $pdo->prepare('SELECT 1 FROM registrations WHERE mobile = ? LIMIT 1');
        $stmt->execute([$mobile]);
        if ($stmt->fetchColumn()) {
            return otp_result(409, false, 'This mobile number is already registered.', ['field' => 'mobile']);
        }

        $stmt = $pdo->prepare(
            "SELECT created_at FROM otp_verifications
              WHERE mobile = ? AND send_status = 'sent' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$mobile]);
        $lastSent = $stmt->fetchColumn();
        if ($lastSent) {
            $wait = (int) $cfg['resend_cooldown_seconds'] - ($now->getTimestamp() - strtotime($lastSent));
            if ($wait > 0) {
                return otp_result(429, false, "Please wait {$wait} seconds before requesting a new OTP.", ['retry_after' => $wait]);
            }
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM otp_verifications WHERE mobile = ? AND created_at >= ?');
        $stmt->execute([$mobile, $hourAgo]);
        if ((int) $stmt->fetchColumn() >= (int) $cfg['max_sends_per_mobile_per_hour']) {
            return otp_result(429, false, 'OTP request limit reached for this mobile number. Please try again after an hour.');
        }

        $devMode = otp_dev_mode();
        $length = (int) $cfg['length'];
        $otp = $devMode
            ? otp_dev_code()
            : str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);

        $pdo->prepare(
            "INSERT INTO otp_verifications (mobile, otp_hash, send_status, expires_at, ip_address, created_at)
             VALUES (?, ?, 'pending', ?, ?, ?)"
        )->execute([
            $mobile,
            otp_hash($mobile, $otp),
            db_datetime($now->modify('+' . (int) $cfg['expiry_seconds'] . ' seconds')),
            $ip,
            db_datetime($now),
        ]);
        $otpId = (int) $pdo->lastInsertId();

        $sms = $devMode
            ? ['ok' => true, 'reason' => null, 'response' => 'DEV OTP mode - SMS not sent']
            : sms_send_otp($mobile, $otp);

        $pdo->prepare('UPDATE otp_verifications SET send_status = ?, provider_response = ? WHERE id = ?')
            ->execute([$sms['ok'] ? 'sent' : 'failed', mb_substr($sms['response'], 0, 500), $otpId]);
    } finally {
        $stmt = $pdo->prepare('SELECT RELEASE_LOCK(?)');
        $stmt->execute([$lock]);
        $stmt->fetchColumn();
    }

    if (!$sms['ok']) {
        return $sms['reason'] === 'not_configured'
            ? otp_result(503, false, 'OTP service is temporarily unavailable. Please try again later.')
            : otp_result(502, false, 'We could not send the OTP right now. Please try again in a few minutes.');
    }

    $message = $devMode
        ? "Dev mode: no SMS is sent. Use OTP {$otp}."
        : 'OTP sent to +91 ' . mask_mobile($mobile) . '.';

    return otp_result(200, true, $message, [
        'cooldown'   => (int) $cfg['resend_cooldown_seconds'],
        'expires_in' => (int) $cfg['expiry_seconds'],
        'dev_mode'   => $devMode,
    ]);
}

function otp_verify(string $mobile, string $otp): array
{
    $pdo = db();
    $cfg = config('otp');
    $maxAttempts = (int) $cfg['max_verify_attempts'];

    if (!preg_match('/^\d{' . (int) $cfg['length'] . '}$/', $otp)) {
        return otp_result(422, false, 'Please enter the ' . (int) $cfg['length'] . '-digit OTP.');
    }

    $stmt = $pdo->prepare(
        "SELECT * FROM otp_verifications WHERE mobile = ? AND send_status = 'sent' ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$mobile]);
    $row = $stmt->fetch();

    if (!$row) {
        return otp_result(400, false, 'Please request an OTP first.');
    }
    if ($row['verified_at'] !== null) {
        return otp_result(400, false, 'This OTP has already been used. Please request a new OTP.', ['expired' => true]);
    }
    if (strtotime($row['expires_at']) < time()) {
        return otp_result(400, false, 'OTP has expired. Please request a new OTP.', ['expired' => true]);
    }

    // Count the attempt atomically before comparing, so parallel guesses cannot exceed the limit.
    $stmt = $pdo->prepare('UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ? AND attempts < ?');
    $stmt->execute([$row['id'], $maxAttempts]);
    if ($stmt->rowCount() === 0) {
        return otp_result(429, false, 'Too many incorrect attempts. Please request a new OTP.', ['expired' => true]);
    }

    if (!hash_equals($row['otp_hash'], otp_hash($mobile, $otp))) {
        $left = $maxAttempts - ((int) $row['attempts'] + 1);
        return $left > 0
            ? otp_result(422, false, "Incorrect OTP. {$left} attempt(s) left.")
            : otp_result(429, false, 'Incorrect OTP. No attempts left - please request a new OTP.', ['expired' => true]);
    }

    $stmt = $pdo->prepare('UPDATE otp_verifications SET verified_at = ? WHERE id = ? AND verified_at IS NULL');
    $stmt->execute([db_datetime(now()), $row['id']]);
    if ($stmt->rowCount() === 0) {
        return otp_result(400, false, 'This OTP has already been used. Please request a new OTP.', ['expired' => true]);
    }

    session_regenerate_id(true);
    $_SESSION['otp_verified'] = ['mobile' => $mobile, 'id' => (int) $row['id'], 'at' => time()];

    return otp_result(200, true, 'Mobile number verified successfully.');
}

/** OTP row id when this session verified exactly this mobile and it is still unused. */
function otp_verified_id(string $mobile): ?int
{
    $verified = $_SESSION['otp_verified'] ?? null;
    if (!is_array($verified) || ($verified['mobile'] ?? null) !== $mobile) {
        return null;
    }
    if (time() - (int) $verified['at'] > (int) config('otp.verified_valid_seconds', 1800)) {
        return null;
    }
    $stmt = db()->prepare(
        'SELECT id FROM otp_verifications
          WHERE id = ? AND mobile = ? AND verified_at IS NOT NULL AND consumed_at IS NULL'
    );
    $stmt->execute([(int) $verified['id'], $mobile]);
    $id = $stmt->fetchColumn();
    return $id ? (int) $id : null;
}

function otp_result(int $status, bool $ok, string $message, array $extra = []): array
{
    return ['status' => $status, 'body' => ['ok' => $ok, 'message' => $message] + $extra];
}
