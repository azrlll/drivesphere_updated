<?php
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Help &amp; Support</title>
    <link rel="stylesheet" href="css/global.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="help-page-body">

    <!-- Header Section (matching SCREEN P05) -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="help-main-bg">
        <!-- Help Hero Section -->
        <section class="help-hero">
            <div class="container text-center">
                <h1 class="help-title">How Can We Help Your Journey Today?</h1>
                <p class="help-subtitle">Find quick answers to rental policies, airport handovers, insurance coverage, and corporate booking solutions. Our operations dispatch team is on standby 24/7.</p>
                
                <div class="search-box-wrapper">
                    <form class="help-search-form" id="helpSearchForm" onsubmit="event.preventDefault(); filterFaqs(document.getElementById('faqSearchInput').value);">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="faqSearchInput" placeholder="Search questions, keywords (e.g., security deposit, airport pickup, chauffeur, cancellation...">
                        <button type="submit" class="btn btn-dark">Search FAQs <i class="fa-solid fa-arrow-right"></i></button>
                    </form>
                    <div class="popular-tags">
                        <span class="pop-label">POPULAR:</span>
                        <a href="policies.php" class="pop-tag">Security Deposit Policy</a>
                        <a href="rental.php" class="pop-tag">NAIA Airport Delivery</a>
                        <a href="policies.php" class="pop-tag">Driver License Requirements</a>
                        <a href="policies.php" class="pop-tag">Accident Protocol</a>
                        <a href="contact.php" class="pop-tag">Corporate Invoicing</a>
                    </div>
                </div>

                <!-- Info Stats Grid (3 cards matching SCREEN P05) -->
                <div class="help-stats-grid">
                    <div class="h-stat-card">
                        <div class="h-icon"><i class="fa-solid fa-bolt"></i></div>
                        <div class="h-stat-info">
                            <span>Live Dispatch Response</span>
                            <strong>&lt; 1.8 mins <span class="highlight-blue">avg wait</span></strong>
                        </div>
                    </div>
                    <div class="h-stat-card">
                        <div class="h-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="h-stat-info">
                            <span>Fleet Safety Verification</span>
                            <strong>100% Insured <span class="highlight-green">CTPL &amp; CDW</span></strong>
                        </div>
                    </div>
                    <div class="h-stat-card">
                        <div class="h-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="h-stat-info">
                            <span>Service Coverage Hubs</span>
                            <strong>18 Terminals <span class="highlight-gray">Luzon, Visayas, Mindanao</span></strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section (matching SCREEN P05) -->
        <section class="faq-section">
            <div class="container">
                <!-- FAQ Categories -->
                <div class="faq-categories">
                    <button class="faq-pill active" type="button" data-category="all">All Categories</button>
                    <button class="faq-pill" type="button" data-category="booking">Booking &amp; Reservations</button>
                    <button class="faq-pill" type="button" data-category="pickup">Vehicle Pick-up &amp; Return</button>
                    <button class="faq-pill" type="button" data-category="insurance">Insurance &amp; Damage Protection</button>
                    <button class="faq-pill" type="button" data-category="billing">Billing &amp; Payment</button>
                    <button class="faq-pill" type="button" data-category="chauffeur">Chauffeur Services</button>
                </div>

                <!-- Accordion Wrapper -->
                <div class="faq-accordion-wrapper">
                    <!-- Q1 (Active by default per SCREEN P05) -->
                    <div class="accordion-item active" data-category="booking">
                        <div class="accordion-header" onclick="toggleFaq(this)">
                            <div class="acc-title">
                                <span class="q-num">01</span>
                                <h3>What are the driver license and identification requirements to rent a vehicle?</h3>
                            </div>
                            <button class="toggle-icon" type="button" aria-label="Toggle answer"><i class="fa-solid fa-minus"></i></button>
                        </div>
                        <div class="accordion-body">
                            <div class="acc-content-split">
                                <div class="acc-text">
                                    <p>Renters must present a valid Philippine Professional or Non-Professional Driver's License (held for at least 1 year) or a valid Foreign Driver's License alongside a passport and international driving permit. Two government-issued IDs are required for primary account verification.</p>
                                </div>
                                <div class="acc-side-box">
                                    <i class="fa-solid fa-id-card"></i>
                                    <div>
                                        <strong>Accepted Primary IDs:</strong> Passport, UMID, Driver's License, SSS card, or PRC License.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="accordion-item" data-category="billing">
                        <div class="accordion-header" onclick="toggleFaq(this)">
                            <div class="acc-title">
                                <span class="q-num">02</span>
                                <h3>How does the security deposit work, and when is it refunded?</h3>
                            </div>
                            <button class="toggle-icon" type="button" aria-label="Toggle answer"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="accordion-body">
                            <div class="acc-content-split">
                                <div class="acc-text">
                                    <p>Security deposits are held temporarily on your credit card and vary based on the vehicle category (₱5,000 for Sedans, ₱8,000 for SUVs, ₱12,000 for VIP Vans). They are automatically released within 3-5 business days upon a clean vehicle return inspection, assuming zero toll violations or damage.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="accordion-item" data-category="pickup">
                        <div class="accordion-header" onclick="toggleFaq(this)">
                            <div class="acc-title">
                                <span class="q-num">03</span>
                                <h3>Can I arrange for airport pick-up and drop-off at NAIA, Clark, or Cebu Mactan?</h3>
                            </div>
                            <button class="toggle-icon" type="button" aria-label="Toggle answer"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="accordion-body">
                            <div class="acc-content-split">
                                <div class="acc-text">
                                    <p>Yes, DriveSphere coordinates white-glove curbside handover at NAIA Terminals 1, 2, 3, Clark International, and Mactan-Cebu. Simply input your flight number during reservation for synchronized terminal bay dispatch.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="accordion-item" data-category="insurance">
                        <div class="accordion-header" onclick="toggleFaq(this)">
                            <div class="acc-title">
                                <span class="q-num">04</span>
                                <h3>What collision damage coverage and insurance options are included in standard bookings?</h3>
                            </div>
                            <button class="toggle-icon" type="button" aria-label="Toggle answer"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="accordion-body">
                            <div class="acc-content-split">
                                <div class="acc-text">
                                    <p>Every booking includes standard Compulsory Third-Party Liability (CTPL) and Collision Damage Waiver (CDW) with standard excess caps. You can upgrade to our Premium ₱0 Deductible Protection during checkout for complete indemnity.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="accordion-item" data-category="insurance">
                        <div class="accordion-header" onclick="toggleFaq(this)">
                            <div class="acc-title">
                                <span class="q-num">05</span>
                                <h3>What should I do in the unlikely event of an accident or mechanical breakdown?</h3>
                            </div>
                            <button class="toggle-icon" type="button" aria-label="Toggle answer"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="accordion-body">
                            <div class="acc-content-split">
                                <div class="acc-text">
                                    <p>Prioritize your safety and call our 24/7 emergency dispatch hotline immediately at (02) 8888-DRIVE. Our roadside response unit will coordinate traffic management, localized mechanics, and dispatch an instant replacement vehicle within 60 minutes in Metro Manila.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom Action Cards (3 cards matching SCREEN P05) -->
        <section class="help-action-cards" style="padding-bottom: 80px;">
            <div class="container action-grid">
                <div class="action-card">
                    <div class="ac-icon"><i class="fa-solid fa-book-open"></i></div>
                    <h3>Browse Knowledge Base &amp; FAQs</h3>
                    <p>Read through our complete collection of user guides, driving manuals, and step-by-step vehicle booking documentation.</p>
                    <a href="<?= BASE_URL ?>policies.php" class="btn-card-action">Open Knowledge Base <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="action-card">
                    <div class="ac-icon"><i class="fa-solid fa-headset"></i></div>
                    <h3>Contact Direct Support</h3>
                    <p>Connect with our 24/7 reservation desk via live chat, call our hotline (02) 8888-DRIVE, or send an urgent ticket to support@drivesphere.ph.</p>
                    <a href="<?= BASE_URL ?>contact.php" class="btn-card-action">Contact Support <i class="fa-regular fa-message"></i></a>
                </div>
                <div class="action-card">
                    <div class="ac-icon"><i class="fa-solid fa-file-invoice"></i></div>
                    <h3>Corporate &amp; Billing Support</h3>
                    <p>Assistance with enterprise agreements, BIR 2307 tax documentation, corporate invoicing, and long-term fleet bookings.</p>
                    <a href="<?= BASE_URL ?>contact.php" class="btn-card-action">Contact Billing <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Section (matching SCREEN P05) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
    <script>
    function toggleFaq(header) {
        const item = header.closest('.accordion-item');
        const icon = item.querySelector('.toggle-icon i');
        const isActive = item.classList.contains('active');
        
        // Close other items or toggle this
        item.classList.toggle('active');
        if (icon) {
            if (isActive) {
                icon.className = 'fa-solid fa-plus';
            } else {
                icon.className = 'fa-solid fa-minus';
            }
        }
    }

    // Category pills filter
    document.querySelectorAll('.faq-pill').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.faq-pill').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const cat = this.getAttribute('data-category');
            
            document.querySelectorAll('.accordion-item').forEach(item => {
                if (cat === 'all' || item.getAttribute('data-category') === cat) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Search filter
    function filterFaqs(query) {
        query = query.toLowerCase().trim();
        document.querySelectorAll('.accordion-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            if (!query || text.includes(query)) {
                item.style.display = 'block';
                if (query) {
                    item.classList.add('active');
                    const icon = item.querySelector('.toggle-icon i');
                    if (icon) icon.className = 'fa-solid fa-minus';
                }
            } else {
                item.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
