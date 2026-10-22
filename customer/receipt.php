<?php
$pageTitle = 'Rental Receipt';
$nav = '';
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../includes/customer_booking.php';

$refCode = $_GET['ref'] ?? '';
$b = load_my_booking($pdo, $refCode);

$isReservedView = in_array($b['status'], ['reserved', 'pending']);
$pickupTime = strtotime($b['pickup_date']);
$returnTime = strtotime($b['return_date']);
$deadlineTime = strtotime($b['pickup_date'] . ' + 2 hours');
$days = max(1, (int)$b['days']);
$specimenCode = substr($b['reference_code'], -4);
?>

<div class="rcpt-container">
    <!-- Top Bar Navigation (Matches Images 1 & 2) -->
    <div class="rcpt-top-bar">
        <a href="<?= $C ?>history.php" class="settings-back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Rental History
        </a>

        <?php if ($isReservedView): ?>
            <div class="rcpt-top-actions">
                <button type="button" class="rcpt-btn-top" onclick="window.print();">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
                <button type="button" class="rcpt-btn-top rcpt-btn-top-pdf" onclick="exportReceiptPDF();">
                    <i class="fa-solid fa-file-pdf"></i> PDF Export
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Receipt Card -->
    <div class="rcpt-main-card">
        <?php if (!$isReservedView): ?>
            <!-- ================= VIEW 1: EN ROUTE / COMPLETED OFFICIAL CUSTOMER VOUCHER (IMAGE 1) ================= -->
            <!-- Header Row -->
            <div class="rcpt-header">
                <div class="rcpt-header-left">
                    <div class="rcpt-header-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="rcpt-title-wrap">
                        <h1 class="rcpt-title-main">Rental Receipt <span>/ Official Customer Voucher</span></h1>
                        <div class="rcpt-header-tag">
                            DRIVESPHERE FLEET VERIFIED &bull; <b>Secured Digital Token</b>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4-Column Meta Summary Box -->
            <div class="rcpt-summary-grid">
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">RENTAL ID</div>
                    <div class="rcpt-summary-val"><?= e($b['reference_code']) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">DATE ISSUED</div>
                    <div class="rcpt-summary-val"><?= date('M d, Y \bull; h:i A', strtotime($b['created_at'])) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">CUSTOMER NAME</div>
                    <div class="rcpt-summary-val"><?= e($b['driver_name']) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">ISSUED BY</div>
                    <div class="rcpt-summary-val blue">BGC Station #04</div>
                </div>
            </div>

            <!-- Fleet Unit Box -->
            <div class="rcpt-fleet-card">
                <div class="rcpt-specimen-box">
                    <img src="<?= BASE_URL ?>images/history_registry_reference.png" alt="Fleet Blueprint" class="rcpt-specimen-img">
                    <span class="rcpt-specimen-tag">PREMIUM SUV CLASS</span>
                </div>
                <div class="rcpt-fleet-info">
                    <div class="rcpt-fleet-top">
                        <span class="rcpt-fleet-tag-blue">SELECTED FLEET UNIT</span>
                        <span class="rcpt-fleet-plate">Plate: <?= e($b['plate_number']) ?></span>
                    </div>
                    <h2 class="rcpt-fleet-model"><?= e($b['make'] . ' ' . $b['model']) ?></h2>
                    <p class="rcpt-fleet-desc">Includes Comprehensive Gold Insurance, Smart RFID Autopass, and 24/7 Customer Support.</p>
                    <div class="rcpt-pills-wrap">
                        <span class="rcpt-pill-item"><i class="fa-regular fa-user"></i> <?= (int)$b['seats'] ?> Seats</span>
                        <span class="rcpt-pill-item"><i class="fa-solid fa-gear"></i> <?= e($b['transmission']) ?></span>
                        <span class="rcpt-pill-item"><i class="fa-solid fa-gas-pump"></i> <?= e($b['fuel_type']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Trip Itinerary & Handover -->
            <div class="rcpt-sec-heading">TRIP ITINERARY &amp; HANDOVER</div>
            <div class="rcpt-itinerary-2col">
                <div class="rcpt-itinerary-card">
                    <div class="rcpt-itinerary-header">
                        <span>PICK-UP DETAILS</span>
                        <i class="fa-solid fa-plane-departure"></i>
                    </div>
                    <div class="rcpt-itinerary-date"><?= date('M d, Y', $pickupTime) ?></div>
                    <div class="rcpt-itinerary-time"><?= date('h:i A', $pickupTime) ?> PST</div>
                    <div class="rcpt-itinerary-hub"><?= e($b['pickup_location']) ?></div>
                    <div class="rcpt-itinerary-addr">5th Avenue cor. 28th St., Bonifacio Global City, Taguig</div>
                </div>

                <div class="rcpt-itinerary-card">
                    <div class="rcpt-itinerary-header">
                        <span>EXPECTED RETURN</span>
                        <i class="fa-solid fa-plane-arrival"></i>
                    </div>
                    <div class="rcpt-itinerary-date"><?= date('M d, Y', $returnTime) ?></div>
                    <div class="rcpt-itinerary-time"><?= date('h:i A', $returnTime) ?> PST (Duration: <?= $days ?> Days)</div>
                    <div class="rcpt-itinerary-hub"><?= e($b['return_location']) ?></div>
                    <div class="rcpt-itinerary-addr">Same Hub Return (Late buffer tolerance: 45 mins)</div>
                </div>
            </div>

            <!-- Fiscal Breakdown -->
            <div class="rcpt-breakdown-bar">
                <span>FISCAL BREAKDOWN</span>
                <span class="rcpt-fiscal-curr">Currency: Philippine Peso (PHP)</span>
            </div>
            <div class="rcpt-breakdown-box">
                <div class="rcpt-breakdown-row">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">Vehicle Base Tariff</div>
                        <div class="rcpt-breakdown-sub"><?= $days ?> billing days @ &#8369;<?= number_format($b['daily_rate'], 2) ?> / day</div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['base_amount'], 2) ?></div>
                </div>
                <div class="rcpt-breakdown-row">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">Mandatory Value-Added Tax (VAT)</div>
                        <div class="rcpt-breakdown-sub">Official Philippine BIR tax rate (12%)</div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['vat_amount'], 2) ?></div>
                </div>
                <div class="rcpt-breakdown-row">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">
                            Pre-authorized Security Deposit <i class="fa-solid fa-circle-info" style="color: #94a3b8; font-size: 12px;"></i>
                        </div>
                        <div class="rcpt-breakdown-sub">Fully refundable within 24h post-inspection</div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['deposit_amount'], 2) ?> <span style="font-size:11px;color:#0284c7;font-weight:700;">[Hold]</span></div>
                </div>

                <!-- Total Settled Banner -->
                <div class="rcpt-settled-bar">
                    <div>
                        <div class="rcpt-settled-title">TOTAL SETTLED AMOUNT</div>
                        <div class="rcpt-settled-amt">
                            &#8369;<?= number_format($b['total_price'], 2) ?>
                            <span class="rcpt-paid-pill">PAID</span>
                        </div>
                    </div>
                    <div>
                        <div class="rcpt-channel-title">PAYMENT CHANNEL</div>
                        <div class="rcpt-channel-name">&bull; GCash Verified e-Wallet</div>
                        <div class="rcpt-channel-ref">Ref: 9028-118-2940</div>
                    </div>
                </div>
            </div>

            <!-- Express Hub Gate Pass Token -->
            <div class="rcpt-gate-pass">
                <div class="rcpt-gate-left">
                    <i class="fa-solid fa-qrcode rcpt-gate-icon"></i>
                    <div>
                        <div class="rcpt-gate-title">Express Hub Gate Pass</div>
                        <div class="rcpt-gate-sub">Present this scannable token to DriveSphere valet at station entry or return.</div>
                    </div>
                </div>
                <img src="<?= BASE_URL ?>images/history_color_codes.png" alt="Pass Bar" style="height: 28px; width: 60px; object-fit: cover; border-radius: 4px;">
            </div>

            <!-- Footer Action Buttons -->
            <div class="rcpt-bottom-actions">
                <a href="#" onclick="exportReceiptPDF(); return false;" class="rcpt-pdf-link">
                    <i class="fa-solid fa-download"></i> Download Official PDF
                </a>
                <button type="button" onclick="window.print();" class="btn-rcpt-confirm">
                    <i class="fa-solid fa-print"></i> Confirm &amp; Print Receipt
                </button>
            </div>

        <?php else: ?>
            <!-- ================= VIEW 2: RESERVED BOOKING RECEIPT (IMAGE 2) ================= -->
            <!-- Header Row -->
            <div class="rcpt-header">
                <div class="rcpt-header-left">
                    <div class="rcpt-header-icon blue-soft">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="rcpt-title-wrap">
                        <div class="rcpt-header-tag" style="margin:0 0 2px;">DRIVESPHERE OFFICIAL VOUCHER</div>
                        <h1 class="rcpt-title-main">Rental Reservation Receipt</h1>
                        <p style="color:#64748b; font-size:12px; margin:2px 0 0;">Your pre-authorized booking has been processed and locked in the central fleet queue.</p>
                    </div>
                </div>
                <span class="rcpt-status-badge-reserved">
                    <span class="rcpt-status-dot-amber"></span> STATUS: RESERVED
                </span>
            </div>

            <!-- 4-Column Meta Summary Box -->
            <div class="rcpt-summary-grid">
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">VEHICLE NAME</div>
                    <div class="rcpt-summary-val"><?= e($b['make'] . ' ' . $b['model']) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">CUSTOMER NAME</div>
                    <div class="rcpt-summary-val"><?= e($b['driver_name']) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">DATE ISSUED</div>
                    <div class="rcpt-summary-val"><?= date('M d, Y', strtotime($b['created_at'])) ?></div>
                </div>
                <div class="rcpt-summary-col">
                    <div class="rcpt-summary-lbl">RENTAL ID</div>
                    <div class="rcpt-summary-val blue"><?= e($b['reference_code']) ?></div>
                </div>
            </div>

            <!-- Dual Specimen & Brand Sync Box -->
            <div class="rcpt-specimen-dual-grid">
                <div class="rcpt-specimen-box" style="height: 140px;">
                    <img src="<?= BASE_URL ?>images/history_registry_reference.png" alt="Fleet Blueprint" class="rcpt-specimen-img">
                    <span class="rcpt-specimen-tag">Reserved Fleet Specimen #<?= e($specimenCode) ?></span>
                </div>
                <div class="rcpt-theme-sync-box">
                    <div class="rcpt-theme-sync-title">
                        <i class="fa-solid fa-circle-check"></i> Brand Theme Sync
                    </div>
                    <img src="<?= BASE_URL ?>images/history_color_codes.png" alt="Color Theme" class="rcpt-theme-sync-img">
                    <span style="font-size: 10px; color: #94a3b8; margin-top: 4px;">Verified Telemetry Profile</span>
                </div>
            </div>

            <!-- Handover & Logistical Specifications -->
            <div class="rcpt-sec-heading">HANDOVER &amp; LOGISTICAL SPECIFICATIONS</div>
            <div class="rcpt-specs-4grid">
                <!-- Card 1: Rate -->
                <div class="rcpt-spec-card">
                    <div class="rcpt-spec-icon">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                    <div class="rcpt-spec-info">
                        <div class="rcpt-spec-lbl">Price Per Day</div>
                        <div class="rcpt-spec-val">&#8369;<?= number_format($b['daily_rate'], 2) ?></div>
                    </div>
                </div>

                <!-- Card 2: Duration -->
                <div class="rcpt-spec-card">
                    <div class="rcpt-spec-icon">
                        <i class="fa-regular fa-calendar"></i>
                    </div>
                    <div class="rcpt-spec-info">
                        <div class="rcpt-spec-lbl">Rental Duration</div>
                        <div class="rcpt-spec-val"><?= $days ?> Days</div>
                        <div class="rcpt-spec-sub"><?= date('M d', $pickupTime) ?> &ndash; <?= date('M d, Y', $returnTime) ?></div>
                    </div>
                </div>

                <!-- Card 3: Date of Pick-up -->
                <div class="rcpt-spec-card">
                    <div class="rcpt-spec-icon">
                        <i class="fa-regular fa-clock"></i>
                    </div>
                    <div class="rcpt-spec-info">
                        <div class="rcpt-spec-lbl">Date of Pick-up</div>
                        <div class="rcpt-spec-val"><?= date('M d, Y \bull; h:i A', $pickupTime) ?></div>
                        <div class="rcpt-spec-sub">Primary check-in slot</div>
                    </div>
                </div>

                <!-- Card 4: Location -->
                <div class="rcpt-spec-card">
                    <div class="rcpt-spec-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div class="rcpt-spec-info">
                        <div class="rcpt-spec-lbl">Pick-up Hub Location</div>
                        <div class="rcpt-spec-val"><?= e($b['pickup_location']) ?></div>
                        <div class="rcpt-spec-sub">Arrival Level Bay 4, Pasay City</div>
                    </div>
                </div>
            </div>

            <!-- Deadline Alert Box -->
            <div class="rcpt-deadline-banner">
                <i class="fa-solid fa-hourglass-half rcpt-deadline-icon"></i>
                <div>
                    <div class="rcpt-deadline-title">DEADLINE OF PICK-UP: <?= date('M d, Y \bull; h:i A', $deadlineTime) ?></div>
                    <p class="rcpt-deadline-desc">Strict 2-hour reservation grace window. Vehicles uncollected by <?= date('h:i A', $deadlineTime) ?> will be re-assigned to the standby fleet according to LTFRB regulations.</p>
                </div>
            </div>

            <!-- Fiscal Breakdown & Pre-Authorization -->
            <div class="rcpt-sec-heading">FISCAL BREAKDOWN &amp; PRE-AUTHORIZATION</div>
            <div class="rcpt-breakdown-box">
                <div class="rcpt-breakdown-row">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">Base Rental (<?= $days ?> Days &times; &#8369;<?= number_format($b['daily_rate'], 2) ?>)</div>
                        <div class="rcpt-breakdown-sub">Includes unlimited Luzon Island tollway telematics</div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['base_amount'], 2) ?></div>
                </div>

                <div class="rcpt-breakdown-row">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">Mandatory Government VAT (12%)</div>
                        <div class="rcpt-breakdown-sub">BIR Official Electronic Invoice itemized</div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['vat_amount'], 2) ?></div>
                </div>

                <!-- Highlight Total Hold -->
                <div class="rcpt-hold-card">
                    <span class="rcpt-hold-lbl">TOTAL RESERVATION HOLD</span>
                    <span class="rcpt-hold-amt">&#8369;<?= number_format($b['total_price'], 2) ?></span>
                </div>

                <div class="rcpt-breakdown-row" style="margin-top: 6px;">
                    <div class="rcpt-fiscal-left">
                        <div class="rcpt-breakdown-title">
                            <i class="fa-solid fa-shield-halved" style="color: #64748b; font-size: 12px; margin-right: 4px;"></i>
                            Security Deposit Due on Handover
                        </div>
                    </div>
                    <div class="rcpt-breakdown-val">&#8369;<?= number_format($b['deposit_amount'], 2) ?></div>
                </div>

                <div style="font-size: 11px; font-style: italic; color: #64748b; margin-top: 10px;">
                    *Refundable within 24 hours post-inspection upon vehicle drop-off with full fuel tank.
                </div>
            </div>

            <!-- Full-width CTA Button -->
            <button type="button" class="btn-rcpt-confirm" style="width: 100%; margin-top: 10px;" onclick="addToCalendar();">
                <i class="fa-regular fa-calendar-check"></i> Confirm Reservation &amp; Add to Calendar
            </button>
        <?php endif; ?>

        <?php if (in_array($b['status'], ['returned', 'completed'])): ?>
            <div class="rcpt-bottom-actions" style="margin-top: 16px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                <a class="btn-hist-action btn-hist-light" href="<?= $C ?>return_record.php?ref=<?= urlencode($b['reference_code']) ?>">
                    <i class="fa-solid fa-clipboard-check"></i> View Return Record
                </a>
                <a class="btn-hist-action btn-hist-blue" href="<?= $C ?>charges.php?ref=<?= urlencode($b['reference_code']) ?>">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Final Charges
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= BASE_URL ?>js/html2pdf.bundle.min.js"></script>
<script>
function exportReceiptPDF() {
    const refCode = '<?= e($b['reference_code']) ?>';
    const card = document.querySelector('.rcpt-main-card');
    const pdfButtons = document.querySelectorAll('.rcpt-btn-top-pdf, .rcpt-pdf-link');

    const triggerServerDownload = function() {
        window.location.href = '<?= BASE_URL ?>actions/download_receipt_pdf.php?ref=' + encodeURIComponent(refCode);
    };

    if (typeof html2pdf === 'undefined' || !card) {
        triggerServerDownload();
        return;
    }

    const originalHtml = [];
    pdfButtons.forEach(function(btn, i) {
        originalHtml[i] = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Downloading PDF...';
        btn.style.pointerEvents = 'none';
    });

    const restoreButtons = function() {
        pdfButtons.forEach(function(btn, i) {
            btn.innerHTML = originalHtml[i] || '<i class="fa-solid fa-file-pdf"></i> PDF Export';
            btn.style.pointerEvents = 'auto';
        });
    };

    document.body.classList.add('rcpt-exporting-pdf');

    const opt = {
        margin: [8, 8, 8, 8],
        filename: 'DriveSphere_Receipt_' + refCode + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            logging: false,
            scrollY: 0
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().set(opt).from(card).save()
        .then(function() {
            document.body.classList.remove('rcpt-exporting-pdf');
            restoreButtons();
        })
        .catch(function(err) {
            console.warn('html2pdf client render fallback:', err);
            document.body.classList.remove('rcpt-exporting-pdf');
            restoreButtons();
            triggerServerDownload();
        });
}

function addToCalendar() {
    const title = 'DriveSphere Rental: <?= addslashes($b['make'] . ' ' . $b['model']) ?>';
    const description = 'Reservation Reference: <?= addslashes($b['reference_code']) ?>\\nPick-up Location: <?= addslashes($b['pickup_location']) ?>';
    const location = '<?= addslashes($b['pickup_location']) ?>';
    const start = '<?= date('Ymd\THis', $pickupTime) ?>';
    const end = '<?= date('Ymd\THis', $returnTime) ?>';

    const icsContent = 
`BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//DriveSphere Philippines//Rental Booking//EN
BEGIN:VEVENT
SUMMARY:${title}
DESCRIPTION:${description}
LOCATION:${location}
DTSTART:${start}
DTEND:${end}
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR`;

    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.setAttribute('download', 'DriveSphere_Reservation_<?= e($b['reference_code']) ?>.ics');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert('Reservation event downloaded! You can import it into Google Calendar, Outlook, or Apple Calendar.');
}
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
