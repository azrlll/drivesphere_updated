<?php
require_once __DIR__ . '/includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');

if (!empty($_GET['confirmed'])) {
    require __DIR__ . '/register_confirm.php';
    exit;
}

if (is_customer()) {
    redirect($redirectTarget);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Create Customer Account</title>
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

    <!-- Main Registration Card Area -->
    <main class="auth-main-container">
        <div class="auth-card auth-card-register">
            <span class="auth-pill-badge">USER ACCOUNT REGISTER</span>
            
            <h1 class="auth-card-title">Create Customer Account</h1>
            <p class="auth-card-sub">Join DriveSphere to reserve premium SUVs, sedans, and passenger vans across Metro Manila, Clark, and Cebu.</p>

            <?php flash_show(); ?>

            <form method="post" action="<?= BASE_URL ?>actions/register_action.php" id="registerForm">
                <?= csrf_field() ?>
                <?php if ($redirectTarget !== 'customer/dashboard.php'): ?>
                    <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                <?php endif; ?>

                <div class="auth-field-group">
                    <label class="auth-label">FULL NAME <span class="auth-req">*</span></label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-user auth-input-icon"></i>
                        <input type="text" name="full_name" class="auth-input" placeholder="e.g. Maria Cristina Santos" required maxlength="100" autocomplete="name">
                    </div>
                </div>

                <div class="auth-field-group">
                    <label class="auth-label">EMAIL ADDRESS <span class="auth-req">*</span></label>
                    <div class="auth-input-wrapper">
                        <i class="fa-regular fa-envelope auth-input-icon"></i>
                        <input type="email" name="email" class="auth-input" placeholder="m.santos@company.ph" required autocomplete="email">
                    </div>
                </div>

                <div class="auth-2col-row">
                    <div class="auth-field-group" style="margin-bottom: 0;">
                        <label class="auth-label">PASSWORD <span class="auth-req">*</span></label>
                        <div class="auth-input-wrapper">
                            <input type="password" name="password" id="regPassword" class="auth-input" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="auth-field-group" style="margin-bottom: 0;">
                        <label class="auth-label">CONFIRM PASSWORD <span class="auth-req">*</span></label>
                        <div class="auth-input-wrapper">
                            <input type="password" name="confirm_password" id="regConfirmPassword" class="auth-input" placeholder="Repeat password" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="auth-checkbox-row" style="margin-top: 18px;">
                    <input type="checkbox" id="agreeTerms" name="agree" value="1" required>
                    <label for="agreeTerms">
                        I agree to the DriveSphere <a href="<?= BASE_URL ?>policies.php" class="auth-link">Terms of Rental Service</a>, <a href="<?= BASE_URL ?>policies.php" class="auth-link">Security Deposit Guidelines</a>, and confirm compliance with Philippine Data Privacy Act (RA 10173).
                    </label>
                </div>

                <button type="submit" class="auth-btn-dark">
                    REGISTER ACCOUNT &rarr;
                </button>
            </form>

            <div class="auth-card-footer" style="margin-top: 20px;">
                Already registered? 
                <a href="<?= BASE_URL ?>login.php<?= $redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="auth-link">Sign in to your account &rarr;</a>
            </div>
        </div>
    </main>

    <!-- Page Footer -->
    <footer class="auth-page-footer">
        &copy; 2025 DriveSphere Philippines Inc. All rights reserved. Enterprise Fleet Systems.
    </footer>

</body>
</html>
