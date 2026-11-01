<?php
// Customer submits a rental request -> saved as 'pending' for the admin to approve.
require_once __DIR__ . '/../includes/functions.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('customer/dashboard.php');
}
require_customer();
csrf_check('customer/dashboard.php');
$pdo = getDBConnection();

$vehicleId = (int)($_POST['vehicle_id'] ?? 0);

// Resolve pick-up and return date & time inputs
$pDateRaw = trim($_POST['pickup_date'] ?? '');
$rDateRaw = trim($_POST['return_date'] ?? '');
$pTimeRaw = trim($_POST['pickup_time'] ?? '09:00');
$rTimeRaw = trim($_POST['return_time'] ?? '18:00');

// Format clean times (HH:MM:00)
if (!str_contains($pTimeRaw, ':')) $pTimeRaw = '09:00';
if (strlen($pTimeRaw) === 5) $pTimeRaw .= ':00';
if (!str_contains($rTimeRaw, ':')) $rTimeRaw = '18:00';
if (strlen($rTimeRaw) === 5) $rTimeRaw .= ':00';

if (str_contains($pDateRaw, 'T') || str_contains($pDateRaw, ' ')) {
    $pickup = date('Y-m-d H:i:s', strtotime($pDateRaw));
} else {
    $pickup = date('Y-m-d H:i:s', strtotime($pDateRaw . ' ' . $pTimeRaw));
}

if (str_contains($rDateRaw, 'T') || str_contains($rDateRaw, ' ')) {
    $return = date('Y-m-d H:i:s', strtotime($rDateRaw));
} else {
    $return = date('Y-m-d H:i:s', strtotime($rDateRaw . ' ' . $rTimeRaw));
}

$pLoc   = trim($_POST['pickup_location'] ?? '');
$rLoc   = trim($_POST['return_location'] ?? $pLoc);
$driver = trim($_POST['driver_name'] ?? '');
$phone  = trim($_POST['driver_phone'] ?? '');
$licNo  = trim($_POST['driver_license'] ?? '');

// Payment method & reference (Phase 8)
$allowedPayments = ['Cash on Pickup', 'GCash', 'Maya', 'Bank Transfer'];
$paymentMethod = in_array($_POST['payment_method'] ?? '', $allowedPayments) ? $_POST['payment_method'] : 'Cash on Pickup';
$paymentRef = trim($_POST['payment_reference'] ?? '');

$vehicle = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$vehicle->execute([$vehicleId]);
$vehicle = $vehicle->fetch();

$error = '';
if (!$vehicle) {
    $error = 'Vehicle not found.';
} elseif ($driver === '' || $phone === '' || $licNo === '' || $pLoc === '' || $rLoc === '') {
    $error = 'Please complete all driver, license, and pick-up location fields.';
} elseif (empty($_POST['agree'])) {
    $error = 'You must agree to the Terms of Rental and Driver Requirements.';
} else {
    // Branch lookup
    $branch = get_branch_by_id_or_code($pdo, $pLoc) ?: get_branch_by_id_or_code($pdo, $vehicle['branch_id'] ?? 1);
    $branchId = $branch ? (int)$branch['id'] : (int)($vehicle['branch_id'] ?? 1);

    // Validate office hours, 1-hour lead time, and 48–72-hour advance reservation policy
    $schedCheck = validate_booking_schedule($pdo, $pickup, $return, $branchId);
    if (!$schedCheck['valid']) {
        $error = $schedCheck['error'];
    }
}

if ($error !== '') {
    flash_set('error', $error);
    redirect($vehicle ? 'customer/vehicle.php?id=' . $vehicleId . '&pickup_date=' . urlencode(substr($pickup, 0, 10)) . '&return_date=' . urlencode(substr($return, 0, 10)) : 'customer/browse.php');
}

// Atomic database transaction to prevent double-bookings (Phase 5)
try {
    $pdo->beginTransaction();

    if (!vehicle_is_available($pdo, $vehicleId, $pickup, $return)) {
        $pdo->rollBack();
        flash_set('error', 'Sorry, this vehicle is not available for those dates. Please choose different dates.');
        redirect('customer/vehicle.php?id=' . $vehicleId);
    }

    $price = calc_price($vehicle['daily_rate'], rental_days($pickup, $return));
    $next  = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM reservations")->fetchColumn();
    $ref   = 'DS-RN-' . date('Y') . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    $cUserId = customer_user_id();
    $cUserName = customer_user_name() ?: $driver;

    $ins = $pdo->prepare("INSERT INTO reservations
      (reference_code, user_id, vehicle_id, branch_id, pickup_location, return_location,
       pickup_date, return_date, pickup_time, return_time, days, base_amount, vat_amount,
       total_price, deposit_amount, payment_method, payment_reference, payment_status,
       driver_name, driver_phone, driver_license, status)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");

    $ins->execute([
        $ref, $cUserId, $vehicleId, $branchId, $pLoc, $rLoc,
        $pickup, $return, substr($pickup, 11), substr($return, 11),
        $price['days'], $price['base'], $price['vat'],
        $price['total'], $price['deposit'], $paymentMethod,
        $paymentRef !== '' ? $paymentRef : null, 'pending',
        $driver, $phone, $licNo
    ]);

    // Update user profile info for faster future bookings
    $pdo->prepare("UPDATE users SET phone = COALESCE(NULLIF(phone,''), ?), license_number = COALESCE(NULLIF(license_number,''), ?) WHERE id = ?")
        ->execute([$phone, $licNo, $cUserId]);

    // Customer & Admin in-app notifications
    add_notification($pdo, $cUserId, 'customer', 'booking',
        'Rental Request Sent — ' . $vehicle['make'] . ' ' . $vehicle['model'],
        'Request ' . $ref . ' (' . $price['days'] . ' day(s) · ' . money($price['total']) . ') is pending Fleet Merchant approval.',
        'customer/receipt.php?ref=' . $ref
    );

    add_notification($pdo, null, 'admin', 'booking',
        'Rental Booking Authorization Required',
        $cUserName . ' requested the ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $ref . ' at ' . ($branch['name'] ?? $pLoc) . ').',
        'admin/requests.php'
    );

    $pdo->commit();

    flash_set('success', 'Your rental request was successfully submitted and is awaiting administrator confirmation.');
    redirect('customer/receipt.php?ref=' . $ref);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'An error occurred while submitting your reservation: ' . $e->getMessage());
    redirect('customer/vehicle.php?id=' . $vehicleId);
}
