<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
print_r($cats);
$vehs = $pdo->query("SELECT id, make, model, category_id, daily_rate, status FROM vehicles")->fetchAll();
print_r($vehs);
