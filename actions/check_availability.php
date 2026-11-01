<?php
// API Endpoint: Check vehicle availability and validate policy constraints for selected dates/times
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = getDBConnection();

$vehicleId = (int)($_GET['vehicle_id'] ?? $_POST['vehicle_id'] ?? 0);
$pickupIn  = trim($_GET['pickup_date'] ?? $_POST['pickup_date'] ?? '');
$returnIn  = trim($_GET['return_date'] ?? $_POST['return_date'] ?? '');
$pickupTimeIn = trim($_GET['pickup_time'] ?? $_POST['pickup_time'] ?? '09:00');
$returnTimeIn = trim($_GET['return_time'] ?? $_POST['return_time'] ?? '18:00');
$branchCode = trim($_GET['branch_code'] ?? $_GET['hub'] ?? $_GET['pickup_location'] ?? '');

if ($vehicleId <= 0) {
    echo json_encode(['available' => false, 'message' => 'Invalid vehicle ID specified.']);
    exit;
}

$vStmt = $pdo->prepare("SELECT v.*, b.name AS branch_name, b.operating_hours, b.opening_time, b.closing_time 
                        FROM vehicles v 
                        LEFT JOIN branches b ON v.branch_id = b.id 
                        WHERE v.id = ?");
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

// Format clean times
if (strlen($pickupTimeIn) === 5) $pickupTimeIn .= ':00';
if (strlen($returnTimeIn) === 5) $returnTimeIn .= ':00';

// Combine date and time
if (str_contains($pickupIn, 'T') || str_contains($pickupIn, ' ')) {
    $pickupSql = date('Y-m-d H:i:s', strtotime($pickupIn));
} else {
    $pickupSql = date('Y-m-d H:i:s', strtotime($pickupIn . ' ' . $pickupTimeIn));
}

if (str_contains($returnIn, 'T') || str_contains($returnIn, ' ')) {
    $returnSql = date('Y-m-d H:i:s', strtotime($returnIn));
} else {
    $returnSql = date('Y-m-d H:i:s', strtotime($returnIn . ' ' . $returnTimeIn));
}

$pickupTimestamp = strtotime($pickupSql);
$returnTimestamp = strtotime($returnSql);

// Validate office hours, 1-hour lead time, and 48–72-hour advance reservation policy
$branchId = $vehicle['branch_id'] ?? 1;
if ($branchCode !== '') {
    $bRow = get_branch_by_id_or_code($pdo, $branchCode);
    if ($bRow) $branchId = $bRow['id'];
}

$sched = validate_booking_schedule($pdo, $pickupSql, $returnSql, $branchId);
if (!$sched['valid']) {
    echo json_encode([
        'available' => false,
        'policy_error' => true,
        'message' => $sched['error']
    ]);
    exit;
}

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
    $cStart = date('M d, Y g:i A', strtotime($conflict['pickup_date']));
    $cEnd   = date('M d, Y g:i A', strtotime($conflict['return_date']));
    $cStatus = ucfirst($conflict['status']);
    echo json_encode([
        'available' => false,
        'conflict'  => true,
        'message'   => "Vehicle has an active booking ({$cStatus}) from {$cStart} to {$cEnd}. Please pick different dates or hours.",
        'booked_from' => $cStart,
        'booked_to'   => $cEnd
    ]);
    exit;
}

// Calculate days (supports same-day rental duration with 1 day minimum)
$days = rental_days($pickupSql, $returnSql);
$dailyRate = (float)$vehicle['daily_rate'];
$pricing = calc_price($dailyRate, $days);

echo json_encode([
    'available'   => true,
    'message'     => 'Vehicle is verified in fleet stock and open for booking for your selected dates!',
    'vehicle_id'  => $vehicleId,
    'brand'       => $vehicle['brand'] ?: $vehicle['make'],
    'model'       => $vehicle['model'],
    'year'        => (int)$vehicle['year'],
    'days'        => $days,
    'daily_rate'  => $dailyRate,
    'subtotal'    => $pricing['base'],
    'vat'         => $pricing['vat'],
    'deposit'     => $pricing['deposit'],
    'total'       => $pricing['total'],
    'pickup_date' => date('Y-m-d', $pickupTimestamp),
    'return_date' => date('Y-m-d', $returnTimestamp),
    'pickup_time' => date('H:i', $pickupTimestamp),
    'return_time' => date('H:i', $returnTimestamp),
    'operating_hours' => $vehicle['operating_hours'] ?: '8:00 AM – 8:00 PM'
]);
