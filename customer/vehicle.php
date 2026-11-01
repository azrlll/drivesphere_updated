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

// Fetch all active Philippine branches from database
$allBranches = get_all_branches($pdo, true);
$branchesByCode = [];
$hubLabels = [];
foreach ($allBranches as $bRow) {
    $branchesByCode[$bRow['code']] = $bRow;
    $hubLabels[$bRow['code']] = $bRow['name'];
}
if (empty($hubLabels)) {
    $hubLabels = [
        'bgc'     => 'Metro Manila - BGC Central Hub (Active)',
        'mnl'     => "NAIA Airport Terminal 3 Hub - Pasay",
        'makati'  => 'Makati CBD Hub (Ayala Center)',
        'ortigas' => 'Ortigas Center Hub - Pasig',
        'crk'     => "Clark Int'l Airport Hub (Pampanga)",
        'ceb'     => 'Cebu City - Mactan Airport Hub',
        'dvo'     => 'Davao City - Lanang Hub'
    ];
}

// Hub & Location resolution
$hubParam = $_GET['hub'] ?? $_GET['pickup_location'] ?? 'bgc';
$pickupLocationDisplay = $hubLabels[$hubParam] ?? (in_array($hubParam, $hubLabels) ? $hubParam : reset($hubLabels));
$currentBranch = get_branch_by_id_or_code($pdo, $hubParam) ?: get_branch_by_id_or_code($pdo, $v['branch_id'] ?? 1);
$branchOpHours = $currentBranch['operating_hours'] ?? '8:00 AM – 8:00 PM';

// Vehicle specific metadata / defaults matching Image mockup
$fleetPlate = !empty($v['plate_number']) ? $v['plate_number'] : ('MNL-FT-' . str_pad($v['id'], 3, '0', STR_PAD_LEFT));
$categoryClass = strtoupper($v['category_name'] ?? 'PREMIUM SUV CLASS');

// Time parameters (Operating Hours: 08:00 to 20:00)
$pickupTimeVal = $_GET['pickup_time'] ?? '09:00';
$returnTimeVal = $_GET['return_time'] ?? '18:00';

// Date parameters (default to next valid 48-72 hour advance slot, within 8 AM - 8 PM office hours)
$pickupDateVal = $_GET['pickup_date'] ?? null;
$returnDateVal = $_GET['return_date'] ?? null;

if (!$pickupDateVal || !$returnDateVal) {
    // 48-72 hours advance default (+2 days 09:00 AM)
    $candPickupTime = strtotime('+52 hours');
    // Ensure pickup hour is between 08:00 and 20:00
    $candPickupHour = (int)date('G', $candPickupTime);
    if ($candPickupHour < 8) {
        $candPickupTime = strtotime(date('Y-m-d 09:00:00', $candPickupTime));
    } elseif ($candPickupHour >= 20) {
        $candPickupTime = strtotime(date('Y-m-d 09:00:00', strtotime('+1 day', $candPickupTime)));
    }
    
    // Check if 48-72 hour window holds
    $now = time();
    $diffH = ($candPickupTime - $now) / 3600;
    if ($diffH < 48) $candPickupTime = $now + (49 * 3600);
    if ($diffH > 72) $candPickupTime = $now + (70 * 3600);
    
    $candPickupHour = (int)date('G', $candPickupTime);
    if ($candPickupHour < 8 || $candPickupHour >= 20) {
        $candPickupTime = strtotime(date('Y-m-d 10:00:00', $candPickupTime));
    }

    $pickupDateVal = date('Y-m-d', $candPickupTime);
    $pickupTimeVal = date('H:i', $candPickupTime);
    
    // Default return: +3 days at 18:00
    $candReturnTime = strtotime('+3 days 18:00:00', strtotime($pickupDateVal));
    $returnDateVal = date('Y-m-d', $candReturnTime);
    $returnTimeVal = '18:00';
}

$pickupSql = date('Y-m-d H:i:s', strtotime($pickupDateVal . ' ' . $pickupTimeVal));
$returnSql = date('Y-m-d H:i:s', strtotime($returnDateVal . ' ' . $returnTimeVal));

$pickupTime = strtotime($pickupSql);
$returnTime = strtotime($returnSql);
if ($returnTime <= $pickupTime) {
    $returnTime = strtotime('+3 days 18:00:00', $pickupTime);
    $returnDateVal = date('Y-m-d', $returnTime);
    $returnTimeVal = '18:00';
    $returnSql = date('Y-m-d H:i:s', $returnTime);
}

$days = rental_days($pickupSql, $returnSql);
$dailyRate = (float)$v['daily_rate'];
$pricing = calc_price($dailyRate, $days);
$subtotal = $pricing['base'];
$lateFeeRate = $dailyRate * 0.10;
$deposit = $pricing['deposit'];
$total = $pricing['total'];

// Initial server-side availability & policy check
$schedCheck = validate_booking_schedule($pdo, $pickupSql, $returnSql, $currentBranch['id'] ?? 1);
$isScheduleValid = $schedCheck['valid'];
$isInitiallyAvailable = $isScheduleValid && vehicle_is_available($pdo, $v['id'], $pickupSql, $returnSql);

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
                        <span class="key-spec-val"><?= (int)($v['passenger_doors'] ?? ($v['category'] === 'Sedan' ? 4 : 5)) ?> Doors</span>
                    </div>
                </div>
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-gauge-high"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">CONSUMPTION</span>
                        <span class="key-spec-val"><?= e($v['fuel_consumption'] ?? '14.5 km/L') ?></span>
                    </div>
                </div>
                <div class="key-spec-box">
                    <div class="key-spec-icon"><i class="fa-solid fa-dharmachakra"></i></div>
                    <div class="key-spec-text">
                        <span class="key-spec-lbl">TRANSMISSION</span>
                        <span class="key-spec-val"><?= e($v['transmission']) ?></span>
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
                        <span class="v-overview-val"><?= e($v['exterior_color'] ?? 'Platinum White Pearl') ?></span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-couch v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">INTERIOR COLOR</span>
                        <span class="v-overview-val"><?= e($v['interior_color'] ?? 'Dual-Tone Black & Charcoal') ?></span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gauge-high v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">TRANSMISSION</span>
                        <span class="v-overview-val"><?= e($v['transmission']) ?></span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gears v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">ENGINE CAPACITY</span>
                        <span class="v-overview-val"><?= e($v['engine_specs'] ?: ($v['specs_text'] ?: '1.5L 4-Cylinder DOHC')) ?></span>
                    </div>
                </div>
                <div class="v-overview-item">
                    <i class="fa-solid fa-gas-pump v-overview-icon"></i>
                    <div class="v-overview-content">
                        <span class="v-overview-lbl">FUEL TYPE</span>
                        <span class="v-overview-val"><?= e($v['fuel_type']) ?></span>
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

                <!-- Card 2: Rental Period Card with Office Hours & Policy Notice -->
                <div class="merged-card merged-period-card" style="margin-top: 18px;">
                    <div class="merged-period-head">
                        <div class="merged-card-title">
                            <i class="fa-regular fa-calendar-days"></i> Rental Period &amp; Schedule
                        </div>
                        <span class="merged-days-badge" id="daysBadge"><?= $days ?> Day<?= $days > 1 ? 's' : '' ?> Rental</span>
                    </div>

                    <!-- Policy & Operating Hours Notice Banner (Phase 2 & 3) -->
                    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; font-size: 11.5px; line-height: 1.5; color: #1e40af;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; margin-bottom: 2px;">
                            <i class="fa-solid fa-clock"></i> Official Office Hours: 8:00 AM – 8:00 PM (PST)
                        </div>
                        <div>
                            <strong>Advance Booking Policy:</strong> Reservations must be made 48 to 72 hours before the scheduled pickup time. (Same-day pickup and return durations supported).
                        </div>
                    </div>

                    <!-- Date & Time Grid -->
                    <?php
                    $timeSlots = [
                        '08:00' => '08:00 AM', '08:30' => '08:30 AM', '09:00' => '09:00 AM', '09:30' => '09:30 AM',
                        '10:00' => '10:00 AM', '10:30' => '10:30 AM', '11:00' => '11:00 AM', '11:30' => '11:30 AM',
                        '12:00' => '12:00 PM', '12:30' => '12:30 PM', '13:00' => '01:00 PM', '13:30' => '01:30 PM',
                        '14:00' => '02:00 PM', '14:30' => '02:30 PM', '15:00' => '03:00 PM', '15:30' => '03:30 PM',
                        '16:00' => '04:00 PM', '16:30' => '04:30 PM', '17:00' => '05:00 PM', '17:30' => '05:30 PM',
                        '18:00' => '06:00 PM', '18:30' => '06:30 PM', '19:00' => '07:00 PM', '19:30' => '07:30 PM',
                        '20:00' => '08:00 PM'
                    ];
                    ?>
                    <div class="merged-dates-grid">
                        <div class="merged-date-cell">
                            <div class="merged-date-meta">
                                <span class="date-lbl">Pick-up Date</span>
                                <span class="time-lbl"><i class="fa-solid fa-business-time"></i> 8 AM – 8 PM</span>
                            </div>
                            <div class="merged-date-input-wrap" style="margin-bottom: 6px;">
                                <input type="date" name="pickup_date" id="pickupDateInput" value="<?= e($pickupDateVal) ?>" required min="<?= date('Y-m-d') ?>">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #475569;">
                                <span>Time:</span>
                                <select name="pickup_time" id="pickupTimeSelect" style="background:#fff; border:1px solid #cbd5e1; border-radius:6px; padding:3px 6px; font-size:11.5px; font-weight:600; color:#0f172a; outline:none; width:100%;">
                                    <?php foreach ($timeSlots as $tKey => $tLabel): ?>
                                        <option value="<?= $tKey ?>" <?= ($pickupTimeVal === $tKey || substr($pickupTimeVal,0,5) === $tKey) ? 'selected' : '' ?>><?= $tLabel ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="merged-date-cell">
                            <div class="merged-date-meta">
                                <span class="date-lbl">Return Date</span>
                                <span class="time-lbl"><i class="fa-solid fa-business-time"></i> 8 AM – 8 PM</span>
                            </div>
                            <div class="merged-date-input-wrap" style="margin-bottom: 6px;">
                                <input type="date" name="return_date" id="returnDateInput" value="<?= e($returnDateVal) ?>" required min="<?= date('Y-m-d') ?>">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: #475569;">
                                <span>Time:</span>
                                <select name="return_time" id="returnTimeSelect" style="background:#fff; border:1px solid #cbd5e1; border-radius:6px; padding:3px 6px; font-size:11.5px; font-weight:600; color:#0f172a; outline:none; width:100%;">
                                    <?php foreach ($timeSlots as $tKey => $tLabel): ?>
                                        <option value="<?= $tKey ?>" <?= ($returnTimeVal === $tKey || substr($returnTimeVal,0,5) === $tKey) ? 'selected' : '' ?>><?= $tLabel ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hub / Branch Selection with Distance & Travel Time Estimate (Phase 4) -->
                    <div class="merged-hub-row">
                        <i class="fa-solid fa-location-dot" style="color: #0284c7;"></i>
                        <select name="pickup_location" id="pickupLocationSelect" class="merged-hub-select" required>
                            <?php foreach ($allBranches as $b): ?>
                                <option value="<?= e($b['name']) ?>" data-code="<?= e($b['code']) ?>" data-hours="<?= e($b['operating_hours']) ?>" data-city="<?= e($b['city']) ?>" <?= ($pickupLocationDisplay === $b['name'] || $hubParam === $b['code']) ? 'selected' : '' ?>>
                                    <?= e($b['name']) ?> (<?= e($b['city']) ?>) &bull; <?= e($b['operating_hours']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="return_location" id="returnLocationInput" value="<?= e($pickupLocationDisplay) ?>">
                    </div>

                    <!-- Travel Time & Distance Validation Hint -->
                    <div id="travelTimeBox" style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 7px 10px; font-size: 11px; color: #475569; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-route" style="color: #0284c7;"></i>
                        <span><strong>Estimated Travel:</strong> <span id="travelTimeEstimate">~25–40 mins</span> via city arterial roads. Ensure you arrive within operating hours (8 AM – 8 PM).</span>
                    </div>
                </div>

                <!-- Live Availability Feedback Box -->
                <div id="availStatusBox" class="avail-status-box <?= $isInitiallyAvailable ? 'available' : 'unavailable' ?>" style="margin-top: 18px;">
                    <div class="avail-status-icon">
                        <i class="fa-solid <?= $isInitiallyAvailable ? 'fa-circle-check' : 'fa-circle-xmark' ?>" id="availStatusIcon"></i>
                    </div>
                    <div class="avail-status-content">
                        <strong id="availStatusTitle"><?= $isInitiallyAvailable ? 'Available for Selected Dates' : ($isScheduleValid ? 'Unavailable for Selected Dates' : 'Scheduling Policy Notice') ?></strong>
                        <p id="availStatusDesc">
                            <?= $isInitiallyAvailable 
                                ? 'Vehicle is verified in fleet stock and open for booking from ' . date('M d, Y g:i A', $pickupTime) . ' to ' . date('M d, Y g:i A', $returnTime) . '.'
                                : ($isScheduleValid ? 'Vehicle is currently reserved for part of these dates. Please choose different dates.' : e($schedCheck['error'])) ?>
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

                    <!-- Payment Method Selection (Phase 8) -->
                    <div class="req-field" style="margin-top: 14px;">
                        <div class="req-field-head">
                            <label class="req-field-label">Preferred Payment Method</label>
                            <span class="req-field-badge">Secured Settlement</span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px;">
                            <label style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; background: #f8fafc; font-size: 12px; font-weight: 600;">
                                <input type="radio" name="payment_method" value="Cash on Pickup" checked onchange="togglePaymentRef(this.value)">
                                <span><i class="fa-solid fa-money-bill-wave" style="color:#16a34a"></i> Cash on Pickup</span>
                            </label>
                            <label style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; background: #f8fafc; font-size: 12px; font-weight: 600;">
                                <input type="radio" name="payment_method" value="GCash" onchange="togglePaymentRef(this.value)">
                                <span><i class="fa-solid fa-mobile-screen" style="color:#0284c7"></i> GCash</span>
                            </label>
                            <label style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; background: #f8fafc; font-size: 12px; font-weight: 600;">
                                <input type="radio" name="payment_method" value="Maya" onchange="togglePaymentRef(this.value)">
                                <span><i class="fa-solid fa-wallet" style="color:#059669"></i> Maya</span>
                            </label>
                            <label style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; background: #f8fafc; font-size: 12px; font-weight: 600;">
                                <input type="radio" name="payment_method" value="Bank Transfer" onchange="togglePaymentRef(this.value)">
                                <span><i class="fa-solid fa-building-columns" style="color:#475569"></i> Bank Transfer</span>
                            </label>
                        </div>

                        <!-- Dynamic digital payment reference field -->
                        <div id="digitalPaymentInfoBox" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; font-size: 11.5px; color: #166534; margin-top: 8px;">
                            <div style="font-weight: 700; margin-bottom: 4px;" id="digitalPayTitle">GCash E-Wallet Payment:</div>
                            <div style="margin-bottom: 6px;" id="digitalPayAccount">Account: <strong>0917-554-9021</strong> (DriveSphere Fleet Inc.)</div>
                            <div class="req-input-box" style="margin-top: 4px;">
                                <i class="fa-solid fa-receipt"></i>
                                <input type="text" name="payment_reference" id="paymentReferenceInput" maxlength="50" placeholder="Enter Reference / Transaction No. (Optional for demonstration)">
                            </div>
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

<!-- Interactive Date, Time, Payment & Live Availability Script -->
<script>
function togglePaymentRef(val) {
    const box = document.getElementById('digitalPaymentInfoBox');
    const title = document.getElementById('digitalPayTitle');
    const acc = document.getElementById('digitalPayAccount');
    if (!box) return;

    if (val === 'Cash on Pickup') {
        box.style.display = 'none';
    } else {
        box.style.display = 'block';
        if (val === 'GCash') {
            title.textContent = 'GCash E-Wallet Payment:';
            acc.innerHTML = 'Account: <strong>0917-554-9021</strong> (DriveSphere Inc.)';
        } else if (val === 'Maya') {
            title.textContent = 'Maya E-Wallet Payment:';
            acc.innerHTML = 'Account: <strong>0918-888-3748</strong> (DriveSphere Inc.)';
        } else if (val === 'Bank Transfer') {
            title.textContent = 'BDO / BPI Commercial Bank Transfer:';
            acc.innerHTML = 'BDO Account: <strong>0012-3849-2018</strong> | BPI Account: <strong>4820-1928-33</strong>';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const pickupInput = document.getElementById('pickupDateInput');
    const returnInput = document.getElementById('returnDateInput');
    const pickupTimeInput = document.getElementById('pickupTimeSelect');
    const returnTimeInput = document.getElementById('returnTimeSelect');
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
    const travelTimeBox = document.getElementById('travelTimeEstimate');
    const vehicleId = <?= (int)$v['id'] ?>;
    const dailyRate = <?= (float)$dailyRate ?>;

    function recalculateAndCheck() {
        if (!pickupInput || !returnInput) return;
        const pVal = pickupInput.value;
        const rVal = returnInput.value;
        const pTime = pickupTimeInput ? pickupTimeInput.value : '09:00';
        const rTime = returnTimeInput ? returnTimeInput.value : '18:00';
        const loc = locationSelect ? locationSelect.value : '';

        if (!pVal || !rVal) return;

        const pFull = pVal + 'T' + pTime;
        const rFull = rVal + 'T' + rTime;
        const pDate = new Date(pFull);
        const rDate = new Date(rFull);

        let diffSecs = (rDate - pDate) / 1000;
        let diffDays = Math.max(1, Math.ceil(diffSecs / 86400));

        const subtotal = diffDays * dailyRate;
        const vat = subtotal * 0.12;
        const total = subtotal + vat;
        const lateRate = dailyRate * 0.10;

        if (daysBadge) daysBadge.textContent = diffDays + ' Day' + (diffDays > 1 ? 's' : '') + ' Rental';
        if (itemDays) itemDays.textContent = diffDays;
        if (itemBaseSubtotal) itemBaseSubtotal.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (itemLateRate) itemLateRate.textContent = '+₱' + lateRate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' / day';
        if (netTotalVal) netTotalVal.textContent = '₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        // Set status box into querying mode
        if (availBox) {
            availBox.className = 'avail-status-box';
            if (availIcon) availIcon.className = 'fa-solid fa-circle-notch fa-spin';
            if (availTitle) availTitle.textContent = 'Verifying operating hours & availability...';
            if (availDesc) availDesc.textContent = 'Validating 8 AM–8 PM office hours and 48–72h advance policy...';
        }

        const url = '<?= BASE_URL ?>actions/check_availability.php?vehicle_id=' + vehicleId +
            '&pickup_date=' + encodeURIComponent(pVal) +
            '&pickup_time=' + encodeURIComponent(pTime) +
            '&return_date=' + encodeURIComponent(rVal) +
            '&return_time=' + encodeURIComponent(rTime) +
            '&pickup_location=' + encodeURIComponent(loc);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    if (availBox) {
                        availBox.className = 'avail-status-box available';
                        if (availIcon) availIcon.className = 'fa-solid fa-circle-check';
                        if (availTitle) availTitle.textContent = 'Available for Selected Schedule';
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
                        if (availTitle) availTitle.textContent = data.policy_error ? 'Booking Policy Restriction' : 'Unavailable for Selected Dates';
                        if (availDesc) availDesc.textContent = data.message;
                    }
                    if (topAvailBadge) {
                        topAvailBadge.className = 'merged-avail-badge unavailable';
                        if (topAvailText) topAvailText.textContent = data.policy_error ? 'Policy Restriction' : 'Reserved';
                    }
                    if (leftStatusPill) {
                        leftStatusPill.className = 'v-status-pill reserved';
                        leftStatusPill.innerHTML = '<span class="dot"></span> ' + (data.policy_error ? 'Policy Notice' : 'Reserved');
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
            // Update travel estimate based on city
            const opt = this.options[this.selectedIndex];
            const city = opt ? opt.getAttribute('data-city') : '';
            if (travelTimeBox) {
                if (city.includes('Manila') || city.includes('Taguig') || city.includes('Pasay') || city.includes('Makati')) {
                    travelTimeBox.textContent = '~20–35 mins via C-5 / EDSA / Skyway';
                } else if (city.includes('Pampanga') || city.includes('Angeles')) {
                    travelTimeBox.textContent = '~1h 15m via NLEX / SCTEX';
                } else if (city.includes('Cebu') || city.includes('Lapu')) {
                    travelTimeBox.textContent = '~25–40 mins via CCLEX / Mactan Causeway';
                } else {
                    travelTimeBox.textContent = '~30–45 mins via City Arterial Route';
                }
            }
            recalculateAndCheck();
        });
    }

    if (pickupInput && returnInput) {
        pickupInput.addEventListener('change', function() {
            if (new Date(returnInput.value) < new Date(pickupInput.value)) {
                returnInput.value = pickupInput.value;
            }
            recalculateAndCheck();
        });
        returnInput.addEventListener('change', recalculateAndCheck);
    }
    if (pickupTimeInput) pickupTimeInput.addEventListener('change', recalculateAndCheck);
    if (returnTimeInput) returnTimeInput.addEventListener('change', recalculateAndCheck);
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
