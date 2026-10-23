# Campaign Registration Website

Core PHP + MySQL + Bootstrap 5 campaign registration site with mobile OTP
(uEngage SMS panel + Airtel DLT), IMEI barcode scanning, duplicate
prevention, an admin panel with filters, and Excel (.xlsx) export.

## Features

| Area | What it does |
| --- | --- |
| Registration form | Name, Mobile (+OTP), IMEI (+barcode scan), Email, Date of Purchase, Store Name, Store Code (auto), City, in that order. Red `*` on mandatory fields. Separate desktop and mobile banners. |
| OTP | Send / Verify / Resend with 5 min expiry, 60 s resend cooldown, 5 wrong attempts per OTP, hourly limits per mobile and per IP. OTPs are stored only as HMAC hashes. Changing the mobile number requires a new verification. |
| Duplicates | Mobile, email and IMEI are each checked separately with their own error message, and each column has a `UNIQUE` key, so two simultaneous submissions cannot both succeed. |
| Dropdowns | Store Name, Store Code and City come from the `stores` and `cities` tables. Nothing is hard-coded. |
| Admin | Login with lockout, dashboard (total, today, by store, by city), searchable/filterable registrations, Excel export, campaign dates/status and banner uploads, change password. |
| Security | PDO prepared statements, CSRF tokens, output escaping, `password_hash`, session hardening, security headers + CSP, upload validation, `.htaccess` protection for internal folders. |

## Requirements

- PHP **8.1+** with extensions: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `gd`, `zip`, `xml`, `zlib`
  (standard on cPanel/most hosts).
- MySQL 5.7+ / 8.x or MariaDB 10.3+.
- Apache with `.htaccess` enabled (`AllowOverride All`), or Nginx (see below).
- **HTTPS** on the live site. Phone browsers only allow the camera (barcode scanning) on HTTPS.

## Folder structure

```
campaign-registration/
├── index.php                 Registration form (public)
├── api/                      send-otp.php, verify-otp.php, register.php (JSON)
├── admin/                    login, dashboard, registrations, export, settings, change-password
├── includes/                 bootstrap, db, helpers, otp, sms (uEngage), registrations, auth
├── config/config.sample.php  Copy to config/config.php and fill in
├── assets/                   css, js, img (default banners), lib (Bootstrap 5.3.3, html5-qrcode 2.3.8)
├── uploads/banners/          Banners uploaded from the admin panel (must be writable)
├── storage/logs/             sms.log, php-errors.log (must be writable)
├── vendor/                   PhpSpreadsheet (Composer) - already included
├── database.sql              Tables + starter data
└── composer.json / composer.lock
```

## Installation (cPanel / shared hosting)

1. **Upload** the whole `campaign-registration` folder (including `vendor/`) to the server,
   e.g. `public_html/campaign/`. It also works from the domain root.
2. **Create a database** and a database user in cPanel > MySQL Databases, and give the
   user *All Privileges* on that database.
3. **Import** `database.sql` in phpMyAdmin (select the database > Import). The file only
   uses `CREATE TABLE IF NOT EXISTS` / `INSERT IGNORE`, so re-importing never deletes data.
4. **Configure**: copy `config/config.sample.php` to `config/config.php` and set:
   - `db` - host, name, user, password.
   - `app.secret` - a long random string. Generate one with
     `php -r "echo bin2hex(random_bytes(32));"` (or any 64-character random string).
   - `sms` - the uEngage / Airtel DLT values (see the next section).
5. **Permissions**: `uploads/banners/` and `storage/logs/` must be writable by PHP
   (usually `755`; use `775` if uploads fail). Everything else can be read-only.
6. **Log in** at `https://your-domain/campaign/admin/` with `admin` / `Admin@123`. You are
   forced to set a new password first.
7. In **Campaign Settings** set the campaign name, start/end dates, active status and
   upload the desktop (1920 x 600 px) and mobile (1080 x 720 px) banners.
8. Replace the sample stores and cities with your real ones (see "Stores and cities").
9. Delete `database.sql` from the server after importing (it is blocked from the web anyway).

### Local testing without SMS

Set `'env' => 'local'` and `'sms' => ['driver' => 'log', ...]` in `config/config.php`. OTPs are
then written to `storage/logs/otp-local.log` instead of being sent. The `log` driver refuses
to work when `env` is `production`.

```bash
php -S 127.0.0.1:8000      # run from the project folder, then open http://127.0.0.1:8000
```

## uEngage SMS + Airtel DLT configuration

The integration sends exactly what you put in the `sms` section of `config/config.php`.
**No endpoint, parameter name or credential has been guessed** - copy them from your uEngage
API document / panel and the Airtel DLT portal. Until the required values are filled,
customers see "OTP service is temporarily unavailable" and `storage/logs/sms.log` lists the
missing keys.

### Details you need to provide

| # | Item | Where to get it | Config key |
| --- | --- | --- | --- |
| 1 | Send-SMS API URL | uEngage API document | `sms.api_url` |
| 2 | HTTP method (GET/POST) and body format (form/JSON) | uEngage API document | `sms.method`, `sms.post_format` |
| 3 | Authentication (API key / username + password / token header) | uEngage panel | `sms.auth_params` or `sms.headers` |
| 4 | Exact request parameter names for mobile, message, sender ID, DLT template ID, PE ID | uEngage API document | `sms.param_names` |
| 5 | Approved Sender ID / Header (6 characters) | Airtel DLT portal / uEngage panel | `sms.sender_id` |
| 6 | DLT Content Template ID of the OTP template | Airtel DLT portal | `sms.template_id` |
| 7 | Principal Entity ID (PE ID) | Airtel DLT portal | `sms.pe_id` |
| 8 | Approved OTP template text, copied exactly, with `{#var#}` placeholders | Airtel DLT portal | `sms.message_template`, `sms.template_vars` |
| 9 | Mobile number format (`91XXXXXXXXXX` or 10 digits) | uEngage API document | `sms.mobile_prefix` |
| 10 | Text that marks a successful response | uEngage API document (sample response) | `sms.success_match` |
| 11 | Any other fixed parameters (route, SMS type, telemarketer/chain ID, unicode flag) | uEngage API document | `sms.extra_params` |

### Example (placeholders - replace every value)

```php
'sms' => [
    'driver'      => 'uengage',
    'api_url'     => '<SEND SMS URL FROM UENGAGE DOC>',
    'method'      => 'GET',
    'auth_params' => ['<API KEY PARAM NAME>' => '<YOUR API KEY>'],
    'sender_id'   => '<APPROVED HEADER>',
    'template_id' => '<DLT TEMPLATE ID>',
    'pe_id'       => '<PE ID>',
    // Approved template, unchanged. Each {#var#} is filled in order from template_vars.
    'message_template' => '<e.g. Your OTP for campaign registration is {#var#}. Valid for {#var#} minutes. - BRAND>',
    'template_vars'    => ['otp', 'expiry_minutes'],
    'param_names' => [
        'mobile'      => '<MOBILE PARAM NAME>',
        'message'     => '<MESSAGE PARAM NAME>',
        'sender_id'   => '<SENDER PARAM NAME>',
        'template_id' => '<TEMPLATE ID PARAM NAME>',
        'pe_id'       => '<PE ID PARAM NAME>',   // null only if uEngage binds PE ID to the account
    ],
    'extra_params'  => [],
    'mobile_prefix' => '91',
    'success_match' => '<TEXT PRESENT IN A SUCCESS RESPONSE>',
],
```

- The template text must match the DLT-approved template character for character, or the
  operator will reject the SMS. Only the `{#var#}` parts change.
- `template_vars` must list one entry per `{#var#}` and must include `otp`.
  `expiry_minutes` inserts the OTP validity (5 by default). Any other string is inserted as is.
- After configuring, send a test OTP to your own number and check `storage/logs/sms.log`
  (it logs the masked number, HTTP status and the provider response; never the OTP).

### Error messages customers see

| Situation | Message |
| --- | --- |
| SMS settings incomplete | OTP service is temporarily unavailable. Please try again later. |
| uEngage rejects the request / network error | We could not send the OTP right now. Please try again in a few minutes. |
| Resend too early | Please wait N seconds before requesting a new OTP. |
| Too many requests | OTP request limit reached for this mobile number... / Too many OTP requests... |
| Wrong OTP | Incorrect OTP. N attempt(s) left. |
| Expired / used OTP | OTP has expired. Please request a new OTP. |

## Stores and cities

Dropdowns read only active rows (`is_active = 1`), sorted by name. Manage them in phpMyAdmin
or with SQL:

```sql
INSERT INTO cities (name) VALUES ('Surat');
INSERT INTO stores (name, code) VALUES ('Croma - Adajan', 'CRM-SUR-004');  -- code is optional (NULL)
UPDATE stores SET is_active = 0 WHERE id = 9;                              -- hide a store
```

Give stores distinct names (e.g. include the area), because the dropdown shows only the name.
Store codes must be unique when present. The store code is saved with each registration as it
was at that moment.

To import many rows, use phpMyAdmin > Import > CSV into the `stores` table (columns `name`, `code`).

## Campaign rules

- The form is open when the campaign is **Active** and today is on/after the start date.
  Switch it to Inactive in Campaign Settings to close registrations.
- Date of Purchase must be between the campaign start date and the end date, and not in
  the future. This is checked in the browser and again on the server.
- One registration per mobile number, per email ID and per IMEI number.
- IMEI must be 15 digits with a valid check digit. Set `app.imei_luhn_check` to `false` only
  if your products use identifiers that are not real IMEIs.

## Admin panel

- **Dashboard**: total registrations, today's registrations, campaign status, counts and share
  by store and by city (click a row to see those registrations).
- **Registrations**: search by name / mobile / IMEI, filter by store and city, choose *Registration
  Date* or *Purchase Date* with Start Date / End Date, Apply Filters / Reset Filters, total matching
  records, 25 rows per page.
- **Download Excel**: exports *all* records matching the current filters (not just the page) to
  `.xlsx` with bold coloured headers, column widths, `dd-mm-yyyy` dates, Excel filters, frozen
  header row, landscape print setup, and mobile/IMEI stored as text.
- **Campaign Settings**: name, start/end date, active switch, desktop and mobile banners (JPG, PNG
  or WEBP, max 2 MB, validated by content; old uploaded files are removed when replaced).
- Sessions expire after 30 minutes of inactivity; 5 wrong passwords lock the account for 15 minutes.

To add another admin user, generate a hash with `php -r "echo password_hash('NewPassword1', PASSWORD_DEFAULT);"`
and insert it:

```sql
INSERT INTO admin_users (username, password_hash, full_name, must_change_password)
VALUES ('manager', '<hash>', 'Campaign Manager', 1);
```

## Deployment checklist

- [ ] HTTPS enabled (needed for the camera scanner and secure cookies).
- [ ] `config/config.php` created with `'env' => 'production'`, a new `app.secret`, real DB and SMS values.
- [ ] Default admin password changed (forced on first login).
- [ ] `uploads/banners/` and `storage/logs/` writable.
- [ ] Test OTP received on a real phone; check `storage/logs/sms.log`.
- [ ] Campaign dates, active status, banners, stores and cities set.
- [ ] Behind Cloudflare / a load balancer? Set `app.client_ip_header` (e.g. `HTTP_CF_CONNECTING_IP`)
      so OTP limits apply per visitor instead of to the proxy IP.
- [ ] `database.sql` removed from the server after import.
- [ ] Open `/config/config.php`, `/storage/logs/sms.log` and `/vendor/autoload.php` in a browser - all must show **403 Forbidden**.

### Nginx

`.htaccess` files are ignored by Nginx. Add to the server block (adjust the `/campaign` prefix):

```nginx
location ~ ^/campaign/(config|includes|storage|vendor|admin/partials)/ { deny all; }
location ~ ^/campaign/uploads/.*\.(php\d?|phtml|phar|html?|svg|js)$  { deny all; }
location ~ ^/campaign/.*\.(sql|md|lock|log|json)$                      { deny all; }
```

### Updating PhpSpreadsheet

`vendor/` is included so no Composer is needed on the server. To update it on a machine with
Composer: `composer update --no-dev` in the project folder, then upload the new `vendor/`.

## Troubleshooting

| Problem | Fix |
| --- | --- |
| "Setup required: copy config/config.sample.php..." | Create `config/config.php`. |
| "Something went wrong" | See `storage/logs/php-errors.log` (usually DB credentials). Set `'env' => 'local'` temporarily to see the error on screen. |
| OTP not received | `storage/logs/sms.log` shows the provider response. Check template text, IDs and parameter names against the uEngage document. |
| 500 error right after upload | Your host may not allow `Options` in `.htaccess`; remove the `Options ...` lines from `.htaccess` and `uploads/banners/.htaccess`. |
| Banner upload fails | Make `uploads/banners/` writable; check PHP `upload_max_filesize` / `post_max_size` (at least 3M). |
| Scan button opens but camera does not start | Site must be on HTTPS and camera permission allowed. Customers can also upload a photo of the barcode or type the IMEI. |
| Excel download says to run composer install | The `vendor/` folder was not uploaded. |
