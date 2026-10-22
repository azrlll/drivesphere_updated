<?php
$pageTitle = 'Browse Vehicles';
$nav = 'browse';
require_once __DIR__ . '/../includes/customer_header.php';

$q = trim($_GET['q'] ?? '');

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

// Fetch available vehicles dynamically from MySQL matching active filters
$where = ["v.status = 'available'"];
$params = [];

if ($filterType !== 'all') {
    $where[] = "(LOWER(c.slug) = :ftype OR LOWER(v.category) = :ftype)";
    $params['ftype'] = strtolower($filterType);
}
if ($filterTrans !== 'all') {
    $where[] = "LOWER(v.transmission) LIKE :ftrans";
    $params['ftrans'] = '%' . strtolower($filterTrans) . '%';
}
if ($filterBrand !== 'all') {
    $where[] = "(v.brand = :fbrand OR v.make = :fbrand)";
    $params['fbrand'] = $filterBrand;
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
    $where[] = "(v.model LIKE :fq OR v.brand LIKE :fq OR v.make LIKE :fq OR v.description LIKE :fq)";
    $params['fq'] = '%' . $q . '%';
}

$whereSql = implode(' AND ', $where);
$st = $pdo->prepare("SELECT v.*, c.name AS category_name FROM vehicles v 
                   JOIN categories c ON v.category_id = c.id 
                   WHERE $whereSql 
                   ORDER BY v.id ASC LIMIT 12");
$st->execute($params);
$dbFeatured = $st->fetchAll(PDO::FETCH_ASSOC);

$featuredVehicles = [];
foreach ($dbFeatured as $row) {
    $featuredVehicles[] = [
        'id' => (int)$row['id'],
        'name' => ($row['brand'] ?: $row['make']) . ' ' . $row['model'],
        'desc' => $row['description'],
        'image' => vehicle_image_url($row['image_path'] ?: $row['image_url']),
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => $row['transmission'], 'sub' => 'Drivetrain'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => $row['fuel_type'], 'sub' => 'Fuel Type'],
            ['icon' => 'fa-solid fa-user-group', 'title' => $row['seating_capacity'] . ' Seats', 'sub' => 'Capacity']
        ],
        'rate' => number_format($row['daily_rate'], 0)
    ];
};
?>

<!-- Floating Search & Filter Bar (Matching User Image) -->
<form method="get" action="browse.php" class="browse-search-bar" id="browseSearchForm">
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

<!-- Section Header (Matches Image 1) -->
<div class="dash-fleet-header">
    <div>
        <span class="avail-inv-tag"><span class="inv-dot"></span> AVAILABLE INVENTORY</span>
        <h1 class="dash-fleet-title">Featured Rental Fleet</h1>
        <p class="dash-fleet-sub">Choose from our most requested well-maintained models, meticulously detailed and road-ready.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 14px;">
        <button type="button" class="btn-v-action" id="filterToggleBtn2" onclick="document.getElementById('filterToggleBtn').click();" style="border-radius: 6px; padding: 7px 14px;">
            <i class="fa-solid fa-sliders"></i> Filter
        </button>
        <a href="<?= $C ?>dashboard.php" class="view-catalog-link">
            View Full Catalog <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- 2-Column Grid of 6 Featured Vehicles -->
<div class="dash-fleet-grid" id="featuredGrid">
    <?php if (empty($featuredVehicles)): ?>
        <div class="no-results-box" style="grid-column: 1 / -1; margin: 20px 0; text-align: center; padding: 48px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
            <i class="fa-solid fa-car-tunnel" style="font-size: 38px; color: #94a3b8; margin-bottom: 14px;"></i>
            <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No featured vehicles match your search</h3>
            <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">Try adjusting your keyword or browse our complete 30-vehicle catalog.</p>
            <a href="browse.php" class="btn btn-primary" style="display: inline-block; padding: 9px 18px; border-radius: 6px; background: #0284c7; color: #fff; font-weight: 700; text-decoration: none; margin-right: 8px;">Reset Search</a>
            <a href="dashboard.php" class="btn btn-dark" style="display: inline-block; padding: 9px 18px; border-radius: 6px; background: #0f172a; color: #fff; font-weight: 700; text-decoration: none;">View Full Catalog</a>
        </div>
    <?php endif; ?>
    <?php foreach ($featuredVehicles as $v): ?>
        <div class="featured-card">
            <!-- Vehicle Photo -->
            <div class="featured-card-media">
                <img src="<?= e($v['image']) ?>" alt="<?= e($v['name']) ?>" class="featured-card-img" loading="lazy">
            </div>

            <!-- Card Body -->
            <div class="featured-card-body">
                <h3 class="featured-card-title"><?= e($v['name']) ?></h3>
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

<div id="noLiveBrowseResults" style="display: none; margin: 20px 0; text-align: center; padding: 48px 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;">
    <i class="fa-solid fa-car-tunnel" style="font-size: 38px; color: #94a3b8; margin-bottom: 14px;"></i>
    <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">No featured vehicles match your search</h3>
    <p style="font-size: 14px; color: #64748b; margin-bottom: 18px;">Try clearing your search input or view our full 30-vehicle catalog.</p>
    <a href="dashboard.php" style="display: inline-block; padding: 9px 18px; border-radius: 6px; background: #0284c7; color: #fff; font-weight: 700; text-decoration: none;">View Full Catalog</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('browseSearchInput');
    const cards = document.querySelectorAll('.featured-card');
    const noLiveResults = document.getElementById('noLiveBrowseResults');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;
            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (!query || text.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            if (noLiveResults) {
                noLiveResults.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
