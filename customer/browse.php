<?php
$pageTitle = 'Browse Vehicles';
$nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$q = trim($_GET['q'] ?? '');

// Vehicle catalog matching SCREEN mockup
$categories = [
    'sedan' => [
        'id' => 2,
        'name' => 'Sedan & Compact',
        'subtitle' => 'Agile, fuel-efficient daily city commuters and luxury executive sedans',
        'icon' => 'fa-solid fa-car-side',
        'page' => '1 / 2',
        'vehicles' => [
            [
                'id' => 8,
                'name' => 'Toyota Vios 1.5 G',
                'class' => 'Class: City Economy Sedan • 2024',
                'image' => BASE_URL . 'images/browse_vios.png',
                'status' => 'available',
                'badge' => 'Sedan',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => 'CVT 7-Speed'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '5 Seats', 'sub' => 'Ergonomic'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '1.5L Petrol', 'sub' => 'Dual VVT-i']
                ],
                'rate' => '1,800'
            ],
            [
                'id' => 2,
                'name' => 'Honda Civic RS Turbo',
                'class' => 'Class: Premium Performance Sedan • 2024',
                'image' => BASE_URL . 'images/browse_civic.png',
                'status' => 'available',
                'badge' => 'Sport Sedan',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'CVT w/ Paddle', 'sub' => 'Drive Modes'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '5 Seats', 'sub' => 'Leather & Suede'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '1.5L Turbo', 'sub' => 'VTEC Turbo']
                ],
                'rate' => '3,500'
            ],
            [
                'id' => 5,
                'name' => 'Mazda 3 Fastback',
                'class' => 'Class: Premium Compact • 2023',
                'image' => BASE_URL . 'images/browse_mazda3.png',
                'status' => 'reserved',
                'badge' => 'Fastback',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => 'SkyActiv-Drive'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '5 Seats', 'sub' => 'Bose Audio'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.0L Petrol', 'sub' => 'SkyActiv-G']
                ],
                'rate' => '3,200'
            ]
        ]
    ],
    'suv' => [
        'id' => 1,
        'name' => 'SUVs & Crossovers',
        'subtitle' => 'Robust 7-seater family explorers with 4x4 off-road and provincial versatility',
        'icon' => 'fa-solid fa-truck-monster',
        'page' => '1 / 3',
        'vehicles' => [
            [
                'id' => 1,
                'name' => 'Toyota Fortuner LTD 2.8L',
                'class' => 'Class: 7-Seater Premium SUV • 2024',
                'image' => BASE_URL . 'images/browse_fortuner.png',
                'status' => 'available',
                'badge' => 'Mid-Size SUV',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => '4x4 Auto', 'sub' => '6-Speed AT'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Split Foldable'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.8L Diesel', 'sub' => 'Turbo Intercooler']
                ],
                'rate' => '4,200'
            ],
            [
                'id' => 3,
                'name' => 'Mitsubishi Montero Sport GT',
                'class' => 'Class: 7-Seater Executive SUV • 2024',
                'image' => BASE_URL . 'images/browse_montero.png',
                'status' => 'available',
                'badge' => 'Mid-Size SUV',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '8-Speed AT'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Leather Interior'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.4L Diesel', 'sub' => 'MIVEC Clean D']
                ],
                'rate' => '3,900'
            ],
            [
                'id' => 6,
                'name' => 'Ford Everest Titanium+ 4x4',
                'class' => 'Class: Luxury 7-Seater SUV • 2024',
                'image' => BASE_URL . 'images/browse_everest.png',
                'status' => 'available',
                'badge' => 'Full Feature 4x4',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => '10-Speed AT', 'sub' => 'e-Shifter 4WD'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Power Folding 3rd'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.0L Bi-Turbo', 'sub' => 'Diesel 210HP']
                ],
                'rate' => '4,600'
            ]
        ]
    ],
    'van' => [
        'id' => 3,
        'name' => 'Vans & MPVs',
        'subtitle' => 'High-capacity group travel, executive airport transfers, and long-distance comfort',
        'icon' => 'fa-solid fa-van-shuttle',
        'page' => '1 / 2',
        'vehicles' => [
            [
                'id' => 4,
                'name' => 'Toyota HiAce Grandia Elite',
                'class' => 'Class: 10-Passenger Luxury Van • 2024',
                'image' => BASE_URL . 'images/browse_hiace.png',
                'status' => 'available',
                'badge' => 'Luxury Van',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '6-Speed AT'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '10 Seats', 'sub' => 'Ottoman Captains'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.8L Diesel', 'sub' => '1GD-FTV']
                ],
                'rate' => '5,800'
            ],
            [
                'id' => 9,
                'name' => 'Toyota Innova 2.8 V',
                'class' => 'Class: 8-Passenger Family MPV • 2024',
                'image' => BASE_URL . 'images/browse_innova.png',
                'status' => 'available',
                'badge' => 'Multi-Purpose (MPV)',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '6-Speed Sequential'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '8 Seats', 'sub' => 'Spacious Luggage'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.8L Diesel', 'sub' => 'Common Rail']
                ],
                'rate' => '2,800'
            ],
            [
                'id' => 10,
                'name' => 'Hyundai Staria Premium',
                'class' => 'Class: 7-Seater Luxury Lounge • 2024',
                'image' => BASE_URL . 'images/browse_staria.png',
                'status' => 'reserved',
                'badge' => 'VIP Lounge MPV',
                'specs' => [
                    ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '8-Speed Shift-by-Wire'],
                    ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Relaxation Seats'],
                    ['icon' => 'fa-solid fa-gas-pump', 'title' => '2.2L Diesel', 'sub' => 'CRDi Turbo']
                ],
                'rate' => '5,200'
            ]
        ]
    ]
];

// Filter modal parameters
$filterType = $_GET['type'] ?? 'all';
$filterTrans = $_GET['transmission'] ?? 'all';
$filterMinPrice = (int)($_GET['min_price'] ?? 0);
$filterMaxPrice = (int)($_GET['max_price'] ?? 0);
$filterBrand = $_GET['brand'] ?? 'all';

$activeFilters = 0;
if ($filterType !== 'all') $activeFilters++;
if ($filterTrans !== 'all') $activeFilters++;
if ($filterBrand !== 'all') $activeFilters++;
if ($filterMinPrice > 0 || $filterMaxPrice > 0) $activeFilters++;
if ($activeFilters === 0) $activeFilters = 1; // Default 1 (Metro Manila / Location active)

// If search query or filters are active, filter vehicles across categories
foreach ($categories as $catKey => &$catData) {
    if ($filterType !== 'all' && $filterType !== $catKey) {
        $catData['vehicles'] = [];
        continue;
    }

    $catData['vehicles'] = array_filter($catData['vehicles'], function($v) use ($q, $filterTrans, $filterBrand, $filterMinPrice, $filterMaxPrice) {
        if ($q !== '') {
            $qLower = strtolower($q);
            $matchesQ = strpos(strtolower($v['name']), $qLower) !== false 
                || strpos(strtolower($v['class']), $qLower) !== false
                || strpos(strtolower($v['badge']), $qLower) !== false;
            if (!$matchesQ) return false;
        }

        if ($filterBrand !== 'all') {
            if (!str_starts_with(strtolower($v['name']), strtolower($filterBrand))) return false;
        }

        if ($filterTrans !== 'all') {
            $transMatch = false;
            foreach ($v['specs'] as $s) {
                if (stripos($s['title'], $filterTrans) !== false || stripos($s['sub'], $filterTrans) !== false) {
                    $transMatch = true;
                    break;
                }
            }
            if (!$transMatch) return false;
        }

        $numericRate = (float)str_replace(',', '', $v['rate']);
        if ($filterMinPrice > 0 && $numericRate < $filterMinPrice) return false;
        if ($filterMaxPrice > 0 && $numericRate > $filterMaxPrice) return false;

        return true;
    });
}
unset($catData);
?>

<!-- ==========================================================================
     1. FLOATING SEARCH & FILTER BAR
     ========================================================================== -->
<form method="get" action="browse.php" class="browse-search-bar" id="browseSearchForm">
    <div class="search-input-wrap">
        <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
        <input type="text" name="q" id="browseSearchInput" value="<?= e($q) ?>" placeholder="Search by model, brand, or location..." autocomplete="off">
    </div>

    <div class="location-chip" id="locationSelector" title="Selected Pickup Hub">
        <i class="fa-solid fa-location-dot" style="color: #64748b;"></i>
        <span>Metro Manila - BGC Hub (Active)</span>
        <i class="fa-solid fa-chevron-down" style="font-size: 11px; margin-left: 2px;"></i>
    </div>

    <div class="filter-chip-btn" id="filterToggleBtn" title="Active Filter Criteria">
        <i class="fa-solid fa-sliders"></i>
        <span>Filter</span>
        <span class="filter-badge"><?= (int)$activeFilters ?></span>
    </div>

    <button type="submit" class="btn-find-vehicles">
        Find Vehicles
    </button>
</form>

<!-- ==========================================================================
     2. DRIVESPHERE ASSURANCE GUARANTEE HERO BANNER
     ========================================================================== -->
<section class="assurance-hero">
    <div class="assurance-left">
        <div class="assurance-pill">
            <i class="fa-solid fa-shield-halved"></i> DRIVESPHERE ASSURANCE GUARANTEE
        </div>
        <h1 class="assurance-title">
            Premium Car Rental In<br>Metro Manila
        </h1>
        <p class="assurance-desc">
            Immediate dispatch, 100% sanitized vehicles, and transparent daily rates with zero deposit hassle for verified members.
        </p>
        <div class="assurance-perks">
            <span class="perk-item">
                <i class="fa-solid fa-shield-halved"></i> Comprehensive Insurance
            </span>
            <span class="perk-item">
                <i class="fa-solid fa-infinity"></i> Unlimited Mileage Options
            </span>
            <span class="perk-item">
                <i class="fa-solid fa-headset"></i> 24/7 Roadside Assist
            </span>
        </div>
    </div>

    <div class="assurance-right">
        <div class="hero-diag-card">
            <img src="<?= BASE_URL ?>images/browse_hero_diagram.png" alt="Live Fleet Telemetry Architecture" class="hero-diag-img">
            <span class="live-fleet-badge">
                <span class="live-fleet-dot"></span> Live Fleet View
            </span>
        </div>
    </div>

    <div class="hero-dots">
        <span class="h-dot active"></span>
        <span class="h-dot inactive"></span>
    </div>
</section>

<!-- ==========================================================================
     3. LIVE INVENTORY SECTION HEADER
     ========================================================================== -->
<div class="live-inventory-header">
    <span class="tag-live">LIVE INVENTORY</span>
    <h2 class="title-live">Get Our Rental Cars</h2>
    <p class="desc-live">Select from our well-maintained fleet available for immediate booking.</p>
</div>

<!-- ==========================================================================
     4. CATEGORY VEHICLE ROWS (Sedans, SUVs, Vans)
     ========================================================================== -->
<?php foreach ($categories as $catKey => $cat): ?>
    <?php if (empty($cat['vehicles']) && $q !== '') continue; ?>
    <section class="category-row" id="cat-<?= e($catKey) ?>">
        <div class="category-header">
            <div class="cat-title-group">
                <div class="cat-icon-sq">
                    <i class="<?= e($cat['icon']) ?>"></i>
                </div>
                <div>
                    <h3 class="cat-name"><?= e($cat['name']) ?></h3>
                    <p class="cat-sub"><?= e($cat['subtitle']) ?></p>
                </div>
            </div>

            <div class="cat-pagination">
                <button class="cat-page-btn" type="button" aria-label="Previous Page"><i class="fa-solid fa-chevron-left"></i></button>
                <span class="cat-page-num"><?= e($cat['page']) ?></span>
                <button class="cat-page-btn" type="button" aria-label="Next Page"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="vehicle-grid-3">
            <?php foreach ($cat['vehicles'] as $v): ?>
                <?php $isAvailable = ($v['status'] === 'available'); ?>
                <div class="v-card-exact">
                    <!-- Photo & Badges -->
                    <div class="v-card-media">
                        <img src="<?= e($v['image']) ?>" alt="<?= e($v['name']) ?>" class="v-card-img" loading="lazy">
                        
                        <!-- Top-Right Status Badge -->
                        <?php if ($isAvailable): ?>
                            <span class="v-status-badge available">
                                <span class="status-dot"></span> Available
                            </span>
                        <?php else: ?>
                            <span class="v-status-badge reserved">
                                <span class="status-dot"></span> Reserved
                            </span>
                        <?php endif; ?>

                        <!-- Bottom-Left Vehicle Type Pill -->
                        <span class="v-type-badge"><?= e($v['badge']) ?></span>
                    </div>

                    <!-- Card Body -->
                    <div class="v-card-content">
                        <div class="v-card-header">
                            <h4 class="v-card-title"><?= e($v['name']) ?></h4>
                            <button class="v-fav-btn" type="button" aria-label="Add to Favorites" onclick="this.classList.toggle('active');">
                                <i class="fa-regular fa-heart"></i>
                            </button>
                        </div>
                        <div class="v-class-line"><?= e($v['class']) ?></div>

                        <!-- 3-Column Specifications Row -->
                        <div class="v-specs-row">
                            <?php foreach ($v['specs'] as $spec): ?>
                                <div class="spec-col">
                                    <i class="<?= e($spec['icon']) ?>"></i>
                                    <span class="spec-primary"><?= e($spec['title']) ?></span>
                                    <span class="spec-secondary"><?= e($spec['sub']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Card Footer: Rate & Action Button -->
                        <div class="v-card-footer">
                            <div class="v-price-block">
                                <span class="price-label">Rental rate</span>
                                <div>
                                    <span class="price-val">₱<?= e($v['rate']) ?></span>
                                    <span class="price-period">/ day</span>
                                </div>
                            </div>

                            <a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>" class="<?= $isAvailable ? 'btn-details-blue' : 'btn-details-muted' ?>">
                                View Details <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<!-- ==========================================================================
     5. EFFORTLESS SELF-DRIVE CAR RENTAL IN 3 SIMPLE STEPS
     ========================================================================== -->
<section class="steps-container">
    <span class="steps-tag">HOW DRIVESPHERE WORKS</span>
    <h3 class="steps-heading">Effortless Self-Drive Car Rental in 3 Simple Steps</h3>

    <div class="steps-grid">
        <!-- Step 1 -->
        <div class="step-card">
            <div class="step-badge-wrap">
                <div class="step-icon-box">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <span class="step-tag-pill">STEP 01</span>
            </div>
            <h4 class="step-title">Select Hub &amp; Vehicle</h4>
            <p class="step-desc">Choose your desired pick-up hub across Metro Manila or book direct NAIA airport terminal delivery.</p>
        </div>

        <!-- Step 2 -->
        <div class="step-card">
            <div class="step-badge-wrap">
                <div class="step-icon-box">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <span class="step-tag-pill">STEP 02</span>
            </div>
            <h4 class="step-title">Digital ID Verification</h4>
            <p class="step-desc">Upload your Philippine Driver's License or International Driving Permit for approval in under 15 minutes.</p>
        </div>

        <!-- Step 3 -->
        <div class="step-card">
            <div class="step-badge-wrap">
                <div class="step-icon-box">
                    <i class="fa-solid fa-key"></i>
                </div>
                <span class="step-tag-pill">STEP 03</span>
            </div>
            <h4 class="step-title">Keyless Handover &amp; Drive</h4>
            <p class="step-desc">Inspect the vehicle with our digital checklist and enjoy full freedom on North &amp; South Luzon expressways.</p>
        </div>
    </div>
</section>

<!-- Dynamic Instant Search Script for Real-time Card Filtering -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('browseSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.v-card-exact');
            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (!query || text.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });

            // Hide empty category sections
            document.querySelectorAll('.category-row').forEach(row => {
                const visibleCards = row.querySelectorAll('.v-card-exact[style*="display: flex"], .v-card-exact:not([style*="display: none"])');
                row.style.display = (visibleCards.length > 0) ? 'block' : 'none';
            });
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
