<?php
// Customer login. Only accounts with role = 'customer' can log in here.
require_once __DIR__ . '/../includes/functions.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('login.php');
}

csrf_check('login.php');

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Please enter a valid email and your password.');
    redirect('login.php');
}

$pdo  = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    login_user($user);
    if (($user['role'] ?? '') === 'admin') {
        flash_set('info', 'Welcome, Administrator.');
        redirect('admin/dashboard.php');
    } else {
        redirect('customer/dashboard.php');
    }
}

flash_set('error', 'Incorrect email or password.');
redirect('login.php');
