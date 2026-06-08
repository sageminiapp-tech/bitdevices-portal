<?php
require_once __DIR__ . '/db.php';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS device_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    command_type VARCHAR(50) NOT NULL,
    payload_json JSON NULL,
    status ENUM('queued','sent','acked','failed','cancelled') NOT NULL DEFAULT 'queued',
    result_text VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    acked_at TIMESTAMP NULL,
    INDEX idx_device_status (device_id, status),
    CONSTRAINT fk_device_commands_device
      FOREIGN KEY (device_id) REFERENCES devices(id)
      ON DELETE CASCADE
);
SQL;

try {
    db()->exec($sql);
    echo "device_commands table created successfully";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}