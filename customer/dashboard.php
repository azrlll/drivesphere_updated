<?php
$pageTitle = 'Customer Dashboard';
$nav = 'dashboard';
require_once __DIR__ . '/../includes/customer_header.php';

// Featured vehicles matching Image 1 exactly
$featuredVehicles = [
    [
        'id' => 1,
        'name' => 'Toyota Fortuner LTD 2.8L',
        'desc' => 'Top-tier performance with premium leather captain seats and JBL audio system.',
        'image' => BASE_URL . 'images/browse_fortuner.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '6-Speed AT'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Diesel', 'sub' => '2.8L Turbo'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Tri-Zone AC']
        ],
        'rate' => '4,200'
    ],
    [
        'id' => 2,
        'name' => 'Honda Civic RS Turbo',
        'desc' => 'Agile handling with Honda SENSING suite, wireless CarPlay, and sports paddle shifters.',
        'image' => BASE_URL . 'images/browse_civic.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => 'CVT Sport'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Petrol', 'sub' => '1.5L Turbo'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '5 Seats', 'sub' => 'Sport Cockpit']
        ],
        'rate' => '3,500'
    ],
    [
        'id' => 3,
        'name' => 'Mitsubishi Montero Sport GT',
        'desc' => 'High ground clearance, 8-speed automatic, and spacious 3-row comfort for long road trips.',
        'image' => BASE_URL . 'images/browse_montero.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '8-Speed AT'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Diesel', 'sub' => '2.4L MIVEC'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Family Setup']
        ],
        'rate' => '3,900'
    ],
    [
        'id' => 4,
        'name' => 'Toyota HiAce Grandia Elite',
        'desc' => 'First-class individual reclining Ottoman seats, dual power sliding doors, whisper-quiet cabin.',
        'image' => BASE_URL . 'images/browse_hiace.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '6-Speed AT'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Diesel', 'sub' => '2.8L 1GD'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '10 Seats', 'sub' => 'VIP Layout']
        ],
        'rate' => '5,800'
    ],
    [
        'id' => 5,
        'name' => 'Mazda 3 Fastback Premium',
        'desc' => 'Japanese mastercraft interior, heads-up display, and Bose 12-speaker surround sound.',
        'image' => BASE_URL . 'images/browse_mazda3.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => 'SKYACTIV-Drive'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Petrol', 'sub' => '2.0L M-Hybrid'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '5 Seats', 'sub' => 'Luxury Trim']
        ],
        'rate' => '3,200'
    ],
    [
        'id' => 6,
        'name' => 'Ford Everest Titanium+ 4x4',
        'desc' => 'Bi-Turbo power with panoramic moonroof, 12-inch portrait SYNC display, and 360 camera.',
        'image' => BASE_URL . 'images/browse_everest.png',
        'specs' => [
            ['icon' => 'fa-solid fa-dharmachakra', 'title' => 'Automatic', 'sub' => '10-Speed e-Shifter'],
            ['icon' => 'fa-solid fa-gas-pump', 'title' => 'Bi-Turbo', 'sub' => '2.0L Diesel'],
            ['icon' => 'fa-solid fa-user-group', 'title' => '7 Seats', 'sub' => 'Power Fold 3rd Row']
        ],
        'rate' => '4,600'
    ]
];
?>

<!-- Section Header (Matches Image 1) -->
<div class="dash-fleet-header">
    <div>
        <span class="avail-inv-tag"><span class="inv-dot"></span> AVAILABLE INVENTORY</span>
        <h1 class="dash-fleet-title">Featured Rental Fleet</h1>
        <p class="dash-fleet-sub">Choose from our most requested well-maintained models, meticulously detailed and road-ready.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 14px;">
        <button type="button" class="btn-v-action" id="filterToggleBtn" style="border-radius: 6px; padding: 7px 14px;">
            <i class="fa-solid fa-sliders"></i> Filter
        </button>
        <a href="<?= $C ?>browse.php" class="view-catalog-link">
            View Full Catalog <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- 2-Column Grid of 6 Featured Vehicles -->
<div class="dash-fleet-grid">
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

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
