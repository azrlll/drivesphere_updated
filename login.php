<?php
require_once __DIR__ . '/includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');

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
            redirect($redirectTarget);
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
    <!-- FontAwesome for the icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page-bg">

    <!-- Simplified Login Header -->
    <header class="login-header">
        <div class="container lh-container">
            <a href="<?= BASE_URL ?>index.php" class="logo" style="text-decoration:none; color:inherit;">
                <img src="images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
                <div class="logo-text">
                    <span>DriveSphere</span>
                    <small>PHILIPPINE MOBILITY & FLEET</small>
                </div>
            </a>
            <div class="lh-links">
                <a href="<?= BASE_URL ?>index.php">Public Website</a>
                <a href="<?= BASE_URL ?>help.php">Help Center</a>
                <span class="active-portal-pill">Customer Portal</span>
            </div>
        </div>
    </header>

    <main class="login-main">
        <div class="login-card-wrapper">
            <!-- Top Blue Gradient Bar -->
            <div class="lc-top-gradient"></div>
            
            <div class="lc-header">
                <div class="lc-badge">CLIENT GATEWAY</div>
                <div class="lc-secure"><i class="fa-solid fa-shield-halved"></i> 256-Bit SSL Secured</div>
            </div>
            
            <h1>User Account Login</h1>
            <p class="login-subtitle">Welcome back to DriveSphere. Access your vehicle bookings, corporate vouchers, and personal rental itinerary.</p>

            <?php flash_show(); ?>

            <?php if (is_customer()): ?>
                <div style="background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #0369a1; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <i class="fa-solid fa-circle-check" style="color: #0284c7; margin-right: 6px;"></i>
                        Currently logged in as <strong><?= e($_SESSION['user_name'] ?? 'Customer') ?></strong>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="<?= BASE_URL . ltrim($redirectTarget, '/') ?>" style="background: #0284c7; color: #ffffff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            Proceed &rarr;
                        </a>
                        <a href="<?= BASE_URL ?>actions/logout.php" style="color: #64748b; font-size: 12px; text-decoration: underline;">
                            Log Out
                        </a>
                    </div>
                </div>
            <?php elseif (is_admin()): ?>
                <div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; font-size: 13px; color: #92400e; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <i class="fa-solid fa-shield-halved" style="color: #d97706; margin-right: 6px;"></i>
                        Currently logged in as <strong>Administrator</strong>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="<?= BASE_URL ?>admin/dashboard.php" style="background: #0f172a; color: #ffffff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            Admin Console &rarr;
                        </a>
                        <a href="<?= BASE_URL ?>actions/logout.php" style="color: #64748b; font-size: 12px; text-decoration: underline;">
                            Log Out
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <form class="login-form" method="post" action="<?= BASE_URL ?>login.php">
                <?= csrf_field() ?>
                <?php if ($redirectTarget !== 'customer/dashboard.php'): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>USERNAME OR REGISTERED EMAIL <span class="req">*</span></label>
                    <div class="input-icon-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="email" name="email" id="emailInput" placeholder="m.santos@gmail.com" value="carlos@drivesphere.ph" required>
                    </div>
                </div>
                
                <!-- PASSWORD INPUT WITH EYE ICON -->
                <div class="form-group" style="margin-bottom: 32px;">
                    <div class="label-row">
                        <label>PASSWORD <span class="req">*</span></label>
                        <a href="<?= BASE_URL ?>contact.php" class="forgot-pw">Forgot Password?</a>
                    </div>
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Enter your password" id="passwordInput" value="password123" required>
                        <i class="fa-regular fa-eye toggle-pw" id="togglePwIcon"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark-full">LOG IN <i class="fa-solid fa-arrow-right"></i></button>
            </form>

            <div class="lc-footer">
                <p>Don't have a personal or client account? <a href="<?= BASE_URL ?>register.php<?= $redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>">Register Account</a></p>
            </div>
        </div>
    </main>

    <script src="js/script.js"></script>
</body>
</html>