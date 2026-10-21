<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_admin()) { redirect('admin/dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/register.php');
    $merchant = trim($_POST['merchant_name'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $setupKey = $_POST['setup_key'] ?? '';

    $error = '';
    // 1) Setup key check: only people who know the key can create an admin account.
    if (!hash_equals(ADMIN_SETUP_KEY, $setupKey))      $error = 'Invalid admin setup key.';
    elseif ($merchant === '' || $fullName === '')      $error = 'Please fill in your merchant name and full name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Please enter a valid email address.';
    elseif (strlen($password) < 8)                     $error = 'Password must be at least 8 characters.';
    elseif ($password !== $confirm)                    $error = 'Passwords do not match.';
    elseif (empty($_POST['agree']))                    $error = 'You must accept the agreement.';

    if ($error === '') {
        $pdo = getDBConnection();
        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $dup->execute([$email]);
        if ($dup->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $firstAdmin = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() === 0;
            $parts = explode(' ', $fullName, 2);
            $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?,?,?,?, 'admin')")
                ->execute([$parts[0], $parts[1] ?? '-', $email, password_hash($password, PASSWORD_DEFAULT)]);
            if ($firstAdmin) {
                $pdo->prepare("UPDATE merchant_profile SET merchant_name = ?, contact_name = ?, email = ? WHERE id = 1")->execute([$merchant, $fullName, $email]);
            }
            flash_set('success', 'Administrator account created. Please log in.');
            redirect('admin/login.php');
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
    <title>DriveSphere Enterprise - Admin Registration</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/global.css">
    <!-- FontAwesome for the icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="admin-page-bg">

    <!-- Enterprise Header -->
    <header class="enterprise-header">
        <div class="container eh-container">
            
            <!-- 1. Left: Enterprise Logo -->
            <div class="ent-logo-group">
                <span class="site-logo">
                    <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Enterprise" style="height: 44px; width: auto; object-fit: contain; border-radius: 8px;">
                </span>
                <div class="ent-logo-text">
                    <div class="ent-logo-title">
                        DriveSphere <span class="ent-badge">ENTERPRISE</span>
                    </div>
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

    <main class="admin-main" style="padding: 48px 20px;">
        <div class="register-card-wrapper">
            
            <!-- Left Side: Registration Form -->
            <div class="reg-form-side">
                <div class="reg-top-bar">
                    <div class="lc-badge" style="background: #e0e7ff; color: #3730a3; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 6px; margin-right: 4px; color: #4f46e5;"></i> SCREEN P16 • FLEET PARTNER ENROLLMENT</div>
                    <div class="step-indicator" style="display: flex; align-items: center; gap: 8px;">
                        STEP 1 OF 2 
                        <div style="display: flex; gap: 4px;">
                            <div style="width: 16px; height: 4px; background: #0f172a; border-radius: 2px;"></div>
                            <div style="width: 16px; height: 4px; background: #cbd5e1; border-radius: 2px;"></div>
                        </div>
                    </div>
                </div>

                <h1>Admin Account Registration</h1>
                <p class="login-subtitle">Register your merchant or fleet management profile to oversee commercial vehicle telemetry, dispatch orders, and branch operations.</p>

                <?php flash_show(); ?>
                <form class="login-form" id="adminRegisterForm" method="post" action="<?= BASE_URL ?>admin/register.php">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>YOUR MERCHANT NAME <span class="req">*</span></label>
                        <div class="input-icon-wrapper admin-input">
                            <i class="fa-regular fa-building"></i>
                            <input type="text" name="merchant_name" placeholder="e.g. DriveSphere Mobility" required maxlength="120">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>ADMIN SETUP KEY <span class="req">*</span></label>
                        <div class="input-icon-wrapper admin-input">
                            <i class="fa-solid fa-key"></i>
                            <input type="password" name="setup_key" placeholder="Provided by the system owner" required autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>FULL NAME <span class="req">*</span></label>
                        <div class="input-icon-wrapper admin-input">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="full_name" placeholder="e.g. Eduardo Dela Rosa" required maxlength="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>EMAIL ADDRESS <span class="req">*</span></label>
                        <div class="input-icon-wrapper admin-input">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="e.g. admin@drivesphere.ph" required>
                        </div>
                    </div>

                    <div class="form-row-2col" style="margin-bottom: 24px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>PASSWORD <span class="req">*</span></label>
                            <div class="input-icon-wrapper admin-input">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" placeholder="Min. 8 characters" class="standard-input" id="adminRegPassword" name="password" required minlength="8">
                                <i class="fa-regular fa-eye toggle-pw" title="Toggle Password Visibility"></i>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>CONFIRM PASSWORD <span class="req">*</span></label>
                            <div class="input-icon-wrapper admin-input">
                                <i class="fa-solid fa-arrow-rotate-right"></i>
                                <input type="password" placeholder="Repeat password" class="standard-input" id="adminRegConfirmPassword" name="confirm_password" required minlength="8">
                                <i class="fa-regular fa-eye toggle-pw" title="Toggle Password Visibility"></i>
                            </div>
                        </div>
                    </div>

                    <div class="checkbox-row terms-row">
                        <input type="checkbox" id="agreeAdminTerms" name="agree" value="1" required style="accent-color: #0f172a;">
                        <label for="agreeAdminTerms">I agree to the DriveSphere Merchant Master Service Agreement, Telematics Tracking Protocol, and confirm compliance with Philippine Data Privacy Act (RA 10173).</label>
                    </div>

                    <button type="submit" class="btn-dark-full" style="background: #0f172a;">REGISTER ACCOUNT <i class="fa-solid fa-arrow-right"></i></button>
                </form>

                <div class="reg-form-footer">
                    <span>Already registered as an Administrator?</span> 
                    <a href="<?= BASE_URL ?>admin/login.php" class="admin-redirect-link">Sign in to Admin Portal &rarr;</a>
                </div>
            </div>

            <!-- Right Side: Info Panel -->
            <div class="reg-info-side" style="background: #0f172a;">
                <div class="info-badge" style="background: rgba(255,255,255,0.1); border: none; color: #cbd5e1; font-size: 10px; letter-spacing: 0.5px;"><i class="fa-solid fa-network-wired"></i> ENTERPRISE FLEET NETWORK • COMMAND TIER</div>
                
                <h2 style="font-size: 32px;">Full-Spectrum Fleet Intelligence</h2>
                <p>Empower your transport organization with real-time OBD-II telemetry, automated dispatch routing, LTFRB compliance logs, and instant roadside immobilization.</p>
                
                <div class="info-feature-list" style="gap: 20px; margin-bottom: 32px;">
                    <div class="info-feature">
                        <div class="feat-icon" style="background: transparent; color: #10b981; font-size: 16px;"><i class="fa-regular fa-circle-check"></i></div>
                        <div class="feat-text">
                            <strong>Centralized Multi-Vehicle Telemetry</strong>
                            <span>Monitor live GPS, fuel levels, tire pressure, and scheduled maintenance alerts across archipelago networks.</span>
                        </div>
                    </div>
                    <div class="info-feature">
                        <div class="feat-icon" style="background: transparent; color: #38bdf8; font-size: 16px;"><i class="fa-regular fa-circle-check"></i></div>
                        <div class="feat-text">
                            <strong>Automated B2B Billing & BIR Forms</strong>
                            <span>Instant 2307 issuance, consolidated corporate billing, and automated digital receipts directly to accounting.</span>
                        </div>
                    </div>
                    <div class="info-feature">
                        <div class="feat-icon" style="background: transparent; color: #94a3b8; font-size: 16px;"><i class="fa-regular fa-circle-check"></i></div>
                        <div class="feat-text">
                            <strong>24/7 Priority Emergency Dispatch</strong>
                            <span>Integrated hotline and immediate replacement vehicle mobilization across Luzon & Visayas command zones.</span>
                        </div>
                    </div>
                </div>

                <!-- Telemetry Stats Box -->
                <div class="admin-reg-stats-box">
                    <div class="ars-header">
                        <span>ACTIVE TELEMATICS STREAM</span>
                        <span class="ars-sla"><i class="fa-solid fa-circle"></i> 99.98% SLA</span>
                    </div>
                    <div class="ars-metrics">
                        <div class="ars-metric">
                            <strong>1,480+</strong>
                            <span>Active NCR Units</span>
                        </div>
                        <div class="ars-metric">
                            <strong>4.2 ms</strong>
                            <span>Telemetry Latency</span>
                        </div>
                        <div class="ars-metric">
                            <strong>0 Incidents</strong>
                            <span>Unresolved Critical</span>
                        </div>
                    </div>
                </div>

                <div class="info-footer" style="border-top: none; padding-top: 0;">
                    <span><i class="fa-solid fa-certificate"></i> Official LTFRB Registered Fleet Operations System • SEC Certified Enterprise</span>
                </div>
            </div>

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