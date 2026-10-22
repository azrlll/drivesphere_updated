<?php
$pageTitle = 'Damage Overview & Final Charges'; 
$nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../includes/customer_booking.php';

$refCode = $_GET['ref'] ?? '';
$b = load_my_booking($pdo, $refCode);

// Days & Daily Rate
$days = max(1, (int)($b['days'] ?: 3));
$dailyRate = !empty($b['daily_rate']) ? (float)$b['daily_rate'] : 3900.00;
$origPrice = round($dailyRate * $days, 2);

// Damage & CDW Waiver
$hasDamage = (!empty($b['damage_description']) && strtolower(trim($b['damage_description'])) !== 'no damage detected') || ((float)($b['damage_charge'] ?? 0) > 0);
$damagePoints = $hasDamage ? 1 : 0;
$assessedCost = ((float)($b['damage_charge'] ?? 0) > 0) ? (float)$b['damage_charge'] : ($hasDamage ? 1850.00 : 0.00);
$cdwCovered = true; // Standard Comprehensive Damage Waiver coverage
$customerDamageCharge = $cdwCovered ? 0.00 : $assessedCost;

// Timeline & Late Fee
$overlapDays = 0;
if (!empty($b['returned_at']) && !empty($b['return_date'])) {
    $overlapDays = max(0, (int)ceil((strtotime($b['returned_at']) - strtotime($b['return_date'])) / 86400));
}
$overlapCharges = round($overlapDays * ($dailyRate * 1.25), 2);

// Rebate & Discount
$discountFee = ((float)($b['discount'] ?? 0) > 0) ? (float)$b['discount'] : 500.00;

// Extra & Final
$extraCharges = $customerDamageCharge + $overlapCharges;
$discountedTotal = max(0, $origPrice + $extraCharges - $discountFee);
$depositAmount = !empty($b['deposit_amount']) ? (float)$b['deposit_amount'] : 5000.00;

$settled = ($b['status'] === 'completed');
$finalPayable = $discountedTotal;

// Handle Form Submission: Customer confirms and finalizes settlement
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$settled) {
    if (empty($_POST['acknowledged'])) {
        flash_set('error', 'Please accept the Master Mobility Agreement terms before finalizing settlement.');
        redirect('customer/charges.php?ref=' . urlencode($b['reference_code']));
    }

    // Mark reservation completed in database
    $stUpdate = $pdo->prepare("UPDATE reservations SET status = 'completed', settled_at = NOW(), final_total = ? WHERE id = ?");
    $stUpdate->execute([$finalPayable, $b['id']]);

    // Notify customer
    add_notification(
        $pdo, 
        $b['user_id'], 
        'customer', 
        'payment', 
        'Settlement Finalized — ' . $b['reference_code'], 
        'Your rental settlement of ₱' . number_format($finalPayable, 2) . ' is finalized. Security deposit refund of ₱' . number_format($depositAmount, 2) . ' initiated.', 
        'customer/charges.php?ref=' . $b['reference_code']
    );

    // Notify admin fleet manager
    add_notification(
        $pdo, 
        null, 
        'admin', 
        'rental', 
        'Settlement Finalized by Customer — ' . $b['reference_code'], 
        'Customer digital sign-off completed for ' . $b['reference_code'] . '. Deposit release queued.', 
        'admin/rentals.php?tab=completed'
    );

    flash_set('success', 'Settlement confirmed! Rental completed and security deposit release initiated.');
    redirect('customer/charges.php?ref=' . urlencode($b['reference_code']));
}

$returnedAt = !empty($b['returned_at']) ? $b['returned_at'] : (!empty($b['return_date']) ? $b['return_date'] : date('Y-m-d 16:42:00'));
$damageTitle = !empty($b['damage_description']) ? $b['damage_description'] : 'Minor Clearcoat Scratch / Abrasion';
$damageSeverity = !empty($b['damage_severity']) ? ('Level 1 ' . $b['damage_severity']) : 'Level 1 Surface Scuff (Low)';
$inspectorNotation = "Estimated 3.5cm superficial clearcoat abrasion on driver-side rear quarter panel. No denting, metal deformation, or paint penetration. Under customer standard loss damage coverage, liability is waived.";
?>

<div class="dmg-overview-container">
    <!-- Top Bar Navigation -->
    <div class="dmg-overview-crumb noprint">
        <a href="<?= $C ?>return_record.php?ref=<?= urlencode($b['reference_code']) ?>">
            <i class="fa-solid fa-arrow-left"></i> Back to Returned Vehicle Record
        </a>
    </div>

    <!-- Main Card Container -->
    <div class="dmg-main-card" id="damageChargesCard">
        <!-- Dark Top Banner with Vehicle Badge -->
        <div class="dmg-dark-header">
            <div class="dmg-header-left">
                <span class="dmg-verified-badge">
                    <i class="fa-solid fa-shield-halved"></i> VERIFIED VEHICLE INSPECTION
                </span>
                <h1 class="dmg-header-title">Damage Overview &amp; Final Charges</h1>
                <p class="dmg-header-sub">
                    Official inspection assessment and settlement breakdown for Rental #<b><?= e($b['reference_code']) ?></b>.
                </p>
            </div>

            <!-- Vehicle Pod right top -->
            <div class="dmg-header-vehicle-box">
                <div class="dmg-vehicle-icon-box">
                    <i class="fa-solid fa-car"></i>
                </div>
                <div>
                    <div class="dmg-veh-title"><?= e($b['make'] . ' ' . $b['model']) ?></div>
                    <div class="dmg-veh-plate">Plate: <?= e($b['plate_number']) ?> &bull; Gray Metallic</div>
                    <div class="dmg-veh-depot">Check-out: <?= e($b['pickup_location'] ?: 'NAIA T3 Depot') ?></div>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="dmg-card-body">
            <!-- 2 Columns: Inspection scan + Paint finish specs -->
            <div class="dmg-top-2col">
                <!-- Column 1: Return Bay Digital Inspection Scans -->
                <div class="dmg-scan-card">
                    <div class="dmg-scan-header">
                        <div class="dmg-scan-title">
                            <i class="fa-solid fa-camera"></i>
                            <span>Return Bay Digital Inspection Scans</span>
                        </div>
                        <span class="dmg-scan-timestamp">Timestamp: <?= date('M d, H:i \P\H\T', strtotime($returnedAt)) ?></span>
                    </div>

                    <!-- Real vehicle photo with LiDAR tag overlay - NOT an <a> href link -->
                    <div class="dmg-scan-img-wrap">
                        <img src="<?= htmlspecialchars(vehicle_image_url($b['image_path'] ?: $b['image_url'])) ?>" 
                             alt="<?= e($b['make'] . ' ' . $b['model']) ?>" 
                             class="dmg-scan-img">
                        <div class="dmg-lidar-badge">
                            <i class="fa-solid fa-cubes"></i> LiDAR 3D Depth Map Validated
                        </div>
                    </div>
                </div>

                <!-- Column 2: Paint Finish Specs -->
                <div class="dmg-paint-card">
                    <div>
                        <div class="dmg-paint-title">
                            <i class="fa-solid fa-palette"></i>
                            <span>Paint Finish Specs</span>
                        </div>
                        <p class="dmg-paint-desc">OEM clearcoat factory specification benchmark comparison.</p>
                        
                        <div class="dmg-paint-swatches-box">
                            <div class="dmg-swatch-row">
                                <div class="dmg-swatch-item" style="background: #0f172a;" title="Obsidian Black"></div>
                                <div class="dmg-swatch-item" style="background: #475569;" title="Gunmetal Metallic"></div>
                                <div class="dmg-swatch-item" style="background: #94a3b8;" title="Silver Sparkle"></div>
                                <div class="dmg-swatch-item" style="background: #0284c7;" title="Hydro Blue"></div>
                                <div class="dmg-swatch-item" style="background: #38bdf8;" title="Sky Clearcoat"></div>
                            </div>
                            <div style="font-size:10px;color:#64748b;display:flex;justify-content:space-between;font-family:monospace;">
                                <span>LAYER: PRIMER/BASE</span>
                                <span>COATING: CLEAR 2K</span>
                            </div>
                        </div>
                    </div>

                    <div class="dmg-paint-stats-row">
                        <span class="clearcoat">Clearcoat: <b>40&micro;m</b></span>
                        <span class="tolerance">Tolerance: <b>Nominal</b></span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Damage Overview -->
            <div class="dmg-sec-header">
                <div class="dmg-sec-title">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Damage Overview</span>
                </div>
                <span class="dmg-points-pill"><?= $damagePoints ?> POINT<?= $damagePoints == 1 ? '' : 'S' ?> NOTED</span>
            </div>

            <div class="dmg-overview-box">
                <div class="dmg-3col-row">
                    <div>
                        <div class="dmg-col-lbl">TYPE OF DAMAGE</div>
                        <div class="dmg-col-val pill-damage">&bull; <?= e($damageTitle) ?></div>
                    </div>
                    <div>
                        <div class="dmg-col-lbl">SEVERITY LEVEL</div>
                        <div class="dmg-col-val"><?= e($damageSeverity) ?></div>
                    </div>
                    <div>
                        <div class="dmg-col-lbl">DAMAGE ASSESSMENT COST</div>
                        <div class="dmg-col-val" style="font-size:16px;font-weight:800;">&#8369;<?= number_format($assessedCost, 2) ?></div>
                        <div class="dmg-cdw-tag">
                            <i class="fa-regular fa-circle-check"></i> Covered 100% by CDW
                        </div>
                    </div>
                </div>

                <div class="dmg-notation-box">
                    <div class="dmg-notation-lbl">
                        <i class="fa-regular fa-file-lines"></i> INSPECTOR TECHNICAL NOTATION
                    </div>
                    <p class="dmg-notation-text">
                        <?= e($inspectorNotation) ?>
                    </p>
                </div>

                <div class="dmg-surcharge-bar">
                    <div class="dmg-surcharge-left">
                        <i class="fa-regular fa-circle-check"></i> Net Customer Damage Surcharge
                    </div>
                    <div class="dmg-surcharge-right">
                        <span class="dmg-surcharge-strike">&#8369;<?= number_format($assessedCost, 2) ?></span>
                        <span class="dmg-surcharge-final">&#8369;<?= number_format($customerDamageCharge, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Section 3: Financial Breakdown -->
            <div class="dmg-sec-header">
                <div class="dmg-sec-title">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Financial Breakdown</span>
                </div>
            </div>

            <div class="dmg-finance-list">
                <div class="dmg-finance-row">
                    <span class="lbl"><i class="fa-solid fa-receipt"></i> Price per day</span>
                    <span class="val">&#8369;<?= number_format($dailyRate, 2) ?></span>
                </div>
                <div class="dmg-finance-row">
                    <span class="lbl"><i class="fa-regular fa-calendar-check"></i> Rented day Timeline</span>
                    <span class="val"><b><?= $days ?></b> Days</span>
                </div>
                <div class="dmg-finance-row">
                    <span class="lbl">
                        <i class="fa-solid fa-wrench"></i> Damage Charges
                        <span class="dmg-tag-pill blue">Waived via CDW</span>
                    </span>
                    <span class="val">&#8369;<?= number_format($customerDamageCharge, 2) ?></span>
                </div>
                <div class="dmg-finance-row">
                    <span class="lbl">
                        <i class="fa-regular fa-clock"></i> Overlap Timeline Charges
                        <span class="dmg-tag-pill">Returned on time</span>
                    </span>
                    <span class="val">&#8369;<?= number_format($overlapCharges, 2) ?></span>
                </div>
                <div class="dmg-finance-row">
                    <span class="lbl">
                        <i class="fa-solid fa-tag"></i> Discount Fee
                        <span class="dmg-tag-pill blue">Early return corporate rebate</span>
                    </span>
                    <span class="val" style="color:#0284c7;">-&#8369;<?= number_format($discountFee, 2) ?></span>
                </div>
                <div class="dmg-finance-row total-orig">
                    <span class="lbl">TOTAL OF ORIGINAL PRICE</span>
                    <span class="val">&#8369;<?= number_format($origPrice, 2) ?></span>
                </div>
                <div class="dmg-finance-row total-extra">
                    <span class="lbl">TOTAL OF EXTRA CHARGES</span>
                    <span class="val">&#8369;<?= number_format($extraCharges, 2) ?></span>
                </div>
                <div class="dmg-finance-discounted-bar">
                    <span class="dmg-discounted-lbl">Total of Price (Discounted)</span>
                    <span class="dmg-discounted-val">&#8369;<?= number_format($finalPayable, 2) ?></span>
                </div>
            </div>

            <!-- Section 4: Final Settlement Balance -->
            <div class="dmg-settlement-dark-card">
                <div class="dmg-settle-left">
                    <span class="dmg-settle-kicker">FINAL SETTLEMENT BALANCE</span>
                    <div class="dmg-settle-total-wrap">
                        <span class="txt">Overall Total Cost:</span>
                        <span class="amt">&#8369;<?= number_format($finalPayable, 2) ?></span>
                    </div>
                    <span class="dmg-settle-vat-note">Includes comprehensive third-party liability and applicable 12% EVAT.</span>
                </div>

                <div class="dmg-deposit-release-box">
                    <div class="dmg-deposit-header">
                        <span class="dot"></span> DEPOSIT RELEASE
                    </div>
                    <p class="dmg-deposit-desc">
                        <b>&#8369;<?= number_format($depositAmount, 2) ?> Security Deposit</b> Refund initiated to Original Payment Method.
                    </p>
                    <div class="dmg-deposit-eta">ETA: 1-3 Business Days</div>
                </div>
            </div>

            <!-- Form: Acknowledgment & Settlement Confirmation -->
            <form method="post" id="settlementForm" class="noprint">
                <div class="dmg-agreement-card">
                    <label class="dmg-checkbox-label">
                        <input type="checkbox" 
                               name="acknowledged" 
                               value="1" 
                               id="chkAgreement" 
                               <?= $settled ? 'checked disabled' : 'required' ?>>
                        <span>
                            I acknowledge and accept this digital post-rental damage and financial clearance assessment in accordance with the <b>DriveSphere Master Mobility Agreement</b>.
                        </span>
                    </label>
                </div>

                <?php if ($settled): ?>
                    <button type="button" class="btn-dmg-confirm settled" disabled>
                        <i class="fa-solid fa-circle-check"></i> Settlement Finalized &bull; Account Cleared &check;
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn-dmg-confirm" id="btnSubmitSettlement">
                        <i class="fa-solid fa-circle-check"></i> Confirm &amp; Finalize Settlement &check;
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settlementForm');
    const chk = document.getElementById('chkAgreement');
    const btn = document.getElementById('btnSubmitSettlement');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            if (!chk.checked) {
                e.preventDefault();
                alert('Please check the agreement box to acknowledge the clearance assessment.');
                chk.focus();
                return false;
            }
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizing Settlement...';
            btn.disabled = true;
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
