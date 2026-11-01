<?php
require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        // Pre-check for missing credentials before attempting connection on production
        if (DB_PASS === '' && DB_HOST !== '127.0.0.1' && DB_HOST !== 'localhost') {
            error_log("DriveSphere Database Error: DB_PASS is empty for remote host " . DB_HOST);
            die("Database configuration notice: Database password is not set. Please ensure includes/config.production.php is uploaded to the server.");
        }

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
            }

            // Categorized, non-sensitive diagnostic messages for production without leaking passwords
            $code = (int)$e->getCode();
            $msg = $e->getMessage();

            if (strpos($msg, 'Access denied') !== false || $code === 1045) {
                die("Database connection failed: Authentication failed for database user. Please verify the password in includes/config.production.php.");
            } elseif (strpos($msg, 'Unknown database') !== false || $code === 1049) {
                die("Database connection failed: Database '" . htmlspecialchars(DB_NAME) . "' not found. Please verify the database name in InfinityFree.");
            } elseif (strpos($msg, 'getaddrinfo') !== false || strpos($msg, 'Connection refused') !== false || $code === 2002) {
                die("Database connection failed: Unable to connect to MySQL host (" . htmlspecialchars(DB_HOST) . "). Please verify the host name.");
            } else {
                die("Database connection failed. Please ensure the database service is available.");
            }
        }
    }
    return $pdo;
}
