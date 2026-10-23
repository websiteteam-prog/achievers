# Campaign Registration Website

Core PHP + MySQL + Bootstrap 5 campaign registration site (dark theme) with mobile OTP
(uEngage SMS + Airtel DLT, with a DEV OTP until the SMS API is configured), IMEI barcode
scanning, duplicate prevention, and an admin panel that lists registrations with a date
filter and Excel (.xlsx) download.

## What is included

| Area | What it does |
| --- | --- |
| Registration form | Name, Mobile (+OTP), IMEI (+barcode scan), Email, Date of Purchase, Store Name, Store Code (auto), City, in that order. Red `*` on mandatory fields. Separate desktop and mobile banners. |
| Campaign closed | When the campaign is inactive or its end date has passed, visitors see the "Campaign is over" card. Before the start date they see "Campaign starts soon". |
| OTP | Send / Verify / Resend, 5 min expiry, 60 s resend cooldown, 5 wrong attempts per OTP, hourly limits per mobile and per IP. OTPs are stored only as hashes. Changing the mobile number needs a new verification. |
| DEV OTP | While `SMS_API_URL` in `.env` is empty, no SMS is sent and the OTP is `DEV_OTP` (default `123456`). It is shown on the form, and a **DEV OTP MODE** badge is displayed. As soon as `SMS_API_URL` is filled in, real SMS OTPs are used and the dev OTP stops working. |
| Duplicates | Mobile, email and IMEI are each checked separately with their own message, and each has a `UNIQUE` key in MySQL, so simultaneous submissions cannot create duplicates. |
| Dropdowns | Store Name, Store Code and City come from the `stores` and `cities` tables. |
| Admin | Email + password login (mail icon, eye icon to show/hide the password). Shows only the registrations list: date filter (Registration Date or Purchase Date, Start/End Date), Apply / Reset, total count, and Download Excel. |
| Excel | All records matching the date filter, bold coloured headers, column widths, `dd-mm-yyyy` dates, Excel filters, frozen header row, mobile/IMEI kept as text. |
| Security | PDO prepared statements, CSRF tokens, output escaping, `password_hash`, login lockout, session hardening, security headers + CSP, `.env` and internal folders blocked from the web. |

## Requirements

- PHP **8.1+** with `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd`, `zip`, `xml`, `zlib` (standard on cPanel).
- MySQL 5.7+ / 8.x or MariaDB 10.3+.
- Apache with `.htaccess` enabled, or Nginx (see below).
- **HTTPS** on the live site (phones only allow the camera for barcode scanning on HTTPS).

## Files

```
campaign-registration/
├── .env                 ALL settings: database, OTP, uEngage SMS / DLT  (keep private)
├── .env.example         Same keys, empty - reference copy
├── index.php            Registration form / "Campaign is over" page
├── api/                 send-otp.php, verify-otp.php, register.php
├── admin/               login, registrations list (index.php), export.php, change-password, logout
├── includes/            app code (env loader, db, otp, uEngage sms, auth, ...)
├── assets/              css, js, img (banners), lib (Bootstrap 5.3.3, html5-qrcode 2.3.8)
├── storage/logs/        sms.log, php-errors.log (must be writable)
├── vendor/              PhpSpreadsheet (already included, no Composer needed)
└── database.sql         tables + starter data
```

## Installation

1. Upload the `campaign-registration` folder (including `.env` and `vendor/`) to the server,
   e.g. `public_html/campaign/`. Hidden files like `.env` must be uploaded too (enable
   "show hidden files" in your FTP client / cPanel File Manager).
2. Create a MySQL database and user (cPanel > MySQL Databases) and import `database.sql`
   in phpMyAdmin. Re-importing is safe: it never drops tables or deletes data.
3. Open `.env` and set:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `APP_SECRET` - already filled with a random value; you may replace it with your own
     (`php -r "echo bin2hex(random_bytes(32));"`). Changing it later only invalidates OTPs
     that are pending at that moment.
   - Leave the `SMS_*` values empty for now to use the DEV OTP.
4. Make `storage/logs/` writable (755, or 775 if logs are not created).
5. Open `https://your-domain/campaign/admin/` and log in with
   **admin@example.com / Admin@123**. You must set a new password first.
   To use your own email, change it in phpMyAdmin: table `admin_users`, column `email`.
6. Set the campaign in phpMyAdmin, table `campaign_settings` (row `id = 1`):
   `campaign_name`, `start_date`, `end_date`, `is_active` (1 = open, 0 = "Campaign is over").
7. Replace the banners: overwrite `assets/img/banner-desktop.jpg` (1920 x 600 px) and
   `assets/img/banner-mobile.jpg` (1080 x 720 px), or upload new files and put their paths in
   `campaign_settings.desktop_banner` / `mobile_banner`.
8. Replace the sample stores and cities (see below), then delete `database.sql` from the server.

Local test (XAMPP / PHP built-in server): set `APP_ENV=local` in `.env` to see PHP errors, then
`php -S 127.0.0.1:8000` in the project folder and open http://127.0.0.1:8000.

## Going live with uEngage SMS (Airtel DLT)

Fill the `SMS_*` values in `.env`. The moment `SMS_API_URL` has a value, the site switches from
the DEV OTP to real SMS automatically (`OTP_MODE=auto`). If any other required SMS value is
missing, customers get "OTP service is temporarily unavailable" (the dev OTP is **not** used
as a fallback) and `storage/logs/sms.log` lists what is missing.

No endpoint, parameter name or credential has been guessed. Copy them from the uEngage API
document / panel and the Airtel DLT portal:

| # | Needed | Where from | `.env` key |
| --- | --- | --- | --- |
| 1 | Send-SMS API URL | uEngage API document | `SMS_API_URL` |
| 2 | GET or POST (and form/json body for POST) | uEngage API document | `SMS_METHOD`, `SMS_POST_FORMAT` |
| 3 | API key / username + password | uEngage panel | `SMS_AUTH_PARAMS` (or `SMS_HEADERS`) |
| 4 | Parameter names for mobile, message, sender, template ID, PE ID | uEngage API document | `SMS_PARAM_*` |
| 5 | Approved Sender ID / Header | DLT portal / uEngage panel | `SMS_SENDER_ID` |
| 6 | DLT Content Template ID | DLT portal | `SMS_TEMPLATE_ID` |
| 7 | Principal Entity ID | DLT portal | `SMS_PE_ID` |
| 8 | Approved OTP template text (exact), `{#var#}` placeholders | DLT portal | `SMS_MESSAGE_TEMPLATE`, `SMS_TEMPLATE_VARS` |
| 9 | Mobile format: `91XXXXXXXXXX` or 10 digits | uEngage API document | `SMS_MOBILE_PREFIX` (`91` or `none`) |
| 10 | Text in a successful response | uEngage sample response | `SMS_SUCCESS_MATCH` |
| 11 | Other fixed parameters (route, type, chain/telemarketer ID...) | uEngage API document | `SMS_EXTRA_PARAMS` |

Example (every value is a placeholder):

```dotenv
SMS_API_URL=https://<uengage-send-sms-url-from-document>
SMS_METHOD=GET
SMS_AUTH_PARAMS="<apikey-param-name>=<your-api-key>"
SMS_SENDER_ID=<HEADER>
SMS_TEMPLATE_ID=<dlt-template-id>
SMS_PE_ID=<pe-id>
SMS_MESSAGE_TEMPLATE="Your OTP for campaign registration is {#var#}. Valid for {#var#} minutes. - BRAND"
SMS_TEMPLATE_VARS=otp,expiry_minutes
SMS_PARAM_MOBILE=<mobile-param-name>
SMS_PARAM_MESSAGE=<message-param-name>
SMS_PARAM_SENDER_ID=<sender-param-name>
SMS_PARAM_TEMPLATE_ID=<template-id-param-name>
SMS_PARAM_PE_ID=<pe-id-param-name>
SMS_MOBILE_PREFIX=91
SMS_SUCCESS_MATCH=<text-present-in-success-response>
```

- The template must match the DLT-approved text exactly; only `{#var#}` parts are replaced.
  `SMS_TEMPLATE_VARS` lists what goes into each `{#var#}` in order (`otp`, `expiry_minutes`).
- Put values that contain spaces or `#` in double quotes. A value such as
  `"status":"success"` can be written as is.
- Test by requesting an OTP on your own number, then check `storage/logs/sms.log`
  (masked number, HTTP status, provider response; the OTP itself is never logged).
- `OTP_MODE=dev` forces the dev OTP, `OTP_MODE=live` forces real SMS.

## Stores and cities

Managed in phpMyAdmin (dropdowns show active rows, sorted by name):

```sql
INSERT INTO cities (name) VALUES ('Surat');
INSERT INTO stores (name, code) VALUES ('Croma - Adajan', 'CRM-SUR-004');  -- code optional (NULL)
UPDATE stores SET is_active = 0 WHERE id = 9;                              -- hide a store
```

Give stores distinct names (include the area). Store codes must be unique when present.

## Rules

- The form is open when `is_active = 1` and today is between `start_date` and `end_date`.
- Date of Purchase must be within the campaign period and not in the future (checked in the
  browser and on the server).
- One registration per mobile number, per email ID and per IMEI number.
- IMEI must be 15 digits with a valid check digit (`IMEI_LUHN_CHECK=false` turns the check-digit test off).

## Deployment checklist

- [ ] HTTPS enabled.
- [ ] `.env` uploaded with `APP_ENV=production` and real DB values.
- [ ] Opening `https://your-domain/campaign/.env` shows **403 Forbidden**
      (also `/storage/logs/sms.log` and `/vendor/autoload.php`).
- [ ] Admin password changed (forced at first login) and admin email updated.
- [ ] Campaign dates / status, banners, stores and cities set.
- [ ] Before launch: SMS values filled in `.env`, a real OTP received, DEV OTP MODE badge gone.
- [ ] Behind Cloudflare / a load balancer: set `CLIENT_IP_HEADER` (e.g. `HTTP_CF_CONNECTING_IP`).
- [ ] `database.sql` deleted from the server after import.

### Nginx

`.htaccess` is ignored by Nginx; add to the server block (adjust the `/campaign` prefix):

```nginx
location ~ /\.                                                  { deny all; }
location ~ ^/campaign/(includes|storage|vendor|admin/partials)/ { deny all; }
location ~ ^/campaign/.*\.(sql|md|lock|log|json|example)$       { deny all; }
```

## Troubleshooting

| Problem | Fix |
| --- | --- |
| "Setup required: copy .env.example to .env" | `.env` was not uploaded (hidden file). |
| "Something went wrong" | Check `storage/logs/php-errors.log` (usually DB values in `.env`). `APP_ENV=local` shows the error on screen. |
| OTP not received | See `storage/logs/sms.log`; compare template text, IDs and parameter names with the uEngage document. |
| 500 error after upload | The host may not allow `Options` in `.htaccess`; remove the `Options -Indexes` line. |
| Camera does not start | Needs HTTPS and camera permission. Customers can upload a photo of the barcode or type the IMEI. |
| Excel download asks for composer install | The `vendor/` folder was not uploaded. |
