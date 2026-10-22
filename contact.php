<?php 
require_once __DIR__ . '/includes/functions.php'; 
$presetName = '';
$presetEmail = '';
$presetPhone = '';
if (is_logged_in()) {
    $uid = $_SESSION['user_id'] ?? 0;
    if ($uid > 0) {
        $uStmt = getDBConnection()->prepare("SELECT * FROM users WHERE id = ?");
        $uStmt->execute([$uid]);
        $uData = $uStmt->fetch();
        if ($uData) {
            $presetName = trim(($uData['first_name'] ?? '') . ' ' . ($uData['last_name'] ?? ''));
            $presetEmail = $uData['email'] ?? '';
            $presetPhone = $uData['phone'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Contact Support</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header Section (matching SCREEN P07) -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="contact-main-bg">
        <!-- Sub Navigation -->
        <div class="contact-subnav">
            <div class="container subnav-container">
                <a href="<?= BASE_URL ?>help.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Help Center</a>
                <div class="breadcrumbs">
                    <a href="<?= BASE_URL ?>index.php" style="color:inherit;text-decoration:none;">Home</a> / <a href="<?= BASE_URL ?>help.php" style="color:inherit;text-decoration:none;">Help</a> / <strong>Contact Support</strong>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <section class="contact-hero">
            <div class="container">
                <div class="badge badge-light-blue"><span class="pulse-dot" style="background:#0284c7;width:6px;height:6px;display:inline-block;border-radius:50%;margin-right:6px;"></span>24/7 CUSTOMER CONCIERGE &amp; SUPPORT</div>
                <h1 class="contact-title">Contact DriveSphere Support</h1>
                <p class="contact-subtitle">Have a question about vehicle dispatch, airport handovers, corporate billing, or need support? Send our team a message or reach out through our direct priority lines.</p>
            </div>
        </section>

        <!-- Main Content Grid (matching SCREEN P07) -->
        <section class="contact-content-section" style="padding-bottom: 60px;">
            <div class="container contact-grid">
                
                <!-- Left Column: Contact Form -->
                <div class="contact-form-card">
                    <div class="cf-header">
                        <div>
                            <h2>Send an Inquiry or Support Ticket</h2>
                            <p>Our operations dispatch desk in BGC coordinates nationwide assistance and fleet telemetry monitoring.</p>
                        </div>
                        <span class="badge-time"><i class="fa-solid fa-bolt"></i> ~15 min response</span>
                    </div>

                    <form class="support-form" method="post" action="<?= BASE_URL ?>actions/contact_action.php" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <?php flash_show(); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name <span>*</span></label>
                                <input name="full_name" type="text" value="<?= e($presetName) ?>" placeholder="e.g. Juan Dela Cruz" required>
                            </div>
                            <div class="form-group">
                                <label>Corporate / Personal Email <span>*</span></label>
                                <input name="email" type="email" value="<?= e($presetEmail) ?>" placeholder="e.g. juan.delacruz@company.ph" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Philippine Mobile Number (Optional)</label>
                                <input name="phone" type="tel" value="<?= e($presetPhone) ?>" placeholder="+63 917 123 4567">
                            </div>
                            <div class="form-group">
                                <label>Inquiry Topic <span>*</span></label>
                                <select name="category" required>
                                    <option value="" disabled selected>Select a dispatch topic...</option>
                                    <option value="booking">Booking &amp; Reservations</option>
                                    <option value="support">Customer Support</option>
                                    <option value="billing">Billing &amp; Corporate Invoicing</option>
                                    <option value="other">Other Inquiry</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Detailed Message <span>*</span></label>
                            <textarea name="message" rows="4" placeholder="Provide detailed information regarding your inquiry, booking reference number (e.g. DS-MNL-4820) if applicable, and preferred contact window..." required></textarea>
                        </div>

                        <!-- Supporting Documents Upload Box (matching SCREEN P07) -->
                        <div class="form-group">
                            <label>Supporting Documents or Incident Photos (Optional)</label>
                            <div class="file-upload-box">
                                <div class="file-info-left">
                                    <div class="fu-icon"><i class="fa-solid fa-paperclip"></i></div>
                                    <div class="fu-text">
                                        <strong>Attach vehicle photo, fuel slip, or incident snapshot</strong>
                                        <span>PNG, JPG, PDF up to 15MB</span>
                                    </div>
                                </div>
                                <label class="btn btn-outline-dark btn-sm" style="cursor:pointer;margin:0;">
                                    Browse File
                                    <input type="file" name="attachment" style="display:none;" onchange="this.parentElement.previousElementSibling.querySelector('strong').innerText = this.files[0]?.name || 'File attached';">
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large submit-btn">
                            Send Message <i class="fa-solid fa-paper-plane" style="margin-left:6px;"></i>
                        </button>

                        <div class="privacy-notice">
                            <i class="fa-solid fa-lock"></i> Your inquiry is encrypted under Republic Act 10173 (Data Privacy Act of 2012) compliance.
                        </div>
                    </form>
                </div>

                <!-- Right Column: Contact Info Cards (matching SCREEN P07) -->
                <div class="contact-info-col">
                    
                    <!-- Dispatch Desk Card -->
                    <div class="info-card dispatch-card">
                        <div class="dc-header">
                            <span class="status-indicator"><span class="pulse-dot"></span> DISPATCH DESK ACTIVE</span>
                            <span class="wait-time">Wait: &lt; 2 mins</span>
                        </div>
                        <span class="dc-label">24/7 CUSTOMER HOTLINE</span>
                        <h3 class="dc-number">(02) 8888–DRIVE</h3>
                        
                        <div class="dc-subgrid">
                            <div class="dc-subitem border-right">
                                <span>Mobile Support Line</span>
                                <strong>+63 917 888 3748</strong>
                            </div>
                            <div class="dc-subitem">
                                <span>Dispatch Operations</span>
                                <strong>support@drivesphere.ph</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Regional Hubs Card -->
                    <div class="info-card hubs-card">
                        <h3 class="card-title-icon"><i class="fa-solid fa-circle-nodes"></i> Regional Support Hubs</h3>
                        
                        <div class="hub-list">
                            <div class="hub-item">
                                <div class="hub-item-header">
                                    <h4>Flagship Operations Hub</h4>
                                    <span class="hub-tag">HQ</span>
                                </div>
                                <p>32nd Street cor. 5th Avenue, Bonifacio Global City (BGC), Taguig City, Metro Manila</p>
                                <span class="hub-link"><i class="fa-regular fa-clock"></i> Open 24/7 for Fleet Inspection &amp; Key Turnover</span>
                            </div>

                            <div class="hub-item">
                                <div class="hub-item-header">
                                    <h4>NAIA International Airport Bays</h4>
                                    <span class="hub-tag">T1 / T2 / T3</span>
                                </div>
                                <p>Terminal 3 Arrival Flight Gate Bays &amp; VIP Chauffeur Counter, Pasay City</p>
                                <span class="hub-link"><i class="fa-solid fa-plane-arrival"></i> Express Handover &amp; Luggage Loading</span>
                            </div>

                            <div class="hub-item">
                                <div class="hub-item-header">
                                    <h4>Cebu IT Park Operations Desk</h4>
                                    <span class="hub-tag">Visayas</span>
                                </div>
                                <p>Skyrise Alpha, Salinas Drive, Lahug, Cebu City, Central Visayas</p>
                                <span class="hub-link"><i class="fa-solid fa-map-location-dot"></i> Island Cross-Border Fleet Coordination</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- Footer Section (matching SCREEN P07) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
