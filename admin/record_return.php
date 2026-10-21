<?php
$pageTitle = 'Record Vehicle Return'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_booking.php';
$b = load_booking($pdo, $_GET['id'] ?? $_POST['id'] ?? 0);
if ($b['status'] !== 'en_route') {
    flash_set('error', 'Only vehicles that are En Route can be checked in.');
    redirect('admin/receipt.php?id=' . (int)$b['id']);
}
$fuels = ['Full (8/8)', '3/4 tank', '1/2 tank', '1/4 tank', 'Empty'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/record_return.php?id=' . (int)$b['id']);
    $odoIn = (int)($_POST['odometer_in'] ?? 0);
    $fuel  = in_array($_POST['fuel_level'] ?? '', $fuels) ? $_POST['fuel_level'] : $fuels[0];
    $when  = date('Y-m-d H:i:s', strtotime($_POST['returned_at'] ?? 'now'));
    $notes = trim($_POST['notes'] ?? '');
    if ($odoIn < (int)$b['odometer_out']) {
        flash_set('error', 'Odometer reading cannot be lower than the pick-up reading (' . number_format($b['odometer_out']) . ' km).');
        redirect('admin/record_return.php?id=' . $b['id']);
    }
    $pdo->prepare("UPDATE reservations SET status='returned', returned_at=?, odometer_in=?, fuel_level=?, damage_description=? WHERE id=?")->execute([$when, $odoIn, $fuel, $notes !== '' ? $notes : null, $b['id']]);
    $pdo->prepare("UPDATE vehicles SET status='available', odometer=? WHERE id=?")->execute([$odoIn, $b['vehicle_id']]);
    if ($b['user_id']) add_notification($pdo, $b['user_id'], 'customer', 'security', 'Vehicle return recorded', 'Your ' . $b['make'] . ' ' . $b['model'] . ' was checked in. Final charges will follow.', 'customer/return_record.php?ref=' . $b['reference_code']);

    if (($_POST['next'] ?? '') === 'no_damage') {   // close the rental right away
        $pdo->prepare("UPDATE reservations SET status='completed', final_total=total_price, damage_charge=0, discount=0, settled_at=NOW() WHERE id=?")->execute([$b['id']]);
        if ($b['user_id']) add_notification($pdo, $b['user_id'], 'customer', 'payment', 'Rental settled — ' . $b['reference_code'], 'No damage found. Your security deposit is released.', 'customer/charges.php?ref=' . $b['reference_code']);
        flash_set('success', 'Return confirmed without damage. Rental completed.');
        redirect('admin/rentals.php?tab=returned');
    }
    redirect('admin/damage.php?id=' . $b['id']);
}
?>
<div class="crumb"><a href="<?= $A ?>rentals.php">Rentals</a> &rsaquo; Check-in &amp; return audit</div>
<form method="post" class="split"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
  <div>
    <div class="card"><span class="badge b-blue">MERCHANT CHECK-IN TERMINAL</span><h1 class="t" style="margin-top:8px">Record Vehicle Return</h1><p class="sub">Verify return odometer, fuel level and vehicle condition before finalizing.</p></div>
    <div class="card">
      <div class="kv"><div><small>Rent ID</small><?= e($b['reference_code']) ?></div><div><small>Vehicle</small><?= e($b['make'] . ' ' . $b['model']) ?></div><div><small>Customer</small><?= e($b['cust_name']) ?></div><div><small>Plate</small><?= e($b['plate_number']) ?></div></div>
      <div class="row2">
        <div class="kv" style="display:block"><small>Origin pick-up record</small><b><?= date('M j, Y g:i A', strtotime($b['pickup_date'])) ?></b><br>Odometer out: <b><?= number_format($b['odometer_out']) ?> km</b></div>
        <div>
          <div class="field"><label>Return date &amp; time</label><input type="datetime-local" name="returned_at" value="<?= date('Y-m-d\TH:i') ?>" required></div>
          <div class="field"><label>Odometer reading (final)</label><input type="number" name="odometer_in" min="<?= (int)$b['odometer_out'] ?>" value="<?= (int)$b['odometer_out'] ?>" required></div>
          <div class="field"><label>Fuel level</label><select name="fuel_level"><?php foreach ($fuels as $f): ?><option><?= $f ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
      <div class="field"><label>Visual inspection notes (optional)</label><textarea name="notes" rows="3" placeholder="e.g. Minor clearcoat abrasion on driver-side rear quarter panel."></textarea></div>
      <div class="act"><button class="btn-d" name="next" value="damage">Proceed to Damage Overview &amp; Final Charges &rarr;</button><button class="btn-o" name="next" value="no_damage" data-noconfirm>Confirm Return Without Damage</button><a class="btn-o" href="<?= $A ?>rentals.php">Cancel</a></div>
    </div>
  </div>
  <div>
    <div class="card"><h3>Billing ledger preview</h3>
      <div class="line"><span>Standard rental (<?= (int)$b['days'] ?> day(s))</span><b><?= money($b['base_amount']) ?></b></div>
      <div class="line"><span>VAT (12%)</span><b><?= money($b['vat_amount']) ?></b></div>
      <div class="line"><span>Security deposit pre-auth</span><b><?= money($b['deposit_amount']) ?></b></div>
      <div class="note">Final charges for damage are computed in the next step.</div></div>
  </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
