<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Policies &amp; Compliance</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Header Section (matching SCREEN P06) -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="policies-main-bg">
        <!-- Page Header -->
        <section class="policies-page-header">
            <div class="container">
                <div class="breadcrumbs">
                    <a href="<?= BASE_URL ?>index.php">Home</a> <i class="fa-solid fa-chevron-right"></i> <a href="<?= BASE_URL ?>policies.php">Legal &amp; Compliance</a> <i class="fa-solid fa-chevron-right"></i> <strong>Policies</strong>
                </div>
                
                <div class="ph-top-bar">
                    <div class="ph-title-area">
                        <div class="badge badge-light-blue"><i class="fa-solid fa-shield-halved"></i> GOVERNANCE FRAMEWORK 2025</div>
                        <h1>Policies</h1>
                        <p>Official regulatory terms, customer guidelines, operational safety protocols, and governance standards governing DriveSphere vehicle rentals and fleet solutions in the Philippines.</p>
                        
                        <div class="ph-meta-tags">
                            <span class="meta-tag"><i class="fa-regular fa-calendar"></i> Effective Date: January 1, 2025</span>
                            <span class="meta-tag"><i class="fa-solid fa-code-branch"></i> Version 2.4</span>
                            <span class="meta-tag"><i class="fa-solid fa-scale-balanced"></i> Applicable across Philippine Jurisdictions</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content & Sidebar Layout -->
        <section class="policies-layout" style="padding-bottom: 60px;">
            <div class="container layout-grid">
                
                <!-- Sticky Sidebar (matching SCREEN P06) -->
                <aside class="policies-sidebar">
                    <div class="sidebar-box toc-box">
                        <div class="toc-header">
                            <h4>TABLE OF CONTENTS</h4>
                            <i class="fa-solid fa-list"></i>
                        </div>
                        <ul class="toc-menu">
                            <li><a href="#article-1" class="active">1. Legal &amp; Compliance <span>01</span></a></li>
                            <li><a href="#article-2">2. Customer Rental Policies <span>02</span></a></li>
                            <li><a href="#article-3">3. Internal Fleet &amp; Operations <span>03</span></a></li>
                            <li><a href="#article-4">4. Safety &amp; Regulations <span>04</span></a></li>
                        </ul>
                    </div>

                    <div class="sidebar-box enforcement-box">
                        <div class="e-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h4>Audited Enforcement</h4>
                        <p>All policies are enforced under ISO 9001:2015 fleet quality assurance and Philippine Department of Transportation (DOTr) guidelines.</p>
                    </div>
                </aside>

                <!-- Content Area -->
                <div class="policies-content">
                    
                    <!-- Intro Banner -->
                    <div class="policy-intro-banner">
                        <div class="pi-image" style="background-image:url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&q=80&w=800');">
                            <div class="pi-tag">BGC Headquarters</div>
                        </div>
                        <div class="pi-text">
                            <span class="badge-text-gray"><i class="fa-solid fa-file-contract"></i> PHILIPPINE REGULATORY MANDATE</span>
                            <h3>Integrated Fleet Legal Architecture</h3>
                            <p>Every DriveSphere lease, daily rental, and enterprise account is backed by comprehensive statutory coverage, strict telemetry surveillance, and transparent contractual terms across Metro Manila, Luzon, Visayas, and Mindanao.</p>
                        </div>
                    </div>

                    <!-- Article 1 -->
                    <article id="article-1" class="policy-article">
                        <div class="article-header">
                            <span class="badge badge-light-blue">LEGAL &amp; CORPORATE GOVERNANCE</span>
                            <span class="article-num">ARTICLE I</span>
                        </div>
                        <h2>1. Legal &amp; Compliance Policy</h2>
                        
                        <div class="policy-items-list">
                            <div class="p-item">
                                <h4>1.1 Enterprise Registration &amp; Licensing</h4>
                                <p>DriveSphere operates under full registration with the Philippine Securities and Exchange Commission (SEC) and the Land Transportation Franchising and Regulatory Board (LTFRB) for private tourist and commercial fleet leasing. All motorized commercial units hold valid franchise certifications, CPC permits, and comply with DOTr national emissions standards.</p>
                            </div>
                            <div class="p-item">
                                <h4>1.2 Data Privacy Act Compliance (RA 10173)</h4>
                                <p>Customer telemetry data, GPS tracking, and identification credentials are encrypted using AES-256 and processed solely for vehicle security, trip verification, and billing reconciliation. DriveSphere enforces strict non-disclosure policies and will never distribute, sell, or monetize telematics records to unverified third parties.</p>
                                <div class="sub-badge"><i class="fa-solid fa-lock"></i> National Privacy Commission (NPC) Certified Protocols</div>
                            </div>
                            <div class="p-item border-none">
                                <h4>1.3 Tax Compliance &amp; Invoicing</h4>
                                <p>All vehicle hire rates include 12% Value Added Tax (VAT) with Bureau of Internal Revenue (BIR) compliant Official Receipts issued electronically. Enterprise corporate clients may request creditable withholding tax certificates (BIR Form 2307) during digital settlement and monthly invoicing reconciliation.</p>
                            </div>
                        </div>
                    </article>

                    <!-- Article 2 -->
                    <article id="article-2" class="policy-article">
                        <div class="article-header">
                            <span class="badge badge-light-blue">RENTER RIGHTS &amp; OBLIGATIONS</span>
                            <span class="article-num">ARTICLE II</span>
                        </div>
                        <h2>2. Customer-Facing Rental Policies</h2>
                        
                        <div class="policy-grid-2x2">
                            <div class="p-grid-item">
                                <h4><i class="fa-regular fa-id-card"></i> 2.1 Eligibility &amp; Documentation</h4>
                                <p>Primary drivers must be at least 21 years of age and hold an unexpired Philippine or International driver's license with at least 1 year of driving validity. Two valid government IDs and a major credit card or digital collateral are mandatory upon vehicle dispatch.</p>
                            </div>
                            <div class="p-grid-item">
                                <h4><i class="fa-solid fa-gas-pump"></i> 2.2 Fuel, Mileage &amp; Return</h4>
                                <p>Rentals are dispatched with a 100% full tank and must be returned full, or fuel replacement surcharge applies at prevailing pump rates plus a 10% refueling fee. Unlimited mileage is standard unless specified for designated promo rates.</p>
                            </div>
                            <div class="p-grid-item border-none-bottom">
                                <h4><i class="fa-regular fa-calendar-xmark"></i> 2.3 Cancellation &amp; Grace Period</h4>
                                <p>Cancellations made at least 24 hours prior to booking commencement receive a 100% refund. Cancellations under 24 hours are subject to a 1-day rental charge. A strict 30-minute grace period applies to scheduled vehicle returns prior to billing additional hourly intervals.</p>
                            </div>
                            <div class="p-grid-item border-none-bottom border-none-right">
                                <h4><i class="fa-solid fa-receipt"></i> 2.4 Deposit &amp; Toll Settlements</h4>
                                <p>Security deposits are pre-authorized upon vehicle release and returned within 3-5 business days upon vehicle inspection verifying zero damage and settling electronic tollways (RFID / Autosweep / Easytrip) deducted at exact usage receipts.</p>
                            </div>
                        </div>
                    </article>

                    <!-- Article 3 -->
                    <article id="article-3" class="policy-article">
                        <div class="article-header">
                            <span class="badge badge-light-blue">FLEET OPERATIONS &amp; MAINTENANCE</span>
                            <span class="article-num">ARTICLE III</span>
                        </div>
                        <h2>3. Internal Fleet &amp; Operational Policies</h2>
                        
                        <div class="policy-items-list">
                            <div class="p-item">
                                <div class="p-item-header">
                                    <h4>3.1 40-Point Pre-Trip Mechanical Audit</h4>
                                    <span class="status-badge-small"><i class="fa-solid fa-check"></i> Mandatory Check</span>
                                </div>
                                <p>Prior to customer handover, certified fleet technicians must sign off on computerized brake fluid, tire pressure (>5mm tread depth), fluid levels, and diagnostic error codes. Inspection logs are digitized and paired directly to the customer's reservation ticket for transparency.</p>
                            </div>
                            <div class="p-item">
                                <h4>3.2 Vehicle Retirement &amp; Age Caps</h4>
                                <p>Passenger cars and SUVs are strictly retired or transitioned out of primary rental service at 36 months of service or 60,000 km, whichever arrives first. This guarantees every customer drives a modern, fuel-efficient vehicle equipped with cutting-edge active collision safety features.</p>
                            </div>
                            <div class="p-item border-none">
                                <h4>3.3 Maintenance Logging &amp; Telemetry</h4>
                                <p>Automatic telemetry logs oil lifespan, engine temperatures, and emergency impact detection continuously to our central operations center in BGC. Any critical vehicle alert triggers immediate remote dispatch assessment and dispatch of roadside service units.</p>
                            </div>
                        </div>
                    </article>

                    <!-- Article 4 -->
                    <article id="article-4" class="policy-article">
                        <div class="article-header">
                            <span class="badge badge-light-blue">PASSENGER SAFETY &amp; EMERGENCY PROTOCOLS</span>
                            <span class="article-num">ARTICLE IV</span>
                        </div>
                        <h2>4. Safety &amp; Regulatory Compliance Policies</h2>
                        
                        <div class="policy-items-list">
                            <div class="p-item">
                                <h4>4.1 Speed Monitoring &amp; Geofencing</h4>
                                <p>For passenger and asset safety, speeds exceeding 120 km/h on expressways or travel outside approved island boundaries trigger real-time dispatch safety advisory alerts. Renter is notified via SMS/Mobile App to regulate velocity within lawful Philippine speed restrictions.</p>
                            </div>
                            <div class="p-item">
                                <h4>4.2 Comprehensive Insurance &amp; CDW Guidelines</h4>
                                <p>Mandatory basic collision damage coverage applies to all trips. Willful negligence, off-roading with non-4x4 vehicles, unauthorized sub-leasing, or driving under intoxication immediately voids insurance coverage, placing full mechanical liability upon the renter.</p>
                            </div>
                            <div class="p-item border-none">
                                <h4>4.3 Severe Weather &amp; Typhoons Protocol</h4>
                                <p>DriveSphere reserves the right to recall or reschedule vehicle departures during PAGASA Signal No. 3 or higher typhoons to preserve customer safety. In such force majeure instances, booking dates are re-schedulable without penalties, or credited with a 100% voucher balance.</p>
                            </div>
                        </div>
                    </article>

                </div>
            </div>
        </section>
    </main>

    <!-- Footer Section (matching SCREEN P06) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>
