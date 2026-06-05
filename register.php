<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['uid'])) {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $displayName = trim($_POST['display_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $ins = db()->prepare('INSERT INTO users (username, password_hash, display_name) VALUES (?, ?, ?)');
            $ins->execute([$username, $hash, $displayName !== '' ? $displayName : null]);

            $success = 'Account created successfully. You can now log in.';
        }
    }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<div class="row justify-content-center mt-5">
  <div class="col-12 col-md-7 col-lg-5 col-xl-4">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body p-5">
        <div style="text-align: center; margin-bottom: 2rem;">
          <i class="fas fa-user-plus" style="font-size: 2.5rem; background: linear-gradient(135deg, #00d9ff, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
        </div>
        <h1 class="h4 mb-1 text-center" style="background: linear-gradient(135deg, #00d9ff, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 800;">Create Account</h1>
        <p class="text-secondary mb-4 text-center">Register a new portal user account.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger" role="alert" style="border-left: 4px solid #ec4899;">
            <i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success" role="alert" style="border-left: 4px solid #00d9ff;">
            <i class="fas fa-check-circle me-2"></i><?= h($success) ?>
          </div>
          <a href="<?= APP_BASE ?>/index.php" class="btn btn-primary w-100">
            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
          </a>
        <?php else: ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
              <input type="text" class="form-control" name="username" placeholder="Choose a username" required>
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="fas fa-id-card me-2"></i>Display Name <span style="font-size: 0.8rem; color: #a0aec0;">(optional)</span></label>
              <input type="text" class="form-control" name="display_name" placeholder="Your name (optional)">
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
              <input type="password" class="form-control" name="password" placeholder="Min. 8 characters" required>
              <small class="text-secondary">Must be at least 8 characters long</small>
            </div>

            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock me-2"></i>Confirm Password</label>
              <input type="password" class="form-control" name="confirm_password" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #00d9ff, #d946ef); border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);">
              <i class="fas fa-user-plus me-2"></i>Create Account
            </button>
          </form>

          <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0, 217, 255, 0.1); text-align: center;">
            <p class="text-secondary mb-2">Already have an account?</p>
            <a href="<?= APP_BASE ?>/index.php" style="color: #00d9ff; text-decoration: none; font-weight: 600;">
              <i class="fas fa-sign-in-alt me-2"></i>Go to Login
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
