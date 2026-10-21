<?php
$pageTitle = 'Final Charges'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../includes/customer_booking.php';
$b = load_my_booking($pdo, $_GET['ref'] ?? '');
if (!in_array($b['status'], ['returned', 'completed'])) { flash_set('error', 'Final charges appear after the vehicle is returned.'); redirect('customer/receipt.php?ref=' . urlencode($b['reference_code'])); }
$settled = $b['status'] === 'completed';
$final = $settled ? $b['final_total'] : ($b['total_price'] + $b['damage_charge'] - $b['discount']);
?>
<div class="crumb"><a href="<?= $C ?>history.php">&larr; Back to Rental History</a></div>
<div class="card" style="max-width:820px;margin:0 auto">
  <h1 class="t">Damage Overview &amp; Final Charges</h1>
  <p class="sub">Rental <?= e($b['reference_code']) ?> &middot; <?= e($b['make'] . ' ' . $b['model']) ?> &middot; <?= $settled ? '<span class="badge b-green">Settled</span>' : '<span class="badge b-amber">Waiting for admin settlement</span>' ?></p>
  <h3>Damage overview</h3>
  <?php if ($b['damage_charge'] > 0 || $b['damage_description']): ?>
    <div class="kv"><div><small>Severity</small><?= e($b['damage_severity'] ?: '—') ?></div><div><small>Repair charge</small><?= money($b['damage_charge']) ?></div></div>
    <p><?= e($b['damage_description']) ?></p>
  <?php else: ?><p class="sub">No damage recorded.</p><?php endif; ?>
  <h3>Fiscal breakdown</h3>
  <div class="line"><span>Price per day</span><b><?= money($b['daily_rate']) ?></b></div>
  <div class="line"><span>Rented days</span><b><?= (int)$b['days'] ?></b></div>
  <div class="line"><span>Base rental</span><b><?= money($b['base_amount']) ?></b></div>
  <div class="line"><span>VAT (12%)</span><b><?= money($b['vat_amount']) ?></b></div>
  <div class="line"><span>Damage charges</span><b><?= money($b['damage_charge']) ?></b></div>
  <div class="line"><span>Discount</span><b>- <?= money($b['discount']) ?></b></div>
  <div class="total"><span>OVERALL TOTAL COST</span><b><?= money($final) ?></b></div>
  <p class="sub" style="margin-top:10px">Security deposit of <?= money($b['deposit_amount']) ?> is <?= $settled ? 'released after inspection' : 'held until settlement' ?>.</p>
  <div class="act noprint"><button class="btn-o" data-print>Print</button><a class="btn-o" href="<?= $C ?>history.php">Back to history</a></div>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
