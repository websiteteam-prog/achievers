/*
 * IMEI barcode scanner: live camera + photo upload.
 *
 * Live scanning decodes the area inside the on-screen guide box at the camera's
 * full resolution. (html5-qrcode's own camera mode shrinks every frame to the
 * size of the box on screen - about 300 px on a phone - which is too small for
 * the thin bars of an IMEI barcode, so it rarely read anything.)
 *
 * Decoding uses the browser's built-in BarcodeDetector when it has one
 * (Android Chrome) and otherwise the ZXing decoder bundled in html5-qrcode.
 * Uploaded photos are decoded at full size, also rotated 90 degrees and in
 * horizontal strips; when no IMEI is found the photo is checked for blur,
 * darkness and low resolution so the customer gets a clear reason.
 *
 * Self-contained: it only needs #imei, #appConfig and the #imeiScan* markup.
 */
(function () {
    'use strict';

    var openBtn = document.getElementById('imeiScanBtn');
    var modalEl = document.getElementById('imeiScanModal');
    var imeiInput = document.getElementById('imei');
    if (!openBtn || !modalEl || !imeiInput || !window.bootstrap) {
        return;
    }

    var appConfig = {};
    try {
        appConfig = JSON.parse(document.getElementById('appConfig').textContent) || {};
    } catch (err) { /* defaults below */ }
    var checkLuhn = appConfig.imeiLuhn !== false;

    var el = {
        view: document.getElementById('imeiScanView'),
        video: document.getElementById('imeiScanVideo'),
        guide: document.getElementById('imeiScanGuide'),
        status: document.getElementById('imeiScanStatus'),
        torchBtn: document.getElementById('imeiTorchBtn'),
        zoomWrap: document.getElementById('imeiZoomWrap'),
        zoom: document.getElementById('imeiZoom'),
        photoInput: document.getElementById('imeiPhotoInput')
    };
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    var NATIVE_FORMATS = ['code_128', 'code_39', 'code_93', 'ean_13', 'itf', 'qr_code'];
    var LIVE_INTERVAL_MS = 120;     // time between live decode attempts
    var LIVE_MAX_CROP_WIDTH = 1600; // px of camera image decoded per attempt
    var PHOTO_MAX_SIDE = 2600;      // uploaded photos are decoded at up to this size
    var MIN_PHOTO_SIDE = 600;       // shorter side below this = too low resolution
    var DARK_BRIGHTNESS = 45;       // average brightness (0-255) below this = too dark
    var BLUR_SHARPNESS = 100;       // edge sharpness below this = blurry (see measurePhoto)

    var MESSAGES = {
        starting: 'Starting camera...',
        aim: 'Hold the IMEI barcode inside the box. It scans automatically.',
        searching: 'Still looking... Keep the barcode flat and inside the box, move the phone a little further away or add light. You can also upload a photo.',
        notImeiLive: 'That is not the IMEI barcode. Point the camera at the barcode marked IMEI.',
        reading: 'Reading the photo...',
        notImage: 'Please upload a photo (JPG or PNG) of the IMEI barcode.',
        badImage: 'This file could not be opened as a photo. Please upload a JPG or PNG photo of the IMEI barcode.',
        lowRes: 'This photo is too small or low quality. Please upload a clear, close-up photo of the IMEI barcode.',
        dark: 'This photo is too dark. Please upload a clear photo of the IMEI barcode taken in good light.',
        blurry: 'This photo is blurry. Please upload a clear photo of the IMEI barcode - hold the phone steady and let the camera focus.',
        notImeiPhoto: 'A barcode was read, but not the IMEI barcode. Please upload a clear, close-up photo of the barcode marked IMEI.',
        notFound: 'No IMEI barcode found in this photo. Please upload a clear, close-up photo with the IMEI barcode straight and fully visible.',
        noCameraApi: 'Camera is not available in this browser. Please upload a photo of the IMEI barcode.',
        insecure: 'Camera needs a secure (https) connection. Please upload a photo of the IMEI barcode.',
        denied: 'Camera permission was denied. Allow camera access in the browser settings, or upload a photo of the IMEI barcode.',
        noCamera: 'No camera found. Please upload a photo of the IMEI barcode.',
        cameraFailed: 'The camera could not be started. Please upload a photo of the IMEI barcode or type the IMEI.'
    };

    var stream = null;
    var track = null;
    var session = 0;        // bumps on every open/close so late callbacks are ignored
    var scanning = false;
    var loopTimer = null;
    var startedAt = 0;
    var lastHintAt = 0;
    var torchOn = false;
    var liveCanvas = document.createElement('canvas');

    /* ----------------------------------------------------------- helpers */

    function wait(ms) {
        return new Promise(function (resolve) { setTimeout(resolve, ms); });
    }

    function setStatus(type, message) {
        el.status.className = 'imei-scan-status is-' + type;
        el.status.textContent = message;
    }

    function context2d(canvas) {
        return canvas.getContext('2d', { willReadFrequently: true });
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

    function isImei(value) {
        return /^\d{15}$/.test(value) && !/^(\d)\1{14}$/.test(value) && (!checkLuhn || luhnValid(value));
    }

    /** First valid IMEI in the decoded texts (handles "IMEI:3569..." style content). */
    function findImei(texts) {
        for (var i = 0; i < texts.length; i++) {
            var runs = String(texts[i]).replace(/[\s-]+/g, '').match(/\d+/g) || [];
            for (var j = 0; j < runs.length; j++) {
                if (runs[j].length === 15 && isImei(runs[j])) {
                    return runs[j];
                }
            }
        }
        return null;
    }

    function fillImei(imei) {
        imeiInput.value = imei;
        // let register.js clean up / re-validate the field as if it was typed
        imeiInput.dispatchEvent(new Event('input', { bubbles: true }));
        imeiInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /* ---------------------------------------------------------- decoders */

    var nativeDetector = null;
    function getNativeDetector() {
        if (!nativeDetector) {
            nativeDetector = !('BarcodeDetector' in window)
                ? Promise.resolve(null)
                : Promise.resolve(window.BarcodeDetector.getSupportedFormats ? window.BarcodeDetector.getSupportedFormats() : [])
                    .then(function (supported) {
                        var formats = NATIVE_FORMATS.filter(function (f) { return supported.indexOf(f) !== -1; });
                        return formats.indexOf('code_128') !== -1 ? new window.BarcodeDetector({ formats: formats }) : null;
                    })
                    .catch(function () { return null; });
        }
        return nativeDetector;
    }

    var zxing;
    function getZxing() {
        if (zxing === undefined) {
            zxing = null;
            try {
                var F = window.Html5QrcodeSupportedFormats;
                var reader = new window.Html5Qrcode('imeiDecoderHolder', {
                    verbose: false,
                    formatsToSupport: [F.CODE_128, F.CODE_39, F.CODE_93, F.EAN_13, F.ITF, F.QR_CODE],
                    experimentalFeatures: { useBarCodeDetectorIfSupported: false }
                });
                // html5-qrcode keeps its decoder here; used directly so we control the image size
                if (reader.qrcode && typeof reader.qrcode.decodeAsync === 'function') {
                    zxing = reader.qrcode;
                }
            } catch (err) { /* library missing: native detector only */ }
        }
        return zxing;
    }

    /**
     * All barcode texts found in the canvas.
     * thorough = also try ZXing when the native detector found no IMEI (photos).
     */
    function decodeCanvas(canvas, thorough) {
        return getNativeDetector().then(function (detector) {
            var nativeTexts = detector
                ? detector.detect(canvas).then(function (codes) {
                    return codes.map(function (c) { return c.rawValue; });
                }).catch(function () { return []; })
                : Promise.resolve(null);

            return nativeTexts.then(function (texts) {
                if (texts !== null && (!thorough || findImei(texts))) {
                    return texts;
                }
                var reader = getZxing();
                if (!reader) {
                    return texts || [];
                }
                return reader.decodeAsync(canvas)
                    .then(function (result) { return (texts || []).concat([result.text]); })
                    .catch(function () { return texts || []; });
            });
        });
    }

    /* -------------------------------------------------------- live camera */

    function cameraErrorMessage(err) {
        var name = err && err.name;
        if (name === 'NotAllowedError' || name === 'SecurityError') { return MESSAGES.denied; }
        if (name === 'NotFoundError' || name === 'OverconstrainedError') { return MESSAGES.noCamera; }
        return MESSAGES.cameraFailed;
    }

    function startCamera() {
        var mySession = ++session;
        setStatus('info', MESSAGES.starting);

        if (!window.isSecureContext) {
            setStatus('error', MESSAGES.insecure);
            return;
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('error', MESSAGES.noCameraApi);
            return;
        }

        navigator.mediaDevices.getUserMedia({
            audio: false,
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        }).then(function (newStream) {
            if (mySession !== session) {
                newStream.getTracks().forEach(function (t) { t.stop(); });
                return null;
            }
            stream = newStream;
            track = newStream.getVideoTracks()[0] || null;
            el.video.srcObject = newStream;
            return el.video.play();
        }).then(function () {
            if (mySession !== session || !stream) {
                return;
            }
            tuneCamera();
            el.view.classList.add('is-live');
            setStatus('info', MESSAGES.aim);
            startedAt = Date.now();
            lastHintAt = 0;
            scanning = true;
            scanLoop(mySession);
        }).catch(function (err) {
            if (mySession === session) {
                setStatus('error', cameraErrorMessage(err));
            }
        });
    }

    /** Continuous focus, a little zoom and the flash button, where the camera supports them. */
    function tuneCamera() {
        var caps = track && track.getCapabilities ? track.getCapabilities() : {};
        var advanced = [];

        if (caps.focusMode && caps.focusMode.indexOf('continuous') !== -1) {
            advanced.push({ focusMode: 'continuous' });
        }
        if (caps.zoom && caps.zoom.max > caps.zoom.min) {
            // 1.5x lets the phone stay far enough away to focus on a small label
            var startZoom = Math.min(caps.zoom.max, Math.max(caps.zoom.min, 1.5));
            el.zoom.min = caps.zoom.min;
            el.zoom.max = caps.zoom.max;
            el.zoom.step = caps.zoom.step || 0.1;
            el.zoom.value = startZoom;
            el.zoomWrap.classList.remove('d-none');
            advanced.push({ zoom: startZoom });
        }
        if (caps.torch) {
            el.torchBtn.classList.remove('d-none');
        }
        if (advanced.length) {
            track.applyConstraints({ advanced: advanced }).catch(function () { /* best effort */ });
        }
    }

    /** The part of the camera image inside the guide box, at full camera resolution. */
    function captureGuideArea() {
        var video = el.video;
        var vw = video.videoWidth;
        var vh = video.videoHeight;
        if (!vw || !vh) {
            return null;
        }
        var view = el.view.getBoundingClientRect();
        var guide = el.guide.getBoundingClientRect();
        // the video is shown with object-fit: cover
        var scale = Math.max(view.width / vw, view.height / vh);
        var offsetX = (view.width - vw * scale) / 2;
        var offsetY = (view.height - vh * scale) / 2;
        // a little margin so a barcode touching the box edge is still complete
        var padX = guide.width * 0.08;
        var padY = guide.height * 0.2;

        var sx = Math.max(0, (guide.left - view.left - padX - offsetX) / scale);
        var sy = Math.max(0, (guide.top - view.top - padY - offsetY) / scale);
        var sw = Math.min(vw - sx, (guide.width + 2 * padX) / scale);
        var sh = Math.min(vh - sy, (guide.height + 2 * padY) / scale);
        if (sw < 10 || sh < 10) {
            return null;
        }

        var out = Math.min(1, LIVE_MAX_CROP_WIDTH / sw);
        liveCanvas.width = Math.round(sw * out);
        liveCanvas.height = Math.round(sh * out);
        context2d(liveCanvas).drawImage(video, sx, sy, sw, sh, 0, 0, liveCanvas.width, liveCanvas.height);
        return liveCanvas;
    }

    function scanLoop(mySession) {
        if (!scanning || mySession !== session) {
            return;
        }
        var began = Date.now();
        var canvas = captureGuideArea();

        (canvas ? decodeCanvas(canvas, false) : Promise.resolve([])).then(function (texts) {
            if (!scanning || mySession !== session) {
                return;
            }
            var imei = findImei(texts);
            if (imei) {
                onFound(imei);
                return;
            }
            var now = Date.now();
            if (texts.length && now - lastHintAt > 2500) {
                lastHintAt = now;
                setStatus('warn', MESSAGES.notImeiLive);
            } else if (!texts.length && now - startedAt > 12000 && now - lastHintAt > 8000) {
                lastHintAt = now;
                setStatus('warn', MESSAGES.searching);
            }
            loopTimer = setTimeout(function () { scanLoop(mySession); }, Math.max(0, LIVE_INTERVAL_MS - (now - began)));
        });
    }

    function onFound(imei) {
        scanning = false;
        clearTimeout(loopTimer);
        el.view.classList.add('is-found');
        setStatus('success', 'IMEI found: ' + imei);
        if (navigator.vibrate) {
            navigator.vibrate(80);
        }
        fillImei(imei);
        setTimeout(function () { modal.hide(); }, 700);
    }

    function stopCamera() {
        session++;
        scanning = false;
        clearTimeout(loopTimer);
        if (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
        }
        stream = null;
        track = null;
        torchOn = false;
        el.video.srcObject = null;
        el.view.classList.remove('is-live', 'is-found');
        el.torchBtn.classList.add('d-none');
        el.torchBtn.classList.remove('active');
        el.torchBtn.setAttribute('aria-pressed', 'false');
        el.zoomWrap.classList.add('d-none');
    }

    /* ------------------------------------------------------- photo upload */

    function loadImage(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('bad image')); };
            img.src = url;
        });
    }

    function drawScaled(source, width, height, maxSide) {
        var scale = Math.min(1, maxSide / Math.max(width, height));
        var canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(width * scale));
        canvas.height = Math.max(1, Math.round(height * scale));
        context2d(canvas).drawImage(source, 0, 0, canvas.width, canvas.height);
        return canvas;
    }

    function rotated(canvas) {
        var out = document.createElement('canvas');
        out.width = canvas.height;
        out.height = canvas.width;
        var ctx = context2d(out);
        ctx.translate(out.width, 0);
        ctx.rotate(Math.PI / 2);
        ctx.drawImage(canvas, 0, 0);
        return out;
    }

    /**
     * Overlapping horizontal bands of source(), so stacked barcodes
     * (IMEI 1, IMEI 2, S/N, EAN) are read one by one.
     */
    function strips(source) {
        return [0, 0.2, 0.4, 0.6, 0.66].map(function (start) {
            return function () {
                var canvas = source();
                var bandHeight = Math.round(canvas.height * 0.34);
                var band = document.createElement('canvas');
                band.width = canvas.width;
                band.height = bandHeight;
                context2d(band).drawImage(canvas, 0, Math.round(canvas.height * start), canvas.width, bandHeight,
                    0, 0, canvas.width, bandHeight);
                return band;
            };
        });
    }

    /** Try each canvas in turn until one contains a valid IMEI. */
    function firstImei(attempts) {
        var sawBarcode = false;
        var index = 0;
        function next() {
            if (index >= attempts.length) {
                return Promise.resolve({ imei: null, sawBarcode: sawBarcode });
            }
            var make = attempts[index++];
            // yield so the "Reading the photo..." status can paint between attempts
            return wait(0).then(function () { return decodeCanvas(make(), true); }).then(function (texts) {
                sawBarcode = sawBarcode || texts.length > 0;
                var imei = findImei(texts);
                return imei ? { imei: imei, sawBarcode: true } : next();
            });
        }
        return next();
    }

    /**
     * Brightness and sharpness of the photo, measured on a 1000 px copy.
     * Sharpness = variance of the Laplacian (edge strength) in the central area:
     * crisp barcode bars give high values, a blurred photo gives low values.
     */
    function measurePhoto(canvas) {
        var small = drawScaled(canvas, canvas.width, canvas.height, 1000);
        var w = small.width;
        var h = small.height;
        var data = context2d(small).getImageData(0, 0, w, h).data;
        var gray = new Float32Array(w * h);
        var total = 0;
        for (var p = 0, i = 0; p < gray.length; p++, i += 4) {
            gray[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            total += gray[p];
        }

        var x0 = Math.max(1, Math.round(w * 0.1));
        var x1 = Math.min(w - 1, Math.round(w * 0.9));
        var y0 = Math.max(1, Math.round(h * 0.1));
        var y1 = Math.min(h - 1, Math.round(h * 0.9));
        var sum = 0;
        var sumSq = 0;
        var n = 0;
        for (var y = y0; y < y1; y++) {
            for (var x = x0; x < x1; x++) {
                var c = y * w + x;
                var lap = 4 * gray[c] - gray[c - 1] - gray[c + 1] - gray[c - w] - gray[c + w];
                sum += lap;
                sumSq += lap * lap;
                n++;
            }
        }
        var mean = n ? sum / n : 0;
        return {
            brightness: total / gray.length,
            sharpness: n ? sumSq / n - mean * mean : 0
        };
    }

    function photoProblem(img, canvas, sawBarcode) {
        if (Math.min(img.naturalWidth, img.naturalHeight) < MIN_PHOTO_SIDE) {
            return MESSAGES.lowRes;
        }
        var quality = measurePhoto(canvas);
        if (quality.brightness < DARK_BRIGHTNESS) {
            return MESSAGES.dark;
        }
        if (quality.sharpness < BLUR_SHARPNESS) {
            return MESSAGES.blurry;
        }
        return sawBarcode ? MESSAGES.notImeiPhoto : MESSAGES.notFound;
    }

    function scanPhoto(file) {
        var mySession = session;
        var wasScanning = scanning;
        scanning = false; // pause the live camera while the photo is decoded
        clearTimeout(loopTimer);
        el.view.classList.add('is-busy');
        setStatus('info', MESSAGES.reading);

        function resumeLive() {
            el.view.classList.remove('is-busy');
            if (wasScanning && mySession === session && stream) {
                scanning = true;
                startedAt = Date.now();
                lastHintAt = Date.now(); // keep the photo error visible for a while
                scanLoop(mySession);
            }
        }

        if (file.type && !/^image\//.test(file.type)) {
            setStatus('error', MESSAGES.notImage);
            resumeLive();
            return;
        }

        loadImage(file).then(function (img) {
            var canvas = drawScaled(img, img.naturalWidth, img.naturalHeight, PHOTO_MAX_SIDE);
            var turned = null;
            var upright = function () { return canvas; };
            var sideways = function () { return turned || (turned = rotated(canvas)); };
            // whole photo, then strips, then the same turned 90 degrees (barcode photographed sideways)
            var attempts = [upright].concat(strips(upright), [sideways], strips(sideways));
            return firstImei(attempts).then(function (result) {
                if (mySession !== session) {
                    return;
                }
                if (result.imei) {
                    el.view.classList.remove('is-busy');
                    onFound(result.imei);
                    return;
                }
                setStatus('error', photoProblem(img, canvas, result.sawBarcode));
                resumeLive();
            });
        }).catch(function () {
            if (mySession === session) {
                setStatus('error', MESSAGES.badImage);
                resumeLive();
            }
        });
    }

    /* ------------------------------------------------------------ events */

    openBtn.addEventListener('click', function () {
        modal.show();
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        el.view.classList.remove('is-found', 'is-busy');
        startCamera();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        stopCamera();
        el.view.classList.remove('is-busy');
    });

    // the phone may stop the camera when the browser goes to the background
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && modalEl.classList.contains('show')
            && !el.view.classList.contains('is-found') && (!track || track.readyState === 'ended')) {
            stopCamera();
            startCamera();
        }
    });

    el.torchBtn.addEventListener('click', function () {
        if (!track) {
            return;
        }
        var want = !torchOn;
        track.applyConstraints({ advanced: [{ torch: want }] }).then(function () {
            torchOn = want;
            el.torchBtn.classList.toggle('active', torchOn);
            el.torchBtn.setAttribute('aria-pressed', String(torchOn));
        }).catch(function () { /* flash not available */ });
    });

    el.zoom.addEventListener('input', function () {
        if (track) {
            track.applyConstraints({ advanced: [{ zoom: parseFloat(el.zoom.value) }] }).catch(function () { /* ignore */ });
        }
    });

    el.photoInput.addEventListener('change', function () {
        var file = el.photoInput.files && el.photoInput.files[0];
        el.photoInput.value = ''; // allow choosing the same photo again
        if (file) {
            scanPhoto(file);
        }
    });
})();
