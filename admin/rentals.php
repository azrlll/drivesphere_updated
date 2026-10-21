<?php
$pageTitle = 'Rental Overview'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';
$tabs = ['en_route' => 'En Route', 'returned' => 'Returned', 'reserved' => 'Reserved', 'all' => 'All Rentals'];
$tab = $_GET['tab'] ?? 'en_route'; if (!isset($tabs[$tab])) $tab = 'en_route';
$cust = (int)($_GET['customer'] ?? 0); $q = trim($_GET['q'] ?? '');

$sql = "SELECT r.*, v.make, v.model, v.plate_number, u.first_name, u.last_name FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id LEFT JOIN users u ON r.user_id=u.id WHERE r.status NOT IN ('pending')";
$args = [];
if ($tab === 'all') { /* every non-pending rental */ } elseif ($tab === 'returned') { $sql .= " AND r.status IN ('returned','completed')"; } else { $sql .= " AND r.status = ?"; $args[] = $tab; }
if ($cust) { $sql .= " AND r.user_id = ?"; $args[] = $cust; }
if ($q !== '') { $sql .= " AND CONCAT(u.first_name,' ',u.last_name,' ',v.make,' ',v.model,' ',v.plate_number,' ',r.reference_code) LIKE ?"; $args[] = "%$q%"; }
$st = $pdo->prepare($sql . " ORDER BY r.pickup_date DESC"); $st->execute($args); $rows = $st->fetchAll();

$enRoute  = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='en_route'")->fetchColumn();
$retToday = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN ('returned','completed') AND DATE(returned_at)=CURDATE()")->fetchColumn();
$queue = $pdo->query("SELECT r.*, v.make, v.model, u.first_name, u.last_name FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id LEFT JOIN users u ON r.user_id=u.id WHERE r.status='pending' ORDER BY r.pickup_date LIMIT 5")->fetchAll();
$pendingN = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn();
?>
<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start">
  <div><div class="crumb">Rentals console &middot; Live fleet operations</div><h1 class="t">Rental Overview</h1></div>
  <div class="act"><div class="stat"><small>Active en route</small><b><?= $enRoute ?> vehicle(s)</b></div><div class="stat"><small>Returned today</small><b><?= $retToday ?> unit(s)</b></div>
  <a class="btn-d" href="<?= $A ?>requests.php">Pending inflow requests (<?= $pendingN ?>)</a></div>
</div>
<div class="card" style="margin-top:16px">
  <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
    <div class="tabs" style="margin:0"><?php foreach ($tabs as $k => $l): ?><a class="<?= $tab === $k ? 'on' : '' ?>" href="?tab=<?= $k ?><?= $cust ? '&customer=' . $cust : '' ?>"><?= $l ?></a><?php endforeach; ?></div>
    <form method="get"><input type="hidden" name="tab" value="<?= e($tab) ?>"><?php if ($cust): ?><input type="hidden" name="customer" value="<?= $cust ?>"><?php endif; ?>
      <input name="q" value="<?= e($q) ?>" placeholder="Search customer, plate, or ref..." style="padding:8px 12px;border:1px solid var(--line);border-radius:10px"> <button class="btn-b btn-sm">Search</button></form>
  </div>
  <?php if ($cust): ?><p class="sub">Filtered by one customer. <a href="<?= $A ?>rentals.php?tab=all">Clear filter</a></p><?php endif; ?>
  <div class="tbl-wrap"><table class="tbl"><tr><th>Customer</th><th>Vehicle</th><th>Pick-up</th><th>Return</th><th>Days</th><th>Status</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?><tr>
    <td><b><?= e(trim($r['first_name'] . ' ' . $r['last_name']) ?: $r['driver_name']) ?></b><br><small class="sub"><?= e($r['reference_code']) ?></small></td>
    <td><?= e($r['make'] . ' ' . $r['model']) ?><br><small class="sub"><?= e($r['plate_number']) ?></small></td>
    <td><?= date('M j, g:i A', strtotime($r['pickup_date'])) ?></td><td><?= date('M j, g:i A', strtotime($r['return_date'])) ?></td><td><?= (int)$r['days'] ?></td><td><?= status_badge($r['status']) ?></td>
    <td><div class="act"><a class="btn-o btn-sm" href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>">Open Receipt</a>
      <?php if ($r['status'] === 'reserved'): ?><form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="dispatch"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="back" value="admin/rentals.php?tab=reserved"><button class="btn-b btn-sm">Dispatch</button></form><?php endif; ?>
      <?php if ($r['status'] === 'en_route'): ?><a class="btn-d btn-sm" href="<?= $A ?>record_return.php?id=<?= (int)$r['id'] ?>">Record Return</a><?php endif; ?>
      <?php if ($r['status'] === 'returned'): ?><a class="btn-d btn-sm" href="<?= $A ?>damage.php?id=<?= (int)$r['id'] ?>">Settle</a><?php endif; ?></div></td></tr><?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7">No rentals in this view.</td></tr><?php endif; ?></table></div>
</div>
<div class="card"><h2>Pending Rental Inbound Queue <span class="badge b-amber"><?= $pendingN ?> action required</span></h2>
  <div class="tbl-wrap"><table class="tbl"><tr><th>Customer</th><th>Requested unit</th><th>Date range</th><th>Review</th></tr>
  <?php foreach ($queue as $r): ?><tr><td><?= e(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td><td><?= e($r['make'] . ' ' . $r['model']) ?></td><td><?= date('M j', strtotime($r['pickup_date'])) ?> – <?= date('M j', strtotime($r['return_date'])) ?> (<?= (int)$r['days'] ?>d)</td>
  <td><a class="btn-b btn-sm" href="<?= $A ?>requests.php">Review</a></td></tr><?php endforeach; ?>
  <?php if (!$queue): ?><tr><td colspan="4">No pending requests.</td></tr><?php endif; ?></table></div>
  <p style="margin:10px 0 0"><a href="<?= $A ?>requests.php">View all rental requests &rarr;</a></p>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
