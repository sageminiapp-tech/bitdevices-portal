<?php
require_once __DIR__ . '/db.php';

session_name(PORTAL_SESSION_NAME);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function login_user(string $username, string $password): bool {
    $stmt = db()->prepare('SELECT id, username, password_hash, display_name, is_admin FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;

    $_SESSION['uid'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['display_name'] = $user['display_name'];
    $_SESSION['is_admin'] = (int)$user['is_admin'];
    return true;
}

function require_login(): void {
    if (empty($_SESSION['uid'])) {
        header('Location: ' . APP_BASE . '/index.php');
        exit;
    }
}

function is_admin(): bool {
    return !empty($_SESSION['is_admin']);
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
