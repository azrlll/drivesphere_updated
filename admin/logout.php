<?php
// Admin logout: destroys the session and returns to the admin login page.
require_once __DIR__ . '/../includes/functions.php';
logout_admin();
header('Location: ' . BASE_URL . 'admin/login.php');
exit;
