<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Fleet Standards &amp; Categories</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header Section (matching SCREEN P03) -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- Fleet Hero Section -->
        <section class="fleet-hero">
            <div class="container">
                <div class="fleet-hero-content">
                    <div class="badge-pill-cert">
                        <span class="pulse-dot"></span>PHILIPPINE FLEET CERTIFICATION 2025
                    </div>
                    <h1>Engineered for Safety, Maintained<br>for Perfection</h1>
                    <p>Explore the DriveSphere fleet standards — from agile city commuters to rugged 4&times;4 expedition SUVs and VIP executive transport across Luzon, Visayas, and Mindanao.</p>
                    <div class="hero-cat-pills-row">
                        <a href="#cat-01" class="hero-cat-pill">01. Premium SUVs</a>
                        <a href="#cat-02" class="hero-cat-pill">02. Business Sedans</a>
                        <a href="#cat-03" class="hero-cat-pill">03. VIP Shuttles</a>
                    </div>
                </div>

                <!-- Hero Stats Grid (4 cards matching SCREEN P03) -->
                <div class="hero-stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>150+</h3>
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <strong>Certified Vehicles</strong>
                        <p>Multi-tier active registry</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>100%</h3>
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <strong>40-Point Pre-Trip</strong>
                        <p>Computerized multi-sensor audit</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>&lt; 2 Yrs</h3>
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <strong>Avg Fleet Age</strong>
                        <p>Current generational models</p>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <h3>24 / 7</h3>
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <strong>Dedicated Support</strong>
                        <p>Nationwide dispatch coverage</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Telemetry & Reliability Section -->
        <section class="telemetry-section">
            <div class="container telemetry-grid">
                <div class="telemetry-text">
                    <span class="badge-sop">STANDARD OPERATING PROTOCOL</span>
                    <h2>Precision Telemetry Meets White–Glove Reliability</h2>
                    <p>At DriveSphere, every rental unit undergoes an uncompromising life-cycle regimen. We don't just hand over keys — we commission high-performance machinery primed for the unique topographies of Philippine motorways, mountain passes, and dense urban corridors.</p>
                    <p>From computerized OBD-II engine mapping prior to key dispatch, to hospital-grade interior sanitization, our commitment protects executives, families, and cargo without compromise.</p>
                </div>
                
                <div class="telemetry-features">
                    <div class="t-feature-card">
                        <div class="t-icon"><i class="fa-solid fa-microchip"></i></div>
                        <h4>Computerized Diagnostics</h4>
                        <p>Every engine, brake system, and battery undergoes automated real-time telemetry scans before client handover.</p>
                        <span class="t-badge-link"><span class="t-dot"></span> OBD-II Verified Live</span>
                    </div>
                    <div class="t-feature-card">
                        <div class="t-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4>Comprehensive Coverage</h4>
                        <p>Complete collision damage waiver (CDW), third-party liability (CTPL), and personal accident indemnity embedded automatically.</p>
                        <span class="t-badge-link"><span class="t-dot"></span> ₱0 Deductible Options</span>
                    </div>
                    <div class="t-feature-card">
                        <div class="t-icon"><i class="fa-solid fa-spray-can-sparkles"></i></div>
                        <h4>Guaranteed Purity Seal</h4>
                        <p>Multi-point chemical-free steam sanitization and air filtration detailing guarantees immaculate cabin conditions.</p>
                        <span class="t-badge-link"><span class="t-dot"></span> Sealed &amp; Tagged</span>
                    </div>
                    <div class="t-feature-card">
                        <div class="t-icon"><i class="fa-solid fa-sliders"></i></div>
                        <h4>Road-Ready Hardware</h4>
                        <p>Fresh tread depth (&gt;5mm mandated), optimized tire pressures, and high-spec LED arrays tailored for nocturnal Philippine driving.</p>
                        <span class="t-badge-link"><span class="t-dot"></span> All-Weather Certified</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dedicated Fleet Categories (Timeline Layout matching SCREEN P03) -->
        <section class="fleet-categories-section">
            <div class="container">
                <div class="category-header">
                    <span class="badge-cat-classes">STRUCTURED VEHICLE CLASSES</span>
                    <h2>Our Dedicated Fleet Categories</h2>
                    <p>Precision-calibrated machines configured for corporate delegations, island touring, and high-density metropolis commutes.</p>
                </div>

                <div class="timeline-container">
                    <!-- Vertical Line -->
                    <div class="timeline-line"></div>

                    <!-- Category Node 01: Executive & Premium SUVs -->
                    <div class="timeline-row" id="cat-01">
                        <div class="timeline-col img-col">
                            <div class="cat-image-card">
                                <img src="<?= BASE_URL ?>images/fleet_suv_card.png" alt="Executive &amp; Premium SUVs" class="fleet-node-img">
                            </div>
                        </div>
                        <div class="timeline-node node-dark">01</div>
                        <div class="timeline-col text-col text-right-col">
                            <div class="node-meta-badge"><i class="fa-solid fa-mountain"></i> Category Node 01 • Heavy-Duty Luxury</div>
                            <h3>Executive &amp; Premium SUVs</h3>
                            <p>Engineered for both VIP transit and rugged island journeys. Featuring high ground clearance, advanced all-wheel drivetrain systems, and reinforced chassis dynamics, our SUV class masters provincial mountain grades and flooded urban downpours alike.</p>
                            
                            <div class="bullet-grid-2col">
                                <div class="node-feature-box">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <div>
                                        <strong>Armored / Escort Ready</strong>
                                        <span>Heavy duty suspension &amp; tinted ballistic film options.</span>
                                    </div>
                                </div>
                                <div class="node-feature-box">
                                    <i class="fa-solid fa-compass"></i>
                                    <div>
                                        <strong>Rough Terrain Mastery</strong>
                                        <span>Hill-descent control and terrain selector electronics.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="active-models-box">
                                <div class="model-info">
                                    <span class="active-label">ACTIVE EXEMPLARS:</span>
                                    <strong>Toyota Fortuner LTD 4x4 • Ford Everest Titanium+ • Montero Sport</strong>
                                </div>
                                <div class="model-price">
                                    <span>Starting from</span>
                                    <strong>₱3,800 <span>/ day</span></strong>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>rental.php?category=suv" class="btn btn-primary">View Available SUVs in Rental <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>

                    <!-- Category Node 02: Modern City & Business Sedans -->
                    <div class="timeline-row reverse-row" id="cat-02">
                        <div class="timeline-col text-col text-left-col">
                            <div class="node-meta-badge"><i class="fa-regular fa-file-lines"></i> Category Node 02 • Urban Efficiency &amp; Elegance</div>
                            <h3>Modern City &amp; Business Sedans</h3>
                            <p>Crafted for seamless navigation through Bonifacio Global City, Makati Central Business District, and regional expressways. These sedans deliver refined aerodynamic ride comfort, whisper-quiet cabin acoustics, and class-leading fuel consumption.</p>
                            
                            <div class="efficiency-card">
                                <div class="eff-labels">
                                    <span class="eff-title">Efficiency Index</span>
                                    <span class="eff-val">Up to 18.4 km/L</span>
                                </div>
                                <div class="progress-track"><div class="progress-fill" style="width: 82%;"></div></div>
                                <div class="eff-labels bottom">
                                    <span>Stop • Start Metro Traffic Optimized</span>
                                    <span>Euro 6 Certified</span>
                                </div>
                            </div>

                            <div class="active-models-box">
                                <div class="model-info">
                                    <span class="active-label">ACTIVE EXEMPLARS:</span>
                                    <strong>Honda Civic RS Turbo • Mazda 3 Fastback • Toyota Altis Hybrid</strong>
                                </div>
                                <div class="model-price">
                                    <span>Starting from</span>
                                    <strong>₱2,400 <span>/ day</span></strong>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>rental.php?category=sedan" class="btn btn-dark">View Sedans in Rental <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                        <div class="timeline-node node-blue">02</div>
                        <div class="timeline-col img-col">
                            <div class="cat-image-card">
                                <img src="<?= BASE_URL ?>images/fleet_sedan_card.png" alt="Modern City &amp; Business Sedans" class="fleet-node-img">
                            </div>
                        </div>
                    </div>

                    <!-- Category Node 03: Commercial & Group VIP Shuttles -->
                    <div class="timeline-row" id="cat-03">
                        <div class="timeline-col img-col">
                            <div class="cat-image-card">
                                <img src="<?= BASE_URL ?>images/fleet_van_card.png" alt="Commercial &amp; Group VIP Shuttles" class="fleet-node-img">
                            </div>
                        </div>
                        <div class="timeline-node node-dark">03</div>
                        <div class="timeline-col text-col text-right-col">
                            <div class="node-meta-badge"><i class="fa-solid fa-users"></i> Category Node 03 • Corporate Delegation &amp; Group VIP</div>
                            <h3>Commercial &amp; Group VIP Shuttles</h3>
                            <p>The benchmark for corporate airport transfers, summit transport, and cross-island corporate tours. Furnished with first-class captain seats, dual-climate rear compressors, USB-C ports on all rows, and ample storage for golf gear and international luggage.</p>
                            
                            <div class="vertical-features-stack">
                                <div class="v-feat-item">
                                    <div class="v-feat-icon"><i class="fa-solid fa-plane-departure"></i></div>
                                    <div class="v-feat-text">
                                        <strong>NAIA &amp; Clark Airport Seamless Handover</strong>
                                        <p>Dedicated curbside pickup clearance and chauffeur coordination.</p>
                                    </div>
                                </div>
                                <div class="v-feat-item">
                                    <div class="v-feat-icon"><i class="fa-solid fa-couch"></i></div>
                                    <div class="v-feat-text">
                                        <strong>Quilted Ottoman Captain Chairs</strong>
                                        <p>Motorized reclining with individual power outlets and reading lamps.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="active-models-box">
                                <div class="model-info">
                                    <span class="active-label">ACTIVE EXEMPLARS:</span>
                                    <strong>Toyota HiAce Super Grandia Elite • Commuter Deluxe • Hyundai Staria VIP</strong>
                                </div>
                                <div class="model-price">
                                    <span>Starting from</span>
                                    <strong>₱4,500 <span>/ day</span></strong>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>rental.php?category=van" class="btn btn-primary">View Vans in Rental <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Section (matching SCREEN P03) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
