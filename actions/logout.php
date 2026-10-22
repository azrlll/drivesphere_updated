<?php
// Logs out whoever is logged in and destroys the session.
require_once __DIR__ . '/../includes/functions.php';
$wasAdmin = is_admin();
logout_user();
if ($wasAdmin) {
    header('Location: ' . BASE_URL . 'admin/login.php');
} else {
    header('Location: ' . BASE_URL . 'index.php');
}
exit;
