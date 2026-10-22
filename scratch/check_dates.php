<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$rows = $pdo->query("SELECT id, reference_code, pickup_date, return_date, status FROM reservations ORDER BY pickup_date")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
