<?php
require_once 'c:/xampp/htdocs/drivesphere_updated/includes/functions.php';
$pdo = getDBConnection();

$cols = array_column($pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC), 'Field');

if (!in_array('avatar', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER phone");
    echo "Added avatar column\n";
}
if (!in_array('phone_verified', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN phone_verified TINYINT(1) DEFAULT 1 AFTER phone");
    echo "Added phone_verified column\n";
}
if (!in_array('email_verified', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 1 AFTER email");
    echo "Added email_verified column\n";
}

echo "Users table updated successfully!\n";
