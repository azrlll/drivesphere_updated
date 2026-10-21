<?php
$pageTitle = 'Return Record'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../includes/customer_booking.php';
$b = load_my_booking($pdo, $_GET['ref'] ?? '');
if (!in_array($b['status'], ['returned', 'completed'])) { flash_set('error', 'This vehicle has not been returned yet.'); redirect('customer/receipt.php?ref=' . urlencode($b['reference_code'])); }
$km = ($b['odometer_in'] && $b['odometer_out']) ? $b['odometer_in'] - $b['odometer_out'] : null;
$late = max(0, (int)ceil((strtotime($b['returned_at']) - strtotime($b['return_date'])) / 86400));
?>
<div class="crumb"><a href="<?= $C ?>history.php">&larr; Back to Rental History</a></div>
<div class="card" style="max-width:820px;margin:0 auto">
  <div class="badge b-green">OFFICIAL CHECK-IN RECORD</div>
  <h1 class="t" style="margin-top:8px">Vehicle Return Record</h1>
  <div class="kv">
    <div><small>Vehicle</small><?= e($b['make'] . ' ' . $b['model']) ?></div>
    <div><small>Customer</small><?= e($b['driver_name']) ?></div>
    <div><small>Return status</small><?= status_badge($b['status']) ?></div>
    <div><small>Rental ID</small><?= e($b['reference_code']) ?></div>
  </div>
  <div class="grid4">
    <div class="stat"><small>Return date</small><b style="font-size:16px"><?= date('M j, Y g:i A', strtotime($b['returned_at'])) ?></b></div>
    <div class="stat"><small>Pick-up date</small><b style="font-size:16px"><?= date('M j, Y g:i A', strtotime($b['pickup_date'])) ?></b></div>
    <div class="stat"><small>Odometer in</small><b style="font-size:16px"><?= $b['odometer_in'] ? number_format($b['odometer_in']) . ' km' : '—' ?></b><span class="sub"><?= $km !== null ? 'Traveled ' . number_format($km) . ' km' : '' ?></span></div>
    <div class="stat"><small>Fuel level in</small><b style="font-size:16px"><?= e($b['fuel_level'] ?: '—') ?></b></div>
  </div>
  <div class="card dark" style="margin-top:18px">
    <h3>Vehicle inspection <?= $b['damage_charge'] > 0 ? '&mdash; incident logged' : '&mdash; no damage' ?></h3>
    <p style="color:#c9d6ea"><?= e($b['damage_description'] ?: 'No damage reported at check-in.') ?></p>
    <a class="btn-b" href="<?= $C ?>charges.php?ref=<?= urlencode($b['reference_code']) ?>">Proceed to Damage Overview &amp; Final Charges &rarr;</a>
  </div>
  <p class="sub"><?= $late > 0 ? 'Returned ' . $late . ' day(s) after the scheduled return.' : 'Returned on time.' ?></p>
  <div class="act noprint"><button class="btn-o" data-print>Print</button><a class="btn-o" href="<?= $C ?>history.php">Back</a></div>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
