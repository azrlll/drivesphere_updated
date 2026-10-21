<?php
$pageTitle = 'Rental Receipt'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../includes/customer_booking.php';
$b = load_my_booking($pdo, $_GET['ref'] ?? '');
$titles = ['pending' => 'Rental Request (Waiting for Confirmation)', 'reserved' => 'Rental Reservation Receipt', 'en_route' => 'Rental Receipt — Vehicle On the Road',
           'returned' => 'Rental Receipt — Returned', 'completed' => 'Rental Receipt — Completed', 'rejected' => 'Rental Request Declined', 'cancelled' => 'Booking Cancelled'];
?>
<div class="crumb noprint"><a href="<?= $C ?>history.php">&larr; Back to Rental History</a></div>
<div class="card" style="max-width:820px;margin:0 auto">
  <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap" class="noprint">
    <span></span>
    <div class="act"><button class="btn-o btn-sm" data-print><i class="fa-solid fa-print"></i> Print Receipt</button></div>
  </div>
  <div class="badge b-blue">DRIVESPHERE OFFICIAL VOUCHER</div> <?= status_badge($b['status']) ?>
  <h1 class="t" style="margin-top:8px"><?= e($titles[$b['status']] ?? 'Rental Receipt') ?></h1>
  <div class="kv">
    <div><small>Vehicle</small><?= e($b['make'] . ' ' . $b['model']) ?></div>
    <div><small>Customer</small><?= e($b['driver_name']) ?></div>
    <div><small>Date requested</small><?= date('M j, Y', strtotime($b['created_at'])) ?></div>
    <div><small>Rental ID</small><b style="color:var(--blue)"><?= e($b['reference_code']) ?></b></div>
  </div>
  <div style="height:200px;border-radius:12px;background:url('<?= e($b['image_url']) ?>') center/cover;margin-bottom:16px"></div>
  <div class="kv">
    <div><small>Price per day</small><?= money($b['daily_rate']) ?></div>
    <div><small>Rental duration</small><?= (int)$b['days'] ?> day(s)</div>
    <div><small>Pick-up</small><?= date('M j, Y g:i A', strtotime($b['pickup_date'])) ?><br><?= e($b['pickup_location']) ?></div>
    <div><small>Return</small><?= date('M j, Y g:i A', strtotime($b['return_date'])) ?><br><?= e($b['return_location']) ?></div>
  </div>
  <?php if ($b['status'] === 'reserved'): ?><div class="note"><b>Deadline of pick-up:</b> <?= date('M j, Y', strtotime($b['pickup_date'])) ?> + 2 hours. Unclaimed vehicles are re-assigned to the standby fleet.</div><?php endif; ?>
  <?php if ($b['status'] === 'rejected'): ?><div class="flash flash-error">Reason: <?= e($b['rejection_reason'] ?: 'No reason provided.') ?></div><?php endif; ?>
  <h3>Fiscal breakdown</h3>
  <div class="line"><span>Base rental (<?= (int)$b['days'] ?> day(s))</span><b><?= money($b['base_amount']) ?></b></div>
  <div class="line"><span>Mandatory government VAT (12%)</span><b><?= money($b['vat_amount']) ?></b></div>
  <div class="total"><span>TOTAL RESERVATION</span><b><?= money($b['total_price']) ?></b></div>
  <div class="line"><span>Security deposit due on handover (refundable)</span><b><?= money($b['deposit_amount']) ?></b></div>
  <div class="act noprint" style="margin-top:16px">
    <?php if (in_array($b['status'], ['pending', 'reserved'])): ?>
      <form method="post" action="<?= BASE_URL ?>actions/cancel_request.php" data-confirm="Cancel this booking?"><?= csrf_field() ?><input type="hidden" name="ref" value="<?= e($b['reference_code']) ?>"><button class="btn-r">Cancel booking</button></form>
    <?php endif; ?>
    <?php if (in_array($b['status'], ['returned', 'completed'])): ?>
      <a class="btn-d" href="<?= $C ?>return_record.php?ref=<?= urlencode($b['reference_code']) ?>">View Return Record</a>
      <a class="btn-o" href="<?= $C ?>charges.php?ref=<?= urlencode($b['reference_code']) ?>">Final Charges</a>
    <?php endif; ?>
    <a class="btn-o" href="<?= $C ?>history.php">All rentals</a>
  </div>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
