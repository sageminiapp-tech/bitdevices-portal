<?php
/**
 * Device Registration Endpoint Tester
 * Access: http://localhost/portal/test_devreg.php
 * Tests the devreg.php endpoint with sample device data
 */

require_once __DIR__ . '/config.php';

?><!DOCTYPE html>
<html>
<head>
    <title>Device Registration Tester</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; }
        .section { border: 1px #ddd solid; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .endpoint { background: #f0f0f0; padding: 10px; font-family: monospace; margin: 10px 0; }
        .api-key { background: #ffffcc; padding: 10px; margin: 10px 0; border-left: 4px solid #ffb347; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; overflow-x: auto; border-radius: 4px; }
    </style>
</head>
<body>

<h1>Device Registration Endpoint Tester</h1>

<div class="section info">
    <strong>ℹ️ Info:</strong> This page tests your devreg.php endpoint to ensure it's ready to receive ESP8266 device registrations.
</div>

<div class="section">
    <h2>Endpoint Configuration</h2>
    <div>
        <strong>Endpoint URL:</strong>
        <div class="endpoint"><?php echo 'http://localhost/portal/devreg.php'; ?></div>
    </div>
    <div>
        <strong>HTTP Method:</strong> POST
    </div>
    <div>
        <strong>Content-Type:</strong> application/json
    </div>
    <div class="api-key">
        <strong>X-API-Key Header:</strong> <code><?php echo PORTAL_API_KEY; ?></code>
    </div>
</div>

<div class="section">
    <h2>Test Connection</h2>
    <p>Click the button below to send a test device registration:</p>
    <button onclick="testRegistration()">Test Device Registration</button>
    <div id="result"></div>
</div>

<div class="section">
    <h2>Sample ESP8266 Code</h2>
    <p>Use these settings in your ESP8266 sketch:</p>
    <pre>static const char* DEVICE_PORTAL_URL = "http://192.168.x.x/portal/devreg.php";
static const char* DEVICE_API_KEY    = "<?php echo PORTAL_API_KEY; ?>";</pre>
</div>

<div class="section">
    <h2>Sample Payload</h2>
    <p>The endpoint expects a JSON POST with this structure:</p>
    <pre id="samplePayload"></pre>
</div>

<script>
const sampleData = {
    chipid: "abc123def456",
    imei: "359769090086015",
    site: "test-site",
    ip: "192.168.1.100",
    iccid: "89011401200061599999",
    mode: "imei",
    fw_version: "1.0.0",
    gsm_operator: "Vodacom-SA",
    gsm_network: "LTE",
    gsm_rssi: -72,
    wifi_ssid: "TestSSID",
    battery_v: 4.20,
    adc_raw: 512
};

// Display sample payload
document.getElementById('samplePayload').textContent = JSON.stringify(sampleData, null, 2);

async function testRegistration() {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '<p style="color: #ff9800;">Testing...</p>';

    try {
        const response = await fetch('/portal/devreg.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': '<?php echo PORTAL_API_KEY; ?>'
            },
            body: JSON.stringify(sampleData)
        });

        const data = await response.json();

        if (response.ok && data.ok) {
            resultDiv.innerHTML = `
                <div style="margin-top: 15px;">
                    <p class="success">✓ <strong>Success!</strong> Device registered with ID: ${data.device_id}</p>
                    <p>Your endpoint is working correctly. You can now configure your ESP8266 to post to:</p>
                    <div class="endpoint">http://your-server-ip/portal/devreg.php</div>
                    <p><strong>Make sure to:</strong></p>
                    <ul>
                        <li>Replace 'your-server-ip' with your actual server IP or domain</li>
                        <li>Use HTTPS if your server supports it (recommended)</li>
                        <li>Ensure the API key matches: <code><?php echo PORTAL_API_KEY; ?></code></li>
                    </ul>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div style="margin-top: 15px;">
                    <p class="error">✗ <strong>Error:</strong> ${data.error || 'Unknown error'}</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
    } catch (error) {
        resultDiv.innerHTML = `
            <div style="margin-top: 15px;">
                <p class="error">✗ <strong>Request Failed:</strong> ${error.message}</p>
                <p>Make sure your server is running and the endpoint is accessible.</p>
            </div>
        `;
    }
}
</script>

</body>
</html>
<?php
