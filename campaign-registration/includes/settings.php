<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/** The single campaign_settings row (id = 1). */
function campaign_settings(bool $fresh = false): array
{
    static $settings = null;
    if ($settings === null || $fresh) {
        $settings = db()->query('SELECT * FROM campaign_settings WHERE id = 1')->fetch();
        if (!$settings) {
            throw new RuntimeException('campaign_settings row id=1 is missing - import database.sql.');
        }
    }
    return $settings;
}

/**
 * 'over'     - is_active = 0, or the campaign end date has passed
 * 'upcoming' - the campaign start date is in the future
 * 'open'     - registrations accepted
 */
function campaign_state(array $settings): string
{
    $today = date('Y-m-d');
    if (!(int) $settings['is_active'] || $today > $settings['end_date']) {
        return 'over';
    }
    if ($today < $settings['start_date']) {
        return 'upcoming';
    }
    return 'open';
}

/**
 * Allowed Date of Purchase range: campaign start date to campaign end date,
 * but never later than today.
 *
 * @return array{0:string,1:string} [min, max] as Y-m-d
 */
function purchase_date_range(array $settings): array
{
    return [$settings['start_date'], min($settings['end_date'], date('Y-m-d'))];
}

/** Banner URLs relative to the project root; each falls back to the other. */
function banner_paths(array $settings): array
{
    $desktop = $settings['desktop_banner'] ?: $settings['mobile_banner'];
    $mobile  = $settings['mobile_banner'] ?: $settings['desktop_banner'];
    return ['desktop' => $desktop ?: null, 'mobile' => $mobile ?: null];
}

/** @return list<array{id:int,name:string,code:?string}> */
function active_stores(): array
{
    return db()->query('SELECT id, name, code FROM stores WHERE is_active = 1 ORDER BY name')->fetchAll();
}

/** @return list<array{id:int,name:string}> */
function active_cities(): array
{
    return db()->query('SELECT id, name FROM cities WHERE is_active = 1 ORDER BY name')->fetchAll();
}
