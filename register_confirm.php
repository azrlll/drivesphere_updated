<?php
require_once __DIR__ . '/includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');
$loginUrl = BASE_URL . 'login.php' . ($redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Registration Confirmation</title>
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
            <a href="<?= BASE_URL ?>login.php" class="auth-portal-pill">Customer Portal</a>
        </div>
    </header>

    <!-- Main Registration Confirmation Card -->
    <main class="auth-main-container">
        <div class="auth-card auth-card-confirm" style="text-align: center;">
            <div style="margin-bottom: 24px;">
                <span class="auth-pill-badge">REGISTRATION CONFIRMATION</span>
            </div>

            <div style="display: flex; justify-content: center; margin-bottom: 24px;">
                <img src="<?= BASE_URL ?>images/reg_confirm_circle.png" alt="Registration Success" style="width: 88px; height: 88px; display: block; border-radius: 50%;">
            </div>

            <h1 class="auth-card-title" style="font-size: 26px; line-height: 1.25; margin-bottom: 14px;">
                YOU ARE<br>REGISTERED!
            </h1>

            <p class="auth-card-sub" style="max-width: 330px; margin: 0 auto 32px auto; font-size: 13px; line-height: 1.55;">
                Your personal DriveSphere customer profile has been created successfully. You may now log in to review vehicle rates and confirm your itinerary.
            </p>

            <a href="<?= e($loginUrl) ?>" class="auth-btn-dark" id="btnConfirmReg">
                CONFIRM
            </a>
        </div>
    </main>

    <!-- Page Footer -->
    <footer class="auth-page-footer">
        &copy; 2025 DriveSphere Philippines Inc. All rights reserved. Enterprise Fleet Systems.
    </footer>

</body>
</html>
