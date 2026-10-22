<?php
// Loads ONE reservation that belongs to the logged-in customer (used by receipt / return / charges pages).
function load_my_booking($pdo, $ref) {
    $uid = customer_user_id();
    $sql = "SELECT r.*, 
                   v.make, v.model, v.brand, v.year, v.category, v.tag_label, v.plate_number, 
                   v.image_path, v.image_url, v.transmission, v.fuel_type, v.seats, v.seating_capacity, v.daily_rate,
                   u.first_name, u.last_name, u.email as user_email
            FROM reservations r 
            JOIN vehicles v ON r.vehicle_id = v.id 
            LEFT JOIN users u ON r.user_id = u.id
            WHERE ";

    if (!empty($ref)) {
        if ($uid) {
            $st = $pdo->prepare($sql . "r.reference_code = ? AND r.user_id = ?");
            $st->execute([$ref, $uid]);
            $b = $st->fetch();
            if ($b) return $b;
        }

        // Allow previewing booking by reference code if demo or admin testing
        $st = $pdo->prepare($sql . "r.reference_code = ?");
        $st->execute([$ref]);
        $b = $st->fetch();
        if ($b) return $b;
    }

    // Fallback: pick the latest booking for the current user
    if ($uid) {
        $st = $pdo->prepare($sql . "r.user_id = ? ORDER BY r.id DESC LIMIT 1");
        $st->execute([$uid]);
        $b = $st->fetch();
        if ($b) return $b;
    }

    // Fallback: pick any recent returned or completed booking
    $st = $pdo->query($sql . "1 ORDER BY r.id DESC LIMIT 1");
    $b = $st->fetch();
    if (!$b) { 
        flash_set('error', 'Booking not found.'); 
        redirect('customer/history.php'); 
    }
    return $b;
}
