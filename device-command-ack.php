<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

function json_response(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

// API key validation
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

if ($apiKey !== PORTAL_API_KEY) {
    json_response(401, ['ok' => false, 'error' => 'unauthorized']);
}

$raw = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : [];
if (!is_array($data)) {
    $data = [];
}

$commandId = (int)($data['command_id'] ?? 0);
$status    = trim((string)($data['status'] ?? ''));
$result    = trim((string)($data['result'] ?? ''));

if ($commandId <= 0 || !in_array($status, ['acked','failed','cancelled'], true)) {
    json_response(400, ['ok' => false, 'error' => 'invalid payload']);
}

$pdo = db();

$stmt = $pdo->prepare('
    UPDATE device_commands
    SET status = ?, result_text = ?, acked_at = NOW()
    WHERE id = ?
');
$stmt->execute([$status, $result, $commandId]);

json_response(200, ['ok' => true]);