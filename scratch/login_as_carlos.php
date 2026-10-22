<?php
require_once __DIR__ . '/../includes/functions.php';
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Carlos Alvarez';
$_SESSION['user_role'] = 'customer';
$_SESSION['customer_user_id'] = 1;
$_SESSION['customer_user_name'] = 'Carlos Alvarez';
$_SESSION['customer_user_role'] = 'customer';
echo 'OK';
