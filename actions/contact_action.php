<?php
// Saves the "Contact Us" form into the inquiries table (used by public and customer contact support).
require_once __DIR__ . '/../includes/functions.php';

$redirectTo = !empty($_POST['redirect_to']) && (str_starts_with($_POST['redirect_to'], 'customer/') || $_POST['redirect_to'] === 'contact.php') ? $_POST['redirect_to'] : 'contact.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect($redirectTo);
}
csrf_check($redirectTo);

$name    = trim($_POST['full_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$topic   = trim($_POST['category'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $message === '' || $topic === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Please complete all required fields with a valid email.');
    redirect($redirectTo);
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("INSERT INTO inquiries (full_name, email, phone, category, message) VALUES (?,?,?,?,?)");
$stmt->execute([$name, $email, $phone !== '' ? $phone : null, $topic, $message]);

add_notification($pdo, null, 'admin', 'booking', 'New customer inquiry', $name . ' sent a message about ' . $topic . '.', 'admin/notifications.php');
flash_set('success', 'Thank you! Our support team will reply within 15 minutes during business hours.');
redirect($redirectTo);

