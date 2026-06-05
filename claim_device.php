<?php
require_once __DIR__ . '/auth.php';
require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imei = trim($_POST['imei'] ?? '');
    $chipid = trim($_POST['chipid'] ?? '');

    if ($imei === '') {
        $error = 'IMEI is required.';
    } else {
        $stmt = db()->prepare('SELECT * FROM devices WHERE imei = ? LIMIT 1');
        $stmt->execute([$imei]);
        $device = $stmt->fetch();

        if (!$device) {
            $error = 'Device not found yet. Make sure the device has checked in at least once.';
        } else {
            if ($chipid !== '' && $device['chipid'] !== $chipid) {
                $error = 'ChipID does not match.';
            } elseif (!empty($device['owner_user_id']) && (int)$device['owner_user_id'] !== (int)$_SESSION['uid']) {
                $error = 'This device is already claimed by another user.';
            } else {
                $upd = db()->prepare('UPDATE devices SET owner_user_id = ?, claimed_at = NOW() WHERE id = ?');
                $upd->execute([(int)$_SESSION['uid'], (int)$device['id']]);
                $message = 'Device successfully claimed.';
            }
        }
    }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-lg-6">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body p-5">
        <div style="text-align: center; margin-bottom: 2rem;">
          <i class="fas fa-plus-circle" style="font-size: 2.5rem; background: linear-gradient(135deg, #00d9ff, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
        </div>
        <h1 class="h4 mb-1 text-center" style="background: linear-gradient(135deg, #00d9ff, #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 800;">Claim Device</h1>
        <p class="text-secondary text-center mb-4">Add a new device to your account by providing its IMEI. ChipID verification is optional but recommended for security.</p>

        <?php if ($error): ?>
          <div class="alert alert-danger" style="border-left: 4px solid #ec4899;">
            <i class="fas fa-exclamation-circle me-2"></i><?= h($error) ?>
          </div>
        <?php endif; ?>

        <?php if ($message): ?>
          <div class="alert alert-success" style="border-left: 4px solid #10b981;">
            <i class="fas fa-check-circle me-2"></i><?= h($message) ?>
          </div>
          <a href="<?= APP_BASE ?>/dashboard.php" class="btn btn-primary w-100">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
          </a>
        <?php else: ?>
          <form method="post">
            <div class="mb-4">
              <label class="form-label"><i class="fas fa-microchip me-2"></i>Device IMEI <span style="color: #ec4899;">*</span></label>
              <input type="text" name="imei" class="form-control" placeholder="Enter device IMEI" required autofocus>
              <small class="text-secondary"><i class="fas fa-info-circle me-1"></i>You can find this on your device label or documentation</small>
            </div>

            <div class="mb-4">
              <label class="form-label"><i class="fas fa-id-card me-2"></i>Device ChipID <span style="font-size: 0.8rem; color: #a0aec0;">(recommended)</span></label>
              <input type="text" name="chipid" class="form-control" placeholder="Enter device ChipID (optional)">
              <small class="text-secondary"><i class="fas fa-shield me-1"></i>Providing ChipID adds an extra security layer</small>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #00d9ff, #d946ef); border: none; font-weight: 600; box-shadow: 0 4px 15px rgba(0, 217, 255, 0.3);">
              <i class="fas fa-plus-circle me-2"></i>Claim Device
            </button>
          </form>

          <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(0, 217, 255, 0.1);">
            <p class="text-secondary text-center mb-2"><i class="fas fa-lightbulb me-1"></i>Need help?</p>
            <a href="<?= APP_BASE ?>/dashboard.php" style="color: #00d9ff; text-decoration: none; font-weight: 600; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-arrow-left me-2"></i>Go Back to Dashboard
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
