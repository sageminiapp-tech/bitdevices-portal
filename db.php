<?php
require_once __DIR__ . '/config.php';

function db(): PDO {

// error_log('MYSQLHOST=' . var_export(getenv('MYSQLHOST'), true));
// error_log('MYSQLPORT=' . var_export(getenv('MYSQLPORT'), true));
// error_log('MYSQLUSER=' . var_export(getenv('MYSQLUSER'), true));
// error_log('MYSQLDATABASE=' . var_export(getenv('MYSQLDATABASE'), true));
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    
// Force MySQL session time zone to South Africa (UTC+2)
    $pdo->exec("SET time_zone = '+02:00'");

    return $pdo;
}

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function manifest_data(): array {
    if (!is_file(MANIFEST_PATH)) {
        return [];
    }
    $raw = file_get_contents(MANIFEST_PATH);
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

function latest_fw_version(): ?string {
    $m = manifest_data();
    return $m['version'] ?? null;
}

function latest_fw_url(): ?string {
    $m = manifest_data();
    return $m['bin_url'] ?? null;
}

function is_device_online(?string $lastSeen): bool {
    if (!$lastSeen) return false;
    $ts = strtotime($lastSeen);
    if ($ts === false) return false;
    return (time() - $ts) <= DEVICE_ONLINE_SECONDS;
}

function normalize_float($value): ?float {
    if ($value === null || $value === '') return null;
    return is_numeric($value) ? (float)$value : null;
}

function normalize_int($value): ?int {
    if ($value === null || $value === '') return null;
    return is_numeric($value) ? (int)$value : null;
}
