<?php
require_once __DIR__ . '/../includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'admin/dashboard.php');

if (!empty($_GET['confirmed'])) {
    require __DIR__ . '/login_confirm.php';
    exit;
}

if (is_admin() && empty($_GET['switch'])) {
    redirect($redirectTarget);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminFallback = 'admin/login.php' . ($redirectTarget !== 'admin/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
    csrf_check($adminFallback);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Please enter a valid administrator email and password.');
        redirect($adminFallback);
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        if (($user['role'] ?? '') === 'admin') {
            $confirmUrl = 'admin/login_confirm.php' . ($redirectTarget !== 'admin/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
            redirect($confirmUrl);
        } else {
            flash_set('info', 'Welcome. You have been directed to your Customer Portal.');
            redirect('customer/dashboard.php');
        }
    }
    flash_set('error', 'Incorrect administrator email or password.');
    redirect($adminFallback);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere Enterprise - Administrator Login</title>
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

    <!-- Main Administrator Login Card -->
    <main class="auth-main-container">
        <div class="admin-auth-card admin-auth-card-login">
            <span class="admin-auth-pill-badge">FLEET COMMAND GATEWAY</span>

            <h1 class="auth-card-title">Administrator Login</h1>
            <p class="auth-card-sub">Enter your enterprise credentials to access DriveSphere Fleet Dispatch, Telematics Console, and Operational Controls.</p>

            <?php flash_show(); ?>

            <form method="post" action="<?= BASE_URL ?>admin/login.php" id="adminLoginForm">
                <?= csrf_field() ?>
                <?php if ($redirectTarget !== 'admin/dashboard.php'): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                <?php endif; ?>

                <div class="auth-field-group">
                    <label class="auth-label">ADMINISTRATOR USERNAME OR WORKSTATION ID <span class="auth-req">*</span></label>
                    <div class="admin-input-wrapper">
                        <i class="fa-regular fa-id-badge auth-input-icon"></i>
                        <input type="email" name="email" id="adminEmailInput" class="auth-input" placeholder="admin.dispatch@drivesphere.ph" value="admin@drivesphere.ph" required autocomplete="email">
                    </div>
                </div>

                <div class="auth-field-group" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="auth-label" style="margin-bottom: 0;">PASSWORD <span class="auth-req">*</span></label>
                        <a href="<?= BASE_URL ?>help.php" style="font-size: 11px; font-weight: 600; color: #0284c7; text-decoration: none;">Forgot Credentials?</a>
                    </div>
                    <div class="admin-input-wrapper">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input type="password" name="password" id="adminPasswordInput" class="auth-input" placeholder="EnterpriseSecret2025!" value="password123" required autocomplete="current-password">
                        <i class="fa-regular fa-eye auth-eye-icon" id="toggleAdminPwIcon" title="Toggle password visibility"></i>
                    </div>
                </div>

                <button type="submit" class="auth-btn-dark">
                    LOG IN &rarr;
                </button>
            </form>

            <div class="auth-card-footer">
                Don't have a registered Fleet Administrator account? 
                <a href="<?= BASE_URL ?>admin/register.php<?= $redirectTarget !== 'admin/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" style="font-weight: 700; color: #0284c7; text-decoration: none;">Register Account</a>
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
        const toggleIcon = document.getElementById('toggleAdminPwIcon');
        const pwInput = document.getElementById('adminPasswordInput');
        if (toggleIcon && pwInput) {
            toggleIcon.addEventListener('click', function() {
                const isPw = pwInput.getAttribute('type') === 'password';
                pwInput.setAttribute('type', isPw ? 'text' : 'password');
                toggleIcon.classList.toggle('fa-eye', !isPw);
                toggleIcon.classList.toggle('fa-eye-slash', isPw);
            });
        }
    </script>
</body>
</html>
