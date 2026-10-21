<?php
$pageTitle = 'Dashboard'; $nav = 'dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$fleet   = (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$rented  = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='rented'")->fetchColumn();
$maint   = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='maintenance'")->fetchColumn();
$avail   = $fleet - $rented - $maint;
$pending = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn();
$overdue = $pdo->query("SELECT r.*, v.make, v.model FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id WHERE r.status='en_route' AND r.return_date < NOW()")->fetchAll();
$toSettle = $pdo->query("SELECT r.*, v.make, v.model FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id WHERE r.status='returned'")->fetchAll();
$top = $pdo->query("SELECT v.*, COUNT(r.id) AS trips FROM vehicles v LEFT JOIN reservations r ON r.vehicle_id=v.id AND r.status IN ('reserved','en_route','returned','completed') GROUP BY v.id ORDER BY trips DESC, v.daily_rate DESC LIMIT 5")->fetchAll();
$catalog = $pdo->query("SELECT v.*, c.name AS cat FROM vehicles v JOIN categories c ON v.category_id=c.id ORDER BY v.id DESC LIMIT 5")->fetchAll();
$notes = $pdo->query("SELECT * FROM notifications WHERE audience='admin' ORDER BY created_at DESC, id DESC LIMIT 5")->fetchAll();
$util = $fleet ? round($rented / $fleet * 100) : 0;
?>
<h1 class="t">Fleet Operations Dashboard</h1>
<p class="sub"><?= e($merchant['merchant_name']) ?> &middot; live overview of vehicles, requests and returns.</p>
<div class="grid4" style="margin-bottom:20px">
  <div class="stat"><small>Total fleet</small><b><?= $fleet ?></b></div>
  <div class="stat"><small>Rented / en route</small><b><?= $rented ?></b></div>
  <div class="stat"><small>Utilization</small><b><?= $util ?>%</b></div>
  <div class="stat"><small>Pending requests</small><b><?= $pending ?></b></div>
</div>

<div class="split">
  <div>
    <div class="card"><h2>Needs Attention</h2>
      <?php if (!$pending && !$overdue && !$toSettle && !$maint): ?><p class="sub">Nothing needs attention right now.</p><?php endif; ?>
      <?php if ($pending): ?><div class="line"><div><b><?= $pending ?> rental request(s) awaiting approval</b><br><span>Verify license details and approve or decline.</span></div><a class="btn-d btn-sm" href="<?= $A ?>requests.php">Review requests</a></div><?php endif; ?>
      <?php foreach ($overdue as $o): ?><div class="line"><div><b>Overdue return — <?= e($o['make'] . ' ' . $o['model']) ?></b><br><span><?= e($o['reference_code']) ?> was due <?= date('M j, g:i A', strtotime($o['return_date'])) ?></span></div><a class="btn-d btn-sm" href="<?= $A ?>record_return.php?id=<?= (int)$o['id'] ?>">Record return</a></div><?php endforeach; ?>
      <?php foreach ($toSettle as $o): ?><div class="line"><div><b>Final charges to settle — <?= e($o['make'] . ' ' . $o['model']) ?></b><br><span><?= e($o['reference_code']) ?> has been returned.</span></div><a class="btn-d btn-sm" href="<?= $A ?>damage.php?id=<?= (int)$o['id'] ?>">Settle charges</a></div><?php endforeach; ?>
      <?php if ($maint): ?><div class="line"><div><b><?= $maint ?> vehicle(s) in maintenance</b><br><span>Not offered to customers until marked available.</span></div><a class="btn-o btn-sm" href="<?= $A ?>manage.php?status=maintenance">View vehicles</a></div><?php endif; ?>
    </div>
    <div class="card"><div style="display:flex;justify-content:space-between"><h2>Manage Vehicles Catalog</h2><a class="btn-d btn-sm" href="<?= $A ?>vehicle_form.php">+ Add Vehicle</a></div>
      <div class="tbl-wrap"><table class="tbl"><tr><th>Vehicle</th><th>Category</th><th>Daily rate</th><th>Status</th><th></th></tr>
      <?php foreach ($catalog as $v): ?><tr><td><b><?= e($v['make'] . ' ' . $v['model']) ?></b><br><small class="sub"><?= e($v['plate_number']) ?></small></td><td><?= e($v['cat']) ?></td><td><?= money($v['daily_rate']) ?></td>
        <td><span class="badge <?= $v['status'] === 'available' ? 'b-green' : ($v['status'] === 'rented' ? 'b-blue' : 'b-amber') ?>"><?= e(ucfirst($v['status'])) ?></span></td>
        <td><a class="btn-o btn-sm" href="<?= $A ?>vehicle_form.php?id=<?= (int)$v['id'] ?>">Edit</a></td></tr><?php endforeach; ?></table></div>
      <p style="margin:12px 0 0"><a href="<?= $A ?>manage.php">View all vehicles &rarr;</a></p>
    </div>
  </div>
  <div>
    <div class="card"><h3>Fleet distribution</h3>
      <div class="line"><span>Available</span><b><?= $avail ?></b></div><div class="line"><span>Rented / en route</span><b><?= $rented ?></b></div><div class="line"><span>Maintenance</span><b><?= $maint ?></b></div></div>
    <div class="card"><h3>Top renting vehicles</h3>
      <?php foreach ($top as $v): ?><div class="line"><span><?= e($v['make'] . ' ' . $v['model']) ?></span><b><?= (int)$v['trips'] ?> trips</b></div><?php endforeach; ?></div>
    <div class="card"><div style="display:flex;justify-content:space-between"><h3>Latest alerts</h3><a href="<?= $A ?>notifications.php">All</a></div>
      <?php foreach ($notes as $n): ?><div class="line"><div><b><?= e($n['title']) ?></b><br><span><?= e($n['message']) ?></span></div></div><?php endforeach; ?>
      <?php if (!$notes): ?><p class="sub">No alerts.</p><?php endif; ?></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
