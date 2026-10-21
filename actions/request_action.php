<?php
// Customer submits a rental request -> saved as 'pending' for the admin to approve.
require_once __DIR__ . '/../includes/functions.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('customer/browse.php');
}
require_customer();
csrf_check('customer/browse.php');
$pdo = getDBConnection();

$vehicleId = (int)($_POST['vehicle_id'] ?? 0);
$pickup = date('Y-m-d H:i:s', strtotime($_POST['pickup_date'] ?? ''));
$return = date('Y-m-d H:i:s', strtotime($_POST['return_date'] ?? ''));
$pLoc   = trim($_POST['pickup_location'] ?? '');
$rLoc   = trim($_POST['return_location'] ?? '');
$driver = trim($_POST['driver_name'] ?? '');
$phone  = trim($_POST['driver_phone'] ?? '');
$licNo  = trim($_POST['driver_license'] ?? '');

$vehicle = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$vehicle->execute([$vehicleId]);
$vehicle = $vehicle->fetch();

$error = '';
if (!$vehicle)                                   $error = 'Vehicle not found.';
elseif ($driver === '' || $phone === '' || $licNo === '' || $pLoc === '' || $rLoc === '') $error = 'Please complete all driver and location fields.';
elseif (empty($_POST['agree']))                  $error = 'You must agree to the Terms of Rental.';
elseif (strtotime($return) <= strtotime($pickup) || strtotime($pickup) < time() - 3600) $error = 'Please choose valid pick-up and return dates.';
elseif (!vehicle_is_available($pdo, $vehicleId, $pickup, $return)) $error = 'Sorry, this vehicle was just booked for those dates.';
if ($error !== '') {
    flash_set('error', $error);
    redirect($vehicle ? 'customer/vehicle.php?id=' . $vehicleId : 'customer/browse.php');
}

$price = calc_price($vehicle['daily_rate'], rental_days($pickup, $return));
$next  = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM reservations")->fetchColumn();
$ref   = 'DS-RN-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);

$ins = $pdo->prepare("INSERT INTO reservations
  (reference_code, user_id, vehicle_id, pickup_location, return_location, pickup_date, return_date, days, base_amount, vat_amount,
   total_price, deposit_amount, driver_name, driver_phone, driver_license, status)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
$cUserId = customer_user_id();
$cUserName = customer_user_name() ?: $driver;

$ins->execute([$ref, $cUserId, $vehicleId, $pLoc, $rLoc, $pickup, $return, $price['days'], $price['base'], $price['vat'],
               $price['total'], $price['deposit'], $driver, $phone, $licNo]);

// remember phone / license for next time
$pdo->prepare("UPDATE users SET phone = COALESCE(NULLIF(phone,''), ?), license_number = COALESCE(NULLIF(license_number,''), ?) WHERE id = ?")
    ->execute([$phone, $licNo, $cUserId]);

add_notification($pdo, $cUserId, 'customer', 'booking', 'Rental request sent — ' . $vehicle['make'] . ' ' . $vehicle['model'],
    'Request ' . $ref . ' is waiting for DriveSphere Fleet Command approval.', 'customer/receipt.php?ref=' . $ref);
add_notification($pdo, null, 'admin', 'booking', 'Rental Booking Authorization Required',
    $cUserName . ' requested the ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $ref . ').', 'admin/requests.php');

flash_set('success', 'Your rental request was sent. We will notify you once it is approved.');
redirect('customer/receipt.php?ref=' . $ref);
