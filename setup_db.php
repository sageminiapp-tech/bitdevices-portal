<?php
/**
 * Database initialization script
 * Run this ONCE to create required tables for device registration
 * Access: http://localhost/portal/setup_db.php
 * Then delete this file after successful setup
 */

require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Devices table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `devices` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `imei` VARCHAR(50) UNIQUE NOT NULL,
            `chipid` VARCHAR(50) NOT NULL,
            `site` VARCHAR(100),
            `ip` VARCHAR(45),
            `iccid` VARCHAR(50),
            `mode` VARCHAR(20),
            `fw_version` VARCHAR(50),
            `gsm_operator` VARCHAR(100),
            `gsm_network` VARCHAR(50),
            `gsm_rssi` INT,
            `wifi_ssid` VARCHAR(255),
            `battery_v` DECIMAL(5, 2),
            `adc_raw` INT,
            `last_seen` TIMESTAMP NULL,
            `extra_json` LONGTEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_imei` (`imei`),
            KEY `idx_chipid` (`chipid`),
            KEY `idx_last_seen` (`last_seen`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Device logs table (audit trail)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `device_logs` (
            `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            `device_id` INT UNSIGNED NOT NULL,
            `imei` VARCHAR(50) NOT NULL,
            `chipid` VARCHAR(50),
            `site` VARCHAR(100),
            `ip` VARCHAR(45),
            `iccid` VARCHAR(50),
            `mode` VARCHAR(20),
            `fw_version` VARCHAR(50),
            `gsm_operator` VARCHAR(100),
            `gsm_network` VARCHAR(50),
            `gsm_rssi` INT,
            `wifi_ssid` VARCHAR(255),
            `battery_v` DECIMAL(5, 2),
            `adc_raw` INT,
            `extra_json` LONGTEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_device_id` (`device_id`),
            KEY `idx_imei` (`imei`),
            KEY `idx_created_at` (`created_at`),
            CONSTRAINT `fk_device_logs_devices` FOREIGN KEY (`device_id`) REFERENCES `devices`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<h2 style='color: green;'>✓ Database setup successful!</h2>";
    echo "<p>The following tables have been created:</p>";
    echo "<ul>";
    echo "<li><code>devices</code> - Stores device registration data</li>";
    echo "<li><code>device_logs</code> - Stores device heartbeat history</li>";
    echo "</ul>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Update <code>config.php</code> with your database credentials if needed</li>";
    echo "<li>Update the ESP8266 sketch with the correct URL: <code>https://your-domain.com/portal/devreg.php</code></li>";
    echo "<li>Ensure the API key matches: <code>" . PORTAL_API_KEY . "</code></li>";
    echo "<li>Delete this file (<code>setup_db.php</code>) for security</li>";
    echo "</ol>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Database setup failed!</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Check your database credentials in <code>config.php</code></p>";
}
?>
