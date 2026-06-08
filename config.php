<?php
// Database Configuration
// Railway MySQL provides:
// MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE

define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'bitdevices');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

// Application Settings
define('APP_NAME', 'BitDevices Portal');
define('APP_BASE', ''); // change if portal lives in a subfolder

// Session & Device Settings
define('PORTAL_SESSION_NAME', 'bitdevices_portal');
define('DEVICE_ONLINE_SECONDS', 300); // 5 minutes

// API Key for device registration
define('PORTAL_API_KEY', getenv('PORTAL_API_KEY') ?: 'change-me-in-production');

// Manifest file path for firmware updates
define('MANIFEST_PATH', __DIR__ . '/manifest.json');

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Debug mode
define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE') ?: false, FILTER_VALIDATE_BOOLEAN));