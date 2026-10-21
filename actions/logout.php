<?php
// Logs out whoever is logged in and destroys the session.
require_once __DIR__ . '/../includes/functions.php';
if (is_admin() && !is_customer()) {
    logout_admin();
    header('Location: ' . BASE_URL . 'admin/login.php');
} else {
    logout_customer();
    header('Location: ' . BASE_URL . 'index.php');
}
exit;
