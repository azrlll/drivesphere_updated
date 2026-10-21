<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM vehicles");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);
