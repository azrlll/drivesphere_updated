<?php
require_once 'c:/xampp/htdocs/drivesphere_updated/includes/functions.php';
$pdo = getDBConnection();
$cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} ({$c['Type']})\n";
}
