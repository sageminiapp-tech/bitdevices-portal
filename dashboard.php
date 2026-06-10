<?php
require_once __DIR__ . '/auth.php';
require_login();

$q = trim($_GET['q'] ?? '');
$params = [];

$sql = '
    SELECT 
        d.*,
        COALESCE(NULLIF(u.display_name, \'\'), u.username, CONCAT(\'User #\', d.owner_user_id), \'unclaimed\') AS owner_display_name
    FROM devices d
    LEFT JOIN users u ON u.id = d.owner_user_id
';

$where = [];

if (!is_admin()) {
    $where[] = 'd.owner_user_id = ?';
    $params[] = (int)$_SESSION['uid'];
}

if ($q !== '') {
    $where[] = '(d.imei LIKE ? OR d.chipid LIKE ? OR d.site LIKE ? OR d.wifi_ssid LIKE ? OR u.display_name LIKE ? OR u.username LIKE ?)';
    $needle = '%' . $q . '%';

    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
    $params[] = $needle;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY d.last_seen DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$devices = $stmt->fetchAll();
$latestVersion = latest_fw_version();
$onlineCount = count(array_filter($devices, fn($d)=>is_device_online($d['last_seen'] ?? null)));
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h1 class="h3 mb-1"><i class="fas fa-gauge-high me-2"></i><?= is_admin() ? 'All Devices' : 'My Devices' ?></h1>
    <p class="text-secondary mb-0"><i class="fas fa-info-circle me-2"></i>Monitor online status, firmware, GSM, Wi‑Fi, and battery telemetry.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card shadow-sm border-0 h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <div class="text-secondary small mb-2"><i class="fas fa-download me-2"></i>Latest Firmware</div>
            <div class="display-6"><?= h($latestVersion ?: 'n/a') ?></div>
          </div>
          <div class="text-center" style="font-size: 2.5rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-box"></i>
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
            <div class="text-secondary small mb-2"><i class="fas fa-microchip me-2"></i><?= is_admin() ? 'Total Devices' : 'My Devices' ?></div>
            <div class="display-6"><?= count($devices) ?></div>
          </div>
          <div class="text-center" style="font-size: 2.5rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-server"></i>
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
            <div class="text-secondary small mb-2"><i class="fas fa-wifi me-2"></i>Online Today</div>
            <div class="display-6"><?= $onlineCount ?></div>
          </div>
          <div class="text-center" style="font-size: 2.5rem; opacity: 0.1; margin-left: 1rem;">
            <i class="fas fa-signal"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-12 col-md-9 col-lg-10">
        <div class="input-group">
          <span class="input-group-text" style="background: transparent; border-right: none;">
            <i class="fas fa-search" style="color: var(--text-secondary);"></i>
          </span>
          <input type="text" class="form-control" name="q" value="<?= h($q) ?>" placeholder="Search by IMEI / chipid / site / SSID" style="border-left: none;">
        </div>
      </div>
      <div class="col-12 col-md-3 col-lg-2 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search me-2"></i>Search
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body pb-0">
    <h5 class="mb-3"><i class="fas fa-table me-2"></i>Device List</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th><i class="fas fa-sim-card me-2"></i>IMEI</th>
		  <th><i class="fas fa-sim-card me-2"></i>ICCID</th>
          <th><i class="fas fa-id-card me-2"></i>ChipID</th>
          <?php if (is_admin()): ?><th><i class="fas fa-user me-2"></i>Owner</th><?php endif; ?>
          <th><i class="fas fa-circle me-2"></i>Status</th>
          <th><i class="fas fa-box me-2"></i>FW</th>
          <th><i class="fas fa-cloud-download-alt me-2"></i>Update</th>
          <th><i class="fas fa-mobile-alt me-2"></i>GSM</th>
          <th><i class="fas fa-wifi me-2"></i>WiFi</th>
          <th><i class="fas fa-battery-full me-2"></i>Battery</th>
          <th><i class="fas fa-clock me-2"></i>Last Seen</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody> 
    <?php if (!$devices): ?> 
        <tr>
            <!-- Changed 'text-primary' to 'text-dark' for black text -->
            <td colspan="<?= is_admin() ? '12' : '11' ?>" class="text-center text-dark py-5"> 
                <div><i class="fas fa-inbox fa-3x mb-3" style="opacity: 0.3; color: #000;"></i></div> 
                <div>No devices found.</div> 
            </td>
        </tr> 
    <?php endif; ?> 

    <?php foreach ($devices as $d): 
        $online = is_device_online($d['last_seen'] ?? null); 
        $fw = $d['fw_version'] ?? ''; 
        $update = ($latestVersion && $fw && version_compare($fw, $latestVersion, '<')) ? 'Available' : 'Current'; 
    ?> 
        <!-- Added Bootstrap text-dark class to the row to ensure general text defaults to black -->
        <tr class="text-dark"> 
            <td><code style="color: #000;"><?= h($d['imei']) ?></code></td> 
            <td><code style="color: #000;"><?= h($d['iccid']) ?></code></td> 
            <td><code style="color: #000;"><?= h($d['chipid']) ?></code></td> 
            <?php if (is_admin()): ?>
    <td><?= h((string)($d['owner_display_name'] ?? 'unclaimed')) ?></td>
<?php endif; ?>
            <td> 
                <!-- Changed badge text color to #000 -->
                <span class="badge rounded-pill <?= $online ? 'badge-online' : 'badge-offline' ?>" style="<?= $online ? 'background: linear-gradient(135deg, #00d9ff, #00a8cc); color: #000;' : 'background: linear-gradient(135deg, #ec4899, #be185d); color: #000;' ?>"> 
                    <i class="fas fa-circle fa-xs me-1"></i><?= $online ? 'Online' : 'Offline' ?> 
                </span> 
            </td> 
            <!-- Changed color from var(--accent-cyan) to #000 -->
            <td><span style="font-family: 'Space Mono', monospace; color: #000;"><?= h($fw ?: 'n/a') ?></span></td> 
            <td> 
                <!-- Changed badge text color to #000 for both conditions -->
                <span class="badge rounded-pill" style="<?= $update === 'Available' ? 'background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #000;' : 'background: linear-gradient(135deg, #10b981, #059669); color: #000;' ?>"> 
                    <i class="fas fa-<?= $update === 'Available' ? 'cloud-download-alt' : 'check-circle' ?> me-1"></i><?= h($update) ?> 
                </span> 
            </td> 
            <td> <small><?= h(($d['gsm_operator'] ?: '—') . ' / ' . ($d['gsm_rssi'] ?? 'n/a')) ?></small> </td> 
            <td> <small><?= h($d['wifi_ssid'] ?: '—') ?></small> </td> 
            <td> <small><?= h($d['battery_v'] !== null ? number_format((float)$d['battery_v'], 2) . 'V' : '—') ?></small> </td> 
            <td> <small><?= h($d['last_seen'] ?: 'never') ?></small> </td> 
            <td class="text-center"> 
                <!-- Changed button text color to #000 and updated border color -->
                <a class="btn btn-sm btn-outline-dark" href="<?= APP_BASE ?>/device.php?imei=<?= urlencode($d['imei']) ?>" style="border: 1px solid #000; color: #000;"> 
                    <i class="fas fa-arrow-right me-1"></i>View 
                </a> 
            </td> 
        </tr> 
    <?php endforeach; ?> 
</tbody>

    </table>
  </div>
</div>

<style>
  .input-group-text {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--glass-border);
    border-radius: 10px 0 0 10px;
    color: var(--text-secondary);
  }
  
  code {
    background: rgba(0, 217, 255, 0.1);
    color: var(--accent-cyan);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-size: 0.85rem;
  }
  
  .btn-outline-primary {
    transition: all 0.3s ease;
  }
  
  .btn-outline-primary:hover {
    background: linear-gradient(135deg, var(--accent-cyan), var(--accent-purple));
    border-color: transparent;
    color: var(--primary-dark) !important;
  }
</style>
<?php include __DIR__ . '/partials/footer.php'; ?>
