<?php
require_once __DIR__ . '/auth.php';
require_login();

$imei = trim($_GET['imei'] ?? '');
if ($imei === '') {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM devices WHERE imei = ? LIMIT 1');
$stmt->execute([$imei]);
$device = $stmt->fetch();
if (!$device) {
    http_response_code(404);
    echo 'Device not found';
    exit;
}

if (!is_admin() && (int)$device['owner_user_id'] !== (int)$_SESSION['uid']) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$logsStmt = db()->prepare('SELECT * FROM device_logs WHERE device_id = ? ORDER BY created_at DESC LIMIT 100');
$logsStmt->execute([$device['id']]);
$logs = $logsStmt->fetchAll();
$latestVersion = latest_fw_version();
$latestUrl = latest_fw_url();
$updateAvailable = ($latestVersion && $device['fw_version'] && version_compare($device['fw_version'], $latestVersion, '<'));
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><i class="fas fa-microchip me-2"></i>Device <code style="background: linear-gradient(135deg, rgba(0, 217, 255, 0.2), rgba(217, 70, 239, 0.2)); color: #00d9ff; font-family: 'Space Mono', monospace;"><?= h($device['imei']) ?></code></h1>
    <p class="text-secondary mb-0"><i class="fas fa-info-circle me-2"></i>Detailed device status, firmware state, and telemetry history.</p>
  </div>
  <a href="<?= APP_BASE ?>/dashboard.php" class="btn btn-outline-primary">
    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card shadow-sm border-0 h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <div class="text-secondary small mb-2"><i class="fas fa-circle me-2"></i>Status</div>
            <span class="badge rounded-pill" style="<?= is_device_online($device['last_seen']) ? 'background: linear-gradient(135deg, #00d9ff, #00a8cc); color: var(--primary-dark);' : 'background: linear-gradient(135deg, #ec4899, #be185d); color: white;' ?>">
              <i class="fas fa-circle fa-xs me-1"></i><?= is_device_online($device['last_seen']) ? 'Online' : 'Offline' ?>
            </span>
          </div>
          <div style="font-size: 1.8rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-plug"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card shadow-sm border-0 h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <div class="text-secondary small mb-2"><i class="fas fa-box me-2"></i>Current Firmware</div>
            <div class="display-6" style="font-size: 1.8rem;"><?= h($device['fw_version'] ?: 'n/a') ?></div>
          </div>
          <div style="font-size: 1.8rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-code"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card shadow-sm border-0 h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <div class="text-secondary small mb-2"><i class="fas fa-cloud me-2"></i>Latest Firmware</div>
            <div class="display-6" style="font-size: 1.8rem;"><?= h($latestVersion ?: 'n/a') ?></div>
          </div>
          <div style="font-size: 1.8rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-server"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-id-card me-2"></i>Identity</h5>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-check me-1"></i>ChipID</small>
          <div><code style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['chipid']) ?></code></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-location-dot me-1"></i>Site</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['site'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-network-wired me-1"></i>IP</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['ip'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-gear me-1"></i>Mode</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['mode'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-clock me-1"></i>Last Seen</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><small><?= h($device['last_seen'] ?: 'Never') ?></small></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-mobile-alt me-2"></i>GSM Network</h5>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-building me-1"></i>Operator</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['gsm_operator'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-wifi me-1"></i>Network</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['gsm_network'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-signal me-1"></i>Signal Strength (RSSI)</small>
          <div>
            <span class="badge" style="background: linear-gradient(135deg, #00d9ff, #00a8cc); color: var(--primary-dark);">
              <?= h((string)($device['gsm_rssi'] ?: 'n/a')) ?> dBm
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-battery-full me-2"></i>WiFi & Power</h5>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-wifi me-1"></i>SSID</small>
          <div style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($device['wifi_ssid'] ?: '—') ?></div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-flash me-1"></i>Battery Voltage</small>
          <div>
            <span class="badge" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
              <i class="fas fa-bolt me-1"></i><?= $device['battery_v'] !== null ? h(number_format((float)$device['battery_v'], 2) . 'V') : 'n/a' ?>
            </span>
          </div>
        </div>
        <div class="mb-2">
          <small class="text-secondary"><i class="fas fa-microchip me-1"></i>ADC Raw</small>
          <div><code style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h((string)($device['adc_raw'] ?? 'n/a')) ?></code></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <h5 class="mb-3"><i class="fas fa-cloud-download-alt me-2"></i>Firmware Update Status</h5>
    <?php if ($updateAvailable): ?>
      <div class="alert alert-warning mb-0" style="border-left: 4px solid #fbbf24; background: rgba(251, 191, 36, 0.1); color: #fbbf24;">
        <i class="fas fa-exclamation-triangle me-2"></i><strong>Update Available</strong>
        <div class="mt-2">Device is on <code style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 0.25rem 0.5rem; border-radius: 4px;"><?= h($device['fw_version']) ?></code> and latest is <code style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 0.25rem 0.5rem; border-radius: 4px;"><?= h($latestVersion) ?></code></div>
        <?php if ($latestUrl): ?><div class="mt-2"><i class="fas fa-download me-1"></i>Firmware URL: <a href="<?= h($latestUrl) ?>" target="_blank" style="color: #fbbf24;"><?= h($latestUrl) ?></a></div><?php endif; ?>
      </div>
    <?php else: ?>
      <div class="alert alert-success mb-0" style="border-left: 4px solid #10b981; background: rgba(16, 185, 129, 0.1); color: #10b981;">
        <i class="fas fa-check-circle me-2"></i><strong>Current</strong> - Device firmware is up to date.
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body pb-0">
    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Recent Telemetry History</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th><i class="fas fa-clock me-1"></i>Time</th>
          <th><i class="fas fa-code me-1"></i>FW Version</th>
          <th><i class="fas fa-mobile-alt me-1"></i>GSM</th>
          <th><i class="fas fa-wifi me-1"></i>WiFi SSID</th>
          <th><i class="fas fa-bolt me-1"></i>Battery</th>
          <th><i class="fas fa-network-wired me-1"></i>IP</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$logs): ?>
        <tr><td colspan="6" class="text-center text-secondary py-5">
          <div><i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i></div>
          <div>No telemetry data yet.</div>
        </td></tr>
      <?php endif; ?>
      <?php foreach ($logs as $row): ?>
        <tr>
          <td><small><?= h($row['created_at']) ?></small></td>
          <td><code style="background: rgba(0, 217, 255, 0.1); color: #00d9ff;"><?= h($row['fw_version']) ?></code></td>
          <td><small><?= h(($row['gsm_operator'] ?: '—') . ' / ' . ($row['gsm_rssi'] ?? 'n/a')) ?></small></td>
          <td><small><?= h($row['wifi_ssid'] ?: '—') ?></small></td>
          <td><small><?= $row['battery_v'] !== null ? h(number_format((float)$row['battery_v'], 2) . 'V') : '—' ?></small></td>
          <td><small><?= h($row['ip'] ?: '—') ?></small></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
  code {
    font-family: 'Space Mono', monospace;
    font-size: 0.85rem;
  }
</style>
<?php include __DIR__ . '/partials/footer.php'; ?>
