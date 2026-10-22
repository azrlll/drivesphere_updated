<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();

// Check if Maria Santos exists
$maria = $pdo->query("SELECT id FROM users WHERE email='maria.santos@gmail.com'")->fetchColumn();
if (!$maria) {
    $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role, license_number, phone, email_verified, phone_verified)
                   VALUES ('Maria', 'Santos', 'maria.santos@gmail.com', ?, 'customer', 'N01-22-984712', '+63 917 555 8921', 1, 1)")
        ->execute([password_hash('password123', PASSWORD_DEFAULT)]);
    $mariaId = $pdo->lastInsertId();
    echo "Created user Maria Santos with ID: $mariaId\n";
} else {
    $mariaId = $maria;
    echo "Maria Santos already exists with ID: $mariaId\n";
}

// Check if DS-8921 exists
$res8921 = $pdo->query("SELECT id FROM reservations WHERE reference_code='DS-8921'")->fetchColumn();
if (!$res8921) {
    $pdo->prepare("INSERT INTO reservations (reference_code, user_id, vehicle_id, pickup_location, return_location, pickup_date, return_date, total_price, base_amount, vat_amount, deposit_amount, days, driver_name, driver_phone, driver_license, status)
                   VALUES ('DS-8921', ?, 3, 'Makati Hub, Metro Manila', 'Makati Hub, Metro Manila', '2026-05-15 09:00:00', '2026-05-18 18:00:00', 13104.00, 11700.00, 1404.00, 5000.00, 3, 'Maria Santos', '+63 917 555 8921', 'N01-22-984712', 'pending')")
        ->execute([$mariaId]);
    $res8921Id = $pdo->lastInsertId();
    echo "Created reservation DS-8921 with ID: $res8921Id\n";
} else {
    $res8921Id = $res8921;
    // ensure status is pending
    $pdo->exec("UPDATE reservations SET status='pending' WHERE id=$res8921Id");
    echo "Updated reservation DS-8921 ID $res8921Id to pending\n";
}

// Check if DS-7740 exists
$res7740 = $pdo->query("SELECT id FROM reservations WHERE reference_code='DS-7740'")->fetchColumn();
if (!$res7740) {
    $pdo->prepare("INSERT INTO reservations (reference_code, user_id, vehicle_id, pickup_location, return_location, pickup_date, return_date, total_price, base_amount, vat_amount, deposit_amount, days, driver_name, driver_phone, driver_license, status, returned_at, damage_description, damage_severity, damage_charge, odometer_in, fuel_level)
                   VALUES ('DS-7740', ?, 4, 'BGC Main Terminal', 'BGC Main Terminal', '2026-05-10 08:00:00', '2026-05-13 17:00:00', 16128.00, 14400.00, 1728.00, 5000.00, 3, 'Carlos Alvarez', '+63 917 123 4567', '435135135', 'returned', NOW(), 'Rear bumper scuff final settlement charges', 'Minor', 1500.00, 24500, 'Full')")
        ->execute([1]);
    $res7740Id = $pdo->lastInsertId();
    echo "Created reservation DS-7740 with ID: $res7740Id\n";
} else {
    $res7740Id = $res7740;
    $pdo->exec("UPDATE reservations SET status='returned', damage_description='Rear bumper scuff final settlement charges', damage_severity='Minor', damage_charge=1500.00 WHERE id=$res7740Id");
    echo "Updated reservation DS-7740 ID $res7740Id to returned\n";
}
