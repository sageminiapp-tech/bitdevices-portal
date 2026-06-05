<?php
/**
 * System Diagnostic Check
 * Access: http://localhost/portal/diagnostic.php
 * Verifies all components are working correctly
 */

require_once __DIR__ . '/config.php';

$checks = [];

// 1. PHP Version
$checks['PHP Version'] = [
    'status' => phpversion() >= '7.4' ? 'OK' : 'WARNING',
    'value' => phpversion(),
    'required' => '>= 7.4'
];

// 2. Extensions
$checks['JSON Extension'] = [
    'status' => extension_loaded('json') ? 'OK' : 'ERROR',
    'value' => extension_loaded('json') ? 'Loaded' : 'Not loaded'
];

$checks['PDO Extension'] = [
    'status' => extension_loaded('PDO') ? 'OK' : 'ERROR',
    'value' => extension_loaded('PDO') ? 'Loaded' : 'Not loaded'
];

$checks['MySQL Driver'] = [
    'status' => in_array('mysql', PDO::getAvailableDrivers()) ? 'OK' : 'ERROR',
    'value' => in_array('mysql', PDO::getAvailableDrivers()) ? 'Available' : 'Not available',
    'note' => 'Required for database access'
];

// 3. File Permissions
$checks['devreg.php Readable'] = [
    'status' => is_readable(__DIR__ . '/devreg.php') ? 'OK' : 'ERROR',
    'value' => is_readable(__DIR__ . '/devreg.php') ? 'Yes' : 'No'
];

// 4. Configuration
$checks['Config File'] = [
    'status' => defined('DB_HOST') ? 'OK' : 'ERROR',
    'value' => defined('DB_HOST') ? 'Loaded' : 'Not loaded'
];

$checks['API Key Configured'] = [
    'status' => defined('PORTAL_API_KEY') && PORTAL_API_KEY ? 'OK' : 'ERROR',
    'value' => defined('PORTAL_API_KEY') ? '***' . substr(PORTAL_API_KEY, -4) : 'Not set'
];

// 5. Database Connection
$dbStatus = 'ERROR';
$dbValue = 'Not connected';
$dbError = null;

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Check if database exists
    $result = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
    if ($result->rowCount() > 0) {
        $dbStatus = 'OK';
        $dbValue = 'Connected to ' . DB_NAME;
        
        // Check tables
        $pdo->exec("USE `" . DB_NAME . "`");
        
        $devTable = $pdo->query("SHOW TABLES LIKE 'devices'")->rowCount();
        $logTable = $pdo->query("SHOW TABLES LIKE 'device_logs'")->rowCount();
        
        $checks['Database Connected'] = [
            'status' => 'OK',
            'value' => DB_NAME,
            'note' => 'MySQL connection successful'
        ];
        
        $checks['devices Table'] = [
            'status' => $devTable > 0 ? 'OK' : 'WARNING',
            'value' => $devTable > 0 ? 'Exists' : 'Not found',
            'note' => $devTable > 0 ? 'Ready' : 'Run setup_db.php'
        ];
        
        $checks['device_logs Table'] = [
            'status' => $logTable > 0 ? 'OK' : 'WARNING',
            'value' => $logTable > 0 ? 'Exists' : 'Not found',
            'note' => $logTable > 0 ? 'Ready' : 'Run setup_db.php'
        ];
        
        if ($devTable > 0) {
            // Count records
            $count = $pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
            $checks['Registered Devices'] = [
                'status' => 'OK',
                'value' => (int)$count . ' device(s)',
                'note' => 'Devices in database'
            ];
        }
        
    } else {
        $checks['Database Connected'] = [
            'status' => 'ERROR',
            'value' => 'Database not found',
            'note' => 'Run setup_db.php to create database'
        ];
    }
    
} catch (PDOException $e) {
    $dbError = $e->getMessage();
    $checks['Database Connected'] = [
        'status' => 'ERROR',
        'value' => 'Connection failed',
        'note' => 'Check credentials in config.php',
        'error' => $dbError
    ];
}

// 6. Endpoint Accessibility
$checks['devreg.php Endpoint'] = [
    'status' => 'OK',
    'value' => 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/portal/devreg.php',
    'note' => 'POST to this URL with X-API-Key header'
];

?><!DOCTYPE html>
<html>
<head>
    <title>Device Portal - Diagnostic Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px; }
        .check {
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #ccc;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check.ok {
            background: #f1f8f4;
            border-left-color: #10b981;
        }
        .check.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        .check.warning {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }
        .check-info {
            flex: 1;
        }
        .check-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 3px;
        }
        .check-value {
            font-size: 13px;
            color: #6b7280;
            font-family: monospace;
        }
        .check-note {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 3px;
        }
        .check-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 5px;
            padding: 8px;
            background: rgba(220, 38, 38, 0.1);
            border-radius: 3px;
            font-family: monospace;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            min-width: 70px;
            text-align: center;
        }
        .status-badge.ok {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-badge.warning {
            background: #fef3c7;
            color: #92400e;
        }
        .summary {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .summary-label {
            font-size: 12px;
            color: #6b7280;
            margin-top: 5px;
            text-transform: uppercase;
        }
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🔍 Diagnostic Check</h1>
        <p>Device Portal System Health</p>
    </div>
    
    <div class="content">
        <div class="summary">
            <?php
            $ok = count(array_filter($checks, fn($c) => $c['status'] === 'OK'));
            $warn = count(array_filter($checks, fn($c) => $c['status'] === 'WARNING'));
            $err = count(array_filter($checks, fn($c) => $c['status'] === 'ERROR'));
            ?>
            <div class="summary-item">
                <div class="summary-number" style="color: #10b981;"><?php echo $ok; ?></div>
                <div class="summary-label">Passed</div>
            </div>
            <div class="summary-item">
                <div class="summary-number" style="color: #f59e0b;"><?php echo $warn; ?></div>
                <div class="summary-label">Warnings</div>
            </div>
            <div class="summary-item">
                <div class="summary-number" style="color: #ef4444;"><?php echo $err; ?></div>
                <div class="summary-label">Errors</div>
            </div>
        </div>

        <?php foreach ($checks as $name => $check): ?>
            <div class="check <?php echo strtolower($check['status']); ?>">
                <div class="check-info">
                    <div class="check-name"><?php echo htmlspecialchars($name); ?></div>
                    <?php if (!empty($check['value'])): ?>
                        <div class="check-value"><?php echo htmlspecialchars($check['value']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($check['required'])): ?>
                        <div class="check-note">Required: <?php echo htmlspecialchars($check['required']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($check['note'])): ?>
                        <div class="check-note"><?php echo htmlspecialchars($check['note']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($check['error'])): ?>
                        <div class="check-error"><?php echo htmlspecialchars($check['error']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="status-badge <?php echo strtolower($check['status']); ?>">
                    <?php echo $check['status']; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="footer">
        <p>
            <?php 
            if ($err === 0 && $warn === 0) {
                echo '✓ System is ready! Your device portal is fully operational.';
            } elseif ($err === 0) {
                echo '⚠ System is functional but check warnings above.';
            } else {
                echo '✗ Please fix the errors above before deploying.';
            }
            ?>
        </p>
        <p style="margin-top: 10px; opacity: 0.7;">Last checked: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</div>

</body>
</html>
