<?php
declare(strict_types=1);

// Send OTP and Resend OTP (same endpoint; resend cooldown is enforced server-side).

define('IS_API', true);
require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/otp.php';

api_guard();

if (campaign_state(campaign_settings()) !== 'open') {
    json_response(['ok' => false, 'message' => 'Registrations are currently closed.'], 403);
}

$mobile = normalize_mobile($_POST['mobile'] ?? '');
if ($mobile === null) {
    json_response(['ok' => false, 'message' => 'Please enter a valid 10-digit mobile number.', 'field' => 'mobile'], 422);
}

$result = otp_send($mobile);
json_response($result['body'], $result['status']);
