<?php
declare(strict_types=1);

defined('APP_ROOT') || exit;

/*
 * Minimal .env reader (no Composer package needed).
 *   KEY=value
 *   KEY="value with spaces or # characters"
 *   # comment lines and "  # inline comments" after unquoted values are ignored
 * Values are kept in memory only (not exported to getenv()/phpinfo()).
 */

function load_env_file(string $file): array
{
    $vars = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!preg_match('/^(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $m)) {
            continue;
        }
        $value = $m[2];
        // Quoted only when the quotes wrap the whole value, so text such as
        // "status":"success" (an SMS success response) is kept as written.
        if (preg_match('/^"((?:[^"\\\\]|\\\\.)*)"\s*(?:#.*)?$/', $value, $q)) {
            $value = strtr($q[1], ['\\"' => '"', '\\\\' => '\\', '\\n' => "\n"]);
        } elseif (preg_match("/^'([^']*)'\s*(?:#.*)?$/", $value, $q)) {
            $value = $q[1];
        } else {
            $value = trim(preg_replace('/\s+#.*$/', '', $value));
        }
        $vars[$m[1]] = $value;
    }
    return $vars;
}

function env(string $key, string $default = ''): string
{
    $value = $GLOBALS['__app_env'][$key] ?? '';
    return $value === '' ? $default : $value;
}

function env_int(string $key, int $default): int
{
    $value = env($key);
    return is_numeric($value) ? (int) $value : $default;
}

function env_bool(string $key, bool $default): bool
{
    $value = strtolower(env($key));
    if ($value === '') {
        return $default;
    }
    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

/** "a=1&b=2" -> ['a' => '1', 'b' => '2'] (values may be URL-encoded) */
function env_query(string $key): array
{
    parse_str(env($key), $out);
    return array_map('strval', $out);
}

/** "x, y ,z" -> ['x', 'y', 'z'] */
function env_list(string $key, string $separator = ','): array
{
    return array_values(array_filter(array_map('trim', explode($separator, env($key))), 'strlen'));
}
