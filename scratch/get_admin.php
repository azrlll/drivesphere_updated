<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$cust = $pdo->query("SELECT id, email, first_name, last_name, role, license_number FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cust, JSON_PRETTY_PRINT);
