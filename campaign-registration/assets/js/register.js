(function () {
    'use strict';

    var form = document.getElementById('registrationForm');
    if (!form) {
        return;
    }

    var cfg = JSON.parse(document.getElementById('appConfig').textContent);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var $ = function (id) { return document.getElementById(id); };

    var fields = {
        name: $('name'),
        mobile: $('mobile'),
        imei: $('imei'),
        email: $('email'),
        purchase_date: $('purchase_date'),
        store_id: $('store_id'),
        city_id: $('city_id'),
        otp: $('otp')
    };
    var sendOtpBtn = $('sendOtpBtn');
    var verifyOtpBtn = $('verifyOtpBtn');
    var resendOtpBtn = $('resendOtpBtn');
    var submitBtn = $('submitBtn');
    var otpGroup = $('otpGroup');
    var otpInfo = $('otpInfo');
    var formAlert = $('formAlert');

    var verifiedMobile = null;   // mobile number verified in this page
    var otpSentTo = null;        // mobile number the current OTP was sent to
    var cooldownTimer = null;

    /* ------------------------------------------------------------ helpers */

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-Token': csrfToken, 'Accept': 'application/json' },
            body: new URLSearchParams(data)
        }).then(function (res) {
            return res.json().catch(function () { return null; }).then(function (json) {
                json = json || { ok: false, message: 'Unexpected server response. Please try again.' };
                json.status = res.status;
                return json;
            });
        }).catch(function () {
            return { ok: false, status: 0, message: 'Network error. Please check your internet connection and try again.' };
        });
    }

    function setError(field, message) {
        var input = fields[field];
        var box = form.querySelector('[data-error-for="' + field + '"]');
        if (input) {
            input.classList.toggle('is-invalid', !!message);
            if (message) {
                input.classList.remove('is-valid');
            }
        }
        if (box) {
            box.textContent = message || '';
        }
    }

    function showAlert(type, message) {
        formAlert.className = 'alert alert-' + type;
        formAlert.textContent = message;
    }

    function hideAlert() {
        formAlert.className = 'alert d-none';
        formAlert.textContent = '';
    }

    function setBusy(button, busy, busyText) {
        if (busy) {
            button.dataset.label = button.dataset.label || button.textContent;
            button.textContent = busyText;
        } else if (button.dataset.label) {
            button.textContent = button.dataset.label;
        }
        button.disabled = busy;
    }

    function luhnValid(imei) {
        var sum = 0;
        for (var i = 0; i < 15; i++) {
            var d = parseInt(imei.charAt(i), 10);
            if (i % 2 === 1) {
                d *= 2;
                if (d > 9) { d -= 9; }
            }
            sum += d;
        }
        return sum % 10 === 0;
    }

    // No maxlength on these inputs: a pasted "+91 98765 43210" or "35-693803-564380-9"
    // would be cut off before the non-digits could be removed.
    function digitsOnly(input, maxLength) {
        var clean = input.value.replace(/\D+/g, '');
        if (input === fields.mobile) {
            if (clean.length === 12 && clean.indexOf('91') === 0) {
                clean = clean.slice(2);
            } else if (clean.length === 11 && clean.charAt(0) === '0') {
                clean = clean.slice(1);
            }
        }
        clean = clean.slice(0, maxLength);
        if (clean !== input.value) {
            input.value = clean;
        }
    }

    /* --------------------------------------------------------- validation */

    var validators = {
        name: function (v) {
            v = v.trim();
            if (!v) { return 'Please enter your name.'; }
            if (v.length < 2 || v.length > 100 || !/^\p{L}[\p{L}\p{M}\s.'-]*$/u.test(v)) { return 'Please enter a valid name (letters and spaces only).'; }
            return '';
        },
        mobile: function (v) {
            if (!v) { return 'Please enter your mobile number.'; }
            if (!/^[6-9]\d{9}$/.test(v)) { return 'Please enter a valid 10-digit mobile number.'; }
            return '';
        },
        imei: function (v) {
            if (!v) { return 'Please enter the IMEI number.'; }
            if (!/^\d{15}$/.test(v)) { return 'IMEI number must be exactly 15 digits.'; }
            if (/^(\d)\1{14}$/.test(v) || (cfg.imeiLuhn && !luhnValid(v))) { return 'Please enter a valid IMEI number.'; }
            return '';
        },
        email: function (v) {
            v = v.trim();
            if (!v) { return 'Please enter your email ID.'; }
            if (v.length > 191 || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v)) { return 'Please enter a valid email ID.'; }
            return '';
        },
        purchase_date: function (v) {
            if (!v) { return 'Please select the date of purchase.'; }
            if (v < cfg.purchaseMin || v > cfg.purchaseMax) {
                return 'Date of purchase must be between ' + cfg.purchaseMinText + ' and ' + cfg.purchaseMaxText + '.';
            }
            return '';
        },
        store_id: function (v) { return v ? '' : 'Please select your store.'; },
        city_id: function (v) { return v ? '' : 'Please select your city.'; }
    };

    function validateField(field) {
        var message = validators[field](fields[field].value);
        setError(field, message);
        return !message;
    }

    /* ---------------------------------------------------------------- OTP */

    function startCooldown(seconds) {
        clearInterval(cooldownTimer);
        var remaining = seconds;
        resendOtpBtn.disabled = true;
        var tick = function () {
            if (remaining <= 0) {
                clearInterval(cooldownTimer);
                resendOtpBtn.disabled = false;
                resendOtpBtn.textContent = 'Resend OTP';
                return;
            }
            resendOtpBtn.textContent = 'Resend OTP in ' + remaining + 's';
            remaining--;
        };
        tick();
        cooldownTimer = setInterval(tick, 1000);
    }

    function resetVerification() {
        verifiedMobile = null;
        otpSentTo = null;
        clearInterval(cooldownTimer);
        otpGroup.classList.add('d-none');
        fields.otp.value = '';
        setError('otp', '');
        $('mobileVerifiedText').classList.add('d-none');
        fields.mobile.classList.remove('is-valid');
        sendOtpBtn.disabled = false;
        sendOtpBtn.className = 'btn btn-primary';
        sendOtpBtn.textContent = 'Send OTP';
        delete sendOtpBtn.dataset.label;
    }

    function markVerified(mobile) {
        verifiedMobile = mobile;
        clearInterval(cooldownTimer);
        otpGroup.classList.add('d-none');
        setError('mobile', '');
        fields.mobile.classList.add('is-valid');
        $('mobileVerifiedText').classList.remove('d-none');
        sendOtpBtn.disabled = true;
        sendOtpBtn.className = 'btn btn-success';
        sendOtpBtn.textContent = 'Verified ✔';
        delete sendOtpBtn.dataset.label;
    }

    function sendOtp(isResend) {
        hideAlert();
        if (!validateField('mobile')) {
            fields.mobile.focus();
            return;
        }
        var mobile = fields.mobile.value;
        var button = isResend ? resendOtpBtn : sendOtpBtn;
        setBusy(button, true, 'Sending...');

        post('api/send-otp.php', { mobile: mobile }).then(function (res) {
            setBusy(button, false);
            if (fields.mobile.value !== mobile) {
                return; // number was edited while the request was running
            }
            if (res.ok) {
                otpSentTo = mobile;
                otpGroup.classList.remove('d-none');
                sendOtpBtn.textContent = 'OTP Sent';
                sendOtpBtn.disabled = true;
                otpInfo.textContent = res.message + ' Valid for ' + Math.round(res.expires_in / 60) + ' minutes.';
                setError('otp', '');
                fields.otp.value = '';
                fields.otp.focus();
                startCooldown(res.cooldown);
                return;
            }
            if (res.retry_after && otpSentTo === mobile) {
                startCooldown(res.retry_after);
                setError('otp', res.message);
            } else {
                setError('mobile', res.message);
                if (res.retry_after) {
                    sendOtpBtn.disabled = true;
                    setTimeout(function () {
                        if (fields.mobile.value === mobile && !otpSentTo) { sendOtpBtn.disabled = false; }
                    }, res.retry_after * 1000);
                }
            }
            if (res.session_expired) {
                showAlert('warning', res.message);
            }
        });
    }

    function verifyOtp() {
        var otp = fields.otp.value.trim();
        var pattern = new RegExp('^\\d{' + cfg.otpLength + '}$');
        if (!pattern.test(otp)) {
            setError('otp', 'Please enter the ' + cfg.otpLength + '-digit OTP.');
            fields.otp.focus();
            return;
        }
        var mobile = fields.mobile.value;
        setBusy(verifyOtpBtn, true, 'Verifying...');

        post('api/verify-otp.php', { mobile: mobile, otp: otp }).then(function (res) {
            setBusy(verifyOtpBtn, false);
            if (fields.mobile.value !== mobile) {
                return;
            }
            if (res.ok) {
                markVerified(mobile);
                return;
            }
            setError('otp', res.message);
            if (res.expired) {
                fields.otp.value = '';
            }
            if (res.session_expired) {
                showAlert('warning', res.message);
            }
        });
    }

    sendOtpBtn.addEventListener('click', function () { sendOtp(false); });
    resendOtpBtn.addEventListener('click', function () { sendOtp(true); });
    verifyOtpBtn.addEventListener('click', verifyOtp);

    fields.otp.addEventListener('input', function () {
        digitsOnly(fields.otp, cfg.otpLength);
        setError('otp', '');
    });
    fields.otp.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyOtp();
        }
    });

    // Any change to the mobile number requires a fresh OTP verification.
    fields.mobile.addEventListener('input', function () {
        digitsOnly(fields.mobile, 10);
        if (fields.mobile.value !== verifiedMobile && fields.mobile.value !== otpSentTo) {
            if (verifiedMobile || otpSentTo) {
                resetVerification();
            }
        }
        if (fields.mobile.classList.contains('is-invalid')) {
            validateField('mobile');
        }
    });

    /* ------------------------------------------------------- other fields */

    fields.imei.addEventListener('input', function () {
        digitsOnly(fields.imei, 15);
        if (fields.imei.classList.contains('is-invalid')) { validateField('imei'); }
    });

    fields.store_id.addEventListener('change', function () {
        var option = fields.store_id.options[fields.store_id.selectedIndex];
        $('store_code').value = (option && option.dataset.code) || '';
    });

    ['name', 'email', 'purchase_date', 'store_id', 'city_id'].forEach(function (field) {
        fields[field].addEventListener('blur', function () {
            if (fields[field].value !== '') { validateField(field); }
        });
        fields[field].addEventListener(field.slice(-3) === '_id' || field === 'purchase_date' ? 'change' : 'input', function () {
            if (fields[field].classList.contains('is-invalid')) { validateField(field); }
        });
    });
    fields.mobile.addEventListener('blur', function () {
        if (fields.mobile.value !== '') { validateField('mobile'); }
    });
    fields.imei.addEventListener('blur', function () {
        if (fields.imei.value !== '') { validateField('imei'); }
    });

    /* ------------------------------------------------------------- submit */

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlert();

        var firstInvalid = null;
        Object.keys(validators).forEach(function (field) {
            if (!validateField(field) && !firstInvalid) {
                firstInvalid = fields[field];
            }
        });
        if (!fields.mobile.classList.contains('is-invalid') && verifiedMobile !== fields.mobile.value) {
            setError('mobile', 'Please verify your mobile number with OTP.');
            firstInvalid = firstInvalid || fields.mobile;
        }
        if (firstInvalid) {
            showAlert('danger', 'Please correct the highlighted fields.');
            firstInvalid.focus();
            return;
        }

        var spinner = submitBtn.querySelector('.spinner-border');
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        var data = {};
        new FormData(form).forEach(function (value, key) { data[key] = value; });

        post('api/register.php', data).then(function (res) {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');

            if (res.ok) {
                $('formSection').classList.add('d-none');
                $('successMessage').textContent = res.message;
                $('registrationNo').textContent = res.registration_no;
                $('successSection').classList.remove('d-none');
                window.scrollTo({ top: $('successSection').getBoundingClientRect().top + window.scrollY - 80, behavior: 'smooth' });
                return;
            }

            if (res.reverify) {
                resetVerification();
            }
            var focused = false;
            Object.keys(res.errors || {}).forEach(function (field) {
                if (fields[field]) {
                    setError(field, res.errors[field]);
                    if (!focused) { fields[field].focus(); focused = true; }
                }
            });
            showAlert(res.session_expired ? 'warning' : 'danger', res.message || 'Registration failed. Please try again.');
        });
    });

    /* ------------------------------------------------------ IMEI scanner */

    var scanModalEl = $('scanModal');
    var scanMessage = $('scanMessage');
    var scanModal = new bootstrap.Modal(scanModalEl);
    var scanner = null;

    function extractImei(text) {
        var matches = String(text).replace(/[\s-]+/g, '').match(/\d+/g) || [];
        for (var i = 0; i < matches.length; i++) {
            if (matches[i].length === 15 && !validators.imei(matches[i])) {
                return matches[i];
            }
        }
        return null;
    }

    function onScanned(text) {
        var imei = extractImei(text);
        if (!imei) {
            scanMessage.className = 'small mt-2 text-danger';
            scanMessage.textContent = 'This barcode is not a valid IMEI. Please scan the IMEI barcode.';
            return;
        }
        fields.imei.value = imei;
        validateField('imei');
        scanModal.hide();
    }

    function stopScanner() {
        if (!scanner) {
            return Promise.resolve();
        }
        var current = scanner;
        scanner = null;
        var stopping = current.isScanning ? current.stop() : Promise.resolve();
        return stopping.catch(function () {}).then(function () {
            try { current.clear(); } catch (err) { /* already cleared */ }
        });
    }

    function newScanner() {
        var formats = window.Html5QrcodeSupportedFormats;
        return new window.Html5Qrcode('scannerRegion', {
            verbose: false,
            formatsToSupport: [formats.CODE_128, formats.CODE_39, formats.CODE_93, formats.EAN_13, formats.ITF, formats.QR_CODE],
            experimentalFeatures: { useBarCodeDetectorIfSupported: true }
        });
    }

    $('scanImeiBtn').addEventListener('click', function () {
        if (!window.Html5Qrcode) {
            showAlert('warning', 'Barcode scanner could not be loaded. Please type the IMEI number.');
            return;
        }
        scanModal.show();
    });

    scanModalEl.addEventListener('shown.bs.modal', function () {
        scanMessage.className = 'small mt-2 text-muted';
        scanMessage.textContent = 'Starting camera...';
        var instance = newScanner();
        scanner = instance;
        instance.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: function (w, h) {
                    return { width: Math.floor(w * 0.9), height: Math.max(80, Math.floor(Math.min(w, h) * 0.45)) };
                }
            },
            onScanned,
            function () { /* no barcode in this frame */ }
        ).then(function () {
            if (scanner !== instance) {
                // modal was closed while the camera was starting
                instance.stop().then(function () { instance.clear(); }).catch(function () {});
                return;
            }
            scanMessage.textContent = 'Hold steady until the barcode is read.';
        }).catch(function () {
            if (scanner !== instance) {
                return;
            }
            scanMessage.className = 'small mt-2 text-danger';
            scanMessage.textContent = 'Camera is not available. Allow camera access (site must use https), upload a photo, or type the IMEI.';
        });
    });

    scanModalEl.addEventListener('hidden.bs.modal', function () {
        stopScanner();
        $('scanFileInput').value = '';
    });

    $('scanFileInput').addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        if (!file) {
            return;
        }
        scanMessage.className = 'small mt-2 text-muted';
        scanMessage.textContent = 'Reading barcode from photo...';
        stopScanner().then(function () {
            scanner = newScanner();
            return scanner.scanFile(file, false);
        }).then(onScanned).catch(function () {
            scanMessage.className = 'small mt-2 text-danger';
            scanMessage.textContent = 'No barcode found in the photo. Try a clearer, closer photo or type the IMEI.';
        });
    });
})();
