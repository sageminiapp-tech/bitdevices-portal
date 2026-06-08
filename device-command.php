<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('X-Powered-By: BitDevices/1.0');

function json_response(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// API key validation (same pattern as devreg.php)
$apiKey = null;

if (function_exists('getallheaders')) {
    $headers = getallheaders();
    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'x-api-key') {
            $apiKey = $value;
            break;
        }
    }
}

if (!$apiKey && isset($_SERVER['HTTP_X_API_KEY'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'];
}

if (!$apiKey) {
    $apiKey = $_GET['api_key'] ?? null;
}

if ($apiKey !== PORTAL_API_KEY) {
    json_response(401, ['ok' => false, 'error' => 'unauthorized']);
}

$imei = trim((string)($_GET['imei'] ?? ''));
if ($imei === '') {
    json_response(400, ['ok' => false, 'error' => 'imei required']);
}

$pdo = db();

$dev = $pdo->prepare('SELECT id, imei, fw_version FROM devices WHERE imei = ? LIMIT 1');
$dev->execute([$imei]);
$device = $dev->fetch();

if (!$device) {
    json_response(404, ['ok' => false, 'error' => 'device not found']);
}

$sql = '
    SELECT id, command_type, payload_json
    FROM device_commands
    WHERE device_id = ?
      AND status = "queued"
    ORDER BY created_at ASC, id ASC
    LIMIT 1
';

$stmt = $pdo->prepare($sql);
$stmt->execute([(int)$device['id']]);
$cmd = $stmt->fetch();

if (!$cmd) {
    json_response(200, [
        'ok' => true,
        'command' => null
    ]);
}

$upd = $pdo->prepare('UPDATE device_commands SET status = "sent", sent_at = NOW() WHERE id = ?');
$upd->execute([(int)$cmd['id']]);

json_response(200, [
    'ok' => true,
    'command' => [
        'id'      => (int)$cmd['id'],
        'type'    => $cmd['command_type'],
        'payload' => $cmd['payload_json'] ? json_decode($cmd['payload_json'], true) : null,
    ]
]);