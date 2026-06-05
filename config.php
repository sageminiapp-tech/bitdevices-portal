<?php
// Database Configuration
// Railway provides environment variables that can be accessed via $_ENV or getenv()
// This config tries multiple possible variable names for compatibility

// Try multiple common environment variable names for Rails/Railway hosting
$host = $_ENV['DB_HOST'] 
    ?? $_ENV['MYSQL_HOST'] 
    ?? $_ENV['mysql.railway.internal']
    ?? getenv('DB_HOST')
    ?? getenv('MYSQL_HOST')
    ?? 'localhost';

$user = $_ENV['DB_USER'] 
    ?? $_ENV['MYSQL_USER'] 
    ?? $_ENV['root']
    ?? getenv('DB_USER')
    ?? getenv('MYSQL_USER')
    ?? 'root';

$pass = $_ENV['DB_PASSWORD'] 
    ?? $_ENV['MYSQL_PASSWORD'] 
    ?? $_ENV['NuyKmWtdCkIosUAJWDBnspwjdSrnuLEw']
    ?? getenv('DB_PASSWORD')
    ?? getenv('MYSQL_PASSWORD')
    ?? '';

$name = $_ENV['DB_NAME'] 
    ?? $_ENV['MYSQL_DATABASE'] 
    ?? $_ENV['railway']
    ?? getenv('DB_NAME')
    ?? getenv('MYSQL_DATABASE')
    ?? 'bitdevices';

define('DB_HOST', $host);
define('DB_NAME', $name);
define('DB_USER', $user);
define('DB_PASS', $pass);

// Application Settings
define('APP_NAME', 'BitDevices Portal');
define('APP_BASE', ''); // change if portal lives in a subfolder

// Session & Device Settings
define('PORTAL_SESSION_NAME', 'bitdevices_portal');
define('DEVICE_ONLINE_SECONDS', 300); // 5 minutes

// API Key for device registration (set via environment or hardcode here)
define('PORTAL_API_KEY', $_ENV['PORTAL_API_KEY'] ?? getenv('PORTAL_API_KEY') ?? 'change-me-in-production');

// Manifest file path for firmware updates
define('MANIFEST_PATH', __DIR__ . '/manifest.json');

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Debug mode (set to false in production)
define('DEBUG_MODE', $_ENV['DEBUG_MODE'] ?? getenv('DEBUG_MODE') ?? false);
