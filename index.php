<?php
require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['uid'])) {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!login_user($username, $password)) {
        $error = 'Invalid username or password';
    } else {
        header('Location: ' . APP_BASE . '/dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h(APP_NAME) ?> - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * { font-family: 'Inter', sans-serif; }
    
    :root {
      --primary-dark: #0f1419;
      --secondary-dark: #1a1f2e;
      --tertiary-dark: #252d3d;
      --accent-cyan: #00d9ff;
      --accent-purple: #d946ef;
      --accent-pink: #ec4899;
      --text-primary: #f0f4f8;
      --text-secondary: #a0aec0;
      --glass-bg: rgba(26, 31, 46, 0.7);
      --glass-border: rgba(0, 217, 255, 0.1);
    }
    
    body {
      background: linear-gradient(135deg, var(--primary-dark) 0%, #1a1a2e 50%, #16213e 100%);
      color: var(--text-primary);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(0, 217, 255, 0.15) 0%, transparent 40%),
        radial-gradient(circle at 80% 20%, rgba(217, 70, 239, 0.15) 0%, transparent 40%);
      pointer-events: none;
      z-index: -1;
    }
    
    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }
    
    .card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.6s ease-out;
    }
    
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .card-body {
      padding: 2.5rem;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    
    .login-icon {
      font-size: 3rem;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1rem;
      display: block;
    }
    
    .login-title {
      font-size: 1.8rem;
      font-weight: 800;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: 0.5px;
      margin-bottom: 0.5rem;
    }
    
    .login-subtitle {
      color: var(--text-secondary);
      font-size: 0.95rem;
      line-height: 1.5;
    }
    
    .form-group {
      margin-bottom: 1.25rem;
    }
    
    .form-label {
      color: var(--text-secondary);
      font-weight: 600;
      margin-bottom: 0.6rem;
      font-size: 0.95rem;
    }
    
    .form-control {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      color: var(--text-primary);
      border-radius: 10px;
      padding: 0.85rem 1rem;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }
    
    .form-control:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--accent-cyan);
      box-shadow: 0 0 0 0.2rem rgba(0, 217, 255, 0.25);
      color: var(--text-primary);
    }
    
    .form-control::placeholder {
      color: var(--text-secondary);
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      border: none;
      color: var(--primary-dark);
      font-weight: 700;
      padding: 0.85rem 1.5rem;
      border-radius: 10px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);
      font-size: 1rem;
      letter-spacing: 0.5px;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 217, 255, 0.4);
      color: var(--primary-dark);
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .alert {
      background: rgba(236, 72, 153, 0.1);
      border: 1px solid rgba(236, 72, 153, 0.3);
      border-radius: 10px;
      backdrop-filter: blur(10px);
      color: #ff6b9d;
      margin-bottom: 1.5rem;
      animation: shake 0.5s ease-in-out;
    }
    
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }
    
    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid var(--glass-border);
    }
    
    .login-footer-text {
      color: var(--text-secondary);
      font-size: 0.95rem;
      margin-bottom: 0.5rem;
    }
    
    .login-footer a {
      color: var(--accent-cyan);
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
    }
    
    .login-footer a:hover {
      color: var(--accent-purple);
      text-decoration: underline;
    }
  </style>
</head>
<body>
<div class="login-container">
  <div class="card">
    <div class="card-body">
      <div class="login-header">
        <i class="fas fa-microchip login-icon"></i>
        <h1 class="login-title"><?= h(APP_NAME) ?></h1>
        <p class="login-subtitle">Device Management Portal</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
          <i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="post">
        <div class="form-group">
          <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
          <input type="text" class="form-control" name="username" placeholder="Enter your username" required autofocus>
        </div>
        
        <div class="form-group">
          <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
          <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
        </div>
        
        <button type="submit" class="btn btn-login w-100">
          <i class="fas fa-sign-in-alt me-2"></i>Login to Portal
        </button>
      </form>
      
      <div class="login-footer">
        <p class="login-footer-text">Don't have an account?</p>
        <a href="<?= APP_BASE ?>/register.php">
          <i class="fas fa-user-plus me-2"></i>Create New Account
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
