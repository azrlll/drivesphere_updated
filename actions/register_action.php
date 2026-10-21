<?php
require_once __DIR__ . '/../includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');
$registerFallback = 'register.php' . ($redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect($registerFallback);
}
csrf_check($registerFallback);

$fullName = trim($_POST['full_name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$agree    = !empty($_POST['agree']);

$error = '';
if ($fullName === '' || strlen($fullName) > 100) {
    $error = 'Please enter your full name.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
} elseif (strlen($password) < 8) {
    $error = 'Password must be at least 8 characters.';
} elseif ($password !== $confirm) {
    $error = 'Passwords do not match.';
} elseif (!$agree) {
    $error = 'You must agree to the Terms of Rental Service.';
}
if ($error !== '') {
    flash_set('error', $error);
    redirect($registerFallback);
}

$pdo = getDBConnection();
$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    flash_set('error', 'That email is already registered. Please log in instead.');
    redirect($registerFallback);
}

$parts = explode(' ', $fullName, 2);
$first = $parts[0];
$last  = $parts[1] ?? '-';

$insert = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'customer')");
$insert->execute([$first, $last, $email, password_hash($password, PASSWORD_DEFAULT)]);

$user = ['id' => $pdo->lastInsertId(), 'first_name' => $first, 'last_name' => $last, 'email' => $email, 'role' => 'customer'];
login_user($user);
flash_set('success', 'Welcome to DriveSphere! Your account has been created.');
redirect($redirectTarget);
