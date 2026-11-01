<?php
require_once __DIR__ . '/../includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'admin/dashboard.php');
$targetUrl = BASE_URL . ltrim($redirectTarget, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere Enterprise - Session Authenticated</title>
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

    <!-- Main Authentication Confirmation Card -->
    <main class="auth-main-container">
        <div class="admin-auth-card admin-auth-card-confirm" style="text-align: center;">
            <div style="margin-bottom: 22px;">
                <span class="admin-auth-pill-badge">ADMIN SESSION AUTHENTICATED</span>
            </div>

            <div style="display: flex; justify-content: center; margin-bottom: 22px;">
                <img src="<?= BASE_URL ?>images/admin_logged_in_circle.png" alt="Authenticated" style="width: 88px; height: 88px; display: block; border-radius: 50%;">
            </div>

            <h1 class="auth-card-title" style="font-size: 26px; line-height: 1.25; margin-bottom: 12px;">
                YOU ARE LOGGED<br>IN!
            </h1>

            <p class="auth-card-sub" style="font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Administrator credentials validated. Your dispatch session has been granted full enterprise clearance to fleet telemetry, driver schedules, and vehicle assets.
            </p>

            <!-- Enterprise Session Info Box -->
            <div class="admin-info-box" style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 8px; background: #0f172a; color: #ffffff; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    FA
                </div>
                <div style="flex: 1; text-align: left;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <strong style="color: #0f172a; font-size: 13px;">Fleet Dispatcher &mdash; Central Command</strong>
                        <i class="fa-solid fa-circle-check" style="color: #60a5fa; font-size: 12px;"></i>
                    </div>
                    <div style="color: #64748b; font-size: 11px; margin-top: 2px;">
                        Super Admin / Master Tier
                    </div>
                </div>
            </div>

            <a href="<?= e($targetUrl) ?>" class="auth-btn-dark" id="btnAdminConfirmLogin" style="margin-bottom: 16px;">
                CONFIRM &rarr;
            </a>

            <div style="font-size: 11px; color: #64748b;">
                Need secondary verification switch? <a href="<?= BASE_URL ?>index.php" style="color: #0284c7; font-weight: 600; text-decoration: underline;">Return to Portal Gateway</a>
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
        setTimeout(function() {
            window.location.href = <?= json_encode($targetUrl) ?>;
        }, 2600);
    </script>
</body>
</html>
