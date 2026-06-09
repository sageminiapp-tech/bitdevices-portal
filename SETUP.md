# Device Portal Setup Guide

## Overview
This is a PHP-based device registration portal that works with ESP8266 devices to collect telemetry data including:
- Device ID (IMEI/chipid)
- Network information (WiFi SSID, IP address)
- GSM telemetry (operator, signal strength, network type)
- Battery voltage
- Firmware version
- Location/site information

## Quick Start

### 1. Database Setup
Run the database initialization script **once**:

```
http://localhost/portal/setup_db.php
```

This creates two tables:
- `devices` - Current device status
- `device_logs` - Historical heartbeat records

**⚠️ Important:** Delete `setup_db.php` after successful setup for security.

### 2. Verify Configuration
Edit `config.php` if needed:

```php
define('DB_HOST', 'localhost');      // MySQL host
define('DB_NAME', 'bitdev');         // Database name
define('DB_USER', 'root');           // MySQL user
define('DB_PASS', '');               // MySQL password
define('PORTAL_API_KEY', 'xxxxxxxxxxxx');  // Must match ESP8266
```

### 3. Test the Endpoint
Visit: `http://localhost/portal/test_devreg.php`

Click "Test Device Registration" to verify the endpoint is working.

## ESP8266 Configuration

Update these constants in your sketch:

```cpp
// For local network (HTTP)
static const char* DEVICE_PORTAL_URL = "http://192.168.x.x/portal/devreg.php";

// For internet access (HTTPS) - if you have SSL certificate
static const char* DEVICE_PORTAL_URL = "https://bitdevices.up.railway.app/devreg.php";

// Must match config.php PORTAL_API_KEY
static const char* DEVICE_API_KEY = "xxxxxxxxxxxxxxxx";
```

### Network Requirements
- ESP8266 must have WiFi connectivity to reach the PHP server
- Firewall must allow HTTP/HTTPS traffic to port 80/443
- For HTTPS, use a valid SSL certificate

## API Endpoint

**URL:** `/portal/devreg.php`

**Method:** POST

**Headers:**
```
Content-Type: application/json
X-API-Key: xxxxxxxxxxxxxxx
```

**Request Body (JSON):**
```json
{
  "chipid": "abc123def456",
  "imei": "359769090086015",
  "site": "location-name",
  "ip": "192.168.1.100",
  "iccid": "89011401200061599999",
  "mode": "imei",
  "fw_version": "1.0.0",
  "gsm_operator": "Vodacom-SA",
  "gsm_network": "LTE",
  "gsm_rssi": -72,
  "wifi_ssid": "WiFiNetwork",
  "battery_v": 4.20,
  "adc_raw": 512
}
```

**Required Fields:**
- `imei` - Device IMEI number (unique identifier)
- `chipid` - ESP8266 chip ID

**Response (200 OK):**
```json
{
  "ok": true,
  "device_id": 42
}
```

**Response (401 Unauthorized):**
```json
{
  "ok": false,
  "error": "unauthorized"
}
```

**Response (400 Bad Request):**
```json
{
  "ok": false,
  "error": "imei and chipid are required"
}
```

## Database Schema

### devices table
```sql
id                INT PRIMARY KEY
imei              VARCHAR(50) UNIQUE - Device IMEI
chipid            VARCHAR(50) - ESP8266 chip ID
site              VARCHAR(100) - Location/site identifier
ip                VARCHAR(45) - Last known IP address
iccid             VARCHAR(50) - SIM card identifier
mode              VARCHAR(20) - Registration mode (imei/chipid)
fw_version        VARCHAR(50) - Firmware version
gsm_operator      VARCHAR(100) - Cellular operator name
gsm_network       VARCHAR(50) - Network type (LTE, 4G, etc)
gsm_rssi          INT - Signal strength (-120 to 0 dBm)
wifi_ssid         VARCHAR(255) - WiFi network name
battery_v         DECIMAL(5,2) - Battery voltage
adc_raw           INT - Raw ADC value
last_seen         TIMESTAMP - Last heartbeat time
extra_json        LONGTEXT - Additional metadata
created_at        TIMESTAMP - Registration time
updated_at        TIMESTAMP - Last update time
```

### device_logs table
```sql
id                INT PRIMARY KEY
device_id         INT FOREIGN KEY - Reference to devices.id
imei              VARCHAR(50)
chipid            VARCHAR(50)
site              VARCHAR(100)
ip                VARCHAR(45)
iccid             VARCHAR(50)
mode              VARCHAR(20)
fw_version        VARCHAR(50)
gsm_operator      VARCHAR(100)
gsm_network       VARCHAR(50)
gsm_rssi          INT
wifi_ssid         VARCHAR(255)
battery_v         DECIMAL(5,2)
adc_raw           INT
extra_json        LONGTEXT
created_at        TIMESTAMP - When this log entry was created
```

## Troubleshooting

### Connection Refused
- Ensure Apache/PHP is running
- Check firewall allows port 80/443
- Verify `config.php` has correct server IP

### 401 Unauthorized
- Verify `X-API-Key` header matches `PORTAL_API_KEY` in config.php
- Check header is not being modified by proxies

### 400 Bad Request
- Ensure JSON payload includes `imei` and `chipid`
- Verify JSON is properly formatted
- Check Content-Type is `application/json`

### Database Error
- Run `setup_db.php` again to verify tables exist
- Check MySQL connection credentials in `config.php`
- Ensure MySQL is running

### Device Not Appearing
- Check ESP8266 serial output for POST response code
- Verify firewall allows outbound connections
- Test endpoint manually with `test_devreg.php`

## Security Considerations

1. **Delete setup script:** Remove `setup_db.php` after initial setup
2. **Change API key:** Modify `PORTAL_API_KEY` in `config.php` to something unique
3. **Use HTTPS:** Deploy with SSL certificate for production
4. **Firewall rules:** Only allow necessary IP ranges to access the endpoint
5. **Database credentials:** Use strong password for MySQL user
6. **Input validation:** All user input is validated and escaped

## Useful Files

- `devreg.php` - Main registration endpoint (DO NOT EDIT)
- `config.php` - Configuration (EDIT as needed)
- `db.php` - Database helpers (DO NOT EDIT)
- `setup_db.php` - Database initialization (RUN ONCE, THEN DELETE)
- `test_devreg.php` - Endpoint testing tool

## Next Steps

After successful setup:

1. Create a dashboard to view registered devices (see `dashboard.php`)
2. Set up device status monitoring
3. Implement firmware update mechanism
4. Add device data visualization
5. Create alerts for offline devices

## Support

For issues or questions, check:
1. Your server logs (Apache error log)
2. MySQL logs
3. The `test_devreg.php` testing page
4. Device serial output from the ESP8266
