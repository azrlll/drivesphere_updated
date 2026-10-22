<?php 
require_once __DIR__ . '/includes/functions.php'; 
$pdo = getDBConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DriveSphere - Premier Philippine Vehicle Rental &amp; Fleet Network</title>

<!-- FontAwesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts (Inter) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Global Stylesheet -->
<link rel="stylesheet" href="css/global.css">
</head>
<body>

<!-- Header (Navbar matching SCREEN P01 & Public_Dropdown.png) -->
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ==========================================================================
    HERO SECTION
    ========================================================================== -->
<section class="hero">
<div class="container hero-container">
    
    <!-- Left Hero Text Content -->
    <div class="hero-content">
        <span class="badge badge-dark">
            <span class="pulse-dot" style="background:#38bdf8;width:7px;height:7px;display:inline-block;border-radius:50%;margin-right:8px;"></span>PREMIER PHILIPPINE FLEET NETWORK
        </span>
        <h1>Experience Premium Drives Across the Philippines</h1>
        <p>Discover self-drive and chauffeur rentals with seamless online booking, verified vehicle maintenance, and 24/7 dedicated customer support.</p>
        <div class="hero-buttons">
            <a href="rental.php" class="btn btn-primary btn-large">
                Explore Rental Fleet <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="fleet.php" class="btn btn-outline btn-large">
                <i class="fa-regular fa-id-badge" style="margin-right: 6px;"></i> View Fleet Guide
            </a>
        </div>
    </div>

    <!-- Right Quick Reservation Widget (matching SCREEN P01) -->
    <div class="hero-widget">
        <div class="widget-header">
            <h2>Quick Reserve</h2>
        </div>

        <form id="reserveForm" class="reservation-form" method="get" action="rental.php">
            <div class="form-group">
                <label>PICK-UP LOCATION</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-location-dot"></i>
                    <select name="pickup_location" required>
                        <option value="bgc" selected>BGC, Taguig City (Flagship Hub)</option>
                        <option value="mnl">Manila NAIA Terminal 3 (MNL)</option>
                        <option value="ceb">Cebu Mactan Int'l Airport (CEB)</option>
                        <option value="crk">Clark Int'l Airport (CRK)</option>
                        <option value="dvo">Davao Francisco Bangoy Int'l Airport (DVO)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>TRIP WINDOW</label>
                    <div class="input-with-icon">
                        <i class="fa-regular fa-calendar"></i>
                        <select name="trip_window">
                            <option value="3days" selected>3 Days (Fri-Sun)</option>
                            <option value="weekend">Weekend Getaway (2 Days)</option>
                            <option value="1week">1 Week (7 Days)</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>CATEGORY</label>
                    <div class="input-with-icon">
                        <i class="fa-solid fa-sliders"></i>
                        <select name="category">
                            <option value="all" selected>All Types</option>
                            <option value="suv">Premium SUV</option>
                            <option value="sedan">Business Sedan</option>
                            <option value="van">Executive Van</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary full-width">
                Search Availability <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>
    </div>

</div>
</section>

<!-- ==========================================================================
    FEATURES BAR (matching SCREEN P01)
    ========================================================================== -->
<section class="features-bar">
<div class="container features-container">
    
    <div class="feature-item">
        <div class="feature-icon"><i class="fa-solid fa-spray-can-sparkles"></i></div>
        <div class="feature-text">
            <h4>100% Sanitized</h4>
            <p>Multi-point hygiene checks</p>
        </div>
    </div>

    <div class="feature-item">
        <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
        <div class="feature-text">
            <h4>24/7 Support</h4>
            <p>Nationwide quick assistance</p>
        </div>
    </div>

    <div class="feature-item">
        <div class="feature-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
        <div class="feature-text">
            <h4>Zero Hidden Fees</h4>
            <p>Transparent all-in rates</p>
        </div>
    </div>

    <div class="feature-item">
        <div class="feature-icon"><i class="fa-regular fa-calendar-check"></i></div>
        <div class="feature-text">
            <h4>Flexible Plans</h4>
            <p>Free cancel up to 24h prior</p>
        </div>
    </div>

</div>
</section>

<!-- ==========================================================================
    FEATURED RENTAL FLEET CATALOG (matching SCREEN P01)
    ========================================================================== -->
<section class="fleet-section">
<div class="container">
    
    <!-- Section Header -->
    <div class="section-header">
        <div class="header-text">
            <span class="badge-text-blue"><i class="fa-solid fa-circle" style="font-size:7px;margin-right:6px;"></i> AVAILABLE INVENTORY</span>
            <h2>Featured Rental Fleet</h2>
            <p>Choose from our most requested well-maintained models, meticulously detailed and road-ready.</p>
        </div>
        <a href="rental.php" class="view-all">
            View Full Catalog <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Fleet Grid (6 Vehicles in 2-column layout fetched dynamically from MySQL) -->
    <div class="fleet-grid">
    <?php 
    $featuredSt = $pdo->query("SELECT v.*, c.name AS category_name FROM vehicles v 
                               JOIN categories c ON v.category_id = c.id 
                               WHERE v.status = 'available' 
                               ORDER BY v.id ASC LIMIT 6");
    $homeFeatured = $featuredSt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($homeFeatured as $fv): 
        $fImg = vehicle_image_url($fv['image_path'] ?: $fv['image_url']);
        $fTitle = ($fv['brand'] ?: $fv['make']) . ' ' . $fv['model'];
        $fTag = $fv['tag_label'] ?: (strtoupper($fv['category']) . ' • ' . $fv['year']);
        $fTarget = 'customer/vehicle.php?id=' . (int)$fv['id'];
        $fLoginLink = BASE_URL . 'login.php?redirect=' . urlencode($fTarget);
    ?>
        <div class="fleet-card">
            <a href="<?= e($fLoginLink) ?>" class="card-image-placeholder" title="View details of <?= e($fTitle) ?>">
                <img src="<?= e($fImg) ?>" alt="<?= e($fTitle) ?>">
                <div class="card-tags">
                    <span class="tag tag-black"><?= e($fTag) ?></span>
                    <span class="tag tag-white"><span class="status-dot"></span> Instant Self-Drive</span>
                </div>
            </a>
            <div class="card-content">
                <h3><a href="<?= e($fLoginLink) ?>" style="color:inherit;text-decoration:none;"><?= e($fTitle) ?></a></h3>
                <p class="description"><?= e($fv['description']) ?></p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong><?= e($fv['transmission']) ?></strong>
                        <span>Drivetrain</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong><?= e($fv['fuel_type']) ?></strong>
                        <span>Fuel Type</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong><?= (int)($fv['seating_capacity'] ?: $fv['seats']) ?> Seats</strong>
                        <span>Capacity</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱<?= number_format($fv['daily_rate'], 0) ?></strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="<?= e($fLoginLink) ?>" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div><!-- /.fleet-grid -->

</div>
</section>

<!-- Footer (matching SCREEN P01) -->
<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Main JavaScript Controller -->
<script src="js/script.js"></script>
</body>
</html>
