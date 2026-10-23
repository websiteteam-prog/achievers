<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/* =====================================================================
 *  Public registration: validation, duplicate checks, insert
 * ===================================================================== */

/**
 * Validate the submitted registration form.
 *
 * @return array{0:array<string,mixed>,1:array<string,string>} [clean data, field errors]
 */
function registration_validate(array $input, array $settings): array
{
    $data = [];
    $errors = [];

    $name = trim(preg_replace('/\s+/u', ' ', (string) ($input['name'] ?? '')));
    if ($name === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !preg_match("/^\p{L}[\p{L}\p{M}\s.'-]*$/u", $name)) {
        $errors['name'] = 'Please enter a valid name (letters and spaces only).';
    }
    $data['name'] = $name;

    $rawMobile = trim((string) ($input['mobile'] ?? ''));
    $mobile = normalize_mobile($rawMobile);
    if ($rawMobile === '') {
        $errors['mobile'] = 'Please enter your mobile number.';
    } elseif ($mobile === null) {
        $errors['mobile'] = 'Please enter a valid 10-digit mobile number.';
    }
    $data['mobile'] = $mobile;

    $imei = preg_replace('/[\s-]+/', '', (string) ($input['imei'] ?? ''));
    if ($imei === '') {
        $errors['imei'] = 'Please enter the IMEI number.';
    } elseif (!preg_match('/^\d{15}$/', $imei)) {
        $errors['imei'] = 'IMEI number must be exactly 15 digits.';
    } elseif (!valid_imei($imei, (bool) config('app.imei_luhn_check', true))) {
        $errors['imei'] = 'Please enter a valid IMEI number.';
    }
    $data['imei'] = $imei;

    $email = mb_strtolower(trim((string) ($input['email'] ?? '')));
    if ($email === '') {
        $errors['email'] = 'Please enter your email ID.';
    } elseif (strlen($email) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email ID.';
    }
    $data['email'] = $email;

    [$minDate, $maxDate] = purchase_date_range($settings);
    $purchase = parse_date($input['purchase_date'] ?? null);
    if (trim((string) ($input['purchase_date'] ?? '')) === '') {
        $errors['purchase_date'] = 'Please select the date of purchase.';
    } elseif ($purchase === null) {
        $errors['purchase_date'] = 'Please select a valid date of purchase.';
    } elseif ($purchase->format('Y-m-d') < $minDate || $purchase->format('Y-m-d') > $maxDate) {
        $errors['purchase_date'] = sprintf(
            'Date of purchase must be between %s and %s.',
            format_date($minDate),
            format_date($maxDate)
        );
    }
    $data['purchase_date'] = $purchase?->format('Y-m-d');

    $storeId = filter_var($input['store_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $store = null;
    if ($storeId !== false) {
        $stmt = db()->prepare('SELECT id, code FROM stores WHERE id = ? AND is_active = 1');
        $stmt->execute([$storeId]);
        $store = $stmt->fetch() ?: null;
    }
    if ($store === null) {
        $errors['store_id'] = 'Please select your store.';
    }
    $data['store_id'] = $store['id'] ?? null;
    $data['store_code'] = ($store['code'] ?? '') !== '' ? $store['code'] : null; // always taken from the DB

    $cityId = filter_var($input['city_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $cityOk = false;
    if ($cityId !== false) {
        $stmt = db()->prepare('SELECT 1 FROM cities WHERE id = ? AND is_active = 1');
        $stmt->execute([$cityId]);
        $cityOk = (bool) $stmt->fetchColumn();
    }
    if (!$cityOk) {
        $errors['city_id'] = 'Please select your city.';
    }
    $data['city_id'] = $cityOk ? $cityId : null;

    return [$data, $errors];
}

/** Checks mobile, email and IMEI independently against existing registrations. */
function registration_duplicate_errors(string $mobile, string $email, string $imei): array
{
    $stmt = db()->prepare(
        'SELECT EXISTS(SELECT 1 FROM registrations WHERE mobile = ?) AS mobile,
                EXISTS(SELECT 1 FROM registrations WHERE email = ?)  AS email,
                EXISTS(SELECT 1 FROM registrations WHERE imei = ?)   AS imei'
    );
    $stmt->execute([$mobile, $email, $imei]);
    $found = $stmt->fetch();

    $errors = [];
    foreach (registration_duplicate_messages() as $field => $message) {
        if ((int) $found[$field] === 1) {
            $errors[$field] = $message;
        }
    }
    return $errors;
}

function registration_duplicate_messages(): array
{
    return [
        'mobile' => 'This mobile number is already registered.',
        'email'  => 'This email ID is already registered.',
        'imei'   => 'This IMEI number is already registered.',
    ];
}

/**
 * Insert the registration and mark the OTP as used, in one transaction.
 * The UNIQUE keys are the final guard against simultaneous duplicates.
 *
 * @return array{id:?int, errors:array<string,string>, reverify:bool}
 */
function registration_create(array $data, int $otpId): array
{
    $pdo = db();
    $now = db_datetime(now());

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE otp_verifications SET consumed_at = ? WHERE id = ? AND consumed_at IS NULL');
        $stmt->execute([$now, $otpId]);
        if ($stmt->rowCount() !== 1) {
            $pdo->rollBack();
            return ['id' => null, 'errors' => ['mobile' => 'Please verify your mobile number with OTP.'], 'reverify' => true];
        }

        $pdo->prepare(
            'INSERT INTO registrations
                (name, mobile, imei, email, purchase_date, store_id, store_code, city_id,
                 otp_verification_id, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $data['name'],
            $data['mobile'],
            $data['imei'],
            $data['email'],
            $data['purchase_date'],
            $data['store_id'],
            $data['store_code'],
            $data['city_id'],
            $otpId,
            client_ip(),
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            $now,
        ]);
        $id = (int) $pdo->lastInsertId();

        $pdo->commit();
        return ['id' => $id, 'errors' => [], 'reverify' => false];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (!is_duplicate_key_error($e)) {
            throw $e;
        }
        // Lost a race with a simultaneous submission: report which value clashed.
        $errors = registration_duplicate_errors($data['mobile'], $data['email'], $data['imei']);
        return ['id' => null, 'errors' => $errors ?: ['form' => 'These details are already registered.'], 'reverify' => false];
    }
}

function registration_number(int $id): string
{
    return 'REG' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

/* =====================================================================
 *  Admin: date filter shared by the registrations list and Excel export
 * ===================================================================== */

/**
 * Read and validate the date filter from the query string.
 *
 * @return array{date_type:string,start_date:string,end_date:string,errors:list<string>}
 */
function registration_filters(array $query): array
{
    $filters = [
        'date_type'  => ($query['date_type'] ?? '') === 'purchase' ? 'purchase' : 'registration',
        'start_date' => '',
        'end_date'   => '',
        'errors'     => [],
    ];

    foreach (['start_date' => 'Start Date', 'end_date' => 'End Date'] as $key => $label) {
        $raw = trim((string) ($query[$key] ?? ''));
        if ($raw === '') {
            continue;
        }
        if (parse_date($raw) === null) {
            $filters['errors'][] = "{$label} is not a valid date and was ignored.";
            continue;
        }
        $filters[$key] = $raw;
    }

    if ($filters['start_date'] !== '' && $filters['end_date'] !== '' && $filters['start_date'] > $filters['end_date']) {
        $filters['errors'][] = 'Start Date cannot be after End Date - the date filter was ignored.';
        $filters['start_date'] = $filters['end_date'] = '';
    }

    return $filters;
}

/** @return array{0:string,1:list<string>} [WHERE clause (may be empty), params] */
function registration_where(array $filters): array
{
    $where = [];
    $params = [];

    if ($filters['date_type'] === 'purchase') {
        if ($filters['start_date'] !== '') {
            $where[] = 'r.purchase_date >= ?';
            $params[] = $filters['start_date'];
        }
        if ($filters['end_date'] !== '') {
            $where[] = 'r.purchase_date <= ?';
            $params[] = $filters['end_date'];
        }
    } else {
        if ($filters['start_date'] !== '') {
            $where[] = 'r.created_at >= ?';
            $params[] = $filters['start_date'] . ' 00:00:00';
        }
        if ($filters['end_date'] !== '') {
            $where[] = 'r.created_at < ?';
            $params[] = (new DateTimeImmutable($filters['end_date']))->modify('+1 day')->format('Y-m-d') . ' 00:00:00';
        }
    }

    return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
}

/** Query-string values for links that keep the current filter. */
function registration_filter_query(array $filters): array
{
    if ($filters['start_date'] === '' && $filters['end_date'] === '') {
        return [];
    }
    return array_filter([
        'date_type'  => $filters['date_type'],
        'start_date' => $filters['start_date'],
        'end_date'   => $filters['end_date'],
    ], static fn ($v) => $v !== '');
}

function registration_select_sql(): string
{
    return 'SELECT r.id, r.name, r.mobile, r.email, r.imei, r.purchase_date, r.store_code, r.created_at,
                   s.name AS store_name, c.name AS city_name
              FROM registrations r
              JOIN stores s ON s.id = r.store_id
              JOIN cities c ON c.id = r.city_id';
}
