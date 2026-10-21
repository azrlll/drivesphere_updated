<?php
$pageTitle = 'Customer Rental Requests'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';
$tabs = ['pending' => 'Pending', 'reserved' => 'Reserved', 'rejected' => 'Rejected', 'all' => 'All Requests'];
$tab = $_GET['tab'] ?? 'pending'; if (!isset($tabs[$tab])) $tab = 'pending';
$q = trim($_GET['q'] ?? '');
$counts = $pdo->query("SELECT status, COUNT(*) n FROM reservations GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$sql = "SELECT r.*, v.make, v.model, u.first_name, u.last_name FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id LEFT JOIN users u ON r.user_id=u.id WHERE 1=1"; $args = [];
if ($tab !== 'all') { $sql .= " AND r.status = ?"; $args[] = $tab; }
if ($q !== '') { $sql .= " AND CONCAT(u.first_name,' ',u.last_name,' ',v.make,' ',v.model,' ',r.reference_code) LIKE ?"; $args[] = "%$q%"; }
$st = $pdo->prepare($sql . " ORDER BY r.pickup_date ASC"); $st->execute($args); $rows = $st->fetchAll();
$pipeline = (float)$pdo->query("SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE status='pending'")->fetchColumn();
?>
<div class="crumb"><a href="<?= $A ?>rentals.php">Rentals</a> &rsaquo; Rental inflow &amp; authorization requests</div>
<div class="grid3" style="margin-bottom:16px">
  <div class="card" style="margin:0"><span class="badge b-blue">FLEET MERCHANT AUTHORIZATION</span><h1 class="t" style="margin-top:8px">Customer Rental Requests</h1><p class="sub" style="margin:0">Review inbound bookings and approve or decline them.</p></div>
  <div class="stat"><small>Queue urgency</small><b><?= (int)($counts['pending'] ?? 0) ?></b><span class="sub">pending approvals required</span></div>
  <div class="card dark" style="margin:0"><small>ESTIMATED PIPELINE VALUE</small><div style="font-size:28px;font-weight:800"><?= money($pipeline) ?></div></div>
</div>
<div class="card">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
    <div class="tabs" style="margin:0"><?php foreach ($tabs as $k => $l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?>"><?= $l ?> (<?= $k === 'all' ? array_sum($counts) : (int)($counts[$k] ?? 0) ?>)</a><?php endforeach; ?></div>
    <form method="get"><input type="hidden" name="tab" value="<?= e($tab) ?>"><input name="q" value="<?= e($q) ?>" placeholder="Search customer, vehicle or ID..." style="padding:8px 12px;border:1px solid var(--line);border-radius:10px"> <button class="btn-b btn-sm">Search</button></form>
  </div>
  <div class="tbl-wrap"><table class="tbl"><tr><th>Customer</th><th>Vehicle</th><th>Pick-up</th><th>Return</th><th>Days</th><th>Status</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?><tr>
    <td><b><?= e(trim($r['first_name'] . ' ' . $r['last_name']) ?: $r['driver_name']) ?></b><br><small class="sub"><?= e($r['reference_code']) ?></small><br><small class="sub">License: <?= e($r['driver_license'] ?: '—') ?></small></td>
    <td><?= e($r['make'] . ' ' . $r['model']) ?></td><td><?= date('M j, g:i A', strtotime($r['pickup_date'])) ?><br><small class="sub"><?= e($r['pickup_location']) ?></small></td>
    <td><?= date('M j, g:i A', strtotime($r['return_date'])) ?><br><small class="sub"><?= e($r['return_location']) ?></small></td><td><?= (int)$r['days'] ?></td><td><?= status_badge($r['status']) ?></td>
    <td><div class="act">
    <?php if ($r['status'] === 'pending'): ?>
      <form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="approve"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="back" value="admin/requests.php"><button class="btn-d btn-sm">Approve</button></form>
      <form method="post" action="<?= $A ?>action.php" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="op" value="reject"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="back" value="admin/requests.php"><input name="reason" placeholder="Reason" style="width:110px;padding:5px 8px;border:1px solid var(--line);border-radius:8px"><button class="btn-r btn-sm">Reject</button></form>
    <?php elseif ($r['status'] === 'rejected'): ?><small class="sub"><?= e($r['rejection_reason']) ?></small>
    <?php else: ?><a class="btn-o btn-sm" href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>">View Receipt</a><?php endif; ?></div></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7">No requests in this view.</td></tr><?php endif; ?></table></div>
</div>
<div class="grid3"><div class="card"><b>Mandatory LTFRB compliance</b><p class="sub">Verify each driver's license before approving.</p></div><div class="card"><b>Pre-authorization hold</b><p class="sub">A ₱5,000 refundable deposit applies at handover.</p></div><div class="card"><b>Automated notifications</b><p class="sub">Customers are notified instantly when you approve or decline.</p></div></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
