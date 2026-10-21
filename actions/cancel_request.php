<?php
// Customer cancels their OWN pending or reserved booking.
require_once __DIR__ . '/../includes/functions.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('customer/history.php');
}
require_customer();
csrf_check('customer/history.php');
$pdo = getDBConnection();

$ref = $_POST['ref'] ?? '';
$st = $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE reference_code = ? AND user_id = ? AND status IN ('pending','reserved')");
$cUserId = customer_user_id();
$cUserName = customer_user_name() ?: 'Customer';
$st->execute([$ref, $cUserId]);
if ($st->rowCount()) {
    add_notification($pdo, null, 'admin', 'booking', 'Booking cancelled by customer', $cUserName . ' cancelled ' . $ref . '.', 'admin/rentals.php');
    flash_set('success', 'Booking ' . $ref . ' was cancelled.');
} else {
    flash_set('error', 'This booking can no longer be cancelled.');
}
redirect('customer/history.php');
