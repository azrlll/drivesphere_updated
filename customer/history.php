<?php
$pageTitle = 'Rental History';
$nav = '';
require_once __DIR__ . '/../includes/customer_header.php';

// Fetch customer reservations from database
$st = $pdo->prepare("SELECT r.*, v.brand, v.make, v.model, v.plate_number, v.image_path, v.image_url, v.category, v.transmission, v.fuel_type, v.seats, c.name as category_name
                     FROM reservations r
                     JOIN vehicles v ON r.vehicle_id = v.id
                     LEFT JOIN categories c ON v.category_id = c.id
                     WHERE r.user_id = ? ORDER BY r.created_at DESC, r.id DESC");
$st->execute([$me['id']]);
$dbRows = $st->fetchAll(PDO::FETCH_ASSOC);

// Exemplary records matching Image 2
$demoHistory = [
    [
        'ref' => 'DS-RN-2026-0001',
        'code' => '#DSP-77402-PH',
        'vehicle_id' => 1,
        'name' => 'Toyota Fortuner LTD 2.8L',
        'image' => BASE_URL . 'assets/images/vehicles/toyota-fortuner.jpg',
        'badge' => 'SUV',
        'status_type' => 'en_route',
        'status_pill' => 'En Route',
        'status_pill_style' => 'background:#eff6ff; color:#2563eb;',
        'status_sub' => '&bull; Live Dispatch',
        'spec' => 'Plate # NQM 5520 &bull; 7-Seater Premium SUV',
        'dates' => 'Nov 14, 2025 &ndash; Nov 17, 2025',
        'price_label' => 'Total Booking',
        'price' => '14,112.00',
        'btn_class' => 'btn-hist-navy',
        'btn_label' => 'View Approved Receipt &rarr;',
        'btn_url' => $C . 'receipt.php?ref=DS-RN-2026-0001',
        'is_active_telemetry' => true,
        'bottom_left' => '<i class="fa-solid fa-location-arrow" style="color:#0284c7"></i> Vehicle Telemetry: <b style="color:#0284c7">C-5 Northbound (Arriving in 24 mins)</b>',
        'bottom_right' => 'Booking ID: #DSP-77402-PH'
    ],
    [
        'ref' => 'DS-RN-2026-0002',
        'code' => '#DSP-81093-PH',
        'vehicle_id' => 2,
        'name' => 'Honda City 1.5 RS',
        'image' => BASE_URL . 'assets/images/vehicles/honda-city.jpg',
        'badge' => 'Sedan',
        'status_type' => 'reserved',
        'status_pill' => 'Reserved',
        'status_pill_style' => 'background:#eff6ff; color:#2563eb;',
        'status_sub' => '&bull; Confirmed Security Deposit',
        'spec' => 'Plate # NAK 5821 &bull; Executive Sport Sedan',
        'dates' => 'Dec 01, 2025 &ndash; Dec 04, 2025',
        'price_label' => 'Total Booking',
        'price' => '11,760.00',
        'btn_class' => 'btn-hist-blue',
        'btn_label' => 'View Reserved Receipt &rarr;',
        'btn_url' => $C . 'receipt.php?ref=DS-RN-2026-0002',
        'is_active_telemetry' => false,
        'bottom_left' => '<i class="fa-solid fa-location-dot" style="color:#0284c7"></i> Terminal Hub: BGC Central Station (Tower 1 Bay 4)',
        'bottom_right' => 'Booking ID: #DSP-81093-PH'
    ],
    [
        'ref' => 'DS-RN-2026-0003',
        'code' => '#DSP-82118-PH',
        'vehicle_id' => 3,
        'name' => 'Mitsubishi Montero Sport GT',
        'image' => BASE_URL . 'assets/images/vehicles/mitsubishi-montero-sport.jpg',
        'badge' => 'Executive',
        'status_type' => 'completed',
        'status_pill' => 'Completed / Returned',
        'status_pill_style' => 'background:#f1f5f9; color:#475569;',
        'status_sub' => '&bull; Inspection Verified',
        'spec' => 'Plate # ABC 8891 &bull; 7-Seater Executive SUV',
        'dates' => 'Oct 10, 2025 &ndash; Oct 13, 2025',
        'price_label' => 'Settled Amount',
        'price' => '14,954.00',
        'btn_class' => 'btn-hist-light',
        'btn_label' => 'View Return &amp; Damage Record &rarr;',
        'btn_url' => $C . 'return_record.php?ref=DS-RN-2026-0003',
        'is_active_telemetry' => false,
        'bottom_left' => '<i class="fa-regular fa-circle-check" style="color:#10b981"></i> Return Inspection: Zero Damage Reported &bull; Deposit Released &#8369;5,000.00',
        'bottom_right' => 'Booking ID: #DSP-82118-PH'
    ]
];

// Combine or populate from actual database records if present
if (!empty($dbRows)) {
    $historyList = [];
    foreach ($dbRows as $r) {
        $img = vehicle_image_url($r['image_path'] ?: $r['image_url']);
        $statusType = $r['status'];
        $statusPill = match($statusType) {
            'en_route' => 'En Route',
            'reserved' => 'Reserved',
            'completed' => 'Completed / Returned',
            'returned' => 'Returned',
            'maintenance' => 'Maintenance',
            'rejected' => 'Rejected',
            default => ucfirst($statusType)
        };
        $statusPillStyle = match($statusType) {
            'en_route', 'reserved' => 'background:#eff6ff; color:#2563eb;',
            'completed', 'returned' => 'background:#f1f5f9; color:#475569;',
            'rejected' => 'background:#fef2f2; color:#dc2626;',
            default => 'background:#eff6ff; color:#2563eb;'
        };
        $statusSub = match($statusType) {
            'en_route' => '&bull; Live Dispatch',
            'reserved' => '&bull; Confirmed Security Deposit',
            'completed', 'returned' => '&bull; Inspection Verified',
            'rejected' => '&bull; Booking Declined',
            default => '&bull; Fleet Verified'
        };
        $btnClass = match($statusType) {
            'en_route' => 'btn-hist-navy',
            'reserved' => 'btn-hist-blue',
            default => 'btn-hist-light'
        };
        $btnLabel = match($statusType) {
            'en_route' => 'View Approved Receipt &rarr;',
            'reserved' => 'View Reserved Receipt &rarr;',
            'completed', 'returned' => 'View Return &amp; Damage Record &rarr;',
            default => 'View Receipt &rarr;'
        };
        $btnUrl = in_array($statusType, ['completed', 'returned']) 
            ? $C . 'return_record.php?ref=' . urlencode($r['reference_code'])
            : $C . 'receipt.php?ref=' . urlencode($r['reference_code']);

        $historyList[] = [
            'ref' => $r['reference_code'],
            'code' => '#' . $r['reference_code'],
            'vehicle_id' => $r['vehicle_id'],
            'name' => ($r['brand'] ?: $r['make']) . ' ' . $r['model'],
            'image' => $img,
            'badge' => strtoupper($r['category'] ?: ($r['category_name'] ?: 'Fleet')),
            'status_type' => $statusType,
            'status_pill' => $statusPill,
            'status_pill_style' => $statusPillStyle,
            'status_sub' => $statusSub,
            'spec' => 'Plate # ' . $r['plate_number'] . ' &bull; ' . ($r['category'] ?: ($r['category_name'] ?: 'Vehicle')),
            'dates' => date('M d, Y', strtotime($r['pickup_date'])) . ' &ndash; ' . date('M d, Y', strtotime($r['return_date'])),
            'price_label' => in_array($statusType, ['completed', 'returned']) ? 'Settled Amount' : 'Total Booking',
            'price' => number_format($r['total_price'], 2),
            'btn_class' => $btnClass,
            'btn_label' => $btnLabel,
            'btn_url' => $btnUrl,
            'is_active_telemetry' => ($statusType === 'en_route'),
            'bottom_left' => ($statusType === 'en_route')
                ? '<i class="fa-solid fa-location-arrow" style="color:#0284c7"></i> Vehicle Telemetry: <b style="color:#0284c7">' . e($r['pickup_location']) . ' (En Route)</b>'
                : '<i class="fa-solid fa-location-dot" style="color:#0284c7"></i> Pick-up Hub: ' . e($r['pickup_location']),
            'bottom_right' => 'Booking ID: #' . e($r['reference_code'])
        ];
    }
} else {
    $historyList = $demoHistory;
}

$totalRecordsCount = count($historyList);
?>

<div class="hist-container">
    <!-- Top Back Bar (Matches Image 2) -->
    <div class="settings-top-bar" style="margin-bottom: 8px;">
        <a href="<?= $C ?>dashboard.php" class="settings-back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Header & Telemetry Banner -->
    <div class="hist-telemetry-tag">
        BOOKING TELEMETRY &amp; RECORDS &bull; Metro Manila Fleet Hub
    </div>
    <div class="hist-header-row">
        <div>
            <h1 class="hist-heading">Rental History</h1>
            <p class="hist-sub">Track your ongoing reservations, en route vehicles, and past completed rentals with LTFRB verified documentation.</p>
        </div>

        <div class="hist-stats-card">
            <div class="hist-stats-icon">
                <i class="fa-regular fa-id-card"></i>
            </div>
            <div>
                <div class="hist-stats-num"><?= $totalRecordsCount ?> Records</div>
                <div class="hist-stats-sub">1 En Route &bull; 1 Upcoming</div>
            </div>
        </div>
    </div>

    <!-- Main Split Layout (Matches Image 2) -->
    <div class="hist-split-layout">
        <!-- ================= LEFT COLUMN: RENTAL CARDS ================= -->
        <div class="hist-cards-col">
            <?php foreach ($historyList as $h): ?>
                <div class="hist-card <?= $h['is_active_telemetry'] ? 'active-telemetry' : '' ?>">
                    <div class="hist-card-main">
                        <!-- Media Thumbnail -->
                        <div class="hist-media">
                            <img src="<?= e($h['image']) ?>" alt="<?= e($h['name']) ?>" class="hist-media-img">
                            <span class="hist-media-tag"><?= e($h['badge']) ?></span>
                        </div>

                        <!-- Card Body Details -->
                        <div class="hist-body">
                            <div class="hist-status-line">
                                <span class="hist-status-pill" style="<?= $h['status_pill_style'] ?>">
                                    <span class="hist-status-dot"></span> <?= e($h['status_pill']) ?>
                                </span>
                                <span class="hist-status-sub"><?= $h['status_sub'] ?></span>
                            </div>
                            <h3 class="hist-card-title"><?= e($h['name']) ?></h3>
                            <div class="hist-card-spec"><?= $h['spec'] ?></div>
                            <div class="hist-card-dates">
                                <i class="fa-regular fa-calendar"></i> <?= $h['dates'] ?>
                            </div>
                        </div>

                        <!-- Pricing & CTA Button -->
                        <div class="hist-pricing-box">
                            <div class="hist-price-lbl"><?= e($h['price_label']) ?></div>
                            <div class="hist-price-val">&#8369;<?= e($h['price']) ?></div>
                            <a href="<?= e($h['btn_url']) ?>" class="btn-hist-action <?= e($h['btn_class']) ?>">
                                <?= $h['btn_label'] ?>
                            </a>
                        </div>
                    </div>

                    <!-- Bottom Bar (Telemetry / Terminal / Inspection) -->
                    <div class="hist-bottom-bar">
                        <div class="hist-bottom-left">
                            <?= $h['bottom_left'] ?>
                        </div>
                        <div class="hist-bottom-right">
                            <?= e($h['bottom_right']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- ================= RIGHT COLUMN: TELEMETRY SIDEBAR ================= -->
        <div class="hist-sidebar-col">
            <!-- Need Assistance Support Card -->
            <div class="hist-side-support">
                <div class="hist-support-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3 class="hist-support-title">Need assistance with your booking?</h3>
                <p class="hist-support-desc">
                    Our 24/7 Customer &amp; Dispatch Support Desk is ready to assist with gate pass extensions, route changes, or booking inquiries.
                </p>
                <a href="tel:+63288883748" class="hist-support-phone">
                    <i class="fa-solid fa-phone"></i> (02) 8888-DRIVE
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
