<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require APP_ROOT . '/includes/auth.php';

$admin = require_admin();
$settings = campaign_settings();
$errors = [];
$form = $settings;

const BANNER_DIR = 'uploads/banners';
const BANNER_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/**
 * Validate one uploaded banner and move it into uploads/banners.
 *
 * @return array{0:?string,1:?string} [new relative path or null when no file, error]
 */
function store_banner(string $field, string $label): array
{
    $file = $_FILES[$field] ?? null;
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    $maxBytes = (int) config('uploads.max_banner_bytes', 2 * 1024 * 1024);
    $maxText = round($maxBytes / 1048576, 1) . ' MB';

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, "{$label} is larger than {$maxText}."];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return [null, "{$label} could not be uploaded. Please try again."];
    }
    if ($file['size'] > $maxBytes) {
        return [null, "{$label} is larger than {$maxText}."];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $info = @getimagesize($file['tmp_name']);
    if (!isset(BANNER_TYPES[$mime]) || $info === false || $info['mime'] !== $mime) {
        return [null, "{$label} must be a JPG, PNG or WEBP image."];
    }
    if ($info[0] < 300 || $info[1] < 100 || $info[0] > 6000 || $info[1] > 6000) {
        return [null, "{$label} dimensions look wrong ({$info[0]} x {$info[1]} px). See the recommended size."];
    }

    $prefix = $field === 'desktop_banner' ? 'desktop' : 'mobile';
    $relative = BANNER_DIR . '/' . $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . BANNER_TYPES[$mime];
    if (!move_uploaded_file($file['tmp_name'], APP_ROOT . '/' . $relative)) {
        return [null, "{$label} could not be saved. Check that uploads/banners is writable."];
    }
    @chmod(APP_ROOT . '/' . $relative, 0644);

    return [$relative, null];
}

/** Delete a previously uploaded banner (never touches the default images in assets/). */
function delete_old_banner(?string $path): void
{
    if ($path && str_starts_with($path, BANNER_DIR . '/') && !str_contains($path, '..')) {
        @unlink(APP_ROOT . '/' . $path);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'The uploaded files are too large for the server. Please upload smaller images.';
    } elseif (!csrf_valid()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $form['campaign_name'] = trim((string) ($_POST['campaign_name'] ?? ''));
        $form['start_date'] = (string) ($_POST['start_date'] ?? '');
        $form['end_date'] = (string) ($_POST['end_date'] ?? '');
        $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

        if ($form['campaign_name'] === '' || mb_strlen($form['campaign_name']) > 150) {
            $errors[] = 'Campaign name is required (maximum 150 characters).';
        }
        $start = parse_date($form['start_date']);
        $end = parse_date($form['end_date']);
        if ($start === null) {
            $errors[] = 'Please enter a valid campaign start date.';
        }
        if ($end === null) {
            $errors[] = 'Please enter a valid campaign end date.';
        }
        if ($start && $end && $end < $start) {
            $errors[] = 'Campaign end date cannot be before the start date.';
        }

        $uploaded = [];
        if (!$errors) {
            foreach (['desktop_banner' => 'Desktop banner', 'mobile_banner' => 'Mobile banner'] as $field => $label) {
                [$path, $error] = store_banner($field, $label);
                if ($error !== null) {
                    $errors[] = $error;
                } elseif ($path !== null) {
                    $uploaded[$field] = $path;
                }
            }
        }

        if ($errors) {
            array_map('delete_old_banner', $uploaded); // keep nothing from a rejected submission
        } else {
            db()->prepare(
                'UPDATE campaign_settings
                    SET campaign_name = ?, start_date = ?, end_date = ?, is_active = ?,
                        desktop_banner = ?, mobile_banner = ?
                  WHERE id = 1'
            )->execute([
                $form['campaign_name'],
                $form['start_date'],
                $form['end_date'],
                $form['is_active'],
                $uploaded['desktop_banner'] ?? $settings['desktop_banner'],
                $uploaded['mobile_banner'] ?? $settings['mobile_banner'],
            ]);
            foreach ($uploaded as $field => $path) {
                delete_old_banner($settings[$field]);
            }
            flash('success', 'Campaign settings saved.');
            redirect('settings.php');
        }
    }
}

$maxMb = round((int) config('uploads.max_banner_bytes', 2 * 1024 * 1024) / 1048576, 1);
$pageTitle = 'Campaign Settings';
$activeNav = 'settings';
require __DIR__ . '/partials/header.php';
?>
<h1 class="h4 mb-3">Campaign Settings</h1>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-danger py-2"><?= e($error) ?></div>
<?php endforeach; ?>

<form method="post" action="settings.php" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Campaign</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="campaign_name" class="form-label">Campaign Name <span class="req">*</span></label>
                        <input type="text" class="form-control" id="campaign_name" name="campaign_name" maxlength="150"
                               value="<?= e($form['campaign_name']) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="start_date" class="form-label">Campaign Start Date <span class="req">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= e($form['start_date']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="end_date" class="form-label">Campaign End Date <span class="req">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= e($form['end_date']) ?>" required>
                        </div>
                    </div>
                    <p class="form-text mt-0">Customers can only select a Date of Purchase within this period (and not after today).</p>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                            <?= (int) $form['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Campaign active (registration form is open)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Banners</div>
                <div class="card-body">
                    <p class="small text-muted">JPG, PNG or WEBP, up to <?= e($maxMb) ?> MB. Leave empty to keep the current banner.</p>
                    <div class="row g-4">
                        <div class="col-12 col-md-7">
                            <label for="desktop_banner" class="form-label">Desktop Banner</label>
                            <?php if ($settings['desktop_banner']): ?>
                                <div class="mb-2"><img src="../<?= e($settings['desktop_banner']) ?>" alt="Current desktop banner" class="banner-preview"></div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="desktop_banner" name="desktop_banner" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Recommended 1920 x 600 px. Shown on screens 768 px and wider.</div>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="mobile_banner" class="form-label">Mobile Banner</label>
                            <?php if ($settings['mobile_banner']): ?>
                                <div class="mb-2"><img src="../<?= e($settings['mobile_banner']) ?>" alt="Current mobile banner" class="banner-preview"></div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="mobile_banner" name="mobile_banner" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Recommended 1080 x 720 px. Shown on phones.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Settings</button>
        <a href="../index.php" class="btn btn-outline-secondary" target="_blank" rel="noopener">View Registration Page</a>
    </div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
