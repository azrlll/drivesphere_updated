<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$cols = $pdo->query("DESCRIBE reservations")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
