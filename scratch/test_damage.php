<?php
session_start();
$_SESSION['admin_user_id'] = 2;
$_SESSION['admin_user_role'] = 'admin';
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'admin';
$_GET['id'] = 21;
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
require_once __DIR__ . '/../includes/admin_booking.php';
$b = load_booking($pdo, 21);
echo "Booking 21: " . json_encode($b) . "\n";
