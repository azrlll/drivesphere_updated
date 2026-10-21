<?php
// Loads ONE reservation that belongs to the logged-in customer (used by receipt / return / charges pages).
// A customer can never open another customer's booking: the query filters by user_id.
function load_my_booking($pdo, $ref) {
    $st = $pdo->prepare("SELECT r.*, v.make, v.model, v.plate_number, v.image_url, v.transmission, v.fuel_type, v.seats, v.daily_rate
        FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id WHERE r.reference_code = ? AND r.user_id = ?");
    $st->execute([$ref, customer_user_id()]);
    $b = $st->fetch();
    if (!$b) { flash_set('error', 'Booking not found.'); redirect('customer/history.php'); }
    return $b;
}
