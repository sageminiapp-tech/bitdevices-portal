<?php
require_once __DIR__ . '/auth.php';
logout_user();
header('Location: ' . APP_BASE . '/index.php');
exit;
