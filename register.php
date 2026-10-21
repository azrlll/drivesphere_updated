<?php
require_once __DIR__ . '/includes/functions.php';
$redirectTarget = get_safe_redirect('redirect', 'customer/dashboard.php');
if (is_customer()) { redirect($redirectTarget); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Create Customer Account</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for the icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="login-page-bg">

    <!-- Registration Header -->
    <header class="login-header">
        <div class="container lh-container">
            <div class="logo">
                <img src="images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
                <div class="logo-text">
                    <span>DriveSphere</span>
                    <small>PHILIPPINE MOBILITY & FLEET</small>
                </div>
            </div>
            <div class="lh-links reg-header-right">
                <a href="<?= BASE_URL ?>login.php<?= $redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>" class="reg-prompt" style="text-decoration: none; color: var(--primary-blue); font-weight: 600; transition: 0.2s;">
                    Already have an account?
                </a>
            </div>
        </div>
    </header>

    <main class="login-main">
        <div class="register-card-wrapper">
            
            <!-- Left Side: Registration Form -->
            <div class="reg-form-side">
                <div class="reg-top-bar">
                    <div class="lc-badge">USER ACCOUNT REGISTER</div>
                    <div class="step-indicator">Step 1 of 2</div>
                </div>

                <h1>Create Customer Account</h1>
                <p class="login-subtitle">Join DriveSphere to reserve premium SUVs, sedans, and passenger vans across Metro Manila, Clark, and Cebu.</p>

                <!-- UPDATED FORM TAG WITH ACTION ATTRIBUTE -->
                <?php flash_show(); ?>
                <form class="login-form" id="registerForm" method="post" action="<?= BASE_URL ?>actions/register_action.php">
                    <?= csrf_field() ?>
                    <?php if ($redirectTarget !== 'customer/dashboard.php'): ?>
                        <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>FULL NAME <span class="req">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="full_name" placeholder="e.g. Maria Cristina Santos" required maxlength="100">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>EMAIL ADDRESS <span class="req">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="m.santos@gmail.com" required>
                        </div>
                    </div>

                    <!-- PASSWORD INPUTS WITH FUNCTIONAL EYE ICONS -->
                    <div class="form-row-2col" style="margin-bottom: 24px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>PASSWORD <span class="req">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" placeholder="Min. 8 characters" class="standard-input" id="regPassword" name="password" required minlength="8">
                                <i class="fa-regular fa-eye toggle-pw" style="cursor: pointer;"></i>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>CONFIRM PASSWORD <span class="req">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-arrow-rotate-right"></i>
                                <input type="password" placeholder="Repeat password" class="standard-input" id="regConfirmPassword" name="confirm_password" required minlength="8">
                                <i class="fa-regular fa-eye toggle-pw" style="cursor: pointer;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="checkbox-row terms-row">
                        <input type="checkbox" id="agreeTerms" name="agree" value="1" required>
                        <label for="agreeTerms">I agree to the DriveSphere <a href="<?= BASE_URL ?>policies.php">Terms of Rental Service</a>, <a href="<?= BASE_URL ?>policies.php">Security Deposit Guidelines</a>, and confirm compliance with Philippine Data Privacy Act (RA 10173).</label>
                    </div>

                    <button type="submit" class="btn-dark-full">REGISTER ACCOUNT <i class="fa-solid fa-arrow-right"></i></button>
                </form>

                <div class="reg-form-footer">
                    Already registered? <a href="<?= BASE_URL ?>login.php<?= $redirectTarget !== 'customer/dashboard.php' ? '?redirect=' . urlencode($redirectTarget) : '' ?>">Sign in to your account &rarr;</a>
                </div>
            </div>

            <!-- Right Side: Info Panel -->
            <div class="reg-info-side">
                <div class="info-badge"><i class="fa-solid fa-circle"></i> Enterprise Mobility Network</div>
                
                <h2>Drive with Prestige & Precision</h2>
                <p>Register your verified customer profile to unlock rapid booking, airport VIP curbside key handovers, and cashless digital rental contracts nationwide.</p>
                
                <div class="info-feature-list">
                    <div class="info-feature">
                        <div class="feat-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="feat-text">
                            <strong>Guaranteed Model Match:</strong>
                            <span>The exact vehicle model and spec you reserve is what arrives.</span>
                        </div>
                    </div>
                    <div class="info-feature">
                        <div class="feat-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="feat-text">
                            <strong>Transparent Philippine Peso (₱) Rates:</strong>
                            <span>Zero hidden surprise surcharges or arbitrary fees.</span>
                        </div>
                    </div>
                    <div class="info-feature">
                        <div class="feat-icon"><i class="fa-solid fa-check"></i></div>
                        <div class="feat-text">
                            <strong>24/7 Roadside Assistance:</strong>
                            <span>Instant mechanical replacement guarantee within 60 minutes.</span>
                        </div>
                    </div>
                </div>

                <div class="info-footer">
                    <span>Official LTFRB Registered Fleet</span>
                    <span class="highlight">150+ Certified Vehicles</span>
                </div>
            </div>

        </div>
    </main>

    <script src="js/script.js"></script>
</body>
</html>