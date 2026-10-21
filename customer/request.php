<?php
$pageTitle = 'Price Overview'; $nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$st = $pdo->prepare("SELECT v.*, c.name AS category_name FROM vehicles v JOIN categories c ON v.category_id = c.id WHERE v.id = ?");
$st->execute([(int)($_GET['vehicle_id'] ?? 0)]);
$v = $st->fetch();
if (!$v) { flash_set('error', 'Please choose a vehicle first.'); redirect('customer/browse.php'); }

$pickupIn = $_GET['pickup_date'] ?? date('Y-m-d\T10:00', strtotime('+1 day'));
$returnIn = $_GET['return_date'] ?? date('Y-m-d\T18:00', strtotime('+4 days'));
if (strlen($pickupIn) === 10) $pickupIn .= 'T10:00';
if (strlen($returnIn) === 10) $returnIn .= 'T18:00';
$pickup = date('Y-m-d H:i:s', strtotime($pickupIn)); $return = date('Y-m-d H:i:s', strtotime($returnIn));
if (strtotime($return) <= strtotime($pickup) || strtotime($pickup) < time() - 3600 || !vehicle_is_available($pdo, $v['id'], $pickup, $return)) {
    flash_set('error', 'That vehicle is not available for the chosen dates. Please pick other dates.');
    redirect('customer/vehicle.php?id=' . (int)$v['id']);
}
$price = calc_price($v['daily_rate'], rental_days($pickup, $return));
$hubs = ['BGC Flagship Station', 'NAIA Terminal 3', 'Makati Central Hub', 'Ortigas Center', 'Clark Freeport'];
?>
<div class="crumb"><a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>">&larr; Back to Vehicle Details</a></div>
<form method="post" action="<?= BASE_URL ?>actions/request_action.php" class="split">
  <?= csrf_field() ?>
  <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
  <input type="hidden" name="pickup_date" value="<?= e($pickupIn) ?>"><input type="hidden" name="return_date" value="<?= e($returnIn) ?>">
  <div class="card">
    <h1 class="t">Price Overview</h1>
    <p class="sub">Review the rental period and fee schedule before sending your request.</p>
    <div class="kv" style="grid-template-columns:100px 1fr">
      <div style="height:70px;border-radius:10px;background:url('<?= e($v['image_url']) ?>') center/cover"></div>
      <div><b><?= e($v['make'] . ' ' . $v['model']) ?></b><br><span class="sub"><?= e($v['transmission']) ?> &middot; <?= (int)$v['seats'] ?> seats &middot; <?= money($v['daily_rate']) ?> / day</span></div>
    </div>
    <div class="kv">
      <div><small>Pick-up</small><?= date('M j, Y g:i A', strtotime($pickup)) ?></div>
      <div><small>Return</small><?= date('M j, Y g:i A', strtotime($return)) ?></div>
      <div><small>Duration</small><?= $price['days'] ?> day(s)</div>
    </div>
    <h3>Itemized breakdown</h3>
    <div class="line"><div><b>Base rental charge</b><br><span><?= $price['days'] ?> day(s) &times; <?= money($v['daily_rate']) ?></span></div><b><?= money($price['base']) ?></b></div>
    <div class="line"><div><b>Standard comprehensive insurance</b><br><span>Zero-deductible collision protection</span></div><b>Included</b></div>
    <div class="line"><div><b>Mandatory government VAT (12%)</b></div><b><?= money($price['vat']) ?></b></div>
    <div class="line"><div><b>Refundable security deposit</b><br><span>Held on arrival, released after inspection</span></div><b><?= money($price['deposit']) ?>*</b></div>
    <div class="total"><span>PAYABLE NET TOTAL<br><small>Excludes refundable deposit</small></span><b><?= money($price['total']) ?></b></div>
  </div>
  <div class="card">
    <h2>Reservation</h2>
    <span class="badge b-amber">Status: Waiting for confirmation</span>
    <div class="field" style="margin-top:14px"><label>Pick-up hub</label><select name="pickup_location"><?php foreach ($hubs as $h): ?><option><?= e($h) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Return hub</label><select name="return_location"><?php foreach ($hubs as $h): ?><option><?= e($h) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Driver full name (as on license)</label><input name="driver_name" required maxlength="100" value="<?= e($me['first_name'] . ' ' . $me['last_name']) ?>"></div>
    <div class="field"><label>Contact mobile phone</label><input name="driver_phone" required maxlength="30" value="<?= e($me['phone']) ?>" placeholder="+63 917 123 4567"></div>
    <div class="field"><label>Philippine LTO driver's license no.</label><input name="driver_license" required maxlength="50" value="<?= e($me['license_number']) ?>" placeholder="N02-14-048291"></div>
    <label style="display:flex;gap:8px;font-size:13px;margin-bottom:14px"><input type="checkbox" name="agree" value="1" required> <span>I agree to the <a href="<?= BASE_URL ?>policies.php" target="_blank">Terms of Rental</a> and driver requirements.</span></label>
    <button class="btn-d btn-full"><i class="fa-solid fa-paper-plane"></i> Request to Book</button>
    <p class="sub" style="font-size:12px;margin-top:10px">*Your request goes to DriveSphere Fleet Command for approval. You are not charged until it is approved.</p>
  </div>
</form>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
