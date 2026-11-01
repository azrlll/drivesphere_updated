<?php
require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DriveSphere Database Connection Error: " . $e->getMessage());
            if (ini_get('display_errors')) {
                die("Database connection failed. Start MySQL in XAMPP and import sql/drivesphere.sql in phpMyAdmin. (" . $e->getMessage() . ")");
            } else {
                die("Database connection failed. Please ensure the database service is available.");
            }
        }
    }
    return $pdo;
}
