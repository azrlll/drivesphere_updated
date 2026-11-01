<?php
$pageTitle = 'Browse Vehicles';
$nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$allBranches = get_all_branches($pdo);

$q = trim($_GET['q'] ?? '');
$filterBranch = isset($_GET['branch']) && $_GET['branch'] !== 'all' && $_GET['branch'] !== '' ? (int)$_GET['branch'] : 0;
$filterType = $_GET['type'] ?? 'all';
$filterTrans = $_GET['transmission'] ?? 'all';
$filterSeats = (int)($_GET['seats'] ?? 0);
$filterMinPrice = (int)($_GET['min_price'] ?? 0);
$filterMaxPrice = (int)($_GET['max_price'] ?? 0);
$filterBrand = $_GET['brand'] ?? 'all';
$userOrigin = $_GET['origin'] ?? 'makati';

$activeFilters = 0;
if ($filterBranch > 0) $activeFilters++;
if ($filterType !== 'all') $activeFilters++;
if ($filterTrans !== 'all') $activeFilters++;
if ($filterSeats > 0) $activeFilters++;
if ($filterBrand !== 'all') $activeFilters++;
if ($filterMinPrice > 0 || $filterMaxPrice > 0) $activeFilters++;

// Standard manual travel-time and distance table between origins and Philippine hub branches (Phase 4)
$travelEstimates = [
    'makati' => [
        1 => ['time' => '15–25 mins', 'dist' => '5.8 km'],
        2 => ['time' => '20–35 mins', 'dist' => '8.4 km'],
        3 => ['time' => '5–10 mins',  'dist' => '1.5 km'],
        4 => ['time' => '25–40 mins', 'dist' => '10.2 km'],
        5 => ['time' => '1.5–2.0 hrs','dist' => '94 km'],
        6 => ['time' => 'Flight required (1.5 hrs)', 'dist' => '570 km'],
        7 => ['time' => 'Flight required (2.0 hrs)', 'dist' => '960 km'],
    ],
    'bgc' => [
        1 => ['time' => '5–10 mins',  'dist' => '1.2 km'],
        2 => ['time' => '20–30 mins', 'dist' => '9.5 km'],
        3 => ['time' => '15–25 mins', 'dist' => '5.8 km'],
        4 => ['time' => '20–35 mins', 'dist' => '8.6 km'],
        5 => ['time' => '1.5–2.0 hrs','dist' => '96 km'],
        6 => ['time' => 'Flight required', 'dist' => '570 km'],
        7 => ['time' => 'Flight required', 'dist' => '960 km'],
    ],
    'quezon_city' => [
        1 => ['time' => '35–55 mins', 'dist' => '16.5 km'],
        2 => ['time' => '45–65 mins', 'dist' => '21.0 km'],
        3 => ['time' => '35–50 mins', 'dist' => '15.2 km'],
        4 => ['time' => '20–35 mins', 'dist' => '9.8 km'],
        5 => ['time' => '1.2–1.5 hrs','dist' => '82 km'],
        6 => ['time' => 'Flight required', 'dist' => '580 km'],
        7 => ['time' => 'Flight required', 'dist' => '970 km'],
    ],
    'pasay' => [
        1 => ['time' => '20–30 mins', 'dist' => '9.5 km'],
        2 => ['time' => '5–10 mins',  'dist' => '1.5 km'],
        3 => ['time' => '15–25 mins', 'dist' => '6.5 km'],
        4 => ['time' => '30–45 mins', 'dist' => '13.2 km'],
        5 => ['time' => '1.5–2.0 hrs','dist' => '98 km'],
        6 => ['time' => 'Flight required', 'dist' => '565 km'],
        7 => ['time' => 'Flight required', 'dist' => '955 km'],
    ],
    'pampanga' => [
        1 => ['time' => '1.5–2.0 hrs','dist' => '96 km'],
        2 => ['time' => '1.5–2.0 hrs','dist' => '98 km'],
        3 => ['time' => '1.5–2.0 hrs','dist' => '94 km'],
        4 => ['time' => '1.5–2.0 hrs','dist' => '90 km'],
        5 => ['time' => '10–20 mins', 'dist' => '5.0 km'],
        6 => ['time' => 'Flight/Transit', 'dist' => '640 km'],
        7 => ['time' => 'Flight/Transit', 'dist' => '1,040 km'],
    ],
    'cebu' => [
        1 => ['time' => 'Flight required', 'dist' => '570 km'],
        2 => ['time' => 'Flight required', 'dist' => '565 km'],
        3 => ['time' => 'Flight required', 'dist' => '570 km'],
        4 => ['time' => 'Flight required', 'dist' => '570 km'],
        5 => ['time' => 'Flight/Transit', 'dist' => '640 km'],
        6 => ['time' => '10–25 mins', 'dist' => '6.2 km'],
        7 => ['time' => 'Flight / Ferry', 'dist' => '400 km'],
    ],
    'davao' => [
        1 => ['time' => 'Flight required', 'dist' => '960 km'],
        2 => ['time' => 'Flight required', 'dist' => '955 km'],
        3 => ['time' => 'Flight required', 'dist' => '960 km'],
        4 => ['time' => 'Flight required', 'dist' => '960 km'],
        5 => ['time' => 'Flight/Transit', 'dist' => '1,040 km'],
        6 => ['time' => 'Flight / Ferry', 'dist' => '400 km'],
        7 => ['time' => '10–25 mins', 'dist' => '5.5 km'],
    ]
];

// Build filter conditions
$where = ["v.status = 'available'"];
$params = [];

if ($filterBranch > 0) {
    $where[] = "v.branch_id = :fbranch";
    $params['fbranch'] = $filterBranch;
}
if ($filterType !== 'all') {
    $where[] = "(LOWER(c.slug) = :ftype1 OR LOWER(v.category) = :ftype2)";
    $params['ftype1'] = strtolower($filterType);
    $params['ftype2'] = strtolower($filterType);
}
if ($filterTrans !== 'all') {
    $where[] = "LOWER(v.transmission) LIKE :ftrans";
    $params['ftrans'] = '%' . strtolower($filterTrans) . '%';
}
if ($filterSeats > 0) {
    $where[] = "(v.seating_capacity >= :fseats1 OR v.seats >= :fseats2)";
    $params['fseats1'] = $filterSeats;
    $params['fseats2'] = $filterSeats;
}
if ($filterBrand !== 'all') {
    $where[] = "(v.brand = :fbrand1 OR v.make = :fbrand2)";
    $params['fbrand1'] = $filterBrand;
    $params['fbrand2'] = $filterBrand;
}
if ($filterMinPrice > 0) {
    $where[] = "v.daily_rate >= :fmin";
    $params['fmin'] = $filterMinPrice;
}
if ($filterMaxPrice > 0) {
    $where[] = "v.daily_rate <= :fmax";
    $params['fmax'] = $filterMaxPrice;
}
if ($q !== '') {
    $where[] = "(v.model LIKE :fq1 OR v.brand LIKE :fq2 OR v.make LIKE :fq3 OR v.description LIKE :fq4 OR b.name LIKE :fq5 OR b.city LIKE :fq6)";
    $likeQ = '%' . $q . '%';
    $params['fq1'] = $likeQ;
    $params['fq2'] = $likeQ;
    $params['fq3'] = $likeQ;
    $params['fq4'] = $likeQ;
    $params['fq5'] = $likeQ;
    $params['fq6'] = $likeQ;
}

$whereSql = implode(' AND ', $where);

try {
    // Count total matching for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v 
                                JOIN categories c ON v.category_id = c.id 
                                LEFT JOIN branches b ON v.branch_id = b.id
                                WHERE $whereSql");
    $countStmt->execute($params);
    $totalMatching = (int)$countStmt->fetchColumn();

    // Numbered pagination calculation (Phase 11: 8 vehicles per page)
    $perPage = 8;
    $totalPages = max(1, (int)ceil($totalMatching / $perPage));
    $page = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
    $offset = ($page - 1) * $perPage;

    $st = $pdo->prepare("SELECT v.*, c.name AS category_name, b.name AS branch_name, b.city AS branch_city 
                        FROM vehicles v 
                        JOIN categories c ON v.category_id = c.id 
                        LEFT JOIN branches b ON v.branch_id = b.id
                        WHERE $whereSql 
                        ORDER BY v.id ASC 
                        LIMIT :limit OFFSET :offset");
    foreach ($params as $k => $val) {
        $st->bindValue(':' . $k, $val);
    }
    $st->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $st->bindValue(':offset', $offset, PDO::PARAM_INT);
    $st->execute();
    $dbFeatured = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DriveSphere Customer Browse Query Error: " . $e->getMessage());
    $totalMatching = 0;
    $perPage = 8;
    $totalPages = 1;
    $page = 1;
    $offset = 0;
    $dbFeatured = [];
}

$start = $totalMatching > 0 ? ($offset + 1) : 0;
$end = min($offset + $perPage, $totalMatching);

// Base params for pagination links
$pageParams = [];
if ($q !== '') $pageParams['q'] = $q;
if ($filterBranch > 0) $pageParams['branch'] = $filterBranch;
if ($filterType !== 'all') $pageParams['type'] = $filterType;
if ($filterTrans !== 'all') $pageParams['transmission'] = $filterTrans;
if ($filterSeats > 0) $pageParams['seats'] = $filterSeats;
if ($filterBrand !== 'all') $pageParams['brand'] = $filterBrand;
if ($filterMinPrice > 0) $pageParams['min_price'] = $filterMinPrice;
if ($filterMaxPrice > 0) $pageParams['max_price'] = $filterMaxPrice;
if ($userOrigin !== 'makati') $pageParams['origin'] = $userOrigin;
$pageBase = http_build_query($pageParams);

$featuredVehicles = [];
foreach ($dbFeatured as $row) {
    $bId = (int)($row['branch_id'] ?: 1);
    $est = $travelEstimates[$userOrigin][$bId] ?? ['time' => '15–30 mins', 'dist' => '7 km'];

    $featuredVehicles[] = [
        'id' => (int)$row['id'],
        'name' => ($row['brand'] ?: $row['make']) . ' ' . $row['model'],
        'desc' => $row['description'],
        'branch_id' => $bId,
        'branch_name' => $row['branch_name'] ?: 'Metro Manila - BGC Central Hub',
        'branch_city' => $row['branch_city'] ?: 'Taguig',
        'travel_time' => $est['time'],
        'distance' => $est['dist'],
        'image' => vehicle_image_url($row['image_path'] ?: $row['image_url']),
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => $row['transmission'], 'sub' => 'Drivetrain'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => $row['fuel_type'], 'sub' => 'Fuel Type'],
            ['icon' => 'fa-solid fa-user-group', 'title' => $row['seating_capacity'] . ' Seats', 'sub' => 'Capacity']
        ],
        'rate' => number_format($row['daily_rate'], 0)
    ];
}
?>

<!-- Floating Search & Filter Bar (Matching User Image) -->
<form method="get" action="browse.php" class="browse-search-bar" id="browseSearchForm" style="display:flex; align-items:center; gap:10px;">
    <div class="search-input-wrap" style="flex:1;">
        <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
        <input type="text" name="q" id="browseSearchInput" value="<?= e($q) ?>" placeholder="Search by model, brand, or hub location..." autocomplete="off">
    </div>

    <!-- Quick Hub Selector -->
    <div style="position:relative; display:flex; align-items:center;">
        <select name="branch" onchange="this.form.submit()" style="padding:10px 14px; font-size:13px; font-weight:600; color:#0f172a; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; outline:none; cursor:pointer;">
            <option value="all" <?= $filterBranch === 0 ? 'selected' : '' ?>>All Hubs (7 Locations)</option>
            <?php foreach ($allBranches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= $filterBranch == $b['id'] ? 'selected' : '' ?>>
                    📍 <?= e($b['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="button" class="filter-chip-btn" id="filterToggleBtn" onclick="openFilterModal()" title="Detailed Filter Criteria">
        <i class="fa-solid fa-sliders"></i>
        <span>Filter</span>
        <span class="filter-badge"><?= (int)$activeFilters ?></span>
    </button>

    <button type="submit" class="btn-find-vehicles">
        Find Vehicles
    </button>
</form>

<!-- Section Header (Matches Image 1) -->
<div class="dash-fleet-header" style="margin-top:20px;">
    <div>
        <span class="avail-inv-tag"><span class="inv-dot"></span> AVAILABLE INVENTORY</span>
        <h1 class="dash-fleet-title">Featured Rental Fleet</h1>
        <p class="dash-fleet-sub">Choose from our most requested well-maintained models, meticulously detailed and road-ready.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 14px;">
        <div style="font-size:12px; color:#64748b; background:#f1f5f9; padding:6px 12px; border-radius:8px; display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-street-view" style="color:#0284c7;"></i>
            <span>Your Location:</span>
            <strong style="color:#0f172a; text-transform:capitalize;"><?= str_replace('_', ' ', e($userOrigin)) ?></strong>
            <button type="button" onclick="openFilterModal()" style="background:none; border:none; color:#0284c7; text-decoration:underline; font-size:11px; cursor:pointer;">Change</button>
        </div>
        <a href="<?= $C ?>dashboard.php" class="view-catalog-link">
            View Full Catalog <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- 2-Column Grid of Featured Vehicles -->
<div class="dash-fleet-grid" id="featuredGrid">
    <?php if (empty($featuredVehicles)): ?>
        <div class="no-results-box" style="grid-column: 1 / -1; margin: 20px 0; text-align: center; padding: 48px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
            <i class="fa-solid fa-car-tunnel" style="font-size: 38px; color: #94a3b8; margin-bottom: 14px;"></i>
            <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No vehicles match your search or branch filters</h3>
            <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">Try clearing branch or category filters to view our full 53-vehicle fleet.</p>
            <a href="browse.php" class="btn btn-primary" style="display: inline-block; padding: 9px 18px; border-radius: 6px; background: #0284c7; color: #fff; font-weight: 700; text-decoration: none; margin-right: 8px;">Reset Search</a>
            <a href="dashboard.php" class="btn btn-dark" style="display: inline-block; padding: 9px 18px; border-radius: 6px; background: #0f172a; color: #fff; font-weight: 700; text-decoration: none;">View Full Catalog</a>
        </div>
    <?php endif; ?>
    <?php foreach ($featuredVehicles as $v): ?>
        <div class="featured-card">
            <!-- Vehicle Photo with Hub Location Overlay -->
            <div class="featured-card-media" style="position:relative;">
                <img src="<?= e($v['image']) ?>" alt="<?= e($v['name']) ?>" class="featured-card-img" loading="lazy">
                
                <!-- Hub Badge -->
                <div style="position:absolute; top:10px; left:10px; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); color:#ffffff; font-size:11px; font-weight:700; padding:4px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-location-dot" style="color:#38bdf8;"></i> <?= e($v['branch_city']) ?> Hub
                </div>

                <!-- Manual Travel Time Estimate (Phase 4) -->
                <div style="position:absolute; bottom:10px; left:10px; background:rgba(255,255,255,0.92); backdrop-filter:blur(4px); color:#1e293b; font-size:10.5px; font-weight:600; padding:3px 8px; border-radius:6px; border:1px solid rgba(226,232,240,0.8);">
                    <i class="fa-solid fa-car-side" style="color:#0284c7; margin-right:3px;"></i>
                    <?= e($v['travel_time']) ?> &middot; <?= e($v['distance']) ?>
                </div>
            </div>

            <!-- Card Body -->
            <div class="featured-card-body">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:2px;">
                    <h3 class="featured-card-title"><?= e($v['name']) ?></h3>
                </div>
                <div style="font-size:11px; color:#0284c7; font-weight:600; margin-bottom:6px;">
                    <i class="fa-solid fa-building"></i> <?= e($v['branch_name']) ?>
                </div>
                <p class="featured-card-desc"><?= e($v['desc']) ?></p>

                <!-- 3-Column Specifications -->
                <div class="featured-specs-row">
                    <?php foreach ($v['specs'] as $spec): ?>
                        <div class="f-spec-col">
                            <i class="<?= e($spec['icon']) ?>"></i>
                            <span class="f-spec-val"><?= e($spec['title']) ?></span>
                            <span class="f-spec-sub"><?= e($spec['sub']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Footer: Daily Rate & Rent Now Button -->
                <div class="featured-card-footer">
                    <div class="f-rate-block">
                        <span class="f-rate-label">DAILY RATE</span>
                        <div>
                            <span class="f-rate-val">₱<?= e($v['rate']) ?></span>
                            <span class="f-rate-period">/ day</span>
                        </div>
                    </div>

                    <a href="<?= $C ?>vehicle.php?id=<?= (int)$v['id'] ?>" class="btn-rent-now">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Functional Numbered Pagination Bar (Phase 11) -->
<?php if ($totalMatching > 0): ?>
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin:28px 0 36px; padding:16px 20px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px;">
        <div style="font-size:13px; color:#64748b;">
            Showing <strong><?= $start ?>–<?= $end ?></strong> of <strong><?= $totalMatching ?></strong> available vehicles
        </div>

        <div style="display:flex; gap:6px; align-items:center;">
            <?php if ($page > 1): ?>
                <a href="?<?= $pageBase ? $pageBase . '&' : '' ?>page=<?= $page - 1 ?>" style="padding:6px 12px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; color:#0f172a; text-decoration:none; font-weight:600; font-size:12px;">
                    &laquo; Prev
                </a>
            <?php else: ?>
                <span style="padding:6px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; color:#94a3b8; font-weight:600; font-size:12px; cursor:default;">
                    &laquo; Prev
                </span>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span style="padding:6px 12px; background:#0284c7; color:#ffffff; border-radius:6px; font-weight:700; font-size:12px;">
                        <?= $p ?>
                    </span>
                <?php else: ?>
                    <a href="?<?= $pageBase ? $pageBase . '&' : '' ?>page=<?= $p ?>" style="padding:6px 12px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; color:#0f172a; text-decoration:none; font-weight:600; font-size:12px;">
                        <?= $p ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?<?= $pageBase ? $pageBase . '&' : '' ?>page=<?= $page + 1 ?>" style="padding:6px 12px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; color:#0f172a; text-decoration:none; font-weight:600; font-size:12px;">
                    Next &raquo;
                </a>
            <?php else: ?>
                <span style="padding:6px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; color:#94a3b8; font-weight:600; font-size:12px; cursor:default;">
                    Next &raquo;
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Filter Modal (Phase 4: Location, Category, Transmission, Seats, Price, Origin) -->
<div id="customerFilterModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(3px);">
    <div style="background:#ffffff; width:100%; max-width:540px; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:16px 22px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
            <h3 style="margin:0; font-size:17px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-sliders" style="color:#0284c7;"></i> Filter Vehicles &amp; Location
            </h3>
            <button type="button" onclick="closeFilterModal()" style="background:transparent; border:none; font-size:20px; color:#64748b; cursor:pointer;">&times;</button>
        </div>

        <form method="get" action="browse.php" style="padding:20px 22px;">
            <?php if ($q !== ''): ?>
                <input type="hidden" name="q" value="<?= e($q) ?>">
            <?php endif; ?>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                    Pickup Branch Hub
                </label>
                <select name="branch" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                    <option value="all" <?= $filterBranch === 0 ? 'selected' : '' ?>>All Hubs (Overall Fleet)</option>
                    <?php foreach ($allBranches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $filterBranch == $b['id'] ? 'selected' : '' ?>>
                            <?= e($b['name']) ?> (<?= e($b['city']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                    Your Current Location / Origin (For Distance &amp; Travel Time Estimates)
                </label>
                <select name="origin" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                    <option value="makati" <?= $userOrigin === 'makati' ? 'selected' : '' ?>>Makati CBD &amp; Nearby</option>
                    <option value="bgc" <?= $userOrigin === 'bgc' ? 'selected' : '' ?>>Bonifacio Global City (BGC) &amp; Taguig</option>
                    <option value="pasay" <?= $userOrigin === 'pasay' ? 'selected' : '' ?>>Pasay / NAIA Airport Vicinity</option>
                    <option value="quezon_city" <?= $userOrigin === 'quezon_city' ? 'selected' : '' ?>>Quezon City &amp; Northern Metro</option>
                    <option value="pampanga" <?= $userOrigin === 'pampanga' ? 'selected' : '' ?>>Pampanga / Clark Freeport</option>
                    <option value="cebu" <?= $userOrigin === 'cebu' ? 'selected' : '' ?>>Cebu City / Mactan</option>
                    <option value="davao" <?= $userOrigin === 'davao' ? 'selected' : '' ?>>Davao City / Lanang</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                        Vehicle Class
                    </label>
                    <select name="type" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                        <option value="all">All Classes</option>
                        <option value="sedan" <?= $filterType === 'sedan' ? 'selected' : '' ?>>Sedans</option>
                        <option value="suv" <?= $filterType === 'suv' ? 'selected' : '' ?>>SUVs &amp; Crossovers</option>
                        <option value="van" <?= $filterType === 'van' ? 'selected' : '' ?>>Vans &amp; Shuttles</option>
                        <option value="mpv" <?= $filterType === 'mpv' ? 'selected' : '' ?>>MPVs</option>
                        <option value="hatchback" <?= $filterType === 'hatchback' ? 'selected' : '' ?>>Hatchbacks</option>
                        <option value="pickup" <?= $filterType === 'pickup' ? 'selected' : '' ?>>Pickups</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                        Transmission
                    </label>
                    <select name="transmission" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                        <option value="all">All Drivetrains</option>
                        <option value="automatic" <?= $filterTrans === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                        <option value="manual" <?= $filterTrans === 'manual' ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                        Seating Capacity
                    </label>
                    <select name="seats" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                        <option value="0">Any Capacity</option>
                        <option value="5" <?= $filterSeats == 5 ? 'selected' : '' ?>>5+ Seats</option>
                        <option value="7" <?= $filterSeats == 7 ? 'selected' : '' ?>>7+ Seats (Family)</option>
                        <option value="12" <?= $filterSeats == 12 ? 'selected' : '' ?>>12+ Seats (Van)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:5px; text-transform:uppercase;">
                        Max Daily Rate
                    </label>
                    <select name="max_price" style="width:100%; padding:9px 12px; font-size:13px; border:1px solid #cbd5e1; border-radius:8px; outline:none;">
                        <option value="0">No Max Limit</option>
                        <option value="3000" <?= $filterMaxPrice == 3000 ? 'selected' : '' ?>>Up to ₱3,000 / day</option>
                        <option value="5000" <?= $filterMaxPrice == 5000 ? 'selected' : '' ?>>Up to ₱5,000 / day</option>
                        <option value="8000" <?= $filterMaxPrice == 8000 ? 'selected' : '' ?>>Up to ₱8,000 / day</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center;">
                <a href="browse.php" style="font-size:12px; color:#64748b; text-decoration:underline;">Reset All Filters</a>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="closeFilterModal()" class="btn btn-secondary" style="padding:8px 14px; border-radius:6px; background:#f1f5f9; color:#475569; border:none; cursor:pointer;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 18px; border-radius:6px; background:#0284c7; color:#ffffff; border:none; font-weight:700; cursor:pointer;">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openFilterModal() {
    const m = document.getElementById('customerFilterModal');
    if (m) m.style.display = 'flex';
}
function closeFilterModal() {
    const m = document.getElementById('customerFilterModal');
    if (m) m.style.display = 'none';
}
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
