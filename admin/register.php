<?php
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_GET['confirmed'])) {
    require __DIR__ . '/register_confirm.php';
    exit;
}

if (is_admin()) {
    redirect('admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/register.php');
    $merchant = trim($_POST['merchant_name'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $setupKey = $_POST['setup_key'] ?? ADMIN_SETUP_KEY;
    $agree    = !empty($_POST['agree']);

    $error = '';
    if (!hash_equals(ADMIN_SETUP_KEY, $setupKey)) {
        $error = 'Invalid admin setup key.';
    } elseif ($merchant === '' || $fullName === '') {
        $error = 'Please fill in your merchant name and full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!$agree) {
        $error = 'You must accept the agreement.';
    }

    if ($error === '') {
        $pdo = getDBConnection();
        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $dup->execute([$email]);
        if ($dup->fetch()) {
            $error = 'That email is already registered. Please log in instead.';
        } else {
            $parts = explode(' ', $fullName, 2);
            $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?,?,?,?, 'admin')")
                ->execute([$parts[0], $parts[1] ?? '-', $email, password_hash($password, PASSWORD_DEFAULT)]);

            $_SESSION['registered_admin_merchant'] = $merchant;
            $_SESSION['registered_admin_name'] = $fullName;
            $_SESSION['registered_admin_email'] = $email;

            redirect('admin/register_confirm.php');
        }
    }
    flash_set('error', $error);
    redirect('admin/register.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere Enterprise - Admin Account Registration</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-auth-body">

    <!-- Enterprise Header -->
    <header class="admin-auth-header">
        <div class="admin-header-brand-wrap">
            <a href="<?= BASE_URL ?>index.php" class="admin-header-logo-link">
                <img src="<?= BASE_URL ?>images/admin_car_logo.png" alt="DriveSphere Enterprise" class="admin-header-logo-img">
                <div class="admin-header-text">
                    <div class="admin-header-title-row">
                        <span class="admin-header-title">DriveSphere</span>
                        <span class="admin-enterprise-pill">ENTERPRISE</span>
                    </div>
                    <span class="admin-header-sub">Philippine Mobility &amp; Fleet</span>
                </div>
            </a>
            <span style="color: #cbd5e1; font-size: 16px; user-select: none;">|</span>
            <a href="<?= BASE_URL ?>index.php" class="admin-portal-gateway-btn" title="Back to Public Portal">
                <span>Portal</span>
                <span>Gateway</span>
            </a>
        </div>

        <div class="admin-header-right">
            <div class="admin-command-text">
                <span class="admin-command-title">ENTERPRISE FLEET COMMAND</span>
                <span class="admin-command-sub">Admin Portal Gateway</span>
            </div>
            <a href="<?= BASE_URL ?>help.php" class="admin-help-btn">
                <i class="fa-regular fa-circle-question" style="color: #64748b;"></i>
                <span>Help Center</span>
            </a>
            <a href="<?= BASE_URL ?>index.php" class="admin-user-avatar-btn" title="Switch Portal Gateway">
                <i class="fa-solid fa-user"></i>
            </a>
        </div>
    </header>

    <!-- Main Registration Card Area -->
    <main class="auth-main-container">
        <div class="admin-auth-card admin-auth-card-register">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span class="admin-auth-pill-badge">FLEET PARTNER ENROLLMENT</span>
                <span style="font-size: 11px; font-weight: 600; color: #64748b; letter-spacing: 0.5px;">STEP 1 OF 2</span>
            </div>

            <h1 class="auth-card-title">Admin Account Registration</h1>
            <p class="auth-card-sub">Register your merchant or fleet management profile to oversee commercial vehicle telemetry, dispatch orders, and branch operations.</p>

            <?php flash_show(); ?>

            <form method="post" action="<?= BASE_URL ?>admin/register.php" id="adminRegisterForm">
                <?= csrf_field() ?>
                <input type="hidden" name="setup_key" value="<?= ADMIN_SETUP_KEY ?>">

                <div class="auth-field-group">
                    <label class="auth-label">YOUR MERCHANT NAME <span class="auth-req">*</span></label>
                    <div class="admin-input-wrapper">
                        <i class="fa-regular fa-building auth-input-icon"></i>
                        <input type="text" name="merchant_name" class="auth-input" placeholder="e.g. Metro Manila Express Logistics Corp." required maxlength="120">
                    </div>
                </div>

                <div class="auth-field-group">
                    <label class="auth-label">FULL NAME <span class="auth-req">*</span></label>
                    <div class="admin-input-wrapper">
                        <i class="fa-regular fa-user auth-input-icon"></i>
                        <input type="text" name="full_name" class="auth-input" placeholder="e.g. Eduardo Dela Rosa" required maxlength="100" autocomplete="name">
                    </div>
                </div>

                <div class="auth-field-group">
                    <label class="auth-label">EMAIL ADDRESS <span class="auth-req">*</span></label>
                    <div class="admin-input-wrapper">
                        <i class="fa-regular fa-envelope auth-input-icon"></i>
                        <input type="email" name="email" class="auth-input" placeholder="e.g. e.delarosa@metromobility.ph" required autocomplete="email">
                    </div>
                </div>

                <div class="auth-2col-row">
                    <div class="auth-field-group" style="margin-bottom: 0;">
                        <label class="auth-label">PASSWORD <span class="auth-req">*</span></label>
                        <div class="admin-input-wrapper">
                            <i class="fa-solid fa-lock auth-input-icon"></i>
                            <input type="password" name="password" id="adminRegPassword" class="auth-input" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
                            <i class="fa-regular fa-eye auth-eye-icon toggle-admin-pw" data-target="adminRegPassword"></i>
                        </div>
                    </div>
                    <div class="auth-field-group" style="margin-bottom: 0;">
                        <label class="auth-label">CONFIRM PASSWORD <span class="auth-req">*</span></label>
                        <div class="admin-input-wrapper">
                            <i class="fa-solid fa-arrow-rotate-right auth-input-icon"></i>
                            <input type="password" name="confirm_password" id="adminRegConfirmPassword" class="auth-input" placeholder="Repeat password" required minlength="8" autocomplete="new-password">
                            <i class="fa-regular fa-eye auth-eye-icon toggle-admin-pw" data-target="adminRegConfirmPassword"></i>
                        </div>
                    </div>
                </div>

                <div class="auth-checkbox-row" style="margin-top: 18px;">
                    <input type="checkbox" id="agreeAdminTerms" name="agree" value="1" required>
                    <label for="agreeAdminTerms">
                        I agree to the DriveSphere <a href="<?= BASE_URL ?>policies.php" class="auth-link">Merchant Master Service Agreement</a>, <a href="<?= BASE_URL ?>policies.php#article-4" class="auth-link">Telematics Tracking Protocol</a>, and confirm compliance with Philippine Data Privacy Act (RA 10173).
                    </label>
                </div>

                <button type="submit" class="auth-btn-dark">
                    REGISTER ACCOUNT &rarr;
                </button>
            </form>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; font-size: 12.5px; color: #64748b; flex-wrap: wrap; gap: 8px;">
                <span>Already registered as an Administrator?</span>
                <a href="<?= BASE_URL ?>admin/login.php" style="font-weight: 700; color: #0284c7; text-decoration: none;">Sign in to Admin Portal &rarr;</a>
            </div>
        </div>
    </main>

    <!-- Enterprise Footer -->
    <footer class="admin-auth-footer">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="margin-bottom: 24px;">
                <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;">DriveSphere Philippines Inc.</h3>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 10px 0; max-width: 520px; line-height: 1.5;">Enterprise Fleet Systems &amp; Automated Logistics Infrastructure for institutional operations across Metro Manila, Cebu, and Davao network hubs.</p>
                <div style="font-size: 12px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-headset"></i> 24/7 Hotline: (02) 8888-DRIVE
                </div>
            </div>
            <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; font-size: 11px; color: #64748b;">
                <div>&copy; 2025 DriveSphere Philippines Inc. All rights reserved. Registered Enterprise Fleet Portal.</div>
                <div style="display: flex; gap: 20px;">
                    <a href="<?= BASE_URL ?>policies.php" style="color: #64748b; text-decoration: none; font-weight: 600;">Privacy Policy</a>
                    <a href="<?= BASE_URL ?>help.php" style="color: #64748b; text-decoration: none; font-weight: 600;">Help Center</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('.toggle-admin-pw').forEach(function(icon) {
            icon.addEventListener('click', function() {
                var targetId = icon.getAttribute('data-target');
                var input = document.getElementById(targetId);
                if (input) {
                    var isPw = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPw ? 'text' : 'password');
                    icon.classList.toggle('fa-eye', !isPw);
                    icon.classList.toggle('fa-eye-slash', isPw);
                }
            });
        });
    </script>
</body>
</html>
