<?php
// Copy this file as-is and edit the constants below.
// InfinityFree note: use the MySQL hostname shown in your client area, not localhost.

// Get database credentials from Railway environment
$host = $_ENV['MYSQL_HOST'] ?? $_ENV['mysql.railway.internal'] ?? 'localhost';
$user = $_ENV['MYSQL_USER'] ?? $_ENV['root'] ?? 'root';
$pass = $_ENV['MYSQL_PASSWORD'] ?? $_ENV['NuyKmWtdCkIosUAJWDBnspwjdSrnuLEw'] ?? '';
$name = $_ENV['MYSQL_DATABASE'] ?? $_ENV['railway'] ?? 'bitdevices';

define('DB_HOST', $host);
define('DB_NAME', $name);
define('DB_USER', $user);
define('DB_PASS', $pass);

define('APP_NAME', 'BitDevices Portal');
define('APP_BASE', ''); // change if portal lives in a subfolder

define('PORTAL_SESSION_NAME', 'bitdevices-portal');
define('DEVICE_ONLINE_SECONDS', 300); // 5 minutes

define('MANIFEST_PATH', __DIR__ . '/manifest.json');

date_default_timezone_set('Africa/Johannesburg');
