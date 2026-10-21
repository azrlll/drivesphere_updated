<?php
$pageTitle = 'Merchant Overview'; $nav = 'merchant';
require_once __DIR__ . '/../includes/admin_header.php';

$active = "('reserved','en_route','returned','completed')";
$totalRev = (float)$pdo->query("SELECT COALESCE(SUM(COALESCE(final_total, total_price)),0) FROM reservations WHERE status IN $active")->fetchColumn();
$earned   = (float)$pdo->query("SELECT COALESCE(SUM(final_total),0) FROM reservations WHERE status='completed'")->fetchColumn();
$events   = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN $active")->fetchColumn();
$avgDays  = (float)$pdo->query("SELECT COALESCE(AVG(days),0) FROM reservations WHERE status IN $active")->fetchColumn();

// monthly revenue for the chosen year
$year = (int)($_GET['year'] ?? date('Y'));
$st = $pdo->prepare("SELECT MONTH(pickup_date) m, SUM(COALESCE(final_total,total_price)) s FROM reservations WHERE status IN $active AND YEAR(pickup_date)=? GROUP BY m");
$st->execute([$year]);
$byMonth = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$max = max(1, $byMonth ? max($byMonth) : 1);
$years = $pdo->query("SELECT DISTINCT YEAR(pickup_date) y FROM reservations ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN) ?: [date('Y')];

$recent = $pdo->query("SELECT r.*, v.make, v.model, u.first_name, u.last_name FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id LEFT JOIN users u ON r.user_id=u.id WHERE r.status IN $active ORDER BY r.created_at DESC, r.id DESC LIMIT 8")->fetchAll();
?>
<span class="badge b-blue">MERCHANT HUB &middot; <?= e($merchant['merchant_name']) ?></span>
<h1 class="t" style="margin-top:8px">Merchant Operations Overview &amp; Financials</h1>
<p class="sub">Company revenue, booking volume and rental performance. <a href="<?= $A ?>settings.php">Merchant profile &amp; settings &rarr;</a></p>
<div class="grid4" style="margin-bottom:18px">
  <div class="stat"><small>Total revenue (booked)</small><b><?= money($totalRev) ?></b></div>
  <div class="stat"><small>Earned income (settled)</small><b><?= money($earned) ?></b></div>
  <div class="stat"><small>Rental events</small><b><?= $events ?> bookings</b></div>
  <div class="stat"><small>Avg. duration</small><b><?= number_format($avgDays, 1) ?> days</b></div>
</div>
<div class="card"><div style="display:flex;justify-content:space-between;align-items:center"><div><h2 style="margin:0">Revenue Analytics</h2><span class="sub">Monthly gross booking volume (PHP)</span></div>
  <form method="get"><select name="year" onchange="this.form.submit()" style="padding:7px 10px;border-radius:8px;border:1px solid var(--line)"><?php foreach ($years as $y): ?><option <?= $y == $year ? 'selected' : '' ?>><?= (int)$y ?></option><?php endforeach; ?></select></form></div>
  <div class="chart" style="margin-bottom:26px"><?php for ($m = 1; $m <= 12; $m++): $val = $byMonth[$m] ?? 0; ?>
    <div style="height:<?= max(2, round($val / $max * 100)) ?>%" title="<?= date('F', mktime(0, 0, 0, $m, 1)) . ': ' . money($val) ?>"><span><?= date('M', mktime(0, 0, 0, $m, 1)) ?></span></div><?php endfor; ?></div>
</div>
<div class="card"><h2>Recent Rental</h2>
  <div class="tbl-wrap"><table class="tbl"><tr><th>Rental ID</th><th>Vehicle</th><th>Return date</th><th>Customer</th><th>Status</th><th>Amount</th><th></th></tr>
  <?php foreach ($recent as $r): ?><tr><td><a href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>"><b>#<?= e($r['reference_code']) ?></b></a></td><td><?= e($r['make'] . ' ' . $r['model']) ?></td><td><?= date('M j, Y', strtotime($r['return_date'])) ?></td>
    <td><?= e(trim($r['first_name'] . ' ' . $r['last_name'])) ?></td><td><?= status_badge($r['status']) ?></td><td><?= money($r['final_total'] ?? $r['total_price']) ?></td><td><a class="btn-o btn-sm" href="<?= $A ?>receipt.php?id=<?= (int)$r['id'] ?>">View Receipt</a></td></tr><?php endforeach; ?>
  <?php if (!$recent): ?><tr><td colspan="7">No rentals yet.</td></tr><?php endif; ?></table></div>
  <p style="margin:10px 0 0"><a href="<?= $A ?>rentals.php?tab=all">View all rentals &rarr;</a></p></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
