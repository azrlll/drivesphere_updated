<?php
$pageTitle = 'Rental Receipt'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_booking.php';
$b = load_booking($pdo, $_GET['id'] ?? 0);
$st = $b['status'];
$heading = ['pending' => 'Receipt for a Pending Request', 'reserved' => 'Receipt for a Reserved Vehicle', 'en_route' => 'Receipt for an Approved Vehicle',
            'returned' => 'Receipt for a Returned Vehicle', 'completed' => 'Receipt for a Completed Rental', 'rejected' => 'Rejected Request', 'cancelled' => 'Cancelled Booking'][$st];
?>
<div class="crumb noprint"><a href="<?= $A ?>rentals.php">Rentals</a> &rsaquo; <?= e($b['reference_code']) ?></div>
<div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap" class="noprint"><div><span class="badge b-blue">FLEET BOOKING RECORD</span><h1 class="t" style="margin-top:6px"><?= e($heading) ?></h1></div>
  <div class="act"><button class="btn-o" data-print><i class="fa-solid fa-print"></i> Print Receipt</button></div></div>
<div class="split">
  <div class="card">
    <div class="kv"><div><small>Status</small><?= status_badge($st) ?></div><div><small>Rental ID</small><b><?= e($b['reference_code']) ?></b></div><div><small>Customer</small><?= e($b['cust_name']) ?></div><div><small>Date issued</small><?= date('M j, Y g:i A', strtotime($b['created_at'])) ?></div></div>
    <div class="grid2" style="margin-bottom:16px"><div style="height:170px;border-radius:12px;background:url('<?= e($b['image_url']) ?>') center/cover"></div>
      <div><h3 style="margin:0"><?= e($b['make'] . ' ' . $b['model']) ?></h3><p class="sub">Plate <?= e($b['plate_number']) ?><br><?= e($b['transmission']) ?> &middot; <?= e($b['fuel_type']) ?> &middot; <?= (int)$b['seats'] ?> seats</p><b><?= money($b['daily_rate']) ?></b> / day base tariff</div></div>
    <div class="kv"><div><small>Pick-up</small><?= date('M j, Y g:i A', strtotime($b['pickup_date'])) ?><br><?= e($b['pickup_location']) ?></div><div><small>Return</small><?= date('M j, Y g:i A', strtotime($b['return_date'])) ?><br><?= e($b['return_location']) ?></div><div><small>Duration</small><?= (int)$b['days'] ?> day(s)</div></div>
    <h3>Itemized settlement details</h3>
    <div class="line"><span>Vehicle base rental (<?= (int)$b['days'] ?> day(s))</span><b><?= money($b['base_amount']) ?></b></div>
    <div class="line"><span>VAT (12%)</span><b><?= money($b['vat_amount']) ?></b></div>
    <?php if ($b['damage_charge'] > 0): ?><div class="line"><span>Damage charges</span><b><?= money($b['damage_charge']) ?></b></div><?php endif; ?>
    <?php if ($b['discount'] > 0): ?><div class="line"><span>Discount</span><b>- <?= money($b['discount']) ?></b></div><?php endif; ?>
    <div class="total"><span><?= $st === 'completed' ? 'TOTAL SETTLED AMOUNT' : 'TOTAL PRE-AUTHORIZATION' ?></span><b><?= money($b['final_total'] ?? $b['total_price']) ?></b></div>
    <div class="line"><span>Refundable security deposit</span><b><?= money($b['deposit_amount']) ?> hold</b></div>
    <?php if ($st === 'rejected'): ?><div class="note">Rejected: <?= e($b['rejection_reason']) ?></div><?php endif; ?>
    <div class="act noprint" style="margin-top:16px">
      <?php if ($b['user_id']): ?><form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="send_receipt"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="back" value="admin/receipt.php?id=<?= (int)$b['id'] ?>"><button class="btn-b">Send To User</button></form><?php endif; ?>
      <?php if ($st === 'pending'): ?><a class="btn-d" href="<?= $A ?>requests.php">Review request</a><?php endif; ?>
      <?php if ($st === 'reserved'): ?><form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="dispatch"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><input type="hidden" name="back" value="admin/receipt.php?id=<?= (int)$b['id'] ?>"><button class="btn-d">Dispatch vehicle</button></form><?php endif; ?>
      <?php if ($st === 'en_route'): ?><a class="btn-d" href="<?= $A ?>record_return.php?id=<?= (int)$b['id'] ?>">Record Return</a><?php endif; ?>
      <?php if (in_array($st, ['returned', 'completed'])): ?><a class="btn-d" href="<?= $A ?>damage.php?id=<?= (int)$b['id'] ?>">Damage &amp; Final Charges</a><?php endif; ?>
      <a class="btn-o" href="<?= $A ?>rentals.php">Back to rentals</a>
    </div>
  </div>
  <div>
    <div class="card"><h3>Customer</h3><p style="margin:0"><b><?= e($b['cust_name']) ?></b><br><?= e($b['cust_email'] ?? '') ?><br><?= e($b['driver_phone'] ?: $b['cust_phone']) ?><br>License: <?= e($b['driver_license'] ?: '—') ?></p></div>
    <div class="card"><h3>Vehicle checklist</h3><div class="line"><span>Odometer out</span><b><?= $b['odometer_out'] ? number_format($b['odometer_out']) . ' km' : '—' ?></b></div><div class="line"><span>Odometer in</span><b><?= $b['odometer_in'] ? number_format($b['odometer_in']) . ' km' : '—' ?></b></div><div class="line"><span>Fuel level in</span><b><?= e($b['fuel_level'] ?: '—') ?></b></div></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
