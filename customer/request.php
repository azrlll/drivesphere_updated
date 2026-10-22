<?php
$pageTitle = 'Price Overview';
$nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$vehicleId = (int)($_GET['vehicle_id'] ?? 0);
$st = $pdo->prepare("SELECT v.*, c.name AS category_name FROM vehicles v JOIN categories c ON v.category_id = c.id WHERE v.id = ?");
$st->execute([$vehicleId]);
$v = $st->fetch();

if (!$v) {
    flash_set('error', 'Please choose a vehicle first.');
    redirect('customer/dashboard.php');
}

$pickupIn = $_GET['pickup_date'] ?? date('Y-m-d\T09:00', strtotime('+3 days'));
$returnIn = $_GET['return_date'] ?? date('Y-m-d\T18:00', strtotime('+6 days'));
if (strlen($pickupIn) === 10) $pickupIn .= 'T09:00';
if (strlen($returnIn) === 10) $returnIn .= 'T18:00';

$pickupTime = strtotime($pickupIn);
$returnTime = strtotime($returnIn);
$pickup = date('Y-m-d H:i:s', $pickupTime);
$return = date('Y-m-d H:i:s', $returnTime);

if ($returnTime <= $pickupTime || $pickupTime < time() - 3600 || !vehicle_is_available($pdo, $v['id'], $pickup, $return)) {
    flash_set('error', 'That vehicle is not available for the chosen dates. Please pick other dates.');
    redirect('customer/vehicle.php?id=' . (int)$v['id']);
}

$price = calc_price($v['daily_rate'], rental_days($pickup, $return));
$days = max(1, (int)$price['days']);

// Next reservation reference code preview
$nextResId = (int)$pdo->query("SELECT COALESCE(MAX(id),0)+1 FROM reservations")->fetchColumn();
$refCodePreview = 'DS-PH-' . date('Y') . '-' . str_pad($nextResId, 3, '0', STR_PAD_LEFT);

// User driver details
$driverName = trim($me['first_name'] . ' ' . ($me['last_name'] === '-' ? '' : $me['last_name']));
if ($driverName === '') $driverName = 'Eduardo Jose Reyes';
$driverPhone = $me['phone'] ?: '+63 917 554 9021';
$driverLicense = $me['license_number'] ?: 'N02-14-048291';

// Vehicle local downloaded image
$vehicleImg = vehicle_image_url($v['image_path'] ?: $v['image_url']);

// Tag badge text (e.g. Diesel 4x4)
$fuelTag = str_contains(strtolower($v['fuel_type']), 'diesel') ? 'Diesel 4x4' : ($v['tag_label'] ?: 'Premium');
$firstWordModel = explode(' ', $v['model'])[0] ?? 'vehicle';
?>

<!-- Top Breadcrumbs Bar (Matches Image 2) -->
<div class="req-container">
    <div class="req-top-bar">
        <a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>" class="req-back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Vehicle Details
        </a>
    </div>

    <!-- Main Split Grid -->
    <div class="req-split-grid">

        <!-- ================= LEFT COLUMN: PRICE OVERVIEW ================= -->
        <div class="req-overview-card">
            <!-- Header Row with Reference Code -->
            <div class="req-header-row">
                <h1 class="req-heading">Price Overview</h1>
                <span class="req-ref-badge">Reference: <?= e($refCodePreview) ?></span>
            </div>
            <p class="req-header-sub">Review rental period, vehicle schedule, and transparent fee schedule before final authorization.</p>

            <!-- Vehicle Summary Box -->
            <div class="req-vehicle-box">
                <div class="req-v-thumb-wrap">
                    <img src="<?= e($vehicleImg) ?>" alt="<?= e($v['make'] . ' ' . $v['model']) ?>" class="req-v-thumb">
                    <span class="req-v-tag"><?= e($fuelTag) ?></span>
                </div>
                <div class="req-v-info">
                    <div class="req-v-title-row">
                        <h2 class="req-v-title"><?= e($v['make'] . ' ' . $v['model']) ?></h2>
                        <span class="req-ready-badge"><span class="req-ready-dot"></span> Ready</span>
                    </div>
                    <div class="req-v-specs">
                        <?= e($v['transmission']) ?> &bull; <?= (int)$v['seats'] ?> Seats &bull; <?= e($v['tag_label'] ?: 'Platinum White Pearl') ?>
                    </div>
                    <div class="req-v-tariff-box">
                        <span class="req-v-tariff-label">Daily Tariff:</span>
                        <div class="req-v-tariff-val">&#8369;<?= number_format($v['daily_rate'], 2) ?> <span>/ day</span></div>
                    </div>
                </div>
            </div>

            <!-- Rental Period Box -->
            <div class="req-period-box">
                <div class="req-period-head">
                    <div class="req-period-title">
                        <i class="fa-regular fa-calendar-days"></i> Rental Period
                    </div>
                    <span class="req-days-pill"><?= $days ?> Days Rental</span>
                </div>
                <div class="req-dates-grid">
                    <div class="req-date-cell">
                        <div class="req-date-cell-top">
                            <span>Pick-up Date</span>
                            <span class="req-date-cell-time">09:00 AM Standard</span>
                        </div>
                        <div class="req-date-cell-val">
                            <span><?= date('m/d/Y', $pickupTime) ?></span>
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>
                    <div class="req-date-cell">
                        <div class="req-date-cell-top">
                            <span>Return Date</span>
                            <span class="req-date-cell-time">06:00 PM Grace</span>
                        </div>
                        <div class="req-date-cell-val">
                            <span><?= date('m/d/Y', $returnTime) ?></span>
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>
                </div>
                <div class="req-hub-row">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Metro Manila Hub (BGC High Street Pickup / Drop-off Station)</span>
                </div>
            </div>

            <!-- Itemized Breakdown -->
            <div class="req-breakdown-head">
                <span>ITEMIZED BREAKDOWN</span>
                <span class="req-breakdown-curr">Philippine Peso (PHP)</span>
            </div>

            <!-- Line 1: Base Rental -->
            <div class="req-item-row">
                <div class="req-item-left">
                    <div class="req-item-title">Base Rental Charge</div>
                    <div class="req-item-desc"><?= $days ?> Days &times; &#8369;<?= number_format($v['daily_rate'], 2) ?> / day</div>
                </div>
                <div class="req-item-val">&#8369;<?= number_format($price['base'], 2) ?></div>
            </div>

            <!-- Line 2: Comprehensive Insurance -->
            <div class="req-item-row">
                <div class="req-item-left">
                    <div class="req-item-title">
                        Standard Comprehensive Insurance
                        <i class="fa-solid fa-circle-check" style="color: #0284c7; font-size: 13px;"></i>
                    </div>
                    <div class="req-item-desc">Zero Deductible collision protection &amp; third-party liability</div>
                </div>
                <div class="req-item-val">
                    <span class="req-included-badge">INCLUDED</span>
                    <span>&#8369;0.00</span>
                </div>
            </div>

            <!-- Line 3: VAT -->
            <div class="req-item-row">
                <div class="req-item-left">
                    <div class="req-item-title">Mandatory Government VAT (12%)</div>
                    <div class="req-item-desc">Official Bureau of Internal Revenue e-receipt issued</div>
                </div>
                <div class="req-item-val">&#8369;<?= number_format($price['vat'], 2) ?></div>
            </div>

            <!-- Line 4: Security Deposit -->
            <div class="req-item-row">
                <div class="req-item-left">
                    <div class="req-item-title">
                        Refundable Security Deposit
                        <i class="fa-solid fa-circle-info" style="color: #94a3b8; font-size: 13px;"></i>
                    </div>
                    <div class="req-item-desc">Released immediately upon vehicle return check-in inspection</div>
                </div>
                <div class="req-item-val">&#8369;<?= number_format($price['deposit'], 2) ?>*</div>
            </div>

            <div class="req-deposit-note">
                *Security deposit is pre-authorized on arrival and does not incur upfront card billing.
            </div>

            <!-- Payable Net Total Banner -->
            <div class="req-total-banner">
                <div>
                    <div class="req-total-label">PAYABLE NET TOTAL</div>
                    <div class="req-total-sub">Inclusive of all local transport duties and VAT</div>
                </div>
                <div class="req-total-right">
                    <div class="req-total-caption">Total Price:</div>
                    <div class="req-total-amount">&#8369;<?= number_format($price['total'], 2) ?></div>
                </div>
            </div>
        </div>

        <!-- ================= RIGHT COLUMN: RESERVATION FORM ================= -->
        <div class="req-reservation-card">
            <div class="req-res-head">
                <h2 class="req-res-title">
                    <i class="fa-regular fa-id-card"></i> Reservation
                </h2>
                <span class="req-status-pill">
                    <span class="req-status-dot"></span> Status: Waiting for Confirmation
                </span>
            </div>

            <form method="post" action="<?= BASE_URL ?>actions/request_action.php">
                <?= csrf_field() ?>
                <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                <input type="hidden" name="pickup_date" value="<?= e($pickupIn) ?>">
                <input type="hidden" name="return_date" value="<?= e($returnIn) ?>">
                <input type="hidden" name="pickup_location" value="Metro Manila Hub (BGC High Street Pickup / Drop-off Station)">
                <input type="hidden" name="return_location" value="Metro Manila Hub (BGC High Street Pickup / Drop-off Station)">

                <!-- Reservation Agreement Box -->
                <div class="req-sec-tag">RESERVATION AGREEMENT</div>
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

                <!-- LTO License Number -->
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
                <button type="submit" class="btn-req-book" style="margin-top: 18px;">
                    <i class="fa-regular fa-paper-plane"></i> Request to Book
                </button>

                <!-- Assurance Footer -->
                <div class="req-res-footer">
                    <span><i class="fa-regular fa-circle-check"></i> DriveSphere Enterprise Assurance</span>
                    <span>LTFRB Registered Operator #2024-4902</span>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= BOTTOM FEATURES STRIP (3 CARDS) ================= -->
    <div class="req-features-strip">
        <!-- Feature 1: Fuel Policy -->
        <div class="req-feature-card">
            <div class="req-feature-icon">
                <i class="fa-solid fa-gas-pump"></i>
            </div>
            <div>
                <h4 class="req-feature-title">Same-to-Same Fuel Policy</h4>
                <p class="req-feature-desc">Receive the <?= e($firstWordModel) ?> with 100% full tank diesel, return with same gauge level with zero refueling surcharge.</p>
            </div>
        </div>

        <!-- Feature 2: Cancellation -->
        <div class="req-feature-card">
            <div class="req-feature-icon">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
                <h4 class="req-feature-title">Flexible Free Cancellation</h4>
                <p class="req-feature-desc">Cancel up to 24 hours prior to dispatch window for complete 100% refund without booking penalties.</p>
            </div>
        </div>

        <!-- Feature 3: Comprehensive Coverage -->
        <div class="req-feature-card">
            <div class="req-feature-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h4 class="req-feature-title">Comprehensive Insurance</h4>
                <p class="req-feature-desc">Full LTFRB-compliant commercial coverage and collision damage protection for complete peace of mind.</p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
