<?php
require_once 'c:/xampp/htdocs/drivesphere_updated/includes/functions.php';
$pdo = getDBConnection();
$rows = $pdo->query("SELECT id, first_name, last_name, email, phone, avatar, phone_verified, email_verified FROM users WHERE role = 'customer'")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
