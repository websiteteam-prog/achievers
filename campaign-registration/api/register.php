<?php
declare(strict_types=1);

define('IS_API', true);
require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/otp.php';
require APP_ROOT . '/includes/registrations.php';

api_guard();

$settings = campaign_settings();
if (campaign_state($settings) !== 'open') {
    json_response(['ok' => false, 'message' => 'Registrations are currently closed.'], 403);
}

[$data, $errors] = registration_validate($_POST, $settings);
if ($errors) {
    json_response(['ok' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors], 422);
}

$otpId = otp_verified_id($data['mobile']);
if ($otpId === null) {
    json_response([
        'ok'       => false,
        'message'  => 'Please verify your mobile number with OTP before submitting.',
        'errors'   => ['mobile' => 'Please verify your mobile number with OTP.'],
        'reverify' => true,
    ], 422);
}

$duplicates = registration_duplicate_errors($data['mobile'], $data['email'], $data['imei']);
if ($duplicates) {
    json_response(['ok' => false, 'message' => 'Some details are already registered.', 'errors' => $duplicates], 409);
}

$result = registration_create($data, $otpId);
if ($result['reverify']) {
    json_response([
        'ok'       => false,
        'message'  => 'Please verify your mobile number with OTP before submitting.',
        'errors'   => $result['errors'],
        'reverify' => true,
    ], 422);
}
if ($result['id'] === null) {
    json_response([
        'ok'      => false,
        'message' => $result['errors']['form'] ?? 'Some details are already registered.',
        'errors'  => $result['errors'],
    ], 409);
}

unset($_SESSION['otp_verified']);

json_response([
    'ok'              => true,
    'message'         => 'Thank you! Your registration has been submitted successfully.',
    'registration_no' => $result['id'], // serial number: 1, 2, 3 ...
]);
