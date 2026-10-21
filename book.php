<?php
// Public "Rent Now / Vehicle Details" buttons point here.
// Logged-in customers go to the vehicle page; guests must log in first.
require_once __DIR__ . '/includes/functions.php';

$id     = (int)($_GET['vehicle'] ?? 0);
$pickup = $_GET['pickup_date'] ?? '';
$return = $_GET['return_date'] ?? '';

$target = 'customer/vehicle.php?id=' . $id;
if ($pickup !== '' && $return !== '') {
    $target .= '&pickup_date=' . urlencode($pickup) . '&return_date=' . urlencode($return);
}

if (is_customer()) {
    redirect($target);
}
if (is_admin()) {
    flash_set('error', 'Administrator accounts oversee the fleet from the Admin Console. To test booking as a customer, please sign in with a customer account.');
    redirect('admin/manage.php');
}
flash_set('error', 'Please log in or register to reserve a vehicle.');
redirect('login.php?redirect=' . urlencode($target));
