<?php
// Device registration / heartbeat endpoint.
// POST JSON body with X-API-Key header or api_key in POST.

error_log("[devreg] Request: METHOD=" . $_SERVER['REQUEST_METHOD']);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('X-Powered-By: BitDevices/1.0');

function json_response(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    error_log("[devreg] Response CODE=$code");
    exit;
}

// ============================================================================
// API KEY VALIDATION - Try multiple sources for robustness
// ============================================================================
$apiKey = null;

// 1. Try getallheaders() - most reliable for headers
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'x-api-key') {
            $apiKey = $value;
            error_log("[devreg] API Key from header (getallheaders)");
            break;
        }
    }
}

// 2. Fallback to $_SERVER
if (!$apiKey && isset($_SERVER['HTTP_X_API_KEY'])) {
    $apiKey = $_SERVER['HTTP_X_API_KEY'];
    error_log("[devreg] API Key from \$_SERVER");
}

// 3. Check POST/JSON data
if (!$apiKey) {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) && isset($decoded['api_key'])) {
            $apiKey = $decoded['api_key'];
            error_log("[devreg] API Key from POST JSON");
        }
    }
}

// 4. Check POST variable
if (!$apiKey) {
    $apiKey = $_POST['api_key'] ?? null;
}

if ($apiKey !== PORTAL_API_KEY) {
    error_log("[devreg] API Key mismatch! Got: " . ($apiKey ? substr($apiKey, 0, 5) : 'NONE'));
    json_response(401, ['ok' => false, 'error' => 'unauthorized']);
}

error_log("[devreg] API Key validated ✓");

$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
if (!$data) {
    $data = $_POST;
}

$imei = trim((string)($data['imei'] ?? ''));
$chipid = trim((string)($data['chipid'] ?? ''));
if ($imei === '' || $chipid === '') {
    json_response(400, ['ok' => false, 'error' => 'imei and chipid are required']);
}

$site = trim((string)($data['site'] ?? ''));
$ip = trim((string)($data['ip'] ?? ''));
$iccid = trim((string)($data['iccid'] ?? ''));
$mode = trim((string)($data['mode'] ?? ''));
$fwVersion = trim((string)($data['fw_version'] ?? ''));
$gsmOperator = trim((string)($data['gsm_operator'] ?? ''));
$gsmNetwork = trim((string)($data['gsm_network'] ?? ''));
$gsmRssi = normalize_int($data['gsm_rssi'] ?? null);
$wifiSsid = trim((string)($data['wifi_ssid'] ?? ''));
$batteryV = normalize_float($data['battery_v'] ?? null);
$adcRaw = normalize_int($data['adc_raw'] ?? null);
$extra = json_encode($data, JSON_UNESCAPED_SLASHES);

$pdo = db();
$pdo->beginTransaction();

try {
    $sel = $pdo->prepare('SELECT id FROM devices WHERE imei = ? LIMIT 1');
    $sel->execute([$imei]);
    $deviceId = $sel->fetchColumn();

    if ($deviceId) {
        error_log("[devreg] Updating device ID=$deviceId");
        $upd = $pdo->prepare('UPDATE devices SET chipid=?, site=?, ip=?, iccid=?, mode=?, fw_version=?, gsm_operator=?, gsm_network=?, gsm_rssi=?, wifi_ssid=?, battery_v=?, adc_raw=?, last_seen=NOW(), extra_json=? WHERE id=?');
        $upd->execute([$chipid, $site, $ip, $iccid, $mode, $fwVersion, $gsmOperator, $gsmNetwork, $gsmRssi, $wifiSsid, $batteryV, $adcRaw, $extra, $deviceId]);
    } else {
        error_log("[devreg] Creating new device");
        $ins = $pdo->prepare('INSERT INTO devices (imei, chipid, site, ip, iccid, mode, fw_version, gsm_operator, gsm_network, gsm_rssi, wifi_ssid, battery_v, adc_raw, last_seen, extra_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?)');
        $ins->execute([$imei, $chipid, $site, $ip, $iccid, $mode, $fwVersion, $gsmOperator, $gsmNetwork, $gsmRssi, $wifiSsid, $batteryV, $adcRaw, $extra]);
        $deviceId = $pdo->lastInsertId();
    }

    $log = $pdo->prepare('INSERT INTO device_logs (device_id, imei, chipid, site, ip, iccid, mode, fw_version, gsm_operator, gsm_network, gsm_rssi, wifi_ssid, battery_v, adc_raw, extra_json, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())');
    $log->execute([$deviceId, $imei, $chipid, $site, $ip, $iccid, $mode, $fwVersion, $gsmOperator, $gsmNetwork, $gsmRssi, $wifiSsid, $batteryV, $adcRaw, $extra]);

    $pdo->commit();
    error_log("[devreg] Success: device_id=$deviceId");
    json_response(200, ['ok' => true, 'device_id' => (int)$deviceId]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("[devreg] DATABASE ERROR: " . $e->getMessage());
    json_response(500, ['ok' => false, 'error' => 'database error']);
}
