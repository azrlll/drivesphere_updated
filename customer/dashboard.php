<?php
$pageTitle = 'Customer Dashboard';
$nav = 'dashboard';
require_once __DIR__ . '/../includes/customer_header.php';

$q = trim($_GET['q'] ?? '');

// Vehicle catalog loaded directly from MySQL Database
$categoryMeta = [
    'sedan' => [
        'id' => 2,
        'name' => 'Sedan & Compact',
        'subtitle' => 'Agile, fuel-efficient daily city commuters and luxury executive sedans',
        'icon' => 'fa-solid fa-car-side',
        'page' => '1 / 2'
    ],
    'hatchback' => [
        'id' => 4,
        'name' => 'Hatchbacks & City Cars',
        'subtitle' => 'Compact 5-door urban runabouts with exceptional fuel mileage and tight parking ease',
        'icon' => 'fa-solid fa-car',
        'page' => '1 / 2'
    ],
    'suv' => [
        'id' => 1,
        'name' => 'SUVs & Crossovers',
        'subtitle' => 'Robust 7-seater family explorers with 4x4 off-road and provincial versatility',
        'icon' => 'fa-solid fa-truck-monster',
        'page' => '1 / 3'
    ],
    'mpv' => [
        'id' => 5,
        'name' => 'MPVs & Family Shuttles',
        'subtitle' => 'Versatile 7-seater family carriers featuring flexible folding seats and plush riding comfort',
        'icon' => 'fa-solid fa-people-roof',
        'page' => '1 / 2'
    ],
    'van' => [
        'id' => 3,
        'name' => 'Vans & Group Shuttles',
        'subtitle' => 'High-capacity 12 to 15-passenger group travel, corporate shuttles, and airport transfers',
        'icon' => 'fa-solid fa-van-shuttle',
        'page' => '1 / 1'
    ],
    'pickup' => [
        'id' => 6,
        'name' => 'Pickups & Utility 4x4',
        'subtitle' => 'Heavy-duty double-cab 4x4 utility pickups built for provincial cargo and rugged terrain',
        'icon' => 'fa-solid fa-truck-pickup',
        'page' => '1 / 1'
    ]
];

// Query all 30 vehicles from MySQL
$dbVehicles = $pdo->query("SELECT v.*, c.slug AS category_slug, c.name AS category_name 
                        FROM vehicles v 
                        JOIN categories c ON v.category_id = c.id 
                        ORDER BY v.daily_rate ASC")->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
foreach ($categoryMeta as $slug => $meta) {
    $categories[$slug] = $meta;
    $categories[$slug]['vehicles'] = [];
}

foreach ($dbVehicles as $v) {
    $slug = $v['category_slug'];
    if (!isset($categories[$slug])) {
        $slug = strtolower($v['category']);
    }
    if (!isset($categories[$slug])) {
        $slug = 'sedan';
    }

    $image = vehicle_image_url($v['image_path'] ?: $v['image_url']);
    $categories[$slug]['vehicles'][] = [
        'id' => (int)$v['id'],
        'name' => ($v['brand'] ?: $v['make']) . ' ' . $v['model'],
        'class' => 'Class: ' . $v['category'] . ' • ' . $v['year'],
        'image' => $image,
        'status' => $v['status'],
        'badge' => $v['tag_label'] ?: $v['category'],
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => $v['transmission'], 'sub' => 'Transmission'],
            ['icon' => 'fa-solid fa-user-group', 'title' => $v['seating_capacity'] . ' Seats', 'sub' => 'Capacity'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => $v['fuel_type'], 'sub' => 'Engine / Fuel']
        ],
        'rate' => number_format($v['daily_rate'], 0)
    ];
};

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

<!-- Preload 3D Fleet Model & Draco Decoder for Instant Rendering -->
<link rel="preload" href="<?= BASE_URL ?>assets/car.glb" as="fetch" crossorigin>
<link rel="preload" href="<?= BASE_URL ?>js/draco/draco_decoder.wasm" as="fetch" crossorigin>

<!-- ==========================================================================
    1. FLOATING SEARCH & FILTER BAR
    ========================================================================== -->
<form method="get" action="dashboard.php" class="browse-search-bar" id="browseSearchForm">
    <div class="search-input-wrap">
        <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
        <input type="text" name="q" id="browseSearchInput" value="<?= e($q) ?>" placeholder="Search by model, brand, or location..." autocomplete="off">
    </div>

    <?php include __DIR__ . '/../includes/location_selector.php'; ?>

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
                <i class="fa-solid fa-headset"></i> 24/7 Concierge Support
            </span>
        </div>
    </div>

    <div class="assurance-right">
        <div class="hero-diag-card" id="fleet360Card">
            <div class="fleet-360-stage" id="fleet360Stage">
                <div class="fleet-loading-overlay" id="fleetLoadingOverlay">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> &nbsp; Initializing 3D Fleet Radar...
                </div>
                <canvas id="webglFleetCanvas"></canvas>

                <div class="fleet-hud-tag">
                    <span class="live-fleet-dot"></span> LIVE FLEET 360&deg; TELEMETRY
                </div>

                <div class="fleet-hud-angle" id="fleetBearingAngle">
                    BEARING: 0&deg;
                </div>

                <div class="fleet-hud-stats">
                    <span>30/30 ACTIVE</span> &bull; METRO MANILA RADAR
                </div>

                <div class="live-fleet-badge">
                    <span class="live-fleet-dot"></span> Live Fleet View <span class="badge-360-pill">360&deg;</span>
                </div>
            </div>
        </div>
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
    4. CATEGORY VEHICLE ROWS (Sedans, Hatchbacks, SUVs, MPVs, Vans, Pickups)
    ========================================================================== -->
<?php foreach ($categories as $catKey => $cat): ?>
    <?php 
        $perPage = 3;
        $totalVehicles = count($cat['vehicles']);
        $totalPages = max(1, (int)ceil($totalVehicles / $perPage));
        if ($totalVehicles === 0 && $q !== '') continue; 
    ?>
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

            <!-- Category Pagination Controls matching Mockup (< 1/2 >) -->
            <div class="cat-pagination" data-cat="<?= e($catKey) ?>" data-page="1" data-total="<?= $totalPages ?>">
                <button class="cat-page-btn btn-cat-prev" type="button" aria-label="Previous Page in <?= e($cat['name']) ?>" disabled><i class="fa-solid fa-chevron-left"></i></button>
                <span class="cat-page-num"><span class="cat-curr-page">1</span> / <span class="cat-total-page"><?= $totalPages ?></span></span>
                <button class="cat-page-btn btn-cat-next" type="button" aria-label="Next Page in <?= e($cat['name']) ?>" <?= $totalPages <= 1 ? 'disabled' : '' ?>><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="vehicle-grid-3" id="grid-<?= e($catKey) ?>">
            <?php foreach ($cat['vehicles'] as $idx => $v): ?>
                <?php 
                    $pageIndex = (int)floor($idx / $perPage) + 1;
                    $isAvailable = ($v['status'] === 'available'); 
                ?>
                <div class="v-card-exact" data-card-cat="<?= e($catKey) ?>" data-card-page="<?= $pageIndex ?>" style="<?= $pageIndex > 1 ? 'display: none;' : '' ?>">
                    <!-- Photo & Badges (Clickable to Details) -->
                    <a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>" class="v-card-media" title="View details of <?= e($v['name']) ?>">
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
                    </a>

                    <!-- Card Body -->
                    <div class="v-card-content">
                        <div class="v-card-header">
                            <h4 class="v-card-title">
                                <a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>"><?= e($v['name']) ?></a>
                            </h4>
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

<!-- No Search Results Container -->
<div id="noBrowseResults" class="no-results-box" style="display: none; margin: 30px 0; text-align: center; padding: 48px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <i class="fa-solid fa-car-tunnel" style="font-size: 38px; color: #94a3b8; margin-bottom: 14px;"></i>
    <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No vehicles match your search</h3>
    <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">Try adjusting your keyword or reset filters to browse our 30 fleet vehicles.</p>
    <a href="dashboard.php" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; border-radius: 8px; background: #0284c7; color: #fff; font-weight: 700; text-decoration: none;">Reset All Filters</a>
</div>

<!-- Dynamic Category Pagination & Search Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. CATEGORY PAGINATION CONTROLLER (< 1/2 >, < 1/3 >)
    document.querySelectorAll('.cat-pagination').forEach(pagin => {
        const cat = pagin.getAttribute('data-cat');
        const btnPrev = pagin.querySelector('.btn-cat-prev');
        const btnNext = pagin.querySelector('.btn-cat-next');
        const currSpan = pagin.querySelector('.cat-curr-page');
        const totalSpan = pagin.querySelector('.cat-total-page');
        const grid = document.getElementById('grid-' + cat);
        if (!grid) return;

        function updateCategoryPage(newPage) {
            let total = parseInt(pagin.getAttribute('data-total') || '1', 10);
            if (newPage < 1) newPage = 1;
            if (newPage > total) newPage = total;
            pagin.setAttribute('data-page', newPage);
            if (currSpan) currSpan.textContent = newPage;
            if (btnPrev) btnPrev.disabled = (newPage <= 1);
            if (btnNext) btnNext.disabled = (newPage >= total);

            // Filter cards: show cards matching data-card-page == newPage (if not filtered out by search)
            const cards = grid.querySelectorAll('.v-card-exact');
            cards.forEach(card => {
                const cardPage = parseInt(card.getAttribute('data-card-page'), 10);
                const isFilteredOut = card.getAttribute('data-filtered-out') === '1';
                if (!isFilteredOut && cardPage === newPage) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', function(e) {
                e.preventDefault();
                let current = parseInt(pagin.getAttribute('data-page') || '1', 10);
                updateCategoryPage(current - 1);
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', function(e) {
                e.preventDefault();
                let current = parseInt(pagin.getAttribute('data-page') || '1', 10);
                updateCategoryPage(current + 1);
            });
        }
    });

    // 2. REAL-TIME SEARCH & FILTER CONTROLLER
    const searchInput = document.getElementById('browseSearchInput');
    const noResultsBox = document.getElementById('noBrowseResults');

    function applySearchAndPaging() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let totalVisibleCardsOverall = 0;

        document.querySelectorAll('.category-row').forEach(row => {
            const cat = row.id.replace('cat-', '');
            const pagin = row.querySelector('.cat-pagination');
            const grid = document.getElementById('grid-' + cat);
            if (!grid) return;

            const cards = grid.querySelectorAll('.v-card-exact');
            let matchingCards = [];

            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                const matches = (!query || text.includes(query));
                if (matches) {
                    card.removeAttribute('data-filtered-out');
                    matchingCards.push(card);
                } else {
                    card.setAttribute('data-filtered-out', '1');
                    card.style.display = 'none';
                }
            });

            totalVisibleCardsOverall += matchingCards.length;

            if (matchingCards.length === 0) {
                row.style.display = 'none';
            } else {
                row.style.display = 'block';
                // Recalculate pages for matching cards: 3 cards per page
                const perPage = 3;
                const newTotalPages = Math.max(1, Math.ceil(matchingCards.length / perPage));
                if (pagin) {
                    pagin.setAttribute('data-total', newTotalPages);
                    pagin.setAttribute('data-page', '1');
                    const currSpan = pagin.querySelector('.cat-curr-page');
                    const totalSpan = pagin.querySelector('.cat-total-page');
                    const btnPrev = pagin.querySelector('.btn-cat-prev');
                    const btnNext = pagin.querySelector('.btn-cat-next');
                    if (currSpan) currSpan.textContent = '1';
                    if (totalSpan) totalSpan.textContent = newTotalPages;
                    if (btnPrev) btnPrev.disabled = true;
                    if (btnNext) btnNext.disabled = (newTotalPages <= 1);
                }

                // Assign page indexes and show page 1
                matchingCards.forEach((card, idx) => {
                    const page = Math.floor(idx / perPage) + 1;
                    card.setAttribute('data-card-page', page);
                    if (page === 1) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });

        if (noResultsBox) {
            noResultsBox.style.display = (totalVisibleCardsOverall === 0) ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearchAndPaging);
    }
});
</script>

<!-- ==========================================================================
    REAL 3D MODEL CAR LIVE FLEET 360° TURNTABLE ENGINE (NO HREF LINKS)
    ========================================================================== -->
<script src="<?= BASE_URL ?>js/three.min.js"></script>
<script src="<?= BASE_URL ?>js/DRACOLoader.js"></script>
<script src="<?= BASE_URL ?>js/GLTFLoader.js"></script>
<script>
(function() {
    const canvas = document.getElementById('webglFleetCanvas');
    const hudBearing = document.getElementById('fleetBearingAngle');
    const loadingOverlay = document.getElementById('fleetLoadingOverlay');
    if (!canvas || typeof THREE === 'undefined') return;

    const container = canvas.parentElement;
    let width = container.clientWidth || 440;
    let height = container.clientHeight || 180;

    // Scene & Camera
    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x060c14, 0.035);

    const camera = new THREE.PerspectiveCamera(34, width / height, 0.1, 100);
    camera.position.set(0, 2.4, 5.8);
    camera.lookAt(0, 0.35, 0);

    // Renderer
    const renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance'
    });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.35;

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xdbeafe, 1.2);
    scene.add(ambientLight);

    const keyLight = new THREE.DirectionalLight(0xffffff, 2.2);
    keyLight.position.set(6, 10, 5);
    scene.add(keyLight);

    const fillLight = new THREE.DirectionalLight(0x38bdf8, 1.5);
    fillLight.position.set(-6, 5, -4);
    scene.add(fillLight);

    const topSpot = new THREE.SpotLight(0x38bdf8, 3.0, 20, Math.PI / 4, 0.5, 1);
    topSpot.position.set(0, 8, 0);
    scene.add(topSpot);

    // Turntable Group (Car sits on turntable and rotates 360)
    const turntable = new THREE.Group();
    scene.add(turntable);

    // Radar Disc Platform Texture using Canvas
    const radarCanvas = document.createElement('canvas');
    radarCanvas.width = 512;
    radarCanvas.height = 512;
    const rCtx = radarCanvas.getContext('2d');

    function updateRadarTexture(rotAngle) {
        rCtx.clearRect(0, 0, 512, 512);
        const cx = 256;
        const cy = 256;
        const r = 240;

        // Glowing Disc
        const g = rCtx.createRadialGradient(cx, cy, 20, cx, cy, r);
        g.addColorStop(0, 'rgba(14, 165, 233, 0.25)');
        g.addColorStop(0.6, 'rgba(14, 165, 233, 0.08)');
        g.addColorStop(0.95, 'rgba(14, 165, 233, 0.35)');
        g.addColorStop(1, 'rgba(56, 189, 248, 0.95)');

        rCtx.beginPath();
        rCtx.arc(cx, cy, r, 0, Math.PI * 2);
        rCtx.fillStyle = g;
        rCtx.fill();
        rCtx.lineWidth = 4;
        rCtx.strokeStyle = 'rgba(56, 189, 248, 0.8)';
        rCtx.stroke();

        // Concentric rings
        [0.35, 0.65, 0.85].forEach(f => {
            rCtx.beginPath();
            rCtx.arc(cx, cy, r * f, 0, Math.PI * 2);
            rCtx.lineWidth = 1.5;
            rCtx.strokeStyle = 'rgba(56, 189, 248, 0.35)';
            rCtx.setLineDash([6, 8]);
            rCtx.stroke();
            rCtx.setLineDash([]);
        });

        // Sweeping radar beam
        rCtx.save();
        rCtx.beginPath();
        rCtx.arc(cx, cy, r, 0, Math.PI * 2);
        rCtx.clip();

        const beamSpread = 0.65;
        rCtx.beginPath();
        rCtx.moveTo(cx, cy);
        for (let a = rotAngle - beamSpread; a <= rotAngle; a += 0.05) {
            rCtx.lineTo(cx + Math.cos(a) * r, cy + Math.sin(a) * r);
        }
        rCtx.closePath();
        const bGrad = rCtx.createRadialGradient(cx, cy, 20, cx + Math.cos(rotAngle) * r, cy + Math.sin(rotAngle) * r, r);
        bGrad.addColorStop(0, 'rgba(56, 189, 248, 0.0)');
        bGrad.addColorStop(1, 'rgba(56, 189, 248, 0.55)');
        rCtx.fillStyle = bGrad;
        rCtx.fill();
        rCtx.restore();

        // Degree Ticks
        const ticks = 36;
        for (let i = 0; i < ticks; i++) {
            const a = (i / ticks) * Math.PI * 2;
            const isMajor = (i % 9 === 0);
            const len = isMajor ? 16 : 7;
            const x1 = cx + Math.cos(a) * (r - 2);
            const y1 = cy + Math.sin(a) * (r - 2);
            const x2 = cx + Math.cos(a) * (r - 2 - len);
            const y2 = cy + Math.sin(a) * (r - 2 - len);

            rCtx.beginPath();
            rCtx.moveTo(x1, y1);
            rCtx.lineTo(x2, y2);
            rCtx.lineWidth = isMajor ? 3 : 1.5;
            rCtx.strokeStyle = isMajor ? '#38bdf8' : 'rgba(56, 189, 248, 0.4)';
            rCtx.stroke();

            if (isMajor) {
                rCtx.save();
                const deg = ((i / ticks) * 360) + '°';
                rCtx.font = 'bold 16px monospace';
                rCtx.fillStyle = '#94a3b8';
                rCtx.textAlign = 'center';
                rCtx.textBaseline = 'middle';
                rCtx.fillText(deg, cx + Math.cos(a) * (r - 30), cy + Math.sin(a) * (r - 30));
                rCtx.restore();
            }
        }

        // Radar Blips
        const blips = [
            { d: 0.72, a: rotAngle + 1.2, label: 'BGC' },
            { d: 0.85, a: rotAngle + 2.8, label: 'NAIA' },
            { d: 0.58, a: rotAngle + 4.5, label: 'MAKATI' }
        ];
        blips.forEach(b => {
            const bx = cx + Math.cos(b.a) * (r * b.d);
            const by = cy + Math.sin(b.a) * (r * b.d);
            rCtx.beginPath();
            rCtx.arc(bx, by, 5, 0, Math.PI * 2);
            rCtx.fillStyle = '#22c55e';
            rCtx.fill();
            rCtx.beginPath();
            rCtx.arc(bx, by, 10, 0, Math.PI * 2);
            rCtx.strokeStyle = 'rgba(34, 197, 94, 0.5)';
            rCtx.lineWidth = 2;
            rCtx.stroke();
            rCtx.font = 'bold 12px monospace';
            rCtx.fillStyle = '#86efac';
            rCtx.fillText(b.label, bx + 12, by - 4);
        });
    }
    updateRadarTexture(0);

    const radarTex = new THREE.CanvasTexture(radarCanvas);
    const stageGeo = new THREE.CircleGeometry(4.8, 64);
    const stageMat = new THREE.MeshBasicMaterial({
        map: radarTex,
        transparent: true,
        opacity: 0.95,
        side: THREE.DoubleSide
    });
    const stageMesh = new THREE.Mesh(stageGeo, stageMat);
    stageMesh.rotation.x = -Math.PI / 2;
    stageMesh.position.y = -0.05;
    scene.add(stageMesh);

    // Ground contact shadow
    const shadowGeo = new THREE.PlaneGeometry(5.2, 2.6);
    const shadowCanvas = document.createElement('canvas');
    shadowCanvas.width = 256;
    shadowCanvas.height = 128;
    const sCtx = shadowCanvas.getContext('2d');
    const sGrad = sCtx.createRadialGradient(128, 64, 10, 128, 64, 120);
    sGrad.addColorStop(0, 'rgba(0, 0, 0, 0.85)');
    sGrad.addColorStop(0.6, 'rgba(0, 0, 0, 0.5)');
    sGrad.addColorStop(1, 'rgba(0, 0, 0, 0.0)');
    sCtx.fillStyle = sGrad;
    sCtx.fillRect(0, 0, 256, 128);
    const shadowTex = new THREE.CanvasTexture(shadowCanvas);
    const shadowMat = new THREE.MeshBasicMaterial({ map: shadowTex, transparent: true, opacity: 0.85 });
    const shadowMesh = new THREE.Mesh(shadowGeo, shadowMat);
    shadowMesh.rotation.x = -Math.PI / 2;
    shadowMesh.position.y = 0.01;
    turntable.add(shadowMesh);

    // Cyan Underglow Light
    const underglow = new THREE.PointLight(0x38bdf8, 3.5, 3.5);
    underglow.position.set(0, 0.25, 0);
    turntable.add(underglow);

    // LOAD REAL GLTF 3D CAR MODEL WITH DRACO
    const dracoLoader = new THREE.DRACOLoader();
    dracoLoader.setDecoderPath('<?= BASE_URL ?>js/draco/');
    const loader = new THREE.GLTFLoader();
    loader.setDRACOLoader(dracoLoader);

    loader.load(
        '<?= BASE_URL ?>assets/car.glb',
        function(gltf) {
            const carModel = gltf.scene;

            // Compute bounding box to normalize scale and center position
            const box = new THREE.Box3().setFromObject(carModel);
            const size = box.getSize(new THREE.Vector3());
            const maxDim = Math.max(size.x, size.y, size.z);
            const targetScale = 4.2 / maxDim;
            carModel.scale.set(targetScale, targetScale, targetScale);

            // Center car horizontally and place directly on ground
            box.setFromObject(carModel);
            const center = box.getCenter(new THREE.Vector3());
            carModel.position.x -= center.x;
            carModel.position.y -= box.min.y;
            carModel.position.z -= center.z;
            carModel.rotation.y = Math.PI * 0.75; // Front three-quarter dynamic angle

            turntable.add(carModel);
            if (loadingOverlay) {
                loadingOverlay.style.opacity = '0';
                setTimeout(() => { loadingOverlay.style.display = 'none'; }, 250);
            }
            if (!animating) {
                animating = true;
                animate();
            }
        },
        undefined,
        function(err) {
            console.error('Error loading car.glb:', err);
            if (loadingOverlay) {
                loadingOverlay.innerHTML = '<span style="color:#ef4444;">Live 3D Telemetry Offline</span>';
            }
        }
    );

    // Initial render of stage
    renderer.render(scene, camera);

    // Animation & Interactive 360 Drag (No href links, purely interactive)
    let angle = 0;
    let isDragging = false;
    let previousMouseX = 0;
    let autoRotate = true;
    let animating = false;

    canvas.addEventListener('mousedown', function(e) {
        isDragging = true;
        autoRotate = false;
        previousMouseX = e.clientX;
    });

    window.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        const deltaX = e.clientX - previousMouseX;
        turntable.rotation.y += deltaX * 0.015;
        previousMouseX = e.clientX;
    });

    window.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            setTimeout(() => { if (!isDragging) autoRotate = true; }, 1200);
        }
    });

    // Touch support for mobile & tablet
    canvas.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            isDragging = true;
            autoRotate = false;
            previousMouseX = e.touches[0].clientX;
        }
    }, { passive: true });

    window.addEventListener('touchmove', function(e) {
        if (!isDragging || e.touches.length !== 1) return;
        const deltaX = e.touches[0].clientX - previousMouseX;
        turntable.rotation.y += deltaX * 0.018;
        previousMouseX = e.touches[0].clientX;
    }, { passive: true });

    window.addEventListener('touchend', function() {
        if (isDragging) {
            isDragging = false;
            setTimeout(() => { if (!isDragging) autoRotate = true; }, 1200);
        }
    });

    // Resize handler for perfect clarity and responsive layout
    window.addEventListener('resize', function() {
        const newW = container.clientWidth;
        const newH = container.clientHeight;
        if (newW && newH && (newW !== width || newH !== height)) {
            width = newW;
            height = newH;
            camera.aspect = width / height;
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
        }
    });

    function animate() {
        requestAnimationFrame(animate);

        if (autoRotate) {
            turntable.rotation.y += 0.015;
            if (turntable.rotation.y >= Math.PI * 2) {
                turntable.rotation.y -= Math.PI * 2;
            }
        }

        angle += 0.02;
        updateRadarTexture(angle);
        radarTex.needsUpdate = true;

        // Bearing HUD Calculation (0° to 360°)
        const normY = ((turntable.rotation.y % (Math.PI * 2)) + (Math.PI * 2)) % (Math.PI * 2);
        const deg = Math.round(normY * (180 / Math.PI));
        if (hudBearing) {
            hudBearing.textContent = `BEARING: ${deg}°`;
        }

        renderer.render(scene, camera);
    }
    animate();
})();
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
