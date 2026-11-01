<?php
require_once __DIR__ . '/includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');
$defaultEmail = $_SESSION['registered_email'] ?? 'carlos@drivesphere.ph';
if (isset($_SESSION['registered_email'])) {
    unset($_SESSION['registered_email']);
}

if (!empty($_GET['confirmed'])) {
    require __DIR__ . '/login_confirm.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectFallback = 'login.php' . ($redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
    csrf_check($redirectFallback);
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Please enter a valid email and your password.');
        redirect($redirectFallback);
    }

    $pdo  = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        if (($user['role'] ?? '') === 'admin') {
            flash_set('info', 'Welcome, Administrator.');
            $adminTarget = get_safe_redirect('redirect', 'admin/dashboard.php');
            redirect($adminTarget);
        } else {
            $confirmUrl = 'login_confirm.php' . ($redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
            redirect($confirmUrl);
        }
    }

    flash_set('error', 'Incorrect email or password.');
    redirect($redirectFallback);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - User Account Login</title>
    <link rel="stylesheet" href="css/global.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page-body">

    <!-- Top Navigation Header -->
    <header class="auth-header">
        <a href="<?= BASE_URL ?>index.php" class="auth-brand">
            <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo" style="height: 38px; width: auto; object-fit: contain; border-radius: 6px;">
            <div class="auth-brand-text">
                <span class="auth-brand-title">DriveSphere</span>
                <span class="auth-brand-sub">PHILIPPINE MOBILITY &amp; FLEET</span>
            </div>
        </a>
        <div class="auth-nav-right">
            <a href="<?= BASE_URL ?>index.php" class="auth-nav-link">Public Portal</a>
            <a href="<?= BASE_URL ?>help.php" class="auth-nav-link">Help Center</a>
            <span class="auth-nav-divider">|</span>
            <a href="<?= is_customer() ? BASE_URL . 'customer/dashboard.php' : BASE_URL . 'login.php' ?>" class="auth-portal-pill">Customer Portal</a>
        </div>
    </header>

    <!-- Main Login Card Area -->
    <main class="auth-main-container">
        <div class="auth-card auth-card-login">
            <span class="auth-pill-badge">CLIENT GATEWAY</span>
            
            <h1 class="auth-card-title">User Account Login</h1>
            <p class="auth-card-sub">Welcome back to DriveSphere. Access your vehicle bookings, corporate vouchers, and personal rental itinerary.</p>

            <?php flash_show(); ?>

            <?php if (!empty($_GET['redirect']) || ($redirectTarget !== 'customer/dashboard.php')): ?>
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 4px solid #0284c7; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #1e3a8a; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-info" style="color: #0284c7; font-size: 16px; flex-shrink: 0;"></i>
                    <span>Please log in to your DriveSphere customer account to complete your reservation.</span>
                </div>
            <?php endif; ?>

            <?php if (is_customer()): ?>
                <div style="background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; font-size: 12px; color: #0369a1; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <div>
                        <i class="fa-solid fa-circle-check" style="color: #0284c7; margin-right: 4px;"></i>
                        Logged in as <strong><?= e($_SESSION['user_name'] ?? 'Customer') ?></strong>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="<?= BASE_URL . ltrim($redirectTarget, '/') ?>" style="background: #0284c7; color: #ffffff; padding: 4px 10px; border-radius: 4px; text-decoration: none; font-weight: 700; font-size: 11px;">
                            Proceed &rarr;
                        </a>
                        <a href="<?= BASE_URL ?>actions/logout.php" style="color: #64748b; font-size: 11px; text-decoration: underline;">
                            Log Out
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>login.php">
                <?= csrf_field() ?>
                <?php if ($redirectTarget !== 'customer/dashboard.php'): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                <?php endif; ?>

                <div class="auth-field-group">
                    <label class="auth-label">USERNAME OR REGISTERED EMAIL <span class="auth-req">*</span></label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-user auth-input-icon"></i>
                        <input type="email" name="email" id="emailInput" class="auth-input" placeholder="c.alvarez@enterprise.ph" value="<?= e($defaultEmail) ?>" required autocomplete="email">
                    </div>
                </div>

                <div class="auth-field-group" style="margin-bottom: 24px;">
                    <label class="auth-label">PASSWORD <span class="auth-req">*</span></label>
                    <div class="auth-input-wrapper">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input type="password" name="password" id="passwordInput" class="auth-input" placeholder="Enter your password" value="password123" required autocomplete="current-password">
                        <i class="fa-regular fa-eye auth-eye-icon" id="togglePwIcon" title="Toggle password visibility"></i>
                    </div>
                </div>

                <button type="submit" class="auth-btn-dark">
                    LOG IN &rarr;
                </button>
            </form>

            <div class="auth-card-footer">
                Don't have a personal or client account? 
                <a href="<?= BASE_URL ?>register.php<?= $redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="auth-link">Register Account</a>
            </div>
        </div>
    </main>

    <!-- Page Footer -->
    <footer class="auth-page-footer">
        &copy; 2025 DriveSphere Philippines Inc. All rights reserved. Enterprise Fleet Systems.
    </footer>

    <script>
        const toggleIcon = document.getElementById('togglePwIcon');
        const pwInput = document.getElementById('passwordInput');
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
