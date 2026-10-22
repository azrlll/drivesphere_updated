<?php
$pageTitle = 'About Us';
$nav = 'about';
$fullWidth = true;
$includeGlobalCss = true;
require_once __DIR__ . '/../includes/customer_header.php';
?>

<!-- About Hero Section (matching SCREEN P04 and User Uploaded Image) -->
<section class="about-hero">
    <div class="container">
        <div class="about-hero-text">
            <div class="dossier-tag">
                <span class="dossier-blue">CORPORATE DOSSIER</span> <span class="dossier-divider">/</span> <span class="dossier-gray">ABOUT DRIVESPHERE</span>
            </div>
            <h1>Redefining Mobility &amp; Fleet Excellence Across the Philippines</h1>
            <p>Founded with a mission to deliver transparent, technology-enabled, and uncompromising vehicle rental experiences for individuals, enterprises, and VIP delegations nationwide.</p>
        </div>

        <div class="about-stats-grid">
            <div class="a-stat-card">
                <span class="a-stat-label"><i class="fa-solid fa-shield-halved"></i> TRACK RECORD</span>
                <h3>5+ Years</h3>
                <p>Serving Metro Manila &amp; Key Provinces</p>
            </div>
            <div class="a-stat-card">
                <span class="a-stat-label"><i class="fa-regular fa-clock"></i> RELIABILITY</span>
                <h3>99.4%</h3>
                <p>On-Time Handover &amp; Dispatch Rate</p>
            </div>
            <div class="a-stat-card">
                <span class="a-stat-label"><i class="fa-solid fa-car"></i> ACTIVE FLEET</span>
                <h3>150+</h3>
                <p>Certified OEM-Maintained Vehicles</p>
            </div>
            <div class="a-stat-card">
                <span class="a-stat-label"><i class="fa-solid fa-route"></i> EXECUTION</span>
                <h3>12,000+</h3>
                <p>Journeys Completed Flawlessly</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="mission-section">
    <div class="container">
        <div class="mission-card-white">
            <div class="mission-left">
                <div class="badge-pill-light"><span class="pulse-dot-blue"></span>OUR MISSION &amp; VISION</div>
                <h2>A Modern Mobility Platform Built on Integrity <span class="text-underlined">and Precision Engineering</span></h2>
            </div>
            <div class="mission-right">
                <p>DriveSphere bridges the gap between traditional car rentals and modern digital fleet intelligence. Whether providing executive chauffeur services for MNC board meetings in Bonifacio Global City or self-drive utility 4&times;4s for provincial expeditions across Luzon and the Visayas, we hold every vehicle to rigorous OEM-grade standards.</p>
                <div class="core-values-grid">
                    <div class="value-box">
                        <div class="val-icon"><i class="fa-solid fa-compass-drafting"></i></div>
                        <h4>OEM Rigor</h4>
                        <p>Scheduled factory inspections strictly aligned with certified manufacturer tolerances.</p>
                    </div>
                    <div class="value-box">
                        <div class="val-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
                        <h4>Zero Friction</h4>
                        <p>App-based verification, transparent digital contracts, and swift curb handovers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Commitment Split Layouts -->
<section class="split-sections">
    <!-- Split 1: Image Left, Text Right -->
    <div class="container split-row">
        <div class="split-col img-col">
            <div class="split-image-card">
                <img src="<?= BASE_URL ?>images/about_sanitization_bay.png" alt="Hospital-Grade Sanitization" class="split-img-fluid">
            </div>
        </div>
        <div class="split-col text-col padding-left">
            <div class="badge-pill-light">OUR COMMITMENT TO STANDARDS</div>
            <h2>Automated Telemetry &amp; Hospital–Grade Sanitization</h2>
            <p>Every vehicle in our fleet is integrated with real-time OBD-II health diagnostic sensors and undergoes hospital-grade interior ozone decontamination prior to handover. Our customers never worry about maintenance history, hidden mechanical wear, or cleanliness.</p>
            <ul class="custom-bullets">
                <li><i class="fa-solid fa-circle-check"></i> Real-time tire pressure &amp; brake pad monitoring synced to dispatch systems.</li>
                <li><i class="fa-solid fa-circle-check"></i> Contactless lockbox or white-glove airport curb handovers across NAIA Terminals &amp; Clark.</li>
                <li><i class="fa-solid fa-circle-check"></i> Transparent zero-surprise fuel and comprehensive CDW insurance policies.</li>
            </ul>
        </div>
    </div>

    <!-- Split 2: Text Left, Image Right -->
    <div class="container split-row reverse">
        <div class="split-col text-col padding-right">
            <div class="badge-pill-light">ISLAND-WIDE EXPANSION</div>
            <h2>Strategic Mobility Hubs Across Key Metropolises</h2>
            <p>Operating from central hubs in BGC Taguig, Makati, Clark Freeport, and Cebu IT Park, DriveSphere provides rapid regional mobilization. Our dedicated customer support network ensures no driver is ever stranded.</p>
            <ul class="custom-bullets-icons">
                <li><div class="hub-bullet-icon"><i class="fa-solid fa-headset"></i></div> <span>24/7 dedicated bilingual dispatch hotline with localized road mechanics.</span></li>
                <li><div class="hub-bullet-icon"><i class="fa-solid fa-arrows-rotate"></i></div> <span>Instant replacement vehicle guarantee within 60 minutes in Metro Manila.</span></li>
                <li><div class="hub-bullet-icon"><i class="fa-solid fa-file-invoice"></i></div> <span>Corporate fleet invoicing and bespoke multi-vehicle leasing contracts.</span></li>
            </ul>
        </div>
        <div class="split-col img-col">
            <div class="split-image-card">
                <img src="<?= BASE_URL ?>images/about_showroom.png" alt="Strategic Mobility Hubs" class="split-img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Pillars Section (matching SCREEN P04) -->
<section class="pillars-section">
    <div class="container text-center">
        <div class="badge-pill-gray">OUR PRINCIPLES</div>
        <h2 class="section-title">The Pillars of DriveSphere Integrity</h2>
        <p class="section-subtitle">Engineered to instill complete confidence on every commercial or recreational journey.</p>
        
        <div class="pillars-grid">
            <div class="pillar-card">
                <div class="p-icon"><i class="fa-solid fa-certificate"></i></div>
                <h4>Uncompromising Safety</h4>
                <p>40-point mechanical certification before every single rental handover covering drivetrain, braking, and safety telemetry.</p>
            </div>
            <div class="pillar-card">
                <div class="p-icon"><i class="fa-solid fa-vault"></i></div>
                <h4>Radical Transparency</h4>
                <p>All-inclusive rates quoted in Philippine Pesos (₱) with zero hidden handover surcharges or undisclosed admin fees.</p>
            </div>
            <div class="pillar-card">
                <div class="p-icon"><i class="fa-solid fa-bell-concierge"></i></div>
                <h4>Client-First Concierge</h4>
                <p>24/7 localized support, VIP airside greeters, and dedicated enterprise account managers tailored to corporate needs.</p>
            </div>
            <div class="pillar-card">
                <div class="p-icon"><i class="fa-solid fa-leaf"></i></div>
                <h4>Sustainable Mobility</h4>
                <p>Actively growing hybrid and low-emission fleet options to support greener corporate ESG objectives across Luzon.</p>
            </div>
        </div>
    </div>
</section>

<!-- Ecosystem Section (matching SCREEN P04) -->
<section class="ecosystem-section">
    <div class="container">
        <div class="badge-pill-gray text-left" style="margin-bottom:8px;">STRATEGIC INITIATIVES</div>
        <h2 class="section-title text-left" style="margin-bottom:32px;">Deep Dive into DriveSphere Ecosystem</h2>
        
        <div class="eco-grid">
            <div class="eco-card">
                <div class="eco-img-box">
                    <img src="<?= BASE_URL ?>images/about_team.png" alt="Leadership &amp; Fleet Engineering Team" class="eco-img-fluid">
                </div>
                <div class="eco-content">
                    <h4>Leadership &amp; Fleet Engineering Team</h4>
                    <p>Meet the certified technicians and operations directors behind DriveSphere's spotless track record and stringent mechanical oversight.</p>
                </div>
            </div>
            <div class="eco-card">
                <div class="eco-img-box">
                    <img src="<?= BASE_URL ?>images/about_corporate.png" alt="Corporate Fleet Partnerships" class="eco-img-fluid">
                </div>
                <div class="eco-content">
                    <h4>Corporate Fleet Partnerships</h4>
                    <p>Customized corporate mobility agreements, tax invoicing, and long-term executive leasing for Philippine businesses and institutions.</p>
                </div>
            </div>
            <div class="eco-card">
                <div class="eco-img-box">
                    <img src="<?= BASE_URL ?>images/about_ev.png" alt="Sustainability &amp; Future Initiatives" class="eco-img-fluid">
                </div>
                <div class="eco-content">
                    <h4>Sustainability &amp; Future Initiatives</h4>
                    <p>Our roadmap toward hybrid and electric vehicle fleet integration across Metro Manila by 2026 to foster zero-emission transport.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
