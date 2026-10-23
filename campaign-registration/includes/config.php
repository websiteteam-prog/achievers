<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/*
 * Application settings, built from the .env file in the project root.
 * Edit .env - not this file.
 */

$smsParamName = static function (string $key): ?string {
    $value = env($key);
    return strtolower($value) === 'none' ? null : $value;
};

return [
    'app' => [
        'env'              => env('APP_ENV', 'production'),
        'timezone'         => env('APP_TIMEZONE', 'Asia/Kolkata'),
        'secret'           => env('APP_SECRET'),
        'imei_luhn_check'  => env_bool('IMEI_LUHN_CHECK', true),
        'client_ip_header' => env('CLIENT_IP_HEADER'),
    ],

    'db' => [
        'host'    => env('DB_HOST', 'localhost'),
        'port'    => env_int('DB_PORT', 3306),
        'name'    => env('DB_NAME'),
        'user'    => env('DB_USER'),
        'pass'    => env('DB_PASS'),
        'charset' => 'utf8mb4',
    ],

    'otp' => [
        // auto: dev OTP while SMS_API_URL is empty, real SMS once it is set
        'mode'                          => strtolower(env('OTP_MODE', 'auto')),
        'dev_otp'                       => env('DEV_OTP', '123456'),
        'length'                        => env_int('OTP_LENGTH', 6),
        'expiry_seconds'                => env_int('OTP_EXPIRY_SECONDS', 300),
        'resend_cooldown_seconds'       => env_int('OTP_RESEND_SECONDS', 60),
        'max_verify_attempts'           => env_int('OTP_MAX_ATTEMPTS', 5),
        'max_sends_per_mobile_per_hour' => env_int('OTP_MAX_PER_MOBILE_PER_HOUR', 5),
        'max_sends_per_ip_per_hour'     => env_int('OTP_MAX_PER_IP_PER_HOUR', 30),
        'verified_valid_seconds'        => 1800,
    ],

    'sms' => [
        'api_url'          => env('SMS_API_URL'),
        'method'           => strtoupper(env('SMS_METHOD', 'GET')),
        'post_format'      => strtolower(env('SMS_POST_FORMAT', 'form')),
        'timeout'          => env_int('SMS_TIMEOUT', 15),
        'auth_params'      => env_query('SMS_AUTH_PARAMS'),
        'headers'          => env_list('SMS_HEADERS', '||'),
        'sender_id'        => env('SMS_SENDER_ID'),
        'template_id'      => env('SMS_TEMPLATE_ID'),
        'pe_id'            => env('SMS_PE_ID'),
        'message_template' => env('SMS_MESSAGE_TEMPLATE'),
        'template_vars'    => env_list('SMS_TEMPLATE_VARS') ?: ['otp'],
        'param_names'      => [
            'mobile'      => env('SMS_PARAM_MOBILE'),
            'message'     => env('SMS_PARAM_MESSAGE'),
            'sender_id'   => $smsParamName('SMS_PARAM_SENDER_ID'),
            'template_id' => $smsParamName('SMS_PARAM_TEMPLATE_ID'),
            'pe_id'       => $smsParamName('SMS_PARAM_PE_ID'),
        ],
        'extra_params'     => env_query('SMS_EXTRA_PARAMS'),
        'mobile_prefix'    => strtolower(env('SMS_MOBILE_PREFIX', '91')) === 'none' ? '' : env('SMS_MOBILE_PREFIX', '91'),
        'success_match'    => env('SMS_SUCCESS_MATCH'),
    ],

    'admin' => [
        'session_timeout_seconds' => env_int('ADMIN_SESSION_TIMEOUT', 1800),
        'max_login_attempts'      => 5,
        'lockout_minutes'         => 15,
        'per_page'                => 25,
    ],
];
