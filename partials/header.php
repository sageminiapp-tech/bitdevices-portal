<?php require_once __DIR__ . '/../auth.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h(APP_NAME) ?></title>
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
      position: relative;
      overflow-x: hidden;
    }
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 80%, rgba(0, 217, 255, 0.1) 0%, transparent 40%),
        radial-gradient(circle at 80% 20%, rgba(217, 70, 239, 0.1) 0%, transparent 40%);
      pointer-events: none;
      z-index: -1;
    }
    
    .navbar {
      background: var(--glass-bg) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--glass-border);
      box-shadow: 0 8px 32px rgba(0, 217, 255, 0.1);
    }
    
    .navbar-brand {
      font-weight: 800;
      font-size: 1.5rem;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      letter-spacing: 0.5px;
    }
    
    .nav-link {
      color: var(--text-secondary) !important;
      transition: all 0.3s ease;
      position: relative;
      font-weight: 500;
    }
    
    .nav-link:hover {
      color: var(--accent-cyan) !important;
    }
    
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--accent-cyan), var(--accent-purple));
      transition: width 0.3s ease;
    }
    
    .nav-link:hover::after {
      width: 100%;
    }
    
    .container {
      position: relative;
      z-index: 1;
    }
    
    .card {
      background: var(--glass-bg) !important;
      border: 1px solid var(--glass-border) !important;
      backdrop-filter: blur(10px);
      border-radius: 16px;
      transition: all 0.3s ease;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .card:hover {
      border-color: rgba(0, 217, 255, 0.3) !important;
      box-shadow: 0 8px 32px rgba(0, 217, 255, 0.2);
      transform: translateY(-2px);
    }
    
    .stat-card {
      border-left: 3px solid var(--accent-cyan);
    }
    
    .stat-card:nth-child(2) {
      border-left-color: var(--accent-purple);
    }
    
    .stat-card:nth-child(3) {
      border-left-color: var(--accent-pink);
    }
    
    .stat-card .display-6 {
      font-weight: 700;
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-size: 2.5rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple)) !important;
      border: none;
      font-weight: 600;
      padding: 0.7rem 1.5rem;
      border-radius: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 217, 255, 0.4);
    }
    
    .btn-primary:active {
      transform: translateY(0);
    }
    
    .form-control {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      color: var(--text-primary);
      border-radius: 8px;
      transition: all 0.3s ease;
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
    
    .form-label {
      color: var(--text-secondary);
      font-weight: 500;
      margin-bottom: 0.5rem;
    }
    
    .table {
      color: var(--text-primary);
    }
    
    .table thead th {
      white-space: nowrap;
      color: var(--accent-cyan);
      border-bottom: 2px solid var(--glass-border);
      font-weight: 600;
    }
    
    .table tbody tr {
      transition: all 0.3s ease;
      border-bottom: 1px solid var(--glass-border);
    }
    
    .table tbody tr:hover {
      background: rgba(0, 217, 255, 0.1);
    }
    
    .table td {
      color: var(--text-secondary);
    }
    
    .alert {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      backdrop-filter: blur(10px);
    }
    
    .alert-danger {
      border-left: 4px solid var(--accent-pink);
      color: var(--text-primary);
    }
    
    .alert-success {
      border-left: 4px solid var(--accent-cyan);
      color: var(--text-primary);
    }
    
    .text-secondary {
      color: var(--text-secondary) !important;
    }
    
    h1, h2, h3, h4, h5, h6 {
      color: var(--text-primary);
      font-weight: 700;
    }
    
    a {
      color: var(--accent-cyan);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    a:hover {
      color: var(--accent-purple);
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .card {
      animation: fadeInUp 0.6s ease-out;
    }
    
    .badge {
      background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
      color: var(--primary-dark);
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 20px;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-none">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= APP_BASE ?>/dashboard.php">
      <i class="fas fa-microchip me-2"></i><?= h(APP_NAME) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <?php if (!empty($_SESSION['uid'])): ?>
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item me-lg-3 text-white-50 small">
          <i class="fas fa-user-circle me-2"></i>Signed in as <strong><?= h($_SESSION['display_name'] ?: $_SESSION['username']) ?></strong><?= is_admin() ? ' <span class="badge ms-2"><i class="fas fa-crown me-1"></i>Admin</span>' : '' ?>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_BASE ?>/dashboard.php"><i class="fas fa-gauge-high me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_BASE ?>/claim_device.php"><i class="fas fa-plus-circle me-2"></i>Claim Device</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_BASE ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div class="container py-4">
