<?php
require_once __DIR__ . '/../includes/functions.php';
$_SESSION['admin_user_id'] = 2;
$_SESSION['admin_user_name'] = 'Admin Officer';
$_SESSION['admin_user_email'] = 'admin@drivesphere.ph';
$_SESSION['admin_user_role'] = 'admin';
$_SESSION['user_id'] = 2;
$_SESSION['user_name'] = 'Admin Officer';
$_SESSION['user_role'] = 'admin';
header('Location: ' . BASE_URL . 'admin/dashboard.php');
exit;
