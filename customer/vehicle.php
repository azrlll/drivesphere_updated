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
        redirect('customer/browse.php');
    }
}

// Vehicle specific metadata / defaults matching Image 2
$fleetId = 'MNL-FT-' . str_pad($v['id'], 3, '0', STR_PAD_LEFT);
$categoryClass = strtoupper($v['category_name'] ?? 'PREMIUM SUV CLASS');

// Date parameters (default to 3-day rental: Nov 15, 2025 -> Nov 18, 2025 or current relative)
$pickupDateVal = $_GET['pickup_date'] ?? date('Y-m-d', strtotime('+1 day'));
$returnDateVal = $_GET['return_date'] ?? date('Y-m-d', strtotime('+4 days'));

$pickupTime = strtotime($pickupDateVal);
$returnTime = strtotime($returnDateVal);
if ($returnTime <= $pickupTime) {
    $returnTime = strtotime('+3 days', $pickupTime);
    $returnDateVal = date('Y-m-d', $returnTime);
}
$days = max(1, (int)round(($returnTime - $pickupTime) / 86400));
$dailyRate = (float)$v['daily_rate'];
$subtotal = $days * $dailyRate;
$deposit = 5000.00;
$total = $subtotal; // As shown in mockup: Estimated Total matches days * rate, with insurance included and deposit noted

// Image resolution
$vehiclePhoto = !empty($v['image_url']) && str_starts_with($v['image_url'], 'http')
    ? $v['image_url']
    : (file_exists(__DIR__ . '/../images/rental_fortuner_photo.png') && $v['id'] == 1 
        ? BASE_URL . 'images/rental_fortuner_photo.png' 
        : BASE_URL . 'images/browse_fortuner.png');

// Custom descriptions per vehicle or mockup standard
$descP1 = !empty($v['description']) 
    ? $v['description'] 
    : 'Engineered for executive journeys, family holiday retreats, and extended provincial touring across Northern and Southern Luzon. The ' . e($v['make'] . ' ' . $v['model']) . ' delivers an authoritative presence combined with whisper-quiet cabin insulation and responsive performance.';

$descP2 = 'Equipped with full safety sensors, adaptive drive controls, and advanced connectivity. Whether navigating metropolitan Manila corridors or winding ascent routes up to Baguio and Tagaytay, this self-drive vehicle delivers absolute confidence and spacious comfort.';
?>

<div class="v-details-container">
    <!-- Top Action Bar -->
    <div class="v-top-bar">
        <a href="<?= $C ?>dashboard.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <div class="v-top-actions">
            <button type="button" class="btn-v-action" id="wishlistBtn" onclick="this.classList.toggle('active'); this.querySelector('span').innerText = this.classList.contains('active') ? 'Saved to Wishlist' : 'Save to Wishlist';">
                <i class="fa-regular fa-bookmark"></i> <span>Save to Wishlist</span>
            </button>
            <button type="button" class="btn-v-action" id="shareBtn" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');">
                <i class="fa-solid fa-share-nodes"></i> Share
            </button>
        </div>
    </div>

    <!-- Main Split Layout -->
    <div class="v-main-split">
        <!-- Left Column: Details, Visuals, Overview, Features -->
        <div class="v-left-col">
            <!-- Header Block -->
            <div class="v-header-block">
                <div class="v-category-fleet">
                    <?= e($categoryClass) ?> &bull; Fleet ID: <?= e($fleetId) ?>
                </div>
                <div class="v-title-row">
                    <h1 class="v-main-title"><?= e($v['make'] . ' ' . $v['model']) ?></h1>
                    <span class="v-status-pill">
                        <span class="dot"></span> Available for Rent
                    </span>
                </div>
            </div>

            <!-- Big Visual Card -->
            <div class="v-visual-card">
                <img src="<?= e($vehiclePhoto) ?>" alt="<?= e($v['make'] . ' ' . $v['model']) ?>" class="v-visual-img">
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
                        <span class="key-spec-val"><?= (int)$v['seats'] ?> Seats</span>
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
                <span class="v-section-sub">MY2024 LTD Grade</span>
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
                        <span class="v-overview-val"><?= e($v['fuel_type']) ?></span>
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
        </div>

        <!-- Right Column: Sticky Booking Widget Sidebar -->
        <div class="v-right-col">
            <div class="booking-widget">
                <!-- Daily Rental Rate Header -->
                <div class="widget-price-header">
                    <div class="w-price-left">
                        <span class="w-price-lbl">DAILY RENTAL RATE</span>
                        <div>
                            <span class="w-price-amount">₱<?= number_format($dailyRate, 0) ?></span>
                            <span class="w-price-period">/ day</span>
                        </div>
                    </div>
                    <div class="w-price-right">
                        <span class="w-tier-badge">Standard Tier</span>
                        <span class="w-tax-lbl">Taxes Included</span>
                    </div>
                </div>

                <form method="get" action="<?= BASE_URL ?>customer/request.php" id="bookingForm">
                    <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">

                    <!-- Pick-up Location Box -->
                    <div class="w-location-box">
                        <label class="w-input-lbl">Pick-up Location</label>
                        <div class="w-location-input-wrap">
                            <i class="fa-solid fa-location-dot" style="color: #0284c7;"></i>
                            <span>Ninoy Aquino Int'l Airport (T3)</span>
                        </div>
                        <input type="hidden" name="pickup_location" value="Ninoy Aquino Int'l Airport (T3)">
                    </div>

                    <!-- Pick-up & Return Date Boxes -->
                    <div class="w-dates-row">
                        <div class="w-date-box">
                            <span class="w-input-lbl" style="margin:0;"><i class="fa-regular fa-calendar"></i> Pick-up Date</span>
                            <input type="date" name="pickup_date" id="pickupDateInput" value="<?= e($pickupDateVal) ?>" required>
                        </div>
                        <div class="w-date-box">
                            <span class="w-input-lbl" style="margin:0;"><i class="fa-regular fa-calendar"></i> Return Date</span>
                            <input type="date" name="return_date" id="returnDateInput" value="<?= e($returnDateVal) ?>" required>
                        </div>
                    </div>

                    <!-- Price Breakdown List -->
                    <div class="w-breakdown-list">
                        <div class="w-breakdown-item">
                            <span id="rateCalculationLabel">₱<?= number_format($dailyRate, 0) ?> &times; <?= $days ?> Days</span>
                            <b id="rateSubtotalVal">₱<?= number_format($subtotal, 0) ?></b>
                        </div>
                        <div class="w-breakdown-item">
                            <span>Comprehensive Insurance</span>
                            <span class="included">Included</span>
                        </div>
                        <div class="w-breakdown-item">
                            <span>Refundable Security Deposit</span>
                            <b>₱<?= number_format($deposit, 0) ?></b>
                        </div>
                    </div>

                    <div class="w-divider"></div>

                    <!-- Estimated Total -->
                    <div class="w-total-row">
                        <span class="w-total-lbl">Estimated Total</span>
                        <b class="w-total-val" id="estimatedTotalVal">₱<?= number_format($total, 0) ?></b>
                    </div>

                    <!-- Proceed CTA Button -->
                    <button type="submit" class="btn-proceed-reserve">
                        Proceed to Price Overview &amp; Reservation &rarr;
                    </button>
                </form>

                <!-- Trust Guarantee List -->
                <div class="w-trust-list">
                    <div class="w-trust-item">
                        <i class="fa-regular fa-circle-check" style="color: #22c55e;"></i>
                        <span><strong>Free Cancellation</strong> up to 24 hours before pickup time.</span>
                    </div>
                    <div class="w-trust-item">
                        <i class="fa-solid fa-headset" style="color: #0284c7;"></i>
                        <span>24/7 Roadside Assistance &amp; Fleet Hotline: <strong>(02) 8888-DRIVE</strong></span>
                    </div>
                    <div class="w-trust-item">
                        <i class="fa-solid fa-shield-halved" style="color: #0284c7;"></i>
                        <span>Full LTFRB &amp; Comprehensive Commercial Insurance coverage provided.</span>
                    </div>
                </div>

                <!-- DriveSphere Direct Box -->
                <div class="direct-unit-card">
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
    </div>
</div>

<!-- Interactive Date & Price Calculation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickupInput = document.getElementById('pickupDateInput');
    const returnInput = document.getElementById('returnDateInput');
    const rateCalcLabel = document.getElementById('rateCalculationLabel');
    const rateSubtotal = document.getElementById('rateSubtotalVal');
    const estimatedTotal = document.getElementById('estimatedTotalVal');
    const dailyRate = <?= (float)$dailyRate ?>;

    function updatePrice() {
        if (!pickupInput || !returnInput) return;
        const pDate = new Date(pickupInput.value);
        const rDate = new Date(returnInput.value);

        if (isNaN(pDate) || isNaN(rDate)) return;

        const diffTime = rDate - pDate;
        let days = Math.round(diffTime / (1000 * 60 * 60 * 24));
        if (days < 1) days = 1;

        const subtotal = days * dailyRate;

        rateCalcLabel.innerHTML = '₱' + dailyRate.toLocaleString() + ' &times; ' + days + ' Day' + (days > 1 ? 's' : '');
        rateSubtotal.innerText = '₱' + subtotal.toLocaleString();
        estimatedTotal.innerText = '₱' + subtotal.toLocaleString();
    }

    if (pickupInput && returnInput) {
        pickupInput.addEventListener('change', function() {
            if (new Date(returnInput.value) <= new Date(pickupInput.value)) {
                const nextDay = new Date(pickupInput.value);
                nextDay.setDate(nextDay.getDate() + 3);
                returnInput.value = nextDay.toISOString().split('T')[0];
            }
            updatePrice();
        });
        returnInput.addEventListener('change', updatePrice);
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
