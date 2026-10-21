<?php require_once __DIR__ . '/includes/functions.php'; ?>
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
        <p>Discover self-drive and chauffeur rentals with seamless online booking, verified vehicle maintenance, and 24/7 roadside assistance.</p>
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
            <h4>24/7 Roadside</h4>
            <p>Nationwide quick dispatch</p>
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

    <!-- Fleet Grid (6 Vehicles in 2-column layout matching SCREEN P01) -->
    <div class="fleet-grid">
    
        <!-- Vehicle 1: Toyota Fortuner -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&q=80&w=800" alt="Toyota Fortuner LTD 2.8L" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-black">7-SEATER PREMIUM DIESEL SUV</span>
                    <span class="tag tag-white"><span class="status-dot"></span> Instant Self-Drive</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Toyota Fortuner LTD 2.8L</h3>
                <p class="description">Top-tier performance with premium leather captain seats and JBL audio system.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>6-Speed AT</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Diesel</strong>
                        <span>2.8L Turbo</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>7 Seats</strong>
                        <span>Tri-Zone AC</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱4,200</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=1" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle 2: Honda Civic -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&q=80&w=800" alt="Honda Civic RS Turbo" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-black">SPORT EXECUTIVE SEDAN</span>
                    <span class="tag tag-white"><span class="status-dot"></span> Instant Self-Drive</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Honda Civic RS Turbo</h3>
                <p class="description">Agile handling with Honda SENSING suite, wireless CarPlay, and sports paddle shifters.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>CVT Sport</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Petrol</strong>
                        <span>1.5L Turbo</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>5 Seats</strong>
                        <span>Sport Cockpit</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱3,500</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=2" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle 3: Mitsubishi Montero -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&q=80&w=800" alt="Mitsubishi Montero Sport GT" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-black">FAMILY ADVENTURE SUV</span>
                    <span class="tag tag-white"><span class="status-dot"></span> Ready to Drive</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Mitsubishi Montero Sport GT</h3>
                <p class="description">High ground clearance, 8-speed automatic, and spacious 3-row comfort for long road trips.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>8-Speed AT</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Diesel</strong>
                        <span>2.4L MIVEC</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>7 Seats</strong>
                        <span>Family Setup</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱3,900</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=3" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle 4: Toyota HiAce -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&q=80&w=800" alt="Toyota HiAce Grandia Elite" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-blue">EXECUTIVE VAN / CHAUFFEUR READY</span>
                    <span class="tag tag-white"><i class="fa-solid fa-couch" style="margin-right:4px;"></i> Captain Seats</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Toyota HiAce Grandia Elite</h3>
                <p class="description">First-class individual reclining Ottoman seats, dual power sliding doors, whisper-quiet cabin.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>6-Speed AT</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Diesel</strong>
                        <span>2.8L D-4D</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>10 Seats</strong>
                        <span>VIP Layout</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱5,800</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=4" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle 5: Mazda 3 -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=800" alt="Mazda 3 Fastback Premium" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-black">REFINED COMPACT SEDAN</span>
                    <span class="tag tag-white"><span class="status-dot"></span> Instant Self-Drive</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Mazda 3 Fastback Premium</h3>
                <p class="description">Japanese mastercraft interior, heads-up display, and Bose 12-speaker surround sound.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>SKYACTIV-Drive</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Petrol</strong>
                        <span>2.0L M-Hybrid</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>5 Seats</strong>
                        <span>Luxury Trim</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱3,200</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=5" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle 6: Ford Everest -->
        <div class="fleet-card">
            <div class="card-image-placeholder">
                <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80&w=800" alt="Ford Everest Titanium+ 4x4" style="width:100%; height:100%; object-fit:cover;">
                <div class="card-tags">
                    <span class="tag tag-black">LUXURY OFF-ROAD SUV</span>
                    <span class="tag tag-white"><span class="status-dot"></span> All-Terrain 4WD</span>
                </div>
            </div>
            <div class="card-content">
                <h3>Ford Everest Titanium+ 4x4</h3>
                <p class="description">Bi-Turbo power with panoramic moonroof, 12-inch portrait sync display, and 360 camera.</p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <i class="fa-solid fa-gear"></i>
                        <strong>Automatic</strong>
                        <span>10-Speed e-Shifter</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-gas-pump"></i>
                        <strong>Bi-Turbo</strong>
                        <span>2.0L Diesel</span>
                    </div>
                    <div class="spec-item">
                        <i class="fa-solid fa-users"></i>
                        <strong>7 Seats</strong>
                        <span>Power Fold 3rd Row</span>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="price">
                        <span class="currency-label">DAILY RATE</span>
                        <div><strong>₱4,600</strong> <span class="period">/ day</span></div>
                    </div>
                    <a href="book.php?vehicle=6" class="btn btn-primary">
                        Rent Now <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

    </div><!-- /.fleet-grid -->

</div>
</section>

<!-- Footer (matching SCREEN P01) -->
<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Main JavaScript Controller -->
<script src="js/script.js"></script>
</body>
</html>
