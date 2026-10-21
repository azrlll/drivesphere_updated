<?php
$pageTitle = 'Rental History'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
$st = $pdo->prepare("SELECT r.*, v.make, v.model, v.plate_number, v.image_url FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id
                     WHERE r.user_id = ? ORDER BY r.created_at DESC, r.id DESC");
$st->execute([$me['id']]);
$rows = $st->fetchAll();
?>
<h1 class="t">Rental History</h1>
<p class="sub">Track your reservations, vehicles on the road, and completed rentals. <b><?= count($rows) ?></b> record(s).</p>
<?php if (!$rows): ?><div class="card">You have no rentals yet. <a href="<?= $C ?>browse.php">Browse vehicles</a> to make your first request.</div><?php endif; ?>
<?php foreach ($rows as $r):
  $total = $r['final_total'] ?? $r['total_price']; ?>
  <div class="card" style="display:grid;grid-template-columns:140px 1fr auto;gap:18px;align-items:center">
    <div style="height:90px;border-radius:10px;background:url('<?= e($r['image_url']) ?>') center/cover"></div>
    <div>
      <?= status_badge($r['status']) ?>
      <h3 style="margin:6px 0 2px"><?= e($r['make'] . ' ' . $r['model']) ?></h3>
      <div class="sub" style="margin:0">Plate <?= e($r['plate_number']) ?> &middot; <?= date('M j, Y', strtotime($r['pickup_date'])) ?> – <?= date('M j, Y', strtotime($r['return_date'])) ?> &middot; <?= e($r['pickup_location']) ?></div>
      <div class="sub" style="margin:0;font-size:12px">Booking ID <?= e($r['reference_code']) ?></div>
    </div>
    <div style="text-align:right">
      <small class="sub">Total</small><div style="font-size:20px;font-weight:800"><?= money($total) ?></div>
      <div class="act" style="justify-content:flex-end;margin-top:8px">
        <a class="btn-d btn-sm" href="<?= $C ?>receipt.php?ref=<?= urlencode($r['reference_code']) ?>">View Receipt</a>
        <?php if (in_array($r['status'], ['returned', 'completed'])): ?>
          <a class="btn-o btn-sm" href="<?= $C ?>return_record.php?ref=<?= urlencode($r['reference_code']) ?>">Return Record</a>
          <a class="btn-o btn-sm" href="<?= $C ?>charges.php?ref=<?= urlencode($r['reference_code']) ?>">Charges</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
