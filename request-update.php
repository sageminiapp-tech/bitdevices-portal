<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$deviceId = (int)($_POST['device_id'] ?? 0);
if ($deviceId <= 0) {
    http_response_code(400);
    exit('Invalid device id');
}

$pdo = db();

$stmt = $pdo->prepare('SELECT id, imei, chipid, fw_version FROM devices WHERE id = ? LIMIT 1');
$stmt->execute([$deviceId]);
$device = $stmt->fetch();

if (!$device) {
    http_response_code(404);
    exit('Device not found');
}

$currentFw = trim((string)($device['fw_version'] ?? ''));
$latestFw  = latest_fw_version() ?? '';
$latestUrl = latest_fw_url() ?? '';

if ($latestFw === '' || $latestUrl === '') {
    header('Location: ' . APP_BASE . '/device.php?id=' . $deviceId . '&ota=no-manifest');
    exit;
}

if ($currentFw !== '' && !version_compare($latestFw, $currentFw, '>')) {
    header('Location: ' . APP_BASE . '/device.php?id=' . $deviceId . '&ota=up-to-date');
    exit;
}

// avoid duplicate queued OTA commands
$check = $pdo->prepare('
    SELECT id FROM device_commands
    WHERE device_id = ? AND command_type = "ota" AND status IN ("queued","sent")
    ORDER BY id DESC
    LIMIT 1
');
$check->execute([$deviceId]);
$existing = $check->fetchColumn();

if (!$existing) {
    $payload = json_encode([
        'target_version' => $latestFw,
        'bin_url'        => $latestUrl,
        'requested_by'   => $_SESSION['username'] ?? 'unknown',
    ], JSON_UNESCAPED_SLASHES);

    $ins = $pdo->prepare('
        INSERT INTO device_commands (device_id, command_type, payload_json, status)
        VALUES (?, "ota", ?, "queued")
    ');
    $ins->execute([$deviceId, $payload]);
}

header('Location: ' . APP_BASE . '/device.php?id=' . $deviceId . '&ota=queued');
exit;