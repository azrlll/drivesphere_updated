<?php
require_once __DIR__ . '/../includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'admin/dashboard.php');
if (is_admin()) { redirect($redirectTarget); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminFallback = 'admin/login.php' . ($redirectTarget !== 'admin/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
    csrf_check($adminFallback);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        login_user($user);
        if (($user['role'] ?? '') === 'admin') {
            redirect($redirectTarget);
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
    <title>DriveSphere Enterprise - Admin Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/global.css">
    <!-- Restored FontAwesome so your icons load properly -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-page-bg">

    <!-- Enterprise Header -->
    <header class="enterprise-header">
        <div class="container eh-container">
            
            <!-- 1. Left: Enterprise Logo (Restored the correct structure) -->
            <div class="logo">
                <span class="site-logo">
                    <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Enterprise" class="header-logo-img">
                </span>
                <div class="ent-logo-text">
                    <div class="ent-logo-title">DriveSphere <span class="ent-badge">ENTERPRISE</span></div>
                    <div class="ent-logo-subtitle">Philippine Mobility & Fleet</div>
                </div>
            </div>

            <!-- 2. Center: Gateway Links -->
            <div class="eh-center-links"><span class="ssl-pill"><i class="fa-solid fa-lock"></i> 256-Bit SSL Secured</span></div>

            <!-- 3. Right: Command Profile -->
            <div class="eh-right-links">
                <div class="eh-command-text">
                    <strong>ENTERPRISE FLEET COMMAND</strong>
                    <span>Admin Portal Gateway</span>
                </div>
                
                
            </div>
        </div>
    </header>

    <main class="admin-main">
        <div class="admin-card-wrapper">
            <!-- Top Blue Bar -->
            <div class="lc-top-gradient" style="background: #0ea5e9;"></div>
            
            <div class="cc-header" style="justify-content: space-between; margin-bottom: 24px;">
                <div class="lc-badge">FLEET COMMAND GATEWAY</div>
                <div class="lc-secure" style="color: #0f172a;"><i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> 256-Bit SSL Encrypted</div>
            </div>
            
            <h1>Administrator Login</h1>
            <p class="login-subtitle">Enter your enterprise credentials to access DriveSphere Fleet Dispatch, Telematics Console, and Operational Controls.</p>

            <!-- Stats Row -->
            <div class="admin-stats-row">
                <div class="stat-box">
                    <span>NCR NODES</span>
                    <strong>14/14</strong>
                </div>
                <div class="stat-box">
                    <span>ACTIVE UNITS</span>
                    <strong style="color: #0284c7;">1,842</strong>
                </div>
                <div class="stat-box">
                    <span>AUTH STATUS</span>
                    <strong style="color: #10b981;">Standby</strong>
                </div>
            </div>

            <?php flash_show(); ?>
            <form class="login-form" id="adminLoginForm" method="post" action="<?= BASE_URL ?>admin/login.php" novalidate>
                <?= csrf_field() ?>
                <?php if ($redirectTarget !== 'admin/dashboard.php'): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>ADMINISTRATOR USERNAME OR WORKSTATION ID <span class="req">*</span></label>
                    <div class="input-icon-wrapper admin-input">
                        <i class="fa-solid fa-id-badge"></i>
                        <input type="email" name="email" placeholder="admin@drivesphere.ph" required>
                    </div>
                </div>
                
                <!-- PASSWORD INPUT -->
                <div class="form-group">
                    <div class="label-row">
                        <label>PASSWORD <span class="req">*</span></label>
                        Forgot Credentials?
                    </div>
                    <div class="input-icon-wrapper admin-input">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" placeholder="Enter your password" id="adminPasswordInput" name="password" required>
                        <!-- Interactive eye icon toggle -->
                        <i class="fa-regular fa-eye toggle-pw" id="toggleAdminPwIcon"></i>
                    </div>
                </div>
                <button type="submit" class="btn-dark-full" style="background: #0f172a; width: 100%;">LOGIN <i class="fa-solid fa-arrow-right"></i></button>
            </form>

                <!-- ==========================================
                    FIXED SEPARATED REDIRECT LINKS 
                    ========================================== -->
            <div class="lc-footer">
                <p>Need an administrator account? <a href="<?= BASE_URL ?>admin/register.php">Register with a setup key</a></p>
            </div>
            
            <div class="admin-card-bottom-bar" style="margin-top: 32px;">
                <span><i class="fa-solid fa-microchip"></i> FIPS 140-2 Level 3 Ready</span>
                <span><i class="fa-solid fa-server"></i> NCR Hub #02</span>
            </div>
        </div>
        
        <div class="admin-trust-badges">
            <span><i class="fa-solid fa-file-signature"></i> NPC Registration PH-NCR-2025</span>
            <span class="dot">•</span>
            <span><i class="fa-regular fa-circle-check"></i> SOC-2 Type II Certified</span>
        </div>
    </main>

    <!-- Enterprise Footer -->
    <footer class="enterprise-footer">
        <div class="container ef-container">
            <div class="ef-col brand-col">
                <h3>DriveSphere Philippines Inc.</h3>
                <p>Enterprise Fleet Systems & Automated Logistics Infrastructure for institutional operations across Metro Manila, Cebu, and Davao network hubs.</p>
                <div class="ef-contact"><i class="fa-solid fa-headset"></i> 24/7 Hotline: (02) 8888-DRIVE</div>
            </div>
            <div class="ef-col">
                <h4>COMPLIANCE & TRUST</h4>
                <ul>
                    <li><i class="fa-solid fa-shield-check"></i> Privacy Compliance (RA 10173)</li>
                    <li><i class="fa-solid fa-shield-halved"></i> Enterprise Security Terms</li>
                    <li><i class="fa-solid fa-database"></i> Data Residency (PH NCR Zone)</li>
                </ul>
            </div>
            <div class="ef-col">
                <h4>FLEET INFRASTRUCTURE</h4>
                <ul>
                    <li>NOC Telemetry Services</li>
                    <li>Fleet Command API Status</li>
                    <li>Disaster Recovery Protocol</li>
                </ul>
            </div>
        </div>
        <div class="ef-bottom">
            <div class="container ef-bottom-container">
                <div>© 2026 DriveSphere Philippines Inc. All rights reserved. Registered Enterprise Fleet Portal.</div>
                <div class="ef-links">
                    Privacy Policy
                    Security Protocol
                    Help Center
                </div>
            </div>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>js/script.js"></script>
</body>
</html>