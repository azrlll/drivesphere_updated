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
    if (($_SESSION['user_role'] ?? '') === 'customer') {
        return false;
    }
    return (!empty($_SESSION['admin_user_id']) && ($_SESSION['admin_user_role'] ?? '') === 'admin')
        || (!empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin');
}

function is_customer() {
    if (($_SESSION['user_role'] ?? '') === 'admin') {
        return false;
    }
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
    unset($_SESSION['customer_user_id'], $_SESSION['customer_user_name'], $_SESSION['customer_user_email'], $_SESSION['customer_user_role']);
    $_SESSION['admin_user_id']    = (int)$user['id'];
    $_SESSION['admin_user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['admin_user_email'] = $user['email'];
    $_SESSION['admin_user_role']  = 'admin';

    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = 'admin';
}

function login_customer($user) {
    unset($_SESSION['admin_user_id'], $_SESSION['admin_user_name'], $_SESSION['admin_user_email'], $_SESSION['admin_user_role']);
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

/* ---------- vehicle image resolver ---------- */
function vehicle_image_url($pathOrUrl) {
    if (empty($pathOrUrl)) {
        return BASE_URL . 'images/DriveSphere_logo.jpg';
    }
    if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
        return $pathOrUrl;
    }
    return BASE_URL . ltrim($pathOrUrl, '/');
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

/* ---------- system settings & branches ---------- */
function get_system_setting($pdo, $key, $default = null) {
    static $settingsCache = [];
    if (!isset($settingsCache[$key])) {
        try {
            $st = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
            $st->execute([$key]);
            $val = $st->fetchColumn();
            $settingsCache[$key] = ($val !== false) ? $val : $default;
        } catch (Exception $e) {
            $settingsCache[$key] = $default;
        }
    }
    return $settingsCache[$key];
}

function get_all_branches($pdo, $activeOnly = true) {
    try {
        $sql = "SELECT * FROM branches" . ($activeOnly ? " WHERE is_active = 1" : "") . " ORDER BY id ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function get_branch_by_id_or_code($pdo, $idOrCode) {
    try {
        if (is_numeric($idOrCode)) {
            $st = $pdo->prepare("SELECT * FROM branches WHERE id = ?");
            $st->execute([(int)$idOrCode]);
        } else {
            $st = $pdo->prepare("SELECT * FROM branches WHERE code = ? OR name = ?");
            $st->execute([$idOrCode, $idOrCode]);
        }
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        return null;
    }
}

/* ---------- office hours & advance reservation policy validation ---------- */
function validate_booking_schedule($pdo, $pickupDateTime, $returnDateTime, $branchIdOrCode = null) {
    $now = time();
    $pickupTime = strtotime($pickupDateTime);
    $returnTime = strtotime($returnDateTime);

    if (!$pickupTime || !$returnTime) {
        return ['valid' => false, 'error' => 'Please provide valid pick-up and return dates and times.'];
    }

    if ($returnTime <= $pickupTime) {
        return ['valid' => false, 'error' => 'Return date and time must be after the pick-up date and time.'];
    }

    // 1. Past date & time validation (Phase 2A)
    if ($pickupTime < $now) {
        return ['valid' => false, 'error' => 'Pick-up date and time cannot be in the past.'];
    }

    // 2. Branch operating hours (Phase 2A - 8:00 AM to 8:00 PM PST)
    $branch = $branchIdOrCode ? get_branch_by_id_or_code($pdo, $branchIdOrCode) : null;
    $openStr = $branch ? $branch['opening_time'] : (get_system_setting($pdo, 'office_hours_start', '08:00') . ':00');
    $closeStr = $branch ? $branch['closing_time'] : (get_system_setting($pdo, 'office_hours_end', '20:00') . ':00');
    $opHoursLabel = $branch ? $branch['operating_hours'] : '8:00 AM – 8:00 PM';

    $pickupTimeOfDay = date('H:i:s', $pickupTime);
    $returnTimeOfDay = date('H:i:s', $returnTime);

    if ($pickupTimeOfDay < $openStr || $pickupTimeOfDay > $closeStr) {
        return ['valid' => false, 'error' => "Pick-up time must be within official operating hours ($opHoursLabel)."];
    }
    if ($returnTimeOfDay < $openStr || $returnTimeOfDay > $closeStr) {
        return ['valid' => false, 'error' => "Return time must be within official operating hours ($opHoursLabel)."];
    }

    // 3. Minimum one-hour lead time before pickup (Phase 2B)
    $leadMinHours = (float)get_system_setting($pdo, 'lead_time_min_hours', 1);
    $diffFromNowSeconds = $pickupTime - $now;
    if ($diffFromNowSeconds < ($leadMinHours * 3600)) {
        return ['valid' => false, 'error' => 'Please select a pickup time at least one hour from now.'];
    }

    // 4. Confirmed 48–72-Hour Advance Reservation Policy (Phase 3)
    $advMinHours = (float)get_system_setting($pdo, 'advance_min_hours', 48);
    $advMaxHours = (float)get_system_setting($pdo, 'advance_max_hours', 72);
    $diffHours = $diffFromNowSeconds / 3600;

    if ($diffHours < $advMinHours || $diffHours > $advMaxHours) {
        $leadStr = round($diffHours, 1);
        return [
            'valid' => false,
            'error' => "Advance Booking Notice: Reservations must be made $advMinHours to $advMaxHours hours before the scheduled pickup time. (Selected pickup is $leadStr hours from right now). Note: Same-day rental durations (e.g. 8 AM to 6 PM) are fully supported when booked at least {$advMinHours} hours in advance."
        ];
    }

    return ['valid' => true, 'error' => null];
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
