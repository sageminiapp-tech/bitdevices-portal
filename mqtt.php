<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

/**
 * MQTT helper for BitDevices Portal.
 *
 * Requires Railway variables:
 *   MQTT_HOST
 *   MQTT_PORT
 *   MQTT_USER
 *   MQTT_PASS
 *   MQTT_TLS=true|false
 *
 * This helper is designed for HiveMQ Cloud over TLS on port 8883.
 */

function mqtt_config(): array
{
    $host = trim((string)(getenv('MQTT_HOST') ?: ''));
    $port = (int)(getenv('MQTT_PORT') ?: 8883);
    $user = trim((string)(getenv('MQTT_USER') ?: ''));
    $pass = (string)(getenv('MQTT_PASS') ?: '');
    $tls  = filter_var(getenv('MQTT_TLS') ?: true, FILTER_VALIDATE_BOOLEAN);

    if ($host === '') {
        throw new RuntimeException('MQTT_HOST is not configured.');
    }

    if ($user === '') {
        throw new RuntimeException('MQTT_USER is not configured.');
    }

    return [
        'host' => $host,
        'port' => $port,
        'user' => $user,
        'pass' => $pass,
        'tls'  => $tls,
    ];
}

/**
 * Create a unique MQTT client ID for this PHP request.
 */
function mqtt_client_id(string $prefix = 'railway-php'): string
{
    try {
        $rand = bin2hex(random_bytes(4));
    } catch (Throwable $e) {
        $rand = (string)mt_rand(100000, 999999);
    }

    return $prefix . '-' . $rand;
}

/**
 * Build the standard command topic for a device.
 *
 * Example:
 *   devices/862325051807434/cmd
 */
function mqtt_device_command_topic(string $imei): string
{
    $imei = trim($imei);
    if ($imei === '') {
        throw new InvalidArgumentException('IMEI is required for MQTT topic.');
    }

    return 'devices/' . $imei . '/cmd';
}

/**
 * Build the standard status topic for a device.
 *
 * Example:
 *   devices/862325051807434/status
 */
function mqtt_device_status_topic(string $imei): string
{
    $imei = trim($imei);
    if ($imei === '') {
        throw new InvalidArgumentException('IMEI is required for MQTT topic.');
    }

    return 'devices/' . $imei . '/status';
}

/**
 * Build the standard telemetry topic for a device.
 *
 * Example:
 *   devices/862325051807434/telemetry
 */
function mqtt_device_telemetry_topic(string $imei): string
{
    $imei = trim($imei);
    if ($imei === '') {
        throw new InvalidArgumentException('IMEI is required for MQTT topic.');
    }

    return 'devices/' . $imei . '/telemetry';
}

/**
 * Open and connect an MQTT client.
 */
function mqtt_connect(?string $clientId = null): MqttClient
{
    $cfg = mqtt_config();

    $clientId ??= mqtt_client_id();

    $mqtt = new MqttClient(
        $cfg['host'],
        $cfg['port'],
        $clientId
    );

    $settings = (new ConnectionSettings)
        ->setUsername($cfg['user'])
        ->setPassword($cfg['pass'])
        ->setUseTls($cfg['tls'])
        ->setConnectTimeout(5)
        ->setSocketTimeout(5)
        ->setKeepAliveInterval(30)
        ->setReconnectAutomatically(false);

    $mqtt->connect($settings, true);

    return $mqtt;
}

/**
 * Publish an arbitrary payload to a topic.
 *
 * @param string $topic
 * @param array|string $payload
 * @param int $qos
 * @param bool $retain
 * @return bool
 */
function mqtt_publish(string $topic, array|string $payload, int $qos = 1, bool $retain = false): bool
{
    $mqtt = null;

    try {
        $mqtt = mqtt_connect();

        $message = is_array($payload)
            ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : (string)$payload;

        if ($message === false) {
            throw new RuntimeException('Failed to JSON encode MQTT payload.');
        }

        $mqtt->publish($topic, $message, $qos, $retain);

        // Important for QoS 1 and 2 publications.
        if ($qos > 0) {
            $mqtt->loop(true, true, 1);
        }

        $mqtt->disconnect();

        return true;
    } catch (Throwable $e) {
        error_log('MQTT publish failed: ' . $e->getMessage());

        if ($mqtt !== null) {
            try {
                $mqtt->disconnect();
            } catch (Throwable $ignored) {
            }
        }

        return false;
    }
}

/**
 * Publish an OTA command for a given device IMEI.
 *
 * Payload example:
 * {
 *   "action": "ota",
 *   "target_version": "1.2.4",
 *   "bin_url": "https://bitdevices.up.railway.app/firmware-1.2.4.bin",
 *   "requested_by": "admin"
 * }
 */
function mqtt_publish_ota_command(
    string $imei,
    string $targetVersion,
    string $binUrl,
    string $requestedBy = 'system',
    int $qos = 1
): bool {
    $topic = mqtt_device_command_topic($imei);

    $payload = [
        'action'         => 'ota',
        'target_version' => $targetVersion,
        'bin_url'        => $binUrl,
        'requested_by'   => $requestedBy,
        'ts'             => time(),
    ];

    return mqtt_publish($topic, $payload, $qos, false);
}

/**
 * Optional generic command helper.
 */
function mqtt_publish_device_command(
    string $imei,
    string $action,
    array $extra = [],
    int $qos = 1,
    bool $retain = false
): bool {
    $topic = mqtt_device_command_topic($imei);

    $payload = array_merge([
        'action' => $action,
        'ts'     => time(),
    ], $extra);

    return mqtt_publish($topic, $payload, $qos, $retain);
}