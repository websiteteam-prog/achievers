<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$settings = campaign_settings();
$state    = campaign_state($settings);
$banners  = banner_paths($settings);
[$minPurchase, $maxPurchase] = purchase_date_range($settings);

$stores = $state === 'open' ? active_stores() : [];
$cities = $state === 'open' ? active_cities() : [];

$jsConfig = [
    'otpLength'   => (int) config('otp.length', 6),
    'imeiLuhn'    => (bool) config('app.imei_luhn_check', true),
    'purchaseMin' => $minPurchase,
    'purchaseMax' => $maxPurchase,
    'purchaseMinText' => format_date($minPurchase),
    'purchaseMaxText' => format_date($maxPurchase),
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($settings['campaign_name']) ?> - Registration</title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="assets/lib/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="public-page">

<header class="campaign-banner">
    <?php if ($banners['desktop']): ?>
        <picture>
            <source media="(min-width: 768px)" srcset="<?= e($banners['desktop']) ?>">
            <img src="<?= e($banners['mobile']) ?>" alt="<?= e($settings['campaign_name']) ?>">
        </picture>
    <?php else: ?>
        <div class="banner-fallback">
            <h1 class="h2 mb-0"><?= e($settings['campaign_name']) ?></h1>
        </div>
    <?php endif; ?>
</header>

<main class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
            <div class="card form-card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">

                <?php if ($state !== 'open'): ?>
                    <div class="text-center py-4">
                        <h1 class="h4 mb-3"><?= e($settings['campaign_name']) ?></h1>
                        <?php if ($state === 'upcoming'): ?>
                            <p class="text-muted mb-0">Registrations open on <strong><?= e(format_date($settings['start_date'])) ?></strong>. Please visit again.</p>
                        <?php else: ?>
                            <p class="text-muted mb-0">Registrations for this campaign are currently closed. Thank you for your interest.</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>

                    <div id="formSection">
                        <h1 class="h4 mb-1">Campaign Registration</h1>
                        <p class="text-muted small mb-4">
                            Purchases made between <strong><?= e(format_date($settings['start_date'])) ?></strong> and
                            <strong><?= e(format_date($settings['end_date'])) ?></strong> are eligible.
                            Fields marked <span class="req">*</span> are mandatory.
                        </p>

                        <div id="formAlert" class="alert d-none" role="alert"></div>

                        <form id="registrationForm" novalidate autocomplete="on">
                            <?= csrf_field() ?>

                            <!-- 1. Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="req">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" maxlength="100"
                                       autocomplete="name" placeholder="Enter your full name" required>
                                <div class="invalid-feedback" data-error-for="name"></div>
                            </div>

                            <!-- 2. Mobile Number + OTP -->
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile Number <span class="req">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">+91</span>
                                    <input type="tel" class="form-control" id="mobile" name="mobile"
                                           inputmode="numeric" autocomplete="tel-national" placeholder="Mobile number" required>
                                    <button class="btn btn-primary" type="button" id="sendOtpBtn">Send OTP</button>
                                    <div class="invalid-feedback" data-error-for="mobile"></div>
                                </div>
                                <div class="form-text text-success d-none" id="mobileVerifiedText">&#10004; Mobile number verified</div>
                            </div>

                            <div class="mb-3 otp-box d-none" id="otpGroup">
                                <label for="otp" class="form-label">Enter OTP <span class="req">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" class="form-control" id="otp"
                                           inputmode="numeric" autocomplete="one-time-code" placeholder="<?= (int) config('otp.length', 6) ?>-digit OTP">
                                    <button class="btn btn-success" type="button" id="verifyOtpBtn">Verify OTP</button>
                                    <div class="invalid-feedback" data-error-for="otp"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 small">
                                    <span class="text-muted" id="otpInfo"></span>
                                    <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled>Resend OTP</button>
                                </div>
                            </div>

                            <!-- 3. IMEI Number -->
                            <div class="mb-3">
                                <label for="imei" class="form-label">IMEI Number <span class="req">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" class="form-control" id="imei" name="imei"
                                           inputmode="numeric" autocomplete="off" placeholder="15-digit IMEI number" required>
                                    <button class="btn btn-outline-secondary" type="button" id="scanImeiBtn" title="Scan IMEI barcode">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8v8M10 8v8M13 8v8M17 8v8"/></svg>
                                        Scan
                                    </button>
                                    <div class="invalid-feedback" data-error-for="imei"></div>
                                </div>
                                <div class="form-text">Dial *#06# on the phone or scan the IMEI barcode on the box.</div>
                            </div>

                            <!-- 4. Email ID -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email ID <span class="req">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="191"
                                       autocomplete="email" placeholder="name@example.com" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                                <div class="form-text">Winners will be contacted and vouchers sent on this email ID.</div>
                            </div>

                            <!-- 5. Date of Purchase -->
                            <div class="mb-3">
                                <label for="purchase_date" class="form-label">Date of Purchase <span class="req">*</span></label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                       min="<?= e($minPurchase) ?>" max="<?= e($maxPurchase) ?>" required>
                                <div class="invalid-feedback" data-error-for="purchase_date"></div>
                            </div>

                            <!-- 6. Store Name -->
                            <div class="mb-3">
                                <label for="store_id" class="form-label">Store Name <span class="req">*</span></label>
                                <select class="form-select" id="store_id" name="store_id" required>
                                    <option value="">Select store</option>
                                    <?php foreach ($stores as $store): ?>
                                        <option value="<?= (int) $store['id'] ?>" data-code="<?= e($store['code'] ?? '') ?>"><?= e($store['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" data-error-for="store_id"></div>
                            </div>

                            <!-- 7. Store Code (auto-filled) -->
                            <div class="mb-3">
                                <label for="store_code" class="form-label">Store Code</label>
                                <input type="text" class="form-control" id="store_code" readonly tabindex="-1"
                                       placeholder="Auto-filled when available">
                            </div>

                            <!-- 8. City -->
                            <div class="mb-4">
                                <label for="city_id" class="form-label">City <span class="req">*</span></label>
                                <select class="form-select" id="city_id" name="city_id" required>
                                    <option value="">Select city</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= (int) $city['id'] ?>"><?= e($city['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" data-error-for="city_id"></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                                Submit Registration
                            </button>
                        </form>
                    </div>

                    <div id="successSection" class="text-center py-4 d-none">
                        <div class="success-icon mb-3">&#10004;</div>
                        <h2 class="h4">Registration Successful</h2>
                        <p class="text-muted mb-2" id="successMessage"></p>
                        <p class="mb-0">Registration No: <strong id="registrationNo"></strong></p>
                    </div>

                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if ($state === 'open'): ?>
<!-- IMEI barcode scanner -->
<div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="scanModalLabel">Scan IMEI Barcode</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Point the camera at the IMEI barcode on the phone box.</p>
                <div id="scannerRegion"></div>
                <div id="scanMessage" class="small mt-2"></div>
                <label class="btn btn-outline-secondary btn-sm mt-3">
                    Or take / upload a photo of the barcode
                    <input type="file" id="scanFileInput" accept="image/*" capture="environment" hidden>
                </label>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="text-center text-muted small pb-4">&copy; <?= date('Y') ?> <?= e($settings['campaign_name']) ?></footer>

<script type="application/json" id="appConfig"><?= json_encode($jsConfig, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="assets/lib/bootstrap/bootstrap.bundle.min.js"></script>
<?php if ($state === 'open'): ?>
<script src="assets/lib/html5-qrcode/html5-qrcode.min.js"></script>
<script src="assets/js/register.js"></script>
<?php endif; ?>
</body>
</html>
