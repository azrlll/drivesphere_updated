<?php
// ---------------------------------------------------------------
// DriveSphere configuration (XAMPP defaults)
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Buffer output so pages can still redirect after the layout is included.
ob_start();

// Database
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'drivesphere');

// Dynamically detect BASE_URL so links, assets, and redirects work under any folder name (drivesphere_updated, drivesphere, etc.) and host/port
if (!defined('BASE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'];

        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $projDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

        if (!empty($docRoot) && stripos($projDir, $docRoot) === 0) {
            $basePath = substr($projDir, strlen($docRoot));
        } else {
            $basePath = '/' . basename($projDir);
        }

        $basePath = '/' . trim($basePath, '/') . '/';
        if ($basePath === '//') {
            $basePath = '/';
        }
        define('BASE_URL', $scheme . $host . $basePath);
    } else {
        define('BASE_URL', 'http://localhost/' . basename(dirname(__DIR__)) . '/');
    }
}

// Admin registration is only allowed when this setup key is typed in.
// CHANGE THIS before your defense/demo and keep it private.
define('ADMIN_SETUP_KEY', 'DRIVESPHERE-ADMIN-2026');

// Business rules used for pricing
define('VAT_RATE', 0.12);
define('SECURITY_DEPOSIT', 5000);
