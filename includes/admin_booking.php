<?php
// Loads one reservation with its vehicle and customer (admin pages only).
function load_booking($pdo, $id) {
    $st = $pdo->prepare("SELECT r.*, v.make, v.model, v.plate_number, v.image_url, v.transmission, v.fuel_type, v.seats, v.daily_rate, v.odometer AS v_odometer,
                                u.first_name, u.last_name, u.email AS cust_email, u.phone AS cust_phone
                         FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
    $st->execute([(int)$id]);
    $b = $st->fetch();
    if (!$b) { flash_set('error', 'Rental record not found.'); redirect('admin/rentals.php'); }
    $b['cust_name'] = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? '')) ?: ($b['driver_name'] ?? 'Guest');
    return $b;
}
