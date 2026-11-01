<?php
$pageTitle = 'Customer Rental Requests'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';

$tabs = ['pending' => 'Pending', 'reserved' => 'Reserved', 'rejected' => 'Rejected', 'all' => 'All Requests'];
$tab = $_GET['tab'] ?? 'pending'; 
if (!isset($tabs[$tab])) $tab = 'pending';
$q = trim($_GET['q'] ?? '');

$counts = $pdo->query("SELECT status, COUNT(*) n FROM reservations GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

// Filter conditions
$where = "WHERE 1=1";
$args = [];
if ($tab !== 'all') { 
    $where .= " AND r.status = ?"; 
    $args[] = $tab; 
}
if ($q !== '') { 
    $where .= " AND CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''),' ',COALESCE(v.make,''),' ',COALESCE(v.model,''),' ',r.reference_code) LIKE ?"; 
    $args[] = "%$q%"; 
}

// Count matching for pagination
$countSql = "SELECT COUNT(*) FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id LEFT JOIN users u ON r.user_id=u.id $where";
$cst = $pdo->prepare($countSql);
$cst->execute($args);
$totalRequests = (int)$cst->fetchColumn();

// Pagination params
$perPage = 10;
$totalPages = max(1, (int)ceil($totalRequests / $perPage));
$page = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

$sql = "SELECT r.*, v.brand, v.make, v.model, v.plate_number, b.name AS branch_name, u.first_name, u.last_name 
        FROM reservations r 
        JOIN vehicles v ON r.vehicle_id=v.id 
        LEFT JOIN branches b ON r.branch_id=b.id
        LEFT JOIN users u ON r.user_id=u.id 
        $where 
        ORDER BY r.pickup_date ASC 
        LIMIT $perPage OFFSET $offset";
$st = $pdo->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

$start = $totalRequests > 0 ? ($offset + 1) : 0;
$end = min($offset + $perPage, $totalRequests);

$pipeline = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE status='pending'")->fetchColumn();

$pageParams = ['tab' => $tab];
if ($q !== '') $pageParams['q'] = $q;
$baseQuery = http_build_query($pageParams);
?>

<div class="crumb"><a href="<?= $A ?>rentals.php">Rentals</a> &rsaquo; Rental inflow &amp; authorization requests</div>
<div class="grid3" style="margin-bottom:16px">
  <div class="card" style="margin:0"><span class="badge b-blue">FLEET MERCHANT AUTHORIZATION</span><h1 class="t" style="margin-top:8px">Customer Rental Requests</h1><p class="sub" style="margin:0">Review inbound bookings and approve or decline them.</p></div>
  <div class="stat"><small>Queue urgency</small><b><?= (int)($counts['pending'] ?? 0) ?></b><span class="sub">pending approvals required</span></div>
  <div class="card dark" style="margin:0"><small>ESTIMATED PIPELINE VALUE</small><div style="font-size:28px;font-weight:800"><?= money($pipeline) ?></div></div>
</div>

<div class="card">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center;">
    <div class="tabs" style="margin:0">
      <?php foreach ($tabs as $k => $l): ?>
        <a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>">
          <?= $l ?> (<?= $k === 'all' ? array_sum($counts) : (int)($counts[$k] ?? 0) ?>)
        </a>
      <?php endforeach; ?>
    </div>
    <form method="get" style="display:flex; gap:6px;">
      <input type="hidden" name="tab" value="<?= e($tab) ?>">
      <input name="q" value="<?= e($q) ?>" placeholder="Search customer, vehicle or ID..." style="padding:8px 12px;border:1px solid var(--line);border-radius:10px; font-size:13px;"> 
      <button class="btn-b btn-sm">Search</button>
      <?php if ($q !== ''): ?>
        <a href="?tab=<?= e($tab) ?>" class="btn-o btn-sm" style="display:inline-flex; align-items:center;">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="tbl-wrap" style="margin-top:14px;">
    <table class="tbl">
      <thead>
        <tr>
          <th>Customer &amp; Ref</th>
          <th>Vehicle &amp; Branch</th>
          <th>Pick-up Schedule</th>
          <th>Return Schedule</th>
          <th>Days</th>
          <th>Total</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): 
          $vehName = ($r['brand'] ?: $r['make']) . ' ' . $r['model'];
        ?>
          <tr>
            <td>
              <b><?= e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: $r['driver_name']) ?></b><br>
              <span class="badge b-slate" style="font-size:10px; margin-top:2px;"><?= e($r['reference_code']) ?></span><br>
              <small class="sub">License: <?= e($r['driver_license'] ?: '—') ?></small>
            </td>
            <td>
              <strong><?= e($vehName) ?></strong><br>
              <small class="sub"><i class="fa-solid fa-location-dot" style="color:#0284c7;"></i> <?= e($r['branch_name'] ?: ($r['pickup_location'] ?: 'BGC Hub')) ?></small><br>
              <small class="sub">Plate: <?= e($r['plate_number'] ?: '—') ?></small>
            </td>
            <td>
              <?= date('M j, Y', strtotime($r['pickup_date'])) ?><br>
              <strong style="color:#0f172a; font-size:12px;"><?= date('g:i A', strtotime($r['pickup_date'])) ?></strong><br>
              <small class="sub"><?= e($r['pickup_location']) ?></small>
            </td>
            <td>
              <?= date('M j, Y', strtotime($r['return_date'])) ?><br>
              <strong style="color:#0f172a; font-size:12px;"><?= date('g:i A', strtotime($r['return_date'])) ?></strong><br>
              <small class="sub"><?= e($r['return_location']) ?></small>
            </td>
            <td><?= (int)$r['days'] ?> d</td>
            <td><strong><?= money($r['total_price']) ?></strong></td>
            <td>
              <?= status_badge($r['status']) ?>
              <?php if ($r['status'] === 'rejected' && !empty($r['rejection_reason'])): ?>
                <div style="margin-top:4px; font-size:11px; color:#b91c1c; background:#fee2e2; padding:3px 6px; border-radius:4px; max-width:180px;">
                  <i class="fa-solid fa-circle-exclamation"></i> <?= e($r['rejection_reason']) ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div class="act">
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="post" action="<?= $A ?>action.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="op" value="approve">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="back" value="admin/requests.php?tab=<?= urlencode($tab) ?>">
                    <button class="btn-d btn-sm" title="Approve booking and assign reservation">Approve</button>
                  </form>
                  <button type="button" class="btn-r btn-sm" onclick="openRejectModal(<?= (int)$r['id'] ?>, '<?= e(addslashes($r['reference_code'])) ?>', '<?= e(addslashes($vehName)) ?>')" title="Decline with reason">
                    Reject
                  </button>
                <?php elseif ($r['status'] === 'rejected'): ?>
                  <a class="btn-o btn-sm" href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>">Receipt</a>
                <?php else: ?>
                  <a class="btn-o btn-sm" href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>">View Receipt</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="8" style="text-align:center; padding:30px; color:#64748b;">No rental requests in this view.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Numbered Pagination Bar (Phase 11) -->
  <?php if ($totalRequests > 0): ?>
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:20px; padding-top:14px; border-top:1px solid var(--line);">
      <div style="font-size:13px; color:#64748b;">
        Showing <strong><?= $start ?>–<?= $end ?></strong> of <strong><?= $totalRequests ?></strong> requests
      </div>

      <div style="display:flex; gap:6px; align-items:center;">
        <?php if ($page > 1): ?>
          <a href="?<?= $baseQuery ?>&page=<?= $page - 1 ?>" class="btn-o btn-sm" style="padding:5px 10px;">&laquo; Prev</a>
        <?php else: ?>
          <span class="btn-o btn-sm" style="opacity:0.4; cursor:default; padding:5px 10px;">&laquo; Prev</span>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="btn-b btn-sm" style="padding:5px 10px;"><?= $p ?></span>
          <?php else: ?>
            <a href="?<?= $baseQuery ?>&page=<?= $p ?>" class="btn-o btn-sm" style="padding:5px 10px;"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="?<?= $baseQuery ?>&page=<?= $page + 1 ?>" class="btn-o btn-sm" style="padding:5px 10px;">Next &raquo;</a>
        <?php else: ?>
          <span class="btn-o btn-sm" style="opacity:0.4; cursor:default; padding:5px 10px;">Next &raquo;</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<div class="grid3">
  <div class="card"><b>Mandatory LTFRB compliance</b><p class="sub">Verify each driver's license before approving.</p></div>
  <div class="card"><b>Operating Hours Validation</b><p class="sub">Bookings verified between 8:00 AM – 8:00 PM PST office hours.</p></div>
  <div class="card"><b>Automated notifications</b><p class="sub">Customers are notified instantly when you approve or decline.</p></div>
</div>

<!-- Modal: Rejection Reason Dialog (Phase 6) -->
<div id="rejectionModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(3px);">
  <div style="background:#ffffff; width:100%; max-width:520px; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,0.25); overflow:hidden;">
    <div style="padding:18px 22px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
      <div>
        <h3 style="margin:0; font-size:17px; font-weight:800; color:#dc2626; display:flex; align-items:center; gap:8px;">
          <i class="fa-solid fa-triangle-exclamation"></i> Decline Rental Request
        </h3>
        <span id="rejectRefLabel" style="font-size:12px; color:#64748b; font-weight:600;"></span>
      </div>
      <button type="button" onclick="closeRejectModal()" style="background:transparent; border:none; font-size:20px; color:#64748b; cursor:pointer;">&times;</button>
    </div>

    <form method="post" action="<?= $A ?>action.php" style="padding:20px 22px;">
      <?= csrf_field() ?>
      <input type="hidden" name="op" value="reject">
      <input type="hidden" name="id" id="rejectIdInput" value="">
      <input type="hidden" name="back" value="admin/requests.php?tab=<?= urlencode($tab) ?>">

      <div style="margin-bottom:14px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase;">
          Select Standard Preset Reason
        </label>
        <select id="presetReasonSelect" onchange="applyPresetReason(this.value)" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
          <option value="Vehicle unavailable at the selected location.">Vehicle unavailable at the selected location (Default)</option>
          <option value="Driver license verification failed or incomplete documentation.">Driver license verification failed or incomplete documentation</option>
          <option value="Maintenance conflict or scheduled periodic mechanical service.">Maintenance conflict or scheduled periodic mechanical service</option>
          <option value="Requested pickup/return schedule outside branch operating hours (8 AM - 8 PM).">Requested pickup/return outside operating hours (8 AM - 8 PM)</option>
          <option value="Reservation submission outside 48-72 hour advance booking policy.">Reservation submission outside 48-72h advance booking policy</option>
          <option value="custom">Other / Custom reason...</option>
        </select>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase;">
          Explanation / Customer Notice
        </label>
        <textarea name="reason" id="rejectionReasonText" rows="3" required style="width:100%; padding:10px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none; box-sizing:border-box;">Vehicle unavailable at the selected location.</textarea>
        <small style="display:block; font-size:11px; color:#64748b; margin-top:4px;">
          This reason will be sent as an in-app notification to the customer and recorded on their booking receipt.
        </small>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" onclick="closeRejectModal()" class="btn-o" style="padding:7px 14px;">Cancel</button>
        <button type="submit" class="btn-r" style="padding:7px 18px; font-weight:700;">Confirm &amp; Decline</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRejectModal(id, ref, vehName) {
  document.getElementById('rejectIdInput').value = id;
  document.getElementById('rejectRefLabel').textContent = 'Ref #' + ref + ' · ' + vehName;
  document.getElementById('rejectionModal').style.display = 'flex';
}
function closeRejectModal() {
  document.getElementById('rejectionModal').style.display = 'none';
}
function applyPresetReason(val) {
  const txt = document.getElementById('rejectionReasonText');
  if (val === 'custom') {
    txt.value = '';
    txt.focus();
  } else {
    txt.value = val;
  }
}
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
