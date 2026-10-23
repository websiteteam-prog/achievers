<?php
/**
 * Campaign Registration - configuration.
 *
 * 1. Copy this file to config/config.php
 * 2. Fill in the values below.
 *
 * config/config.php holds secrets (DB password, SMS API credentials).
 * It is git-ignored and blocked from web access by config/.htaccess - never
 * commit it or share it.
 */
return [

    'app' => [
        // 'production' on the live server. 'local' only on your own machine
        // (shows PHP errors and allows the 'log' SMS driver).
        'env'      => 'production',
        'timezone' => 'Asia/Kolkata',

        // Long random string used to hash OTPs. Generate one with:
        //   php -r "echo bin2hex(random_bytes(32));"
        'secret'   => 'CHANGE_ME',

        // IMEI numbers must be 15 digits. Also verify the IMEI check digit (Luhn).
        'imei_luhn_check' => true,

        // Leave '' unless the site is behind Cloudflare / a load balancer. Then set the
        // header that carries the real visitor IP, e.g. 'HTTP_CF_CONNECTING_IP' (Cloudflare)
        // or 'HTTP_X_FORWARDED_FOR'. Never set it when there is no proxy (it can be spoofed).
        'client_ip_header' => '',
    ],

    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'campaign_registration',
        'user'    => 'DB_USER',
        'pass'    => 'DB_PASSWORD',
        'charset' => 'utf8mb4',
    ],

    'otp' => [
        'length'                        => 6,
        'expiry_seconds'                => 300,  // OTP is valid for 5 minutes
        'resend_cooldown_seconds'       => 60,   // wait before "Resend OTP"
        'max_verify_attempts'           => 5,    // wrong entries allowed per OTP
        'max_sends_per_mobile_per_hour' => 5,
        'max_sends_per_ip_per_hour'     => 30,   // raise it if promoters register many customers from one store Wi-Fi
        'verified_valid_seconds'        => 1800, // submit the form within 30 min of verifying
    ],

    /*
     * uEngage SMS panel + Airtel DLT.
     *
     * Every value marked REQUIRED must be copied from your uEngage SMS panel /
     * uEngage API document and your Airtel DLT portal. Nothing here is a real
     * endpoint or credential - the code sends exactly what you configure.
     * Until the REQUIRED values are filled in, "Send OTP" shows customers
     * "OTP service is temporarily unavailable" and logs the missing keys to
     * storage/logs/sms.log.
     */
    'sms' => [
        // 'uengage' = live SMS. 'log' = writes the OTP to storage/logs/otp-local.log
        // instead of sending (only works when app.env = 'local').
        'driver'  => 'uengage',

        'api_url' => '',     // REQUIRED: exact "send SMS" API URL from the uEngage API document
        'method'  => 'GET',  // REQUIRED: 'GET' or 'POST', as per the uEngage document
        'post_format' => 'form', // for POST only: 'form' (x-www-form-urlencoded) or 'json'
        'timeout' => 15,     // seconds

        // Account authentication exactly as the uEngage document names it, e.g.
        // ['<api key parameter name>' => '<your key>'] or username/password pairs.
        'auth_params' => [],
        // Extra HTTP headers, if uEngage authenticates by header, e.g. ['Authorization: ...'].
        'headers' => [],

        // Airtel DLT values (from the DLT portal / uEngage panel)
        'sender_id'   => '', // REQUIRED: approved 6-character Sender ID / Header
        'template_id' => '', // REQUIRED: DLT Content Template ID of the approved OTP template
        'pe_id'       => '', // REQUIRED: Principal Entity ID (PE ID)

        // REQUIRED: the approved OTP template text copied EXACTLY as approved on DLT,
        // with every variable left as {#var#}. Do not change any fixed text.
        'message_template' => '',
        // What fills each {#var#} in the template, in order.
        // 'otp' = the OTP, 'expiry_minutes' = OTP validity in minutes. Any other
        // string is inserted literally. Must match the number of {#var#}.
        'template_vars' => ['otp'],

        // REQUIRED: request parameter names exactly as the uEngage API document
        // spells them. Set sender_id / template_id / pe_id to null ONLY if uEngage
        // confirms that value is bound to your account and must not be sent.
        'param_names' => [
            'mobile'      => '',
            'message'     => '',
            'sender_id'   => '',
            'template_id' => '',
            'pe_id'       => '',
        ],
        // Any other fixed parameters the document requires (route, sms type,
        // unicode flag, telemarketer / chain ID, ...): ['name' => 'value'].
        'extra_params' => [],

        // Prefix added before the 10-digit mobile number: '91' if uEngage expects
        // 91XXXXXXXXXX, '' if it expects only 10 digits.
        'mobile_prefix' => '91',

        // Text that appears in uEngage's response body when the SMS is accepted
        // (see the "success response" in the uEngage document). Leave '' to treat
        // any HTTP 2xx response as success.
        'success_match' => '',
    ],

    'uploads' => [
        'max_banner_bytes' => 2 * 1024 * 1024, // 2 MB
    ],

    'admin' => [
        'session_timeout_seconds' => 1800, // auto logout after 30 min of inactivity
        'max_login_attempts'      => 5,
        'lockout_minutes'         => 15,
        'per_page'                => 25,
    ],
];
