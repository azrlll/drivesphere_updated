<?php
$pageTitle = 'Damage Overview & Final Charges'; $nav = 'rentals';
require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_booking.php';
$b = load_booking($pdo, $_GET['id'] ?? $_POST['id'] ?? 0);
if (!in_array($b['status'], ['returned', 'completed'])) { flash_set('error', 'Record the vehicle return first.'); redirect('admin/receipt.php?id=' . (int)$b['id']); }
$settled = $b['status'] === 'completed';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$settled) {
    csrf_check('admin/damage.php?id=' . (int)$b['id']);
    $desc = trim($_POST['damage_description'] ?? '');
    $sev  = in_array($_POST['damage_severity'] ?? '', ['Minor', 'Moderate', 'Major']) ? $_POST['damage_severity'] : null;
    $dmg  = max(0, (float)($_POST['damage_charge'] ?? 0));
    $disc = max(0, (float)($_POST['discount'] ?? 0));
    $final = max(0, $b['total_price'] + $dmg - $disc);
    $pdo->prepare("UPDATE reservations SET damage_description=?, damage_severity=?, damage_charge=?, discount=? WHERE id=?")->execute([$desc !== '' ? $desc : null, $sev, $dmg, $disc, $b['id']]);
    $op = $_POST['op'] ?? 'save';
    if ($op === 'settle') {
        $pdo->prepare("UPDATE reservations SET status='completed', final_total=?, settled_at=NOW() WHERE id=?")->execute([$final, $b['id']]);
        if ($b['user_id']) add_notification($pdo, $b['user_id'], 'customer', 'payment', 'Final charges settled — ' . money($final), 'Rental ' . $b['reference_code'] . ' is complete. Your security deposit is released.', 'customer/charges.php?ref=' . $b['reference_code']);
        flash_set('success', 'Settlement confirmed. Rental completed.');
        redirect('admin/rentals.php?tab=returned');
    }
    if ($op === 'send' && $b['user_id']) {
        add_notification($pdo, $b['user_id'], 'customer', 'payment', 'Charges breakdown available', 'See the damage overview and final charges for ' . $b['reference_code'] . '.', 'customer/charges.php?ref=' . $b['reference_code']);
        flash_set('success', 'Charges sent to the customer.');
    } else { flash_set('success', 'Damage details saved.'); }
    redirect('admin/damage.php?id=' . $b['id']);
}
$final = $settled ? $b['final_total'] : ($b['total_price'] + $b['damage_charge'] - $b['discount']);
$dis = $settled ? 'disabled' : '';
?>
<div class="crumb"><a href="<?= $A ?>rentals.php">Rentals</a> &rsaquo; Check-in &rsaquo; Damage audit &amp; settlement</div>
<form method="post" class="split"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
  <div>
    <div class="card"><h1 class="t">Damage Overview &amp; Final Charges Breakdown</h1>
      <p class="sub">Contract <?= e($b['reference_code']) ?> &middot; <?= e($b['make'] . ' ' . $b['model']) ?> &middot; Renter: <?= e($b['cust_name']) ?> <?= $settled ? '<span class="badge b-green">Settled</span>' : '' ?></p></div>
    <div class="card"><h3>Damage overview</h3>
      <div class="row2">
        <div class="field"><label>Severity</label><select name="damage_severity" <?= $dis ?>><option value="">None</option><?php foreach (['Minor', 'Moderate', 'Major'] as $s): ?><option <?= $b['damage_severity'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Repair tariff (₱)</label><input type="number" step="0.01" min="0" name="damage_charge" value="<?= e($b['damage_charge']) ?>" <?= $dis ?>></div>
      </div>
      <div class="field"><label>Detailed description &amp; technical notes</label><textarea name="damage_description" rows="4" <?= $dis ?>><?= e($b['damage_description']) ?></textarea></div>
      <div class="kv"><div><small>Odometer turn-in</small><?= number_format($b['odometer_in']) ?> km</div><div><small>Fuel level returned</small><?= e($b['fuel_level']) ?></div><div><small>Returned</small><?= date('M j, g:i A', strtotime($b['returned_at'])) ?></div></div>
    </div>
  </div>
  <div class="card"><h3>Fiscal breakdown</h3>
    <div class="line"><span>Price per day</span><b><?= money($b['daily_rate']) ?></b></div>
    <div class="line"><span>Rented days</span><b><?= (int)$b['days'] ?></b></div>
    <div class="line"><span>Base rental + VAT</span><b><?= money($b['total_price']) ?></b></div>
    <div class="line"><span>Damage charges</span><b><?= money($b['damage_charge']) ?></b></div>
    <div class="field" style="margin-top:12px"><label>Discount (₱)</label><input type="number" step="0.01" min="0" name="discount" value="<?= e($b['discount']) ?>" <?= $dis ?>></div>
    <div class="total"><span>OVERALL TOTAL COST<br><small>All VAT inclusive</small></span><b><?= money($final) ?></b></div>
    <p class="sub">Security deposit <?= money($b['deposit_amount']) ?> — ready for release after settlement.</p>
    <?php if (!$settled): ?>
      <p class="sub" style="font-size:12px">Press "Save" to refresh the total after editing the amounts.</p>
      <button class="btn-o btn-full" name="op" value="save" style="margin-bottom:8px">Save damage details</button>
      <button class="btn-d btn-full" name="op" value="settle" style="margin-bottom:8px"><i class="fa-solid fa-circle-check"></i> Confirm Settlement</button>
      <button class="btn-b btn-full" name="op" value="send"><i class="fa-solid fa-paper-plane"></i> Send To User</button>
    <?php else: ?><a class="btn-o btn-full" href="<?= $A ?>rentals.php?tab=returned">Back to rentals</a><?php endif; ?>
  </div>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
