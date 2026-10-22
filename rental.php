<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDBConnection();

// Search parameters
$pickupInput = $_GET['pickup_date'] ?? date('Y-m-d\T10:00', strtotime('+1 day'));
$returnInput = $_GET['return_date'] ?? date('Y-m-d\T18:00', strtotime('+4 days'));
if (strlen($pickupInput) === 10) { $pickupInput .= 'T10:00'; }
if (strlen($returnInput) === 10) { $returnInput .= 'T18:00'; }
$pickupSql = date('Y-m-d H:i:s', strtotime($pickupInput));
$returnSql = date('Y-m-d H:i:s', strtotime($returnInput));
$q = trim($_GET['q'] ?? '');
$cat = $_GET['category'] ?? 'all';
$onlyAvailable = !empty($_GET['available']);
$sort = $_GET['sort'] ?? 'popular';
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : (isset($_GET['budget']) ? (float)$_GET['budget'] : 8500.0);
if ($maxPrice <= 0) { $maxPrice = 8500.0; }

$pickupLocation = $_GET['pickup_location'] ?? 'bgc';
$returnLocation = $_GET['return_location'] ?? 'same';
$withDriver = !empty($_GET['with_driver']);
$airportFast = !empty($_GET['airport_fast']);
$viewMode = ($_GET['view'] ?? 'list') === 'grid' ? 'grid' : 'list';

$days = rental_days($pickupSql, $returnSql);

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

// Vehicle query
$orderBy = "v.daily_rate DESC";
if ($sort === 'price_asc') {
    $orderBy = "v.daily_rate ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "v.daily_rate DESC";
}

$vehicles = $pdo->query("SELECT v.*, c.name AS category_name, c.slug AS category_slug
                         FROM vehicles v JOIN categories c ON v.category_id = c.id ORDER BY $orderBy")->fetchAll();

foreach ($vehicles as &$v) {
    $v['free'] = vehicle_is_available($pdo, $v['id'], $pickupSql, $returnSql);
}
unset($v);

// Filter by search query, category, availability, and budget range
$shown = array_filter($vehicles, function ($v) use ($q, $cat, $onlyAvailable, $maxPrice) {
    if ($cat !== 'all' && $v['category_slug'] !== $cat) return false;
    if ($onlyAvailable && !$v['free']) return false;
    if ($maxPrice < 8500 && (float)$v['daily_rate'] > $maxPrice) return false;
    if ($q !== '') {
        $haystack = strtolower(($v['brand'] ?: $v['make']) . ' ' . $v['model'] . ' ' . $v['category'] . ' ' . $v['plate_number'] . ' ' . $v['description']);
        if (!str_contains($haystack, strtolower($q))) return false;
    }
    return true;
});

// Category counts
$totalCount = count($vehicles);
$freeCount = count(array_filter($vehicles, fn($v) => $v['free']));
$suvCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'suv'));
$sedanCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'sedan'));
$vanCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'van'));
$mpvCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'mpv'));
$hatchCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'hatchback'));
$pickupCount = count(array_filter($vehicles, fn($v) => $v['category_slug'] === 'pickup'));

// Pagination calculation (6 vehicles per page per prompt specification)
$totalMatching = count($shown);
$perPage = 6;
$totalPages = max(1, (int)ceil($totalMatching / $perPage));
$page = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;
$pagedVehicles = array_slice($shown, $offset, $perPage);
$startItem = $totalMatching > 0 ? ($offset + 1) : 0;
$endItem = min($offset + $perPage, $totalMatching);

$baseParams = [
    'pickup_location' => $pickupLocation,
    'return_location' => $returnLocation,
    'pickup_date'     => $pickupInput,
    'return_date'     => $returnInput,
];
if ($q !== '') { $baseParams['q'] = $q; }
if ($cat !== 'all') { $baseParams['category'] = $cat; }
if ($onlyAvailable) { $baseParams['available'] = 1; }
if ($sort !== 'popular') { $baseParams['sort'] = $sort; }
if ($maxPrice < 8500) { $baseParams['max_price'] = (int)$maxPrice; }
if ($withDriver) { $baseParams['with_driver'] = 1; }
if ($airportFast) { $baseParams['airport_fast'] = 1; }
if ($viewMode === 'grid') { $baseParams['view'] = 'grid'; }

$baseQuery = http_build_query($baseParams);
$query = $baseQuery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Rental Inventory</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header Section (matching SCREEN P02) -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Search Hero Section -->
    <section class="search-hero">
        <div class="container">
            <div class="hero-text">
                <div class="badge-dark-pill"><i class="fa-solid fa-car" style="margin-right:6px;"></i> EXECUTIVE &amp; ISLAND-WIDE LOGISTICS</div>
                <h1>Reserve Your Vehicle in Minutes</h1>
                <p>Flexible daily, weekly, and corporate rental options across Metro Manila and key regional hubs. Verified road-ready, fully insured, and instantly dispatched.</p>
            </div>

            <div class="search-widget-horizontal">
                <form id="rentalSearchForm" method="get" action="rental.php">
                    <input type="hidden" name="view" id="viewModeInput" value="<?= e($viewMode) ?>">
                    <?php if ($sort !== 'popular'): ?>
                        <input type="hidden" name="sort" value="<?= e($sort) ?>">
                    <?php endif; ?>
                    <div class="search-grid-top">
                        <div class="form-group">
                            <label><i class="fa-solid fa-location-dot"></i> PICK-UP HUB</label>
                            <select name="pickup_location" id="pickupLocationSelect">
                                <option value="bgc" <?= $pickupLocation === 'bgc' ? 'selected' : '' ?>>BGC Taguig Fleet Hub</option>
                                <option value="mnl" <?= $pickupLocation === 'mnl' ? 'selected' : '' ?>>Manila NAIA Terminal 3 (MNL)</option>
                                <option value="ceb" <?= $pickupLocation === 'ceb' ? 'selected' : '' ?>>Cebu Mactan Int'l Airport (CEB)</option>
                                <option value="crk" <?= $pickupLocation === 'crk' ? 'selected' : '' ?>>Clark Int'l Airport (CRK)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-arrow-rotate-left"></i> RETURN STATION</label>
                            <select name="return_location" id="returnLocationSelect">
                                <option value="same" <?= $returnLocation === 'same' ? 'selected' : '' ?>>Return to Same Location</option>
                                <option value="bgc" <?= $returnLocation === 'bgc' ? 'selected' : '' ?>>BGC Taguig Fleet Hub</option>
                                <option value="mnl" <?= $returnLocation === 'mnl' ? 'selected' : '' ?>>Manila NAIA Terminal 3 (MNL)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> PICKUP DATE &amp; TIME</label>
                            <input type="datetime-local" name="pickup_date" id="pickupDateInput" value="<?= e($pickupInput) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> RETURN DATE &amp; TIME</label>
                            <input type="datetime-local" name="return_date" id="returnDateInput" value="<?= e($returnInput) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-car-side"></i> VEHICLE CATEGORY</label>
                            <select name="category" id="categorySelect">
                                <option value="all" <?= $cat === 'all' ? 'selected' : '' ?>>All Vehicle Classes</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="search-grid-bottom">
                        <div class="budget-slider-box">
                            <label>Daily Budget Range: <span id="budgetLabel">Up to ₱<?= number_format($maxPrice) ?> / day</span></label>
                            <input type="range" class="slider" min="1200" max="8500" step="100" value="<?= (int)$maxPrice ?>" id="budgetRange" name="max_price">
                        </div>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="with_driver" value="1" <?= $withDriver ? 'checked' : '' ?>> With Driver Option</label>
                            <label><input type="checkbox" name="airport_fast" value="1" <?= $airportFast ? 'checked' : '' ?>> Airport Fast Delivery</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-search-submit"><i class="fa-solid fa-magnifying-glass"></i> Search Available Cars</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Filter Bar Section (matching SCREEN P02) -->
    <div class="filter-bar">
        <div class="container filter-container">
            <div class="filter-pills">
                <?php
                $catParams = $baseParams;
                unset($catParams['category'], $catParams['available'], $catParams['page']);
                $catBase = http_build_query($catParams);
                ?>
                <a class="pill <?= $cat === 'all' && !$onlyAvailable ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=all"><i class="fa-solid fa-car" style="margin-right:5px;"></i> All Vehicles (<?= $totalCount ?>)</a>
                <a class="pill <?= $onlyAvailable ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&available=1"><span class="status-dot-green"></span> Available Now (<?= $freeCount ?>)</a>
                <a class="pill <?= $cat === 'sedan' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=sedan">Sedans (<?= $sedanCount ?>)</a>
                <a class="pill <?= $cat === 'hatchback' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=hatchback">Hatchbacks (<?= $hatchCount ?>)</a>
                <a class="pill <?= $cat === 'suv' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=suv">SUVs (<?= $suvCount ?>)</a>
                <a class="pill <?= $cat === 'mpv' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=mpv">MPVs (<?= $mpvCount ?>)</a>
                <a class="pill <?= $cat === 'van' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=van">Vans (<?= $vanCount ?>)</a>
                <a class="pill <?= $cat === 'pickup' ? 'active' : '' ?>" href="rental.php?<?= $catBase ?>&category=pickup">Pickups (<?= $pickupCount ?>)</a>
            </div>

            <div class="filter-controls-right">
                <div class="sort-dropdown">
                    <span class="sort-label">SORT BY:</span>
                    <?php
                    $sortParams = $baseParams;
                    unset($sortParams['sort'], $sortParams['page']);
                    $sortBase = http_build_query($sortParams);
                    ?>
                    <select onchange="location = this.value;" class="sort-select">
                        <option value="rental.php?<?= $sortBase ?>&sort=popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular / Recommended</option>
                        <option value="rental.php?<?= $sortBase ?>&sort=price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="rental.php?<?= $sortBase ?>&sort=price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="view-toggles">
                    <button class="view-btn <?= $viewMode === 'list' ? 'active' : '' ?>" type="button" aria-label="List View" id="btnListView" title="List View"><i class="fa-solid fa-bars"></i></button>
                    <button class="view-btn <?= $viewMode === 'grid' ? 'active' : '' ?>" type="button" aria-label="Grid View" id="btnGridView" title="Grid View"><i class="fa-solid fa-table-cells-large"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory List Section -->
    <section class="inventory-section">
        <div class="container">
            <div class="inventory-header">
                <div class="title-group">
                    <h2>Featured Rental Inventory</h2>
                    <span class="hub-tag">BGC &amp; Metro Manila Hubs</span>
                </div>
                <div class="results-count"><span class="status-dot-green"></span> Showing <strong><?= count($shown) ?> verified vehicles</strong> matching instant booking</div>
            </div>

            <div class="vehicle-list <?= $viewMode === 'grid' ? 'grid-view' : '' ?>" id="vehicleList">
            <?php if (!$shown): ?>
                <div class="no-results-box" style="text-align: center; padding: 48px 24px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; grid-column: 1 / -1;">
                    <i class="fa-solid fa-car-tunnel" style="font-size: 38px; color: #94a3b8; margin-bottom: 12px;"></i>
                    <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No vehicles match your search filter</h3>
                    <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">Try increasing your daily budget or selecting a different category.</p>
                    <a href="rental.php" class="btn btn-primary" style="display:inline-block; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Reset All Filters</a>
                </div>
            <?php endif; ?>
            <?php foreach ($pagedVehicles as $v): 
                $cardImg = vehicle_image_url($v['image_path'] ?: $v['image_url']);
                $division = strtoupper($v['brand'] ?: $v['make']) . ' PHILIPPINES';
                $rating = '4.9' . ($v['id'] % 10);
                $reviews = (30 + ($v['id'] * 3)) . ' rentals';
                $title = ($v['brand'] ?: $v['make']) . ' ' . $v['model'];
                $desc = $v['description'];
                $specTrans = $v['transmission'];
                $specFuel = $v['fuel_type'];
                $specSeats = ($v['seating_capacity'] ?: $v['seats']) . ' Passengers';
                $specLuggage = (($v['seating_capacity'] ?: $v['seats']) >= 7 ? '4 Large Luggage' : '2 Luggage Bags');
                $perks = ['Insurance Included', 'Free Unlimited KM', 'Deep Sanitized'];
                $dailyRate = (float)$v['daily_rate'];
                $weeklyRate = $v['weekly_rate'] ? (float)$v['weekly_rate'] : round($dailyRate * 6.2);
                $savePct = 10;
                $badgeBot = $v['tag_label'] ?: 'Available for Self-Drive or Chauffeur';
                $targetPath = 'customer/vehicle.php?id=' . (int)$v['id'] . ($query !== '' ? '&' . $query : '');
                $vehicleLink = BASE_URL . 'login.php?redirect=' . urlencode($targetPath);
            ?>
                <div class="vehicle-card-horizontal" data-price="<?= (int)$dailyRate ?>" data-category="<?= e($v['category_slug']) ?>">
                    <div class="card-img-col">
                        <a href="<?= e($vehicleLink) ?>" title="View details for <?= e($title) ?>">
                            <img src="<?= e($cardImg) ?>" alt="<?= e($title) ?>" class="vehicle-full-photo">
                        </a>
                    </div>
                    <div class="card-info-col">
                        <div class="brand-rating">
                            <span class="brand"><?= e($division) ?></span>
                            <span class="star-rating"><i class="fa-solid fa-star"></i> <?= e($rating) ?> <span>(<?= e($reviews) ?>)</span></span>
                        </div>
                        <h3><a href="<?= e($vehicleLink) ?>" style="color:inherit; text-decoration:none;"><?= e($title) ?></a></h3>
                        <p class="vehicle-desc"><?= e($desc) ?></p>
                        
                        <div class="specs-grid-2col">
                            <span><i class="fa-solid fa-sliders"></i> <?= e($specTrans) ?></span>
                            <span><i class="fa-solid fa-gas-pump"></i> <?= e($specFuel) ?></span>
                            <span><i class="fa-solid fa-users"></i> <?= e($specSeats) ?></span>
                            <span><i class="fa-solid fa-suitcase"></i> <?= e($specLuggage) ?></span>
                        </div>

                        <div class="perks-tags-row">
                            <?php foreach ($perks as $p): ?>
                                <span class="perk-badge"><i class="fa-solid fa-check"></i> <?= e($p) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-price-col">
                        <div class="price-details">
                            <span class="label">RENTAL PRICING</span>
                            <div class="main-price"><strong>₱<?= number_format($dailyRate) ?></strong> <span>/ day</span></div>
                            <?php if ($weeklyRate): ?>
                                <div class="weekly-price">or <strong>₱<?= number_format($weeklyRate) ?> / week</strong> <span>(Save <?= (int)$savePct ?>%)</span></div>
                            <?php endif; ?>
                            <div class="delivery-perk"><i class="fa-solid fa-car"></i> <?= e($badgeBot) ?></div>
                        </div>
                        <div class="action-area">
                            <a href="<?= e($vehicleLink) ?>" class="btn btn-primary full-width">Rent Now <i class="fa-solid fa-arrow-right"></i></a>
                            <a href="<?= e($vehicleLink) ?>" class="btn btn-card-details full-width"><i class="fa-regular fa-eye"></i> View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Functional Numbered Pagination Bar (6 Vehicles Per Page) -->
            <div class="pagination-bar">
                <div class="page-info">
                    Showing <strong><?= $startItem ?>–<?= $endItem ?></strong> of <?= $totalMatching ?> available vehicles
                </div>
                <div class="page-numbers">
                    <?php
                    $pageParams = $baseParams;
                    unset($pageParams['page']);
                    $pageBase = http_build_query($pageParams);
                    ?>
                    <!-- Previous Button -->
                    <?php if ($page <= 1): ?>
                        <button type="button" class="page-arrow" disabled aria-label="Previous Page"><i class="fa-solid fa-chevron-left"></i></button>
                    <?php else: ?>
                        <a href="rental.php?<?= $pageBase ?>&page=<?= $page - 1 ?>" class="page-arrow" aria-label="Previous Page"><i class="fa-solid fa-chevron-left"></i></a>
                    <?php endif; ?>

                    <!-- Numbered Page Buttons -->
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p === $page): ?>
                            <span class="page-num active"><?= $p ?></span>
                        <?php else: ?>
                            <a href="rental.php?<?= $pageBase ?>&page=<?= $p ?>" class="page-num"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <?php if ($page >= $totalPages): ?>
                        <button type="button" class="page-arrow" disabled aria-label="Next Page"><i class="fa-solid fa-chevron-right"></i></button>
                    <?php else: ?>
                        <a href="rental.php?<?= $pageBase ?>&page=<?= $page + 1 ?>" class="page-arrow" aria-label="Next Page"><i class="fa-solid fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>

                <?php if ($page < $totalPages): ?>
                    <a href="rental.php?<?= $pageBase ?>&page=<?= $page + 1 ?>" class="btn-load-more" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-chevron-down" style="margin-right:6px;"></i> Next Page (<?= $totalMatching - $endItem ?> remaining)
                    </a>
                <?php else: ?>
                    <span class="btn-load-more" style="opacity:0.6; cursor:default; display:inline-flex; align-items:center; justify-content:center;">
                        <i class="fa-solid fa-check" style="margin-right:6px;"></i> All <?= $totalMatching ?> Vehicles Loaded
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer (matching SCREEN P02) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Budget slider live update & automatic form submission on change
        const budgetRange = document.getElementById('budgetRange');
        const budgetLabel = document.getElementById('budgetLabel');
        const searchForm = document.getElementById('rentalSearchForm');
        let sliderDebounce = null;

        if (budgetRange && budgetLabel) {
            budgetRange.addEventListener('input', function() {
                const val = parseInt(this.value, 10);
                budgetLabel.textContent = 'Up to ₱' + val.toLocaleString() + ' / day';
            });

            budgetRange.addEventListener('change', function() {
                clearTimeout(sliderDebounce);
                sliderDebounce = setTimeout(function () {
                    if (searchForm) {
                        searchForm.submit();
                    }
                }, 150);
            });
        }

        // 2. View toggles: List View vs Grid View
        const btnList = document.getElementById('btnListView');
        const btnGrid = document.getElementById('btnGridView');
        const vehicleList = document.getElementById('vehicleList');
        const viewModeInput = document.getElementById('viewModeInput');

        function setView(mode) {
            if (!vehicleList) return;
            if (mode === 'grid') {
                vehicleList.classList.add('grid-view');
                if (btnGrid) btnGrid.classList.add('active');
                if (btnList) btnList.classList.remove('active');
                if (viewModeInput) viewModeInput.value = 'grid';
                localStorage.setItem('ds_rental_view', 'grid');
                const url = new URL(window.location);
                url.searchParams.set('view', 'grid');
                window.history.replaceState({}, '', url);
            } else {
                vehicleList.classList.remove('grid-view');
                if (btnList) btnList.classList.add('active');
                if (btnGrid) btnGrid.classList.remove('active');
                if (viewModeInput) viewModeInput.value = 'list';
                localStorage.setItem('ds_rental_view', 'list');
                const url = new URL(window.location);
                url.searchParams.delete('view');
                window.history.replaceState({}, '', url);
            }
        }

        if (btnList && btnGrid) {
            btnList.addEventListener('click', function () { setView('list'); });
            btnGrid.addEventListener('click', function () { setView('grid'); });

            // Restore preference if no query parameter
            const queryView = new URLSearchParams(window.location.search).get('view');
            if (!queryView) {
                const saved = localStorage.getItem('ds_rental_view');
                if (saved === 'grid') {
                    setView('grid');
                }
            }
        }
    });
    </script>
</body>
</html>
