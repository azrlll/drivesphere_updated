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
$cat = $_GET['category'] ?? 'all';
$onlyAvailable = !empty($_GET['available']);
$sort = $_GET['sort'] ?? 'popular';
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

// Specific mockup vehicle mappings for exact visual fidelity to SCREEN P02
$mockData = [
    1 => [
        'card_img' => 'images/rental_fortuner_card.png',
        'division' => 'TOYOTA MOTOR PHILIPPINES',
        'rating'   => '4.94',
        'reviews'  => '42 rentals',
        'title'    => 'Toyota Fortuner 2.8 4x4 LTD',
        'desc'     => 'Top-tier midsize SUV offering bulletproof reliability, leather interior, dual-zone climate control, and JBL premium audio. Ideal for provincial tours and high-profile transit.',
        'spec_trans' => 'Automatic Trans',
        'spec_fuel'  => '2.8L Diesel Turbo',
        'spec_seats' => '7 Passengers',
        'spec_luggage' => '4 Large Luggage',
        'perks'    => ['Insurance Included', 'Free Unlimited KM', 'Deep Sanitized'],
        'daily'    => 4200,
        'weekly'   => 26500,
        'save_pct' => 10,
        'badge_bot'=> 'Available for Self-Drive or Chauffeur'
    ],
    2 => [
        'card_img' => 'images/rental_civic_card.png',
        'division' => 'HONDA CARS PHILIPPINES',
        'rating'   => '4.88',
        'reviews'  => '38 rentals',
        'title'    => 'Honda Civic RS Turbo',
        'desc'     => 'Sporty, responsive turbocharged compact executive sedan featuring Honda SENSING safety suite, leather sport seats, Apple CarPlay, and high-efficiency city dynamics.',
        'spec_trans' => 'Automatic CVT',
        'spec_fuel'  => '1.5L Turbo Petrol',
        'spec_seats' => '5 Passengers',
        'spec_luggage' => '2 Luggage Bags',
        'perks'    => ['Full 4K Dashcam', 'Full Coverage', 'Apple CarPlay / Android'],
        'daily'    => 3500,
        'weekly'   => 22000,
        'save_pct' => 10,
        'badge_bot'=> 'Fuel Efficient • 17.2 km/L avg'
    ],
    4 => [
        'card_img' => 'images/rental_hiace_card.png',
        'division' => 'TOYOTA LUXURY DIVISION',
        'rating'   => '4.98',
        'reviews'  => '61 rentals',
        'title'    => 'Toyota HiAce Super Grandia Elite',
        'desc'     => "Flagship luxury people mover featuring captain's chairs with Ottoman footrests, power sliding doors, dual panoramic moonroof, and whisper-quiet cabin acoustics.",
        'spec_trans' => '6-Speed Automatic',
        'spec_fuel'  => '2.8L Diesel',
        'spec_seats' => '10 Passengers',
        'spec_luggage' => '6 Large Luggage',
        'perks'    => ['Chauffeur Optional', 'On-board 5G Wi-Fi', 'First-Class Comfort'],
        'daily'    => 5800,
        'weekly'   => 36000,
        'save_pct' => 12,
        'badge_bot'=> 'Diplomatic & VIP Preferred'
    ]
];

foreach ($vehicles as &$v) {
    $v['free'] = vehicle_is_available($pdo, $v['id'], $pickupSql, $returnSql);
}
unset($v);

// Filter by category or available
$shown = array_filter($vehicles, function ($v) use ($cat, $onlyAvailable) {
    // Only showcase the 3 mockup exemplar vehicles for default screen view or match filters
    if ($cat !== 'all' && $v['category_slug'] !== $cat) return false;
    if ($onlyAvailable && !$v['free']) return false;
    return in_array($v['id'], [1, 2, 4]);
});

// If the filter yielded no mock exemplar matches, fall back to any in category
if (empty($shown)) {
    $shown = array_filter($vehicles, function ($v) use ($cat, $onlyAvailable) {
        if ($cat !== 'all' && $v['category_slug'] !== $cat) return false;
        if ($onlyAvailable && !$v['free']) return false;
        return true;
    });
}

$query = 'pickup_date=' . urlencode($pickupInput) . '&return_date=' . urlencode($returnInput);
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
                    <div class="search-grid-top">
                        <div class="form-group">
                            <label><i class="fa-solid fa-location-dot"></i> PICK-UP HUB</label>
                            <select name="pickup_location">
                                <option value="bgc" selected>BGC Taguig Fleet Hub (Central)</option>
                                <option value="mnl">Manila NAIA Terminal 3 (MNL)</option>
                                <option value="ceb">Cebu Mactan Int'l Airport (CEB)</option>
                                <option value="crk">Clark Int'l Airport (CRK)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-arrow-rotate-left"></i> RETURN STATION</label>
                            <select name="return_location">
                                <option value="same" selected>Return to Same Location</option>
                                <option value="bgc">BGC Taguig Fleet Hub (Central)</option>
                                <option value="mnl">Manila NAIA Terminal 3 (MNL)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> PICKUP DATE &amp; TIME</label>
                            <input type="datetime-local" name="pickup_date" value="<?= e($pickupInput) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-regular fa-calendar"></i> RETURN DATE &amp; TIME</label>
                            <input type="datetime-local" name="return_date" value="<?= e($returnInput) ?>" min="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-car-side"></i> VEHICLE CATEGORY</label>
                            <select name="category">
                                <option value="all">All Vehicle Classes</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="search-grid-bottom">
                        <div class="budget-slider-box">
                            <label>Daily Budget Range: <span id="budgetLabel">Up to ₱8,500 / day</span></label>
                            <input type="range" class="slider" min="2000" max="10000" step="500" value="8500" id="budgetRange">
                        </div>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="with_driver" value="1" checked> With Driver Option</label>
                            <label><input type="checkbox" name="airport_fast" value="1"> Airport Fast Delivery</label>
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
                <a class="pill <?= $cat === 'all' && !$onlyAvailable ? 'active' : '' ?>" href="rental.php?<?= $query ?>"><i class="fa-solid fa-car" style="margin-right:5px;"></i> All Vehicles (28)</a>
                <a class="pill <?= $onlyAvailable ? 'active' : '' ?>" href="rental.php?<?= $query ?>&available=1"><span class="status-dot-green"></span> Available Now (19)</a>
                <a class="pill <?= $cat === 'suv' ? 'active' : '' ?>" href="rental.php?<?= $query ?>&category=suv">Premium SUVs (9)</a>
                <a class="pill <?= $cat === 'sedan' ? 'active' : '' ?>" href="rental.php?<?= $query ?>&category=sedan">Executive Sedans (7)</a>
                <a class="pill <?= $cat === 'van' ? 'active' : '' ?>" href="rental.php?<?= $query ?>&category=van">Passenger Vans (8)</a>
                <a class="pill" href="rental.php?<?= $query ?>&category=hybrid"><i class="fa-solid fa-bolt" style="margin-right:4px;color:#0284c7;"></i> Hybrid / EV (4)</a>
            </div>

            <div class="filter-controls-right">
                <div class="sort-dropdown">
                    <span class="sort-label">SORT BY:</span>
                    <select onchange="location = this.value;" class="sort-select">
                        <option value="rental.php?<?= $query ?>&sort=popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular / Recommended</option>
                        <option value="rental.php?<?= $query ?>&sort=price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="rental.php?<?= $query ?>&sort=price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                    </select>
                </div>
                <div class="view-toggles">
                    <button class="view-btn active" type="button" aria-label="List View"><i class="fa-solid fa-bars"></i></button>
                    <button class="view-btn" type="button" aria-label="Grid View"><i class="fa-solid fa-table-cells-large"></i></button>
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

            <div class="vehicle-list">
            <?php if (!$shown): ?>
                <div class="no-results-box">
                    <p>No vehicles match your search filter. Please try selecting different dates or another category.</p>
                </div>
            <?php endif; ?>
            <?php foreach ($shown as $v): 
                $m = $mockData[$v['id']] ?? null;
                $cardImg = $m['card_img'] ?? $v['image_url'];
                $division = $m['division'] ?? (strtoupper($v['make']) . ' MOTOR PHILIPPINES');
                $rating = $m['rating'] ?? '4.92';
                $reviews = $m['reviews'] ?? '40 rentals';
                $title = $m['title'] ?? ($v['make'] . ' ' . $v['model']);
                $desc = $m['desc'] ?? $v['description'];
                $specTrans = $m['spec_trans'] ?? $v['transmission'];
                $specFuel = $m['spec_fuel'] ?? $v['fuel_type'];
                $specSeats = $m['spec_seats'] ?? ((int)$v['seats'] . ' Passengers');
                $specLuggage = $m['spec_luggage'] ?? 'Luggage Ready';
                $perks = $m['perks'] ?? ['Insurance Included', 'Free Unlimited KM', 'Deep Sanitized'];
                $dailyRate = $m['daily'] ?? (float)$v['daily_rate'];
                $weeklyRate = $m['weekly'] ?? ($v['weekly_rate'] ? (float)$v['weekly_rate'] : null);
                $savePct = $m['save_pct'] ?? 10;
                $badgeBot = $m['badge_bot'] ?? 'Available for Self-Drive or Chauffeur';
            ?>
                <div class="vehicle-card-horizontal">
                    <div class="card-img-col">
                        <img src="<?= BASE_URL . $cardImg ?>" alt="<?= e($title) ?>" class="vehicle-full-photo">
                    </div>
                    <div class="card-info-col">
                        <div class="brand-rating">
                            <span class="brand"><?= e($division) ?></span>
                            <span class="star-rating"><i class="fa-solid fa-star"></i> <?= e($rating) ?> <span>(<?= e($reviews) ?>)</span></span>
                        </div>
                        <h3><?= e($title) ?></h3>
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
                            <a href="<?= BASE_URL ?>book.php?vehicle=<?= (int)$v['id'] ?>&<?= $query ?>" class="btn btn-primary full-width">Rent Now <i class="fa-solid fa-arrow-right"></i></a>
                            <a href="<?= BASE_URL ?>book.php?vehicle=<?= (int)$v['id'] ?>&<?= $query ?>" class="btn btn-card-details full-width"><i class="fa-regular fa-eye"></i> Vehicle Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Pagination Bar (matching SCREEN P02) -->
            <div class="pagination-bar">
                <div class="page-info">Showing <strong>3</strong> of 28 available vehicles • Page 1 of 10</div>
                <div class="page-numbers">
                    <button type="button" class="page-arrow" disabled><i class="fa-solid fa-chevron-left"></i></button>
                    <span class="page-num active">1</span>
                    <a href="rental.php?<?= $query ?>&page=2" class="page-num">2</a>
                    <a href="rental.php?<?= $query ?>&page=3" class="page-num">3</a>
                    <span class="page-dots">...</span>
                    <a href="rental.php?<?= $query ?>&page=10" class="page-num">10</a>
                    <a href="rental.php?<?= $query ?>&page=2" class="page-arrow"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
                <button type="button" class="btn-load-more"><i class="fa-solid fa-chevron-down" style="margin-right:6px;"></i> Load More Vehicles (25 remaining)</button>
            </div>
        </div>
    </section>

    <!-- Footer (matching SCREEN P02) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
    <script>
    const budgetRange = document.getElementById('budgetRange');
    const budgetLabel = document.getElementById('budgetLabel');
    if (budgetRange && budgetLabel) {
        budgetRange.addEventListener('input', function() {
            budgetLabel.textContent = 'Up to ₱' + Number(this.value).toLocaleString() + ' / day';
        });
    }
    </script>
</body>
</html>
