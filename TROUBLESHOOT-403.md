# 403 Forbidden Error - Troubleshooting Guide

## Problem Summary

ESP8266 is getting **HTTP 403 Forbidden** when trying to POST to `https://bitdevices.freedev.app/devreg.php`

This typically means:
- Server is responding but blocking the request
- Usually a header validation or .htaccess issue

---

## Quick Fix (Step 1-3)

### Step 1: Upload Updated Files to InfinityFree

These files **must** be on your server:

1. **`/portal/devreg.php`** (Updated version with better header handling)
2. **`/portal/.htaccess`** (NEW - Allows proper access)
3. **`/portal/devreg-debug.php`** (NEW - For debugging)

**Upload via:**
- InfinityFree File Manager, or
- FTP client (FileZilla)

**Folder structure should be:**
```
public_html/
├── portal/
│   ├── devreg.php           ← UPDATED
│   ├── .htaccess            ← NEW
│   ├── devreg-debug.php     ← NEW
│   ├── db.php
│   ├── config.php
│   └── ...other files
```

### Step 2: Verify Configuration

Edit `/portal/config.php` and ensure it has:

```php
define('DB_HOST', 'sql209.infinityfree.com');  // From your InfinityFree account
define('DB_NAME', 'if0_42069601_bitdev');
define('DB_USER', 'if0_42069601_bitdev');     // Your DB user
define('DB_PASS', '*** YOUR PASSWORD ***');
define('PORTAL_API_KEY', '880610BitFluxApp');
```

### Step 3: Test the Endpoint

Visit this URL in your browser:

```
https://bitdevices.freedev.app/devreg-debug.php
```

This page should show:
- ✓ Database connection OK
- List of devices (if any registered)
- Configuration details

---

## Detailed Troubleshooting

### Check 1: Verify .htaccess is working

Create a test file: `/portal/test-htaccess.txt` with content:
```
.htaccess is working if you can see this file
```

Try to access: `https://bitdevices.freedev.app/portal/test-htaccess.txt`

**Result:**
- If you see the text → ✓ .htaccess is working
- If you get 403 → ✗ .htaccess has issues

### Check 2: Test with a simple POST request

Use cURL or Postman to test:

```bash
curl -X POST https://bitdevices.freedev.app/portal/devreg.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 880610BitFluxApp" \
  -d '{
    "chipid": "test123",
    "imei": "123456789012345",
    "site": "test",
    "ip": "1.2.3.4",
    "fw_version": "1.0.0",
    "battery_v": 4.2,
    "adc_raw": 512
  }'
```

**Expected response (if successful):**
```json
{"ok": true, "device_id": 1}
```

**If you get 403:**
```
<html><body><h1>403 Forbidden</h1></body></html>
```

### Check 3: Verify Headers are Being Sent

The ESP8266 code uses `http.addHeader()`:

```cpp
http.addHeader("Content-Type", "application/json");
http.addHeader("X-API-Key", DEVICE_API_KEY);
```

Make sure these lines are **before** `http.POST()`:

```cpp
http.addHeader("Content-Type", "application/json");
http.addHeader("X-API-Key", DEVICE_API_KEY);

int code = http.POST((uint8_t*)jsonPayload.c_str(), jsonPayload.length());
```

### Check 4: PHP Header Reading Issue

Some servers have issues with custom headers. Try alternative: **Send API key in JSON body**

Modify `/portal/devreg.php` to also accept api_key in the JSON:

```cpp
// ESP8266 code - add to payload
String jsonPayload = "{";
payload += "\"api_key\":\"" + String(DEVICE_API_KEY) + "\",";
payload += "\"chipid\":\""      + jsonEscape(chip)        + "\",";
// ... rest of payload
```

---

## Advanced: Check Server Error Logs

Access via InfinityFree cPanel:

1. Go to: `https://www.infinityfree.com/` → Client Area
2. Click on your domain
3. Go to: **Error Logs** or **System Logs**
4. Look for entries with `devreg.php` or `403`

---

## Step-by-Step Upload Guide (If needed)

### Using InfinityFree File Manager:

1. Login to https://www.infinityfree.com/
2. Click on your domain
3. Click **File Manager**
4. Navigate to `/public_html/portal/`
5. Upload:
   - `devreg.php` (replace existing)
   - `.htaccess` (create new)
   - `devreg-debug.php` (create new)

### Using FTP (FileZilla):

1. Download FileZilla: https://filezilla-project.org/
2. Connect with:
   ```
   Host: ftp.bitdevices.freedev.app
   User: if0_42069601
   Pass: [Your FTP password from InfinityFree]
   Port: 21
   ```
3. Navigate to `/public_html/portal/`
4. Drag and drop files to upload

---

## After Upload: Verify Everything

1. **Check debug page:**
   ```
   https://bitdevices.freedev.app/devreg-debug.php
   ```
   Should show: ✓ Database connection OK

2. **Check .htaccess:**
   ```
   https://bitdevices.freedev.app/portal/.htaccess
   ```
   Should show the .htaccess content or 403 (both mean it exists)

3. **Test with curl** (if you have access to terminal):
   ```bash
   curl -v https://bitdevices.freedev.app/portal/devreg.php
   ```

4. **Run ESP8266 again** and check if you get 200 OK instead of 403

---

## If Still Getting 403

### Possible Causes:

1. **Web Application Firewall (WAF) blocking requests**
   - InfinityFree may have ModSecurity enabled
   - Contact InfinityFree support
   - Ask to whitelist POST requests to `/portal/devreg.php`

2. **File permissions issue**
   - File permissions might not allow PHP execution
   - Contact InfinityFree support
   - Ask to set proper permissions on `/portal/` folder

3. **Directory listing blocked**
   - Some servers block certain patterns
   - Try renaming: `devreg.php` → `device-register.php`
   - Update ESP8266 code with new URL

4. **Content-Type validation**
   - Some servers are strict about headers
   - The updated `devreg.php` now handles this better

---

## Contact InfinityFree Support

If you still have issues, contact support with:

- **Issue:** Getting 403 Forbidden on POST request to `/portal/devreg.php`
- **Details:**
  - Sending JSON payload with X-API-Key header
  - Need POST requests allowed
  - Database is working (can connect via phpmyadmin)
  - Request: Allow POST to `/portal/devreg.php` or whitelist PHP execution there

---

## Files Uploaded

These 3 files should be in `/portal/`:

1. ✓ **devreg.php** - Main endpoint (updated with better header handling)
2. ✓ **.htaccess** - Server configuration (new, allows requests)
3. ✓ **devreg-debug.php** - Debug panel (new, for troubleshooting)

All other files (db.php, config.php, etc.) remain the same.

---

## Next Steps Once Working

Once you get a 200 OK response:

1. Verify device appears in debug panel: `https://bitdevices.freedev.app/devreg-debug.php`
2. Check device data in database via phpmyadmin
3. You're ready for production deployment!
