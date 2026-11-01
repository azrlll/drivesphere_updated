<?php
// ---------------------------------------------------------------
// DriveSphere Application Configuration
// Timezone: Asia/Manila (PST - Philippine Standard Time)
// ---------------------------------------------------------------
date_default_timezone_set('Asia/Manila');

// Determine HTTPS scheme (handles Cloudflare and reverse proxies)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    ini_set('session.gc_maxlifetime', 2592000); // 30 days
    ini_set('session.cookie_lifetime', 2592000);
    session_set_cookie_params([
        'lifetime' => 2592000,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Buffer output so pages can still redirect after the layout is included.
ob_start();

// ---------------------------------------------------------------
// Database Configuration
// ---------------------------------------------------------------
// Detect if running in local environment (XAMPP / localhost / CLI)
$isLocalEnv = empty($_SERVER['HTTP_HOST'])
    || in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'], true)
    || (function_exists('str_starts_with') ? str_starts_with($_SERVER['HTTP_HOST'], 'localhost:') : strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0)
    || (function_exists('str_starts_with') ? str_starts_with($_SERVER['HTTP_HOST'], '127.0.0.1:') : strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0);

if (!$isLocalEnv) {
    // 1. Production Hosting (InfinityFree / drivesphere.free.nf):
    //    Loads live database credentials from untracked config.production.php
    if (file_exists(__DIR__ . '/config.production.php')) {
        require_once __DIR__ . '/config.production.php';
    } elseif (file_exists(__DIR__ . '/config.local.php')) {
        require_once __DIR__ . '/config.local.php';
    }
} else {
    // 2. Local Development (XAMPP): optional custom local override
    if (file_exists(__DIR__ . '/config.local.php')) {
        require_once __DIR__ . '/config.local.php';
    }
}

// 3. Fallback to Safe Defaults (Local XAMPP Development)
// Real production credentials should NEVER be committed to Git.
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'drivesphere');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Dynamically detect BASE_URL so links, assets, and redirects work under any folder name (drivesphere_updated, drivesphere, etc.) and host/port
if (!defined('BASE_URL')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $scheme = $isHttps ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'];

        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        $projDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

        // Check if deployed directly into standard webroot (e.g. htdocs, public_html on InfinityFree)
        $projBaseName = basename($projDir);
        if ($docRoot === $projDir || in_array(strtolower($projBaseName), ['htdocs', 'public_html', 'www'], true) || stripos($host, 'free.nf') !== false) {
            $basePath = '/';
        } elseif (!empty($docRoot) && stripos($projDir, $docRoot) === 0) {
            $basePath = substr($projDir, strlen($docRoot));
        } else {
            $basePath = '/' . $projBaseName;
        }

        $basePath = '/' . trim($basePath, '/') . '/';
        if ($basePath === '//') {
            $basePath = '/';
        }
        define('BASE_URL', $scheme . $host . $basePath);
    } else {
        define('BASE_URL', 'https://drivesphere.free.nf/');
    }
}

// Admin registration is only allowed when this setup key is typed in.
// CHANGE THIS before your defense/demo and keep it private.
define('ADMIN_SETUP_KEY', 'DRIVESPHERE-ADMIN-2026');

// Business rules used for pricing
define('VAT_RATE', 0.12);
define('SECURITY_DEPOSIT', 5000);
