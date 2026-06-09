<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mqtt.php';
require_once __DIR__ . '/auth.php';

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

$topic = 'devices/' . $device['imei'] . '/cmd';

$payload = [
    'action'         => 'ota',
    'target_version' => $latestFw,
    'bin_url'        => $latestUrl,
    'requested_by'   => $_SESSION['username'] ?? 'unknown',
];

try {
    mqtt_publish($topic, $payload, 1, false);
    header('Location: ' . APP_BASE . '/device.php?id=' . $deviceId . '&ota=queued');
    exit;
} catch (Throwable $e) {
    error_log('MQTT publish failed: ' . $e->getMessage());
    header('Location: ' . APP_BASE . '/device.php?id=' . $deviceId . '&ota=publish-failed');
    exit;
}
