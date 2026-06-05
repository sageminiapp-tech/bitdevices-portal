<?php
// Copy this file as-is and edit the constants below.
// InfinityFree note: use the MySQL hostname shown in your client area, not localhost.

$host = $_ENV['mysql.railway.internal'];
$user = $_ENV['root'];
$pass = $_ENV['QOAqmBOQaennStCjGqNNySpLZYezvFus'];
$name = $_ENV['railway'];

define('DB_HOST', $host);
define('DB_NAME', $name);
define('DB_USER', $user);
define('DB_PASS', $pass);

define('APP_NAME', 'BitDevices Portal');
define('APP_BASE', ''); // change if portal lives in a subfolder

define('PORTAL_SESSION_NAME', 'bitdevices_portal');
define('DEVICE_ONLINE_SECONDS', 300); // 5 minutes

define('MANIFEST_PATH', __DIR__ . '/manifest.json');

date_default_timezone_set('Africa/Johannesburg');
