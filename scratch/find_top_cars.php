<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$pdo->exec("UPDATE vehicles SET model='Civic RS Turbo' WHERE id=2");
echo "Updated vehicle 2 to Civic RS Turbo\n";
