<?php
// Shared helpers: sessions, roles, flash messages, CSRF, pricing, availability.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . ltrim($path, '/'));
    exit;
}

function money($amount) {
    return '₱' . number_format((float)$amount, 2);
}

/* ---------- flash messages ---------- */
function flash_set($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}
function flash_show() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $class = $f['type'] === 'success' ? 'flash-success' : 'flash-error';
        echo '<div class="flash ' . $class . '">' . e($f['msg']) . '</div>';
    }
}

/* ---------- CSRF protection for every POST form ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_check($redirectFallback = null) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        if ($redirectFallback !== null) {
            redirect($redirectFallback);
        }
        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($ref)) {
            header('Location: ' . $ref);
            exit;
        }
        redirect('login.php');
    }

    $tokenSession = $_SESSION['csrf'] ?? '';
    $tokenPost    = $_POST['csrf'] ?? '';
    $valid = !empty($tokenSession) && !empty($tokenPost) && hash_equals($tokenSession, $tokenPost);

    if (!$valid) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        flash_set('error', 'Your session or security token expired. Please try again.');

        if ($redirectFallback !== null) {
            redirect($redirectFallback);
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($ref)) {
            $refPath = parse_url($ref, PHP_URL_PATH);
            if ($refPath) {
                header('Location: ' . $ref);
                exit;
            }
        }
        redirect('login.php');
    }
}

/* ---------- roles ---------- */
function is_admin() {
    return (!empty($_SESSION['admin_user_id']) && ($_SESSION['admin_user_role'] ?? '') === 'admin')
        || (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin');
}

function is_customer() {
    return (!empty($_SESSION['customer_user_id']) && ($_SESSION['customer_user_role'] ?? '') === 'customer')
        || (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'customer');
}

function is_logged_in() {
    return is_admin() || is_customer() || !empty($_SESSION['user_id']);
}

function admin_user_id() {
    if (!empty($_SESSION['admin_user_id'])) return (int)$_SESSION['admin_user_id'];
    if (($_SESSION['user_role'] ?? '') === 'admin') return (int)($_SESSION['user_id'] ?? 0);
    return 0;
}

function admin_user_name() {
    return $_SESSION['admin_user_name'] ?? (($_SESSION['user_role'] ?? '') === 'admin' ? ($_SESSION['user_name'] ?? 'Fleet Administrator') : 'Fleet Administrator');
}

function admin_user_email() {
    return $_SESSION['admin_user_email'] ?? (($_SESSION['user_role'] ?? '') === 'admin' ? ($_SESSION['user_email'] ?? 'admin@drivesphere.ph') : 'admin@drivesphere.ph');
}

function customer_user_id() {
    if (!empty($_SESSION['customer_user_id'])) return (int)$_SESSION['customer_user_id'];
    if (($_SESSION['user_role'] ?? '') === 'customer') return (int)($_SESSION['user_id'] ?? 0);
    return 0;
}

function customer_user_name() {
    return $_SESSION['customer_user_name'] ?? (($_SESSION['user_role'] ?? '') === 'customer' ? ($_SESSION['user_name'] ?? '') : '');
}

function customer_user_email() {
    return $_SESSION['customer_user_email'] ?? (($_SESSION['user_role'] ?? '') === 'customer' ? ($_SESSION['user_email'] ?? '') : '');
}

// Safely resolve the relative path of the current request from BASE_URL
function current_relative_url() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '/';
    if ($basePath !== '/' && str_starts_with($uri, $basePath)) {
        $uri = substr($uri, strlen($basePath));
    }
    return ltrim($uri, '/');
}

// Validates and extracts a safe internal redirection target
function get_safe_redirect($param = 'redirect', $default = 'customer/dashboard.php') {
    $target = trim($_GET[$param] ?? $_POST[$param] ?? '');
    if ($target === '') {
        return $default;
    }
    // Prevent open redirect vulnerabilities (no absolute URLs or protocol-relative slashes)
    if (preg_match('#^https?://#i', $target) || str_starts_with($target, '//') || str_starts_with($target, '\\')) {
        return $default;
    }
    return ltrim($target, '/');
}

// Customer pages: only logged-in customers.
function require_customer() {
    if (!is_customer()) {
        flash_set('error', 'Please log in with your customer account to continue.');
        $current = current_relative_url();
        if ($current !== '' && !str_starts_with($current, 'login.php') && !str_starts_with($current, 'actions/')) {
            redirect('login.php?redirect=' . urlencode($current));
        }
        redirect('login.php');
    }
}
// Admin pages and admin endpoints: only logged-in administrators.
function require_admin() {
    if (!is_admin()) {
        $current = current_relative_url();
        if ($current !== '' && !str_starts_with($current, 'admin/login.php') && !str_starts_with($current, 'admin/logout.php')) {
            redirect('admin/login.php?redirect=' . urlencode($current));
        }
        redirect('admin/login.php');
    }
}

// Start a fresh login session (prevents session fixation).
function login_user($user) {
    session_regenerate_id(true);
    if (($user['role'] ?? '') === 'admin') {
        login_admin($user);
    } else {
        login_customer($user);
    }
}

function login_admin($user) {
    $_SESSION['admin_user_id']    = (int)$user['id'];
    $_SESSION['admin_user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['admin_user_email'] = $user['email'];
    $_SESSION['admin_user_role']  = 'admin';

    // Set general session if not already occupied by an active customer
    if (empty($_SESSION['customer_user_id'])) {
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = 'admin';
    }
}

function login_customer($user) {
    $_SESSION['customer_user_id']    = (int)$user['id'];
    $_SESSION['customer_user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['customer_user_email'] = $user['email'];
    $_SESSION['customer_user_role']  = 'customer';

    // Primary user session points to customer
    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = 'customer';
}

function logout_admin() {
    unset($_SESSION['admin_user_id'], $_SESSION['admin_user_name'], $_SESSION['admin_user_email'], $_SESSION['admin_user_role']);
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
    }
    if (empty($_SESSION['customer_user_id']) && empty($_SESSION['user_id'])) {
        logout_user();
    }
}

function logout_customer() {
    unset($_SESSION['customer_user_id'], $_SESSION['customer_user_name'], $_SESSION['customer_user_email'], $_SESSION['customer_user_role']);
    if (($_SESSION['user_role'] ?? '') === 'customer') {
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
    }
    if (empty($_SESSION['admin_user_id']) && empty($_SESSION['user_id'])) {
        logout_user();
    }
}

// Remove all session data and the session cookie.
function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------- pricing ---------- */
function rental_days($pickup, $return) {
    $seconds = strtotime($return) - strtotime($pickup);
    return max(1, (int)ceil($seconds / 86400));
}
function calc_price($dailyRate, $days) {
    $base = round($dailyRate * $days, 2);
    $vat  = round($base * VAT_RATE, 2);
    return ['days' => $days, 'base' => $base, 'vat' => $vat, 'total' => $base + $vat, 'deposit' => SECURITY_DEPOSIT];
}

/* ---------- availability ---------- */
// A vehicle is free when it is not in maintenance and no approved
// (reserved / en_route) rental overlaps the requested dates.
function vehicle_is_available($pdo, $vehicleId, $pickup, $return, $ignoreReservationId = 0) {
    $v = $pdo->prepare("SELECT status FROM vehicles WHERE id = ?");
    $v->execute([$vehicleId]);
    $row = $v->fetch();
    if (!$row || $row['status'] === 'maintenance') {
        return false;
    }
    $q = $pdo->prepare("SELECT COUNT(*) FROM reservations
        WHERE vehicle_id = ? AND id <> ? AND status IN ('reserved','en_route')
        AND pickup_date < ? AND return_date > ?");
    $q->execute([$vehicleId, $ignoreReservationId, $return, $pickup]);
    return $q->fetchColumn() == 0;
}

/* ---------- notifications ---------- */
function add_notification($pdo, $userId, $audience, $type, $title, $message, $link = null) {
    $s = $pdo->prepare("INSERT INTO notifications (user_id, audience, type, title, message, link) VALUES (?,?,?,?,?,?)");
    $s->execute([$userId, $audience, $type, $title, $message, $link]);
}

/* ---------- status badges ---------- */
function status_label($status) {
    $map = ['pending' => 'Pending', 'reserved' => 'Reserved', 'en_route' => 'En Route',
            'returned' => 'Returned', 'completed' => 'Completed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];
    return $map[$status] ?? ucfirst($status);
}
function status_badge($status) {
    $cls = ['pending' => 'b-amber', 'reserved' => 'b-amber', 'en_route' => 'b-blue', 'returned' => 'b-green',
            'completed' => 'b-green', 'rejected' => 'b-red', 'cancelled' => 'b-grey'][$status] ?? 'b-grey';
    return '<span class="badge ' . $cls . '">' . e(status_label($status)) . '</span>';
}
