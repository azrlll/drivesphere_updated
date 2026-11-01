<?php
$pageTitle = 'Vehicle Details';
$nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$id = (int)($_GET['id'] ?? 1);
$st = $pdo->prepare("SELECT v.*, c.name AS category_name, c.slug AS category_slug FROM vehicles v JOIN categories c ON v.category_id = c.id WHERE v.id = ?");
$st->execute([$id]);
$v = $st->fetch();

// If ID not found, fallback to vehicle 1 (Toyota Fortuner)
if (!$v) {
    $st->execute([1]);
    $v = $st->fetch();
    if (!$v) {
        flash_set('error', 'Vehicle not found.');
        redirect('customer/dashboard.php');
    }
}

// Hub & Location resolution
$hubParam = $_GET['hub'] ?? $_GET['pickup_location'] ?? 'bgc';
$hubLabels = [
    'bgc'     => 'Metro Manila - BGC Hub (Active)',
    'mnl'     => "Ninoy Aquino Int'l Airport (T3)",
    'makati'  => 'Makati CBD Hub (Ayala Center)',
    'ortigas' => 'Ortigas Center - Pasig Hub',
    'crk'     => "Clark Int'l Airport Hub (Pampanga)",
    'ceb'     => 'Cebu City - Mactan Airport Hub',
    'dvo'     => 'Davao City - Lanang Hub'
];
$pickupLocationDisplay = $hubLabels[$hubParam] ?? (in_array($hubParam, $hubLabels) ? $hubParam : 'Metro Manila - BGC Hub (Active)');

// Vehicle specific metadata / defaults matching Image mockup
$fleetPlate = !empty($v['plate_number']) ? $v['plate_number'] : ('MNL-FT-' . str_pad($v['id'], 3, '0', STR_PAD_LEFT));
$categoryClass = strtoupper($v['category_name'] ?? 'PREMIUM SUV CLASS');

// Date parameters (default to next available 3-day rental slot)
$pickupDateVal = $_GET['pickup_date'] ?? null;
$returnDateVal = $_GET['return_date'] ?? null;

if (!$pickupDateVal || !$returnDateVal) {
    $testStart = strtotime('+2 days');
    for ($i = 0; $i < 30; $i++) {
        $candPickup = date('Y-m-d 09:00:00', strtotime("+$i days", $testStart));
        $candReturn = date('Y-m-d 18:00:00', strtotime("+3 days", strtotime($candPickup)));
        if (vehicle_is_available($pdo, $v['id'], $candPickup, $candReturn)) {
            $pickupDateVal = date('Y-m-d', strtotime($candPickup));
            $returnDateVal = date('Y-m-d', strtotime($candReturn));
            break;
        }
    }
    if (!$pickupDateVal) {
        $pickupDateVal = date('Y-m-d', strtotime('+3 days'));
        $returnDateVal = date('Y-m-d', strtotime('+6 days'));
    }
}

$pickupTime = strtotime($pickupDateVal);
$returnTime = strtotime($returnDateVal);
if ($returnTime <= $pickupTime) {
    $returnTime = strtotime('+3 days', $pickupTime);
    $returnDateVal = date('Y-m-d', $returnTime);
}
$days = max(1, (int)round(($returnTime - $pickupTime) / 86400));
$dailyRate = (float)$v['daily_rate'];
$subtotal = $days * $dailyRate;
$lateFeeRate = $dailyRate * 0.10;
$deposit = 5000.00;
$total = $subtotal;

// Initial server-side availability check
$pickupSql = date('Y-m-d 09:00:00', $pickupTime);
$returnSql = date('Y-m-d 18:00:00', $returnTime);
$isInitiallyAvailable = vehicle_is_available($pdo, $v['id'], $pickupSql, $returnSql);

// Image resolution - local downloaded vehicle photo
$vehiclePhoto = vehicle_image_url($v['image_path'] ?: $v['image_url']);

// Custom descriptions per vehicle or mockup standard
$vehicleFullName = ($v['brand'] ?: $v['make']) . ' ' . $v['model'];
$descP1 = !empty($v['description']) 
    ? $v['description'] 
    : 'Engineered for executive journeys, family holiday retreats, and extended provincial touring across Northern and Southern Luzon. The ' . e($vehicleFullName) . ' delivers an authoritative presence combined with whisper-quiet cabin insulation and responsive performance.';

$descP2 = 'Equipped with full safety sensors, adaptive drive controls, and advanced connectivity. Whether navigating metropolitan Manila corridors or winding ascent routes up to Baguio and Tagaytay, this self-drive vehicle delivers absolute confidence and spacious comfort.';

// User / Driver details from logged-in session
$driverName = trim(($me['first_name'] ?? '') . ' ' . (($me['last_name'] ?? '') === '-' ? '' : ($me['last_name'] ?? '')));
if ($driverName === '') $driverName = 'Eduardo Jose Reyes';
$driverPhone = !empty($me['phone']) ? $me['phone'] : '+63 917 554 9021';
$driverLicense = !empty($me['license_number']) ? $me['license_number'] : 'N02-14-048291';

$backUrl = (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'browse')) ? $C . 'browse.php' : $C . 'dashboard.php';
$backLabel = str_contains($backUrl, 'browse') ? 'Back to Browse' : 'Back to Dashboard';
?>

<div class="v-details-container">
    <!-- Top Action Bar -->
    <div class="v-top-bar">
        <a href="<?= $backUrl ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?= $backLabel ?>
        </a>
        <div class="v-top-actions">
            <button type="button" class="btn-v-action" id="shareBtn" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');">
                <i class="fa-solid fa-share-nodes"></i> Share
            </button>
        </div>
    </div>

    <!-- Main Split Layout (Left: Details, Visuals, Overview | Right: Merged Price Overview & Reservation) -->
    <div class="v-main-split">
        <!-- ================= LEFT COLUMN: DETAILS & SPECS ================= -->
        <div class="v-left-col">
            <!-- Header Block -->
            <div class="v-header-block">
                <div class="v-category-fleet">
                    <?= e($categoryClass) ?> &bull; Fleet Plate: <?= e($fleetPlate) ?>
                </div>
                <div class="v-title-row">
                    <h1 class="v-main-title"><?= e($vehicleFullName) ?></h1>
                    <span class="v-status-pill <?= $isInitiallyAvailable ? '' : 'reserved' ?>" id="vehicleLeftStatusPill">
                        <span class="dot"></span> <?= $isInitiallyAvailable ? 'Available' : 'Reserved' ?>
                    </span>
                </div>
            </div>

            <!-- Big Visual Card -->
            <div class="v-visual-card">
                <img src="<?= e($vehiclePhoto) ?>" alt="<?= e($vehicleFullName) ?>" class="v-visual-img">
                <span class="v-chip-left">
                    <i class="fa-solid fa-shield-halved"></i> Fully Inspected &amp; Sanitized
                </span>
                <span class="v-chip-right">
                    <i class="fa-solid fa-camera"></i> Exterior &amp; Cockpit (360&deg;)
                </span>
            </div>

            <!-- 4 Key Spec Badges -->
            <div class="v-key-specs-grid">
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-user-group"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">PASSENGER</span>
                        <span class="key-spec-val"><?= (int)($v['seating_capacity'] ?: $v['seats']) ?> Seats</span>
                    </div>
                </div>
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-door-closed"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">DOORS</span>
                        <span class="key-spec-val">5 Doors</span>
                    </div>
                </div>
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">CONSUMPTION</span>
                        <span class="key-spec-val">12.5 km/L</span>
                    </div>
                </div>
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-dharmachakra"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">TRANSMISSION</span>
                        <span class="key-spec-val"><?= str_starts_with($v['transmission'], 'Automatic') ? 'Automatic' : e($v['transmission']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Section: Vehicle Overview -->
            <div class="v-section-head">
                <h2 class="v-section-title">Vehicle Overview</h2>
                <span class="v-section-sub"><?= (int)$v['year'] ?> <?= e($v['model']) ?></span>
            </div>
            <div class="v-overview-grid">
                <div class="v-overview-item">
                    <i class="fa-solid fa-palette v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">EXTERIOR COLOR</span>
                        <span class="v-overview-val">Platinum White Pearl</span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-couch v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">INTERIOR COLOR</span>
                        <span class="v-overview-val">Dual-Tone Black &amp; Maroon Leather</span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gauge-high v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">TRANSMISSION</span>
                        <span class="v-overview-val"><?= e($v['transmission']) ?> with Paddle Shifters</span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gears v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">ENGINE CAPACITY</span>
                        <span class="v-overview-val">2.8L 4-Cylinder Turbo Diesel</span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gas-pump v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">FUEL TYPE</span>
                        <span class="v-overview-val">Euro 4 Clean Diesel (80L Tank)</span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-location-arrow v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">GPS NAVIGATION</span>
                        <span class="v-overview-val">Built-in Apple CarPlay &amp; Android Auto GPS</span>
                    </div>
                </div>
            </div>

            <!-- Section: Description -->
            <div class="v-section-head">
                <h2 class="v-section-title">Description</h2>
            </div>
            <div class="v-desc-body">
                <p><?= e($descP1) ?></p>
                <p><?= e($descP2) ?></p>
            </div>

            <!-- Section: Other Features -->
            <div class="v-section-head">
                <h2 class="v-section-title">Other Features</h2>
            </div>
            <div class="v-features-grid">
                <div class="v-feature-card">
                    <div class="v-feature-icon-box"><i class="fa-solid fa-chair"></i></div>
                    <h3 class="v-feature-title">Leather Executive Seating</h3>
                    <p class="v-feature-desc">Ventilated perforated leather driver and co-pilot seating with 8-way power adjustment.</p>
                </div>
                <div class="v-feature-card">
                    <div class="v-feature-icon-box"><i class="fa-solid fa-volume-high"></i></div>
                    <h3 class="v-feature-title">Surround Sound Audio</h3>
                    <p class="v-feature-desc">9-speaker JBL Premium Acoustic System with high-fidelity subwoofer for long road trips.</p>
                </div>
                <div class="v-feature-card">
                    <div class="v-feature-icon-box"><i class="fa-solid fa-mountain"></i></div>
                    <h3 class="v-feature-title">All-Terrain 4WD Drive Modes</h3>
                    <p class="v-feature-desc">Dial-switch 2H, 4H, and 4L drivetrain with Auto Limited-Slip Differential and Downhill Assist.</p>
                </div>
            </div>

            <!-- Trust Guarantees & Direct Unit -->
            <div style="margin-top: 32px; display: flex; flex-direction: column; gap: 14px;">
                <div class="w-trust-list" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px;">
                    <div class="w-trust-item" style="margin-bottom: 12px;">
                        <i class="fa-regular fa-circle-check" style="color: #22c55e;"></i>
                        <span><strong>Free Cancellation</strong> up to 24 hours before pickup time.</span>
                    </div>
                    <div class="w-trust-item" style="margin-bottom: 12px;">
                        <i class="fa-solid fa-headset" style="color: #0284c7;"></i>
                        <span>24/7 Customer Support &amp; Fleet Hotline: <strong>(02) 8888-DRIVE</strong></span>
                    </div>
                    <div class="w-trust-item">
                        <i class="fa-solid fa-shield-halved" style="color: #0284c7;"></i>
                        <span>Full LTFRB &amp; Comprehensive Commercial Insurance coverage provided.</span>
                    </div>
                </div>

                <div class="direct-unit-card" style="margin: 0;">
                    <div class="direct-unit-icon">
                        <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
                    </div>
                    <div>
                        <div class="direct-unit-title">DRIVESPHERE DIRECT</div>
                        <p class="direct-unit-desc">Official fleet unit. No middleman broker markups.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= RIGHT COLUMN: MERGED PRICE OVERVIEW & RESERVATION ================= -->
        <div class="v-right-col v-merged-col">
            <!-- Header Block -->
            <div class="merged-head-block">
                <h2 class="merged-main-title">Price Overview</h2>
                <p class="merged-main-sub">Review rental period, vehicle schedule, and transparent fee schedule before final authorization.</p>
            </div>

            <form method="post" action="<?= BASE_URL ?>actions/request_action.php" id="mergedReservationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">

                <!-- Card 1: VEHICLE NAME Card -->
                <div class="merged-card merged-v-card">
                    <div class="merged-v-top-row">
                        <span class="merged-card-label">VEHICLE NAME</span>
                        <span class="merged-avail-badge <?= $isInitiallyAvailable ? 'available' : 'unavailable' ?>" id="topAvailBadge">
                            <span class="dot"></span> <span id="topAvailText"><?= $isInitiallyAvailable ? 'Available' : 'Reserved' ?></span>
                        </span>
                    </div>
                    <div class="merged-v-bottom-row">
                        <div class="merged-v-title"><?= e($vehicleFullName) ?></div>
                        <div class="merged-v-price-block">
                            <span class="m-price-tag">PRICE</span>
                            <div class="m-price-val-wrap">
                                <span class="m-price-val">₱<?= number_format($dailyRate, 0) ?></span>
                                <span class="m-price-sub">/ day</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Rental Period Card -->
                <div class="merged-card merged-period-card" style="margin-top: 18px;">
                    <div class="merged-period-head">
                        <div class="merged-card-title">
                            <i class="fa-regular fa-calendar-days"></i> Rental Period
                        </div>
                        <span class="merged-days-badge" id="daysBadge"><?= $days ?> Days Rental</span>
                    </div>

                    <div class="merged-dates-grid">
                        <div class="merged-date-cell">
                            <div class="merged-date-meta">
                                <span class="date-lbl">Pick-up Date</span>
                                <span class="time-lbl">09:00 AM Standard</span>
                            </div>
                            <div class="merged-date-input-wrap">
                                <input type="date" name="pickup_date" id="pickupDateInput" value="<?= e($pickupDateVal) ?>" required min="<?= date('Y-m-d') ?>">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                        </div>
                        <div class="merged-date-cell">
                            <div class="merged-date-meta">
                                <span class="date-lbl">Return Date</span>
                                <span class="time-lbl">06:00 PM Grace</span>
                            </div>
                            <div class="merged-date-input-wrap">
                                <input type="date" name="return_date" id="returnDateInput" value="<?= e($returnDateVal) ?>" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                        </div>
                    </div>

                    <div class="merged-hub-row">
                        <i class="fa-solid fa-location-dot" style="color: #0284c7;"></i>
                        <select name="pickup_location" id="pickupLocationSelect" class="merged-hub-select" required>
                            <?php foreach ($hubLabels as $hKey => $hVal): ?>
                                <option value="<?= e($hVal) ?>" <?= ($pickupLocationDisplay === $hVal || $hubParam === $hKey) ? 'selected' : '' ?>><?= e($hVal) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="return_location" id="returnLocationInput" value="<?= e($pickupLocationDisplay) ?>">
                    </div>
                </div>

                <!-- Live Availability Feedback Box -->
                <div id="availStatusBox" class="avail-status-box <?= $isInitiallyAvailable ? 'available' : 'unavailable' ?>" style="margin-top: 18px;">
                    <div class="avail-status-icon">
                        <i class="fa-solid <?= $isInitiallyAvailable ? 'fa-circle-check' : 'fa-circle-xmark' ?>" id="availStatusIcon"></i>
                    </div>
                    <div class="avail-status-content">
                        <strong id="availStatusTitle"><?= $isInitiallyAvailable ? 'Available for Selected Dates' : 'Unavailable for Selected Dates' ?></strong>
                        <p id="availStatusDesc">
                            <?= $isInitiallyAvailable 
                                ? 'Vehicle is verified in fleet stock and open for booking from ' . date('M d, Y', $pickupTime) . ' to ' . date('M d, Y', $returnTime) . '.'
                                : 'Vehicle is currently reserved for part of these dates. Please choose different dates.' ?>
                        </p>
                    </div>
                </div>

                <!-- Card 3: Itemized Breakdown -->
                <div class="merged-breakdown-card" style="margin-top: 18px;">
                    <div class="merged-breakdown-head">
                        <span>ITEMIZED BREAKDOWN</span>
                        <span class="merged-curr-tag">Philippine Peso (PHP)</span>
                    </div>

                    <!-- Line 1: Base Rental -->
                    <div class="merged-item-row">
                        <div class="merged-item-left">
                            <div class="merged-item-title">Base Rental Charge</div>
                            <div class="merged-item-sub">
                                <span id="itemDays"><?= $days ?></span> Days &times; ₱<?= number_format($dailyRate, 2) ?> / day
                            </div>
                        </div>
                        <div class="merged-item-val" id="itemBaseSubtotal">₱<?= number_format($subtotal, 2) ?></div>
                    </div>

                    <!-- Line 2: Late on Deadline Return -->
                    <div class="merged-item-row">
                        <div class="merged-item-left">
                            <div class="merged-item-title">
                                Late on Deadline Return
                                <i class="fa-solid fa-circle-info" style="color: #94a3b8; font-size: 13px;" title="The total of rate per day will add 10%"></i>
                            </div>
                            <div class="merged-item-sub">The total of rate per day will add 10%</div>
                        </div>
                        <div class="merged-item-val" id="itemLateRate">+₱<?= number_format($lateFeeRate, 2) ?> / day</div>
                    </div>

                    <!-- Line 3: Comprehensive Insurance -->
                    <div class="merged-item-row">
                        <div class="merged-item-left">
                            <div class="merged-item-title">
                                Standard Comprehensive Insurance
                                <i class="fa-solid fa-circle-check" style="color: #0284c7; font-size: 13px;"></i>
                            </div>
                            <div class="merged-item-sub">Zero Deductible collision protection &amp; third-party liability</div>
                        </div>
                        <div class="merged-item-val">
                            <span class="req-included-badge">INCLUDED</span> ₱0.00
                        </div>
                    </div>

                    <!-- Line 4: Mandatory Government VAT -->
                    <div class="merged-item-row">
                        <div class="merged-item-left">
                            <div class="merged-item-title">Mandatory Government VAT (12%)</div>
                            <div class="merged-item-sub">Official Bureau of Internal Revenue e-receipt issued</div>
                        </div>
                        <div class="merged-item-val">
                            <span class="req-included-badge">INCLUDED</span>
                        </div>
                    </div>

                    <!-- Line 5: Security Deposit -->
                    <div class="merged-item-row">
                        <div class="merged-item-left">
                            <div class="merged-item-title">
                                Refundable Security Deposit
                                <i class="fa-solid fa-circle-info" style="color: #94a3b8; font-size: 13px;"></i>
                            </div>
                            <div class="merged-item-sub">Released immediately upon vehicle return check-in inspection</div>
                        </div>
                        <div class="merged-item-val">₱<?= number_format($deposit, 2) ?>*</div>
                    </div>

                    <div class="merged-deposit-note">
                        *Security deposit is pre-authorized on arrival and does not incur upfront card billing.
                    </div>

                    <!-- Dark Navy Net Total Banner -->
                    <div class="merged-total-banner">
                        <div>
                            <div class="m-total-title">PAYABLE NET TOTAL</div>
                            <div class="m-total-sub">Total Price:</div>
                        </div>
                        <div class="m-total-right">
                            <div class="m-total-amount" id="netTotalVal">₱<?= number_format($total, 2) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Reservation Form Card -->
                <div class="merged-card merged-res-card" style="margin-top: 18px;">
                    <div class="merged-res-head">
                        <div class="merged-card-title">
                            <i class="fa-regular fa-id-card"></i> Reservation
                        </div>
                        <span class="req-status-pill">
                            <span class="req-status-dot"></span> Status: Waiting for Confirmation
                        </span>
                    </div>

                    <!-- Reservation Agreement Box -->
                    <div class="merged-sec-tag">RESERVATION AGREEMENT</div>
                    <div class="req-terms-box">
                        <div class="req-terms-head">DriveSphere Self-Drive Operating Terms:</div>
                        <p class="req-terms-p">1. Primary driver must be at least 21 years of age with a valid government LTO driver's license (non-professional or professional).</p>
                        <p class="req-terms-p">2. Vehicle is delivered with full tank premium diesel and must be surrendered with equivalent fuel volume.</p>
                    </div>

                    <!-- Terms agreement checkbox -->
                    <label class="req-agree-row">
                        <input type="checkbox" name="agree" value="1" required checked>
                        <span>I have read and agree to the <a href="<?= BASE_URL ?>customer/policies.php" target="_blank" style="color: #0284c7; font-weight: 600; text-decoration: underline;">Terms of Rental</a> and Driver Requirements.</span>
                    </label>

                    <!-- Driver Full Name -->
                    <div class="req-field">
                        <div class="req-field-head">
                            <label class="req-field-label">Driver Full Name (as on License)</label>
                        </div>
                        <div class="req-input-box">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="driver_name" required maxlength="100" value="<?= e($driverName) ?>" placeholder="Full Name">
                        </div>
                    </div>

                    <!-- Contact Mobile Phone -->
                    <div class="req-field">
                        <div class="req-field-head">
                            <label class="req-field-label">Contact Mobile Phone</label>
                        </div>
                        <div class="req-input-box">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" name="driver_phone" required maxlength="30" value="<?= e($driverPhone) ?>" placeholder="+63 917 554 9021">
                        </div>
                    </div>

                    <!-- Philippine LTO Driver's License Number -->
                    <div class="req-field">
                        <div class="req-field-head">
                            <label class="req-field-label">Philippine LTO Driver's License Number</label>
                            <span class="req-field-badge">Verified Member</span>
                        </div>
                        <div class="req-input-box">
                            <i class="fa-regular fa-id-card"></i>
                            <input type="text" name="driver_license" required maxlength="50" value="<?= e($driverLicense) ?>" placeholder="N02-14-048291">
                        </div>
                    </div>

                    <!-- Request to Book Primary CTA -->
                    <button type="submit" class="btn-merged-book" id="btnRequestReservation" <?= !$isInitiallyAvailable ? 'disabled' : '' ?>>
                        <i class="fa-regular fa-paper-plane"></i> Request to Book Reservation
                    </button>

                    <!-- Assurance Footer -->
                    <div class="req-res-footer">
                        <span><i class="fa-regular fa-circle-check"></i> DriveSphere Enterprise Assurance</span>
                        <span>LTFRB Registered Operator #2024-4902</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interactive Date, Price & Live Availability Check Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickupInput = document.getElementById('pickupDateInput');
    const returnInput = document.getElementById('returnDateInput');
    const daysBadge = document.getElementById('daysBadge');
    const itemDays = document.getElementById('itemDays');
    const itemBaseSubtotal = document.getElementById('itemBaseSubtotal');
    const itemLateRate = document.getElementById('itemLateRate');
    const netTotalVal = document.getElementById('netTotalVal');
    const availBox = document.getElementById('availStatusBox');
    const availIcon = document.getElementById('availStatusIcon');
    const availTitle = document.getElementById('availStatusTitle');
    const availDesc = document.getElementById('availStatusDesc');
    const topAvailBadge = document.getElementById('topAvailBadge');
    const topAvailText = document.getElementById('topAvailText');
    const leftStatusPill = document.getElementById('vehicleLeftStatusPill');
    const btnSubmit = document.getElementById('btnRequestReservation');
    const locationSelect = document.getElementById('pickupLocationSelect');
    const returnLocationInput = document.getElementById('returnLocationInput');
    const vehicleId = <?= (int)$v['id'] ?>;
    const dailyRate = <?= (float)$dailyRate ?>;

    function recalculateAndCheck() {
        if (!pickupInput || !returnInput) return;
        const pVal = pickupInput.value;
        const rVal = returnInput.value;
        if (!pVal || !rVal) return;

        const pDate = new Date(pVal);
        const rDate = new Date(rVal);
        let diffDays = Math.round((rDate - pDate) / (1000 * 60 * 60 * 24));
        if (diffDays < 1) diffDays = 1;

        const subtotal = diffDays * dailyRate;
        const lateRate = dailyRate * 0.10;

        if (daysBadge) daysBadge.textContent = diffDays + ' Day' + (diffDays > 1 ? 's' : '') + ' Rental';
        if (itemDays) itemDays.textContent = diffDays;
        if (itemBaseSubtotal) itemBaseSubtotal.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (itemLateRate) itemLateRate.textContent = '+₱' + lateRate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / day';
        if (netTotalVal) netTotalVal.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Set status box into querying mode
        if (availBox) {
            availBox.className = 'avail-status-box';
            if (availIcon) availIcon.className = 'fa-solid fa-circle-notch fa-spin';
            if (availTitle) availTitle.textContent = 'Verifying availability...';
            if (availDesc) availDesc.textContent = 'Querying live fleet database for ' + pVal + ' to ' + rVal + '...';
        }

        fetch('<?= BASE_URL ?>actions/check_availability.php?vehicle_id=' + vehicleId + '&pickup_date=' + encodeURIComponent(pVal) + '&return_date=' + encodeURIComponent(rVal))
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    if (availBox) {
                        availBox.className = 'avail-status-box available';
                        if (availIcon) availIcon.className = 'fa-solid fa-circle-check';
                        if (availTitle) availTitle.textContent = 'Available for Selected Dates';
                        if (availDesc) availDesc.textContent = data.message;
                    }
                    if (topAvailBadge) {
                        topAvailBadge.className = 'merged-avail-badge available';
                        if (topAvailText) topAvailText.textContent = 'Available';
                    }
                    if (leftStatusPill) {
                        leftStatusPill.className = 'v-status-pill';
                        leftStatusPill.innerHTML = '<span class="dot"></span> Available';
                    }
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                    }
                } else {
                    if (availBox) {
                        availBox.className = 'avail-status-box unavailable';
                        if (availIcon) availIcon.className = 'fa-solid fa-circle-xmark';
                        if (availTitle) availTitle.textContent = 'Unavailable for Selected Dates';
                        if (availDesc) availDesc.textContent = data.message;
                    }
                    if (topAvailBadge) {
                        topAvailBadge.className = 'merged-avail-badge unavailable';
                        if (topAvailText) topAvailText.textContent = 'Reserved';
                    }
                    if (leftStatusPill) {
                        leftStatusPill.className = 'v-status-pill reserved';
                        leftStatusPill.innerHTML = '<span class="dot"></span> Reserved';
                    }
                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                    }
                }
            })
            .catch(err => {
                console.error('Availability check error:', err);
                if (availBox) {
                    availBox.className = 'avail-status-box unavailable';
                    if (availIcon) availIcon.className = 'fa-solid fa-triangle-exclamation';
                    if (availTitle) availTitle.textContent = 'Check Failed';
                    if (availDesc) availDesc.textContent = 'Unable to verify dates right now. Please try again.';
                }
            });
    }

    if (locationSelect && returnLocationInput) {
        locationSelect.addEventListener('change', function() {
            returnLocationInput.value = this.value;
        });
    }

    if (pickupInput && returnInput) {
        pickupInput.addEventListener('change', function() {
            if (new Date(returnInput.value) <= new Date(pickupInput.value)) {
                const nextDay = new Date(pickupInput.value);
                nextDay.setDate(nextDay.getDate() + 3);
                returnInput.value = nextDay.toISOString().split('T')[0];
            }
            recalculateAndCheck();
        });
        returnInput.addEventListener('change', recalculateAndCheck);
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
