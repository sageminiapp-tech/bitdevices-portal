<?php
// Database Configuration
define('DB_HOST', getenv('MYSQLHOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'bitdevices');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');

// Application Settings
define('APP_NAME', 'BitDevices Portal');
define('APP_BASE', '');

// Session & Device Settings
define('PORTAL_SESSION_NAME', 'bitdevices_portal');
define('DEVICE_ONLINE_SECONDS', 82800);

// API Key
define('PORTAL_API_KEY', getenv('PORTAL_API_KEY') ?: 'change-me-in-production');

// Manifest file path
define('MANIFEST_PATH', __DIR__ . '/manifest.json');

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Debug mode
define('DEBUG_MODE', filter_var(getenv('DEBUG_MODE') ?: false, FILTER_VALIDATE_BOOLEAN));
