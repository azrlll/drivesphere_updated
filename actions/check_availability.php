<?php
// API Endpoint: Check vehicle availability for selected dates against MySQL database
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDBConnection();

$vehicleId = (int)($_GET['vehicle_id'] ?? $_POST['vehicle_id'] ?? 0);
$pickupIn  = trim($_GET['pickup_date'] ?? $_POST['pickup_date'] ?? '');
$returnIn  = trim($_GET['return_date'] ?? $_POST['return_date'] ?? '');

if ($vehicleId <= 0) {
    echo json_encode(['available' => false, 'message' => 'Invalid vehicle ID specified.']);
    exit;
}

$vStmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$vStmt->execute([$vehicleId]);
$vehicle = $vStmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    echo json_encode(['available' => false, 'message' => 'Vehicle not found in database.']);
    exit;
}

if ($vehicle['status'] === 'maintenance') {
    echo json_encode([
        'available' => false,
        'message' => 'This vehicle is currently undergoing scheduled maintenance and is unavailable for booking.',
        'status' => 'maintenance'
    ]);
    exit;
}

if ($pickupIn === '' || $returnIn === '') {
    echo json_encode(['available' => false, 'message' => 'Please select both pick-up and return dates.']);
    exit;
}

// Append default times if only date is passed
if (strlen($pickupIn) === 10) $pickupIn .= ' 09:00:00';
if (strlen($returnIn) === 10) $returnIn .= ' 18:00:00';

$pickupTime = strtotime($pickupIn);
$returnTime = strtotime($returnIn);

if (!$pickupTime || !$returnTime || $returnTime <= $pickupTime) {
    echo json_encode([
        'available' => false,
        'message' => 'Return date must be after the pick-up date.'
    ]);
    exit;
}

$now = time();
// Allow slight grace period of 1 hour in the past for today's pickup
if ($pickupTime < $now - 3600) {
    echo json_encode([
        'available' => false,
        'message' => 'Pick-up date cannot be in the past.'
    ]);
    exit;
}

$pickupSql = date('Y-m-d H:i:s', $pickupTime);
$returnSql = date('Y-m-d H:i:s', $returnTime);

// Check conflicting bookings in reservations table (reserved, en_route, or pending)
$confStmt = $pdo->prepare("SELECT id, reference_code, pickup_date, return_date, status 
                           FROM reservations 
                           WHERE vehicle_id = ? 
                             AND status IN ('reserved', 'en_route', 'pending') 
                             AND pickup_date < ? 
                             AND return_date > ? 
                           LIMIT 1");
$confStmt->execute([$vehicleId, $returnSql, $pickupSql]);
$conflict = $confStmt->fetch(PDO::FETCH_ASSOC);

if ($conflict) {
    $cStart = date('M d, Y', strtotime($conflict['pickup_date']));
    $cEnd   = date('M d, Y', strtotime($conflict['return_date']));
    $cStatus = ucfirst($conflict['status']);
    echo json_encode([
        'available' => false,
        'conflict'  => true,
        'message'   => "Vehicle is already booked ({$cStatus}) from {$cStart} to {$cEnd}. Please pick different dates.",
        'booked_from' => $cStart,
        'booked_to'   => $cEnd
    ]);
    exit;
}

// Vehicle is 100% available! Calculate rates and breakdown
$days = max(1, (int)round(($returnTime - $pickupTime) / 86400));
$dailyRate = (float)$vehicle['daily_rate'];
$subtotal = $days * $dailyRate;
$deposit = 5000.00;
$total = $subtotal;

echo json_encode([
    'available'   => true,
    'message'     => 'Vehicle is available for your selected dates! Ready for immediate reservation.',
    'vehicle_id'  => $vehicleId,
    'brand'       => $vehicle['brand'] ?: $vehicle['make'],
    'model'       => $vehicle['model'],
    'year'        => (int)$vehicle['year'],
    'days'        => $days,
    'daily_rate'  => $dailyRate,
    'subtotal'    => $subtotal,
    'deposit'     => $deposit,
    'total'       => $total,
    'pickup_date' => date('Y-m-d', $pickupTime),
    'return_date' => date('Y-m-d', $returnTime)
]);
