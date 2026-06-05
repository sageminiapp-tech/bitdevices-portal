<?php
/**
 * Device Registration Debug & Status Page
 * Access: https://bitdevices.freedev.app/devreg-debug.php
 * Shows last registrations and logs
 */

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'logs') {
    header('Content-Type: text/plain');
    
    // Read error log
    $logPath = '/home/if0_42069601/logs/error.log';
    
    if (is_readable($logPath)) {
        $logs = file_get_contents($logPath);
        // Show last 100 lines related to devreg
        $lines = explode("\n", $logs);
        $devreg_lines = array_filter($lines, function($line) {
            return strpos($line, 'devreg') !== false || strpos($line, 'Database') !== false;
        });
        echo implode("\n", array_slice($devreg_lines, -100));
    } else {
        echo "Error log not accessible. Check file path or permissions.\n";
        echo "Expected path: " . $logPath . "\n";
    }
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title>Device Registration Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-box .number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-box .label {
            font-size: 12px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #f0f0f0;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .code {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background: #764ba2;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            border-left: 4px solid #667eea;
            background: #f9f9f9;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Device Registration Debug Panel</h1>
    
    <div class="section">
        <h2>Database Status</h2>
        <?php
        try {
            $pdo = db();
            
            // Count devices
            $devCount = $pdo->query("SELECT COUNT(*) FROM devices")->fetchColumn();
            $logCount = $pdo->query("SELECT COUNT(*) FROM device_logs")->fetchColumn();
            $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            // Get latest registrations
            $latest = $pdo->query("SELECT * FROM devices ORDER BY last_seen DESC LIMIT 5")->fetchAll();
            
            echo "<div class='stats'>";
            echo "<div class='stat-box'>";
            echo "<div class='label'>Total Devices</div>";
            echo "<div class='number'>" . $devCount . "</div>";
            echo "</div>";
            
            echo "<div class='stat-box'>";
            echo "<div class='label'>Total Logs</div>";
            echo "<div class='number'>" . $logCount . "</div>";
            echo "</div>";
            
            echo "<div class='stat-box'>";
            echo "<div class='label'>Users</div>";
            echo "<div class='number'>" . $userCount . "</div>";
            echo "</div>";
            echo "</div>";
            
            echo "<p class='success'>✓ Database connection OK</p>";
            
        } catch (Exception $e) {
            echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>Latest Registrations</h2>
        <?php if ($latest): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>IMEI</th>
                        <th>Chip ID</th>
                        <th>Site</th>
                        <th>IP</th>
                        <th>FW Version</th>
                        <th>Battery</th>
                        <th>Last Seen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest as $device): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($device['id']); ?></td>
                            <td><code><?php echo htmlspecialchars($device['imei']); ?></code></td>
                            <td><code><?php echo htmlspecialchars($device['chipid']); ?></code></td>
                            <td><?php echo htmlspecialchars($device['site']); ?></td>
                            <td><?php echo htmlspecialchars($device['ip']); ?></td>
                            <td><?php echo htmlspecialchars($device['fw_version']); ?></td>
                            <td><?php echo $device['battery_v'] ? number_format($device['battery_v'], 2) . 'V' : 'N/A'; ?></td>
                            <td><?php echo $device['last_seen'] ? htmlspecialchars($device['last_seen']) : 'Never'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class='error'>No devices registered yet</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>API Configuration</h2>
        <p><strong>API Endpoint:</strong> <code>https://bitdevices.freedev.app/devreg.php</code></p>
        <p><strong>API Key:</strong> <code>880610BitFluxApp</code></p>
        <p><strong>Method:</strong> POST</p>
        <p><strong>Content-Type:</strong> application/json</p>
        
        <h3>Sample Request (ESP8266)</h3>
        <div class="code">
// Headers:
POST /devreg.php HTTP/1.1
Host: bitdevices.freedev.app
Content-Type: application/json
X-API-Key: 880610BitFluxApp

// Body:
{
  "chipid": "00076460",
  "imei": "862325051807434",
  "site": "jhb",
  "ip": "172.17.220.150",
  "iccid": "89330000000027792097",
  "mode": "imei",
  "fw_version": "1.0.0",
  "gsm_operator": "65501",
  "gsm_network": "LTE",
  "gsm_rssi": 2,
  "wifi_ssid": "FSK Guest",
  "battery_v": 13.283,
  "adc_raw": 610
}
        </div>
    </div>

    <div class="section">
        <h2>Troubleshooting</h2>
        
        <h3>If getting 403 Forbidden:</h3>
        <ul>
            <li>✓ Verify X-API-Key header is included</li>
            <li>✓ Check API key matches: <code>880610BitFluxApp</code></li>
            <li>✓ Ensure Content-Type is: <code>application/json</code></li>
            <li>✓ Verify .htaccess file is in place</li>
            <li>✓ Check server error logs</li>
        </ul>

        <h3>Common Issues:</h3>
        <ul>
            <li><strong>401 Unauthorized:</strong> API key mismatch</li>
            <li><strong>400 Bad Request:</strong> Missing imei or chipid</li>
            <li><strong>403 Forbidden:</strong> Header issue or .htaccess problem</li>
            <li><strong>500 Server Error:</strong> Database or PHP error</li>
        </ul>
    </div>

    <div class="section">
        <button class="button" onclick="location.href='?action=logs'">View Error Logs</button>
        <button class="button" onclick="location.reload()">Refresh</button>
    </div>

</div>

</body>
</html>
