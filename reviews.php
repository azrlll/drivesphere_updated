<?php require_once __DIR__ . '/includes/functions.php';
$reviews = getDBConnection()->query("SELECT * FROM reviews ORDER BY review_date DESC")->fetchAll(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveSphere - Customer Reviews &amp; Fleet Ratings</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="reviews-main-bg">
        <?php
        $backHelp = is_customer() ? BASE_URL . 'customer/help.php' : (is_admin() ? BASE_URL . 'admin/help.php' : BASE_URL . 'help.php');
        $homeUrl  = is_customer() ? BASE_URL . 'customer/dashboard.php' : (is_admin() ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'index.php');
        ?>
        <div class="contact-subnav">
            <div class="container subnav-container">
                <div class="subnav-left">
                    <a href="<?= $backHelp ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Help</a>
                    <span class="breadcrumb-divider">|</span>
                    <div class="breadcrumbs">
                        <a href="<?= $homeUrl ?>" style="color:inherit;text-decoration:none;">Home</a> / <a href="<?= $backHelp ?>" style="color:inherit;text-decoration:none;">Help</a> / <strong>Customer Reviews</strong>
                    </div>
                </div>
                <div class="subnav-right">
                    <span class="status-indicator"><span class="pulse-dot"></span> Live Verified Ledger • ISO 9001:2015 Audited</span>
                </div>
            </div>
        </div>

        <section class="reviews-hero">
            <div class="container rh-container">
                <div class="rh-content">
                    <div class="badge badge-light-blue"><i class="fa-regular fa-circle-check"></i> VERIFIED RENTER EXPERIENCES</div>
                    <h1 class="reviews-title">Customer Reviews &amp; Fleet Ratings</h1>
                    <p class="reviews-subtitle">Real feedback from corporate executives, business travelers, family vacationers, and VIP delegations who rely on DriveSphere across Metro Manila, Clark, and Cebu.</p>
                </div>
                <div class="rh-stats">
                    <span class="rh-label">NATIONAL TRUST INDEX</span>
                    <strong class="rh-score">99.8% <span>Service SLA Met</span></strong>
                    <span class="rh-locations">NCR • Central Luzon • Central Visayas</span>
                </div>
            </div>
        </section>

        <section class="reviews-aggregate-section">
            <div class="container">
                <div class="aggregate-dashboard">
                    <div class="agg-left">
                        <div class="big-rating">4.92<span>/5.0</span></div>
                        <div class="agg-stars">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                        <p class="agg-based">Based on <strong>1,480+</strong> verified completed rentals</p>
                        <span class="agg-secured"><i class="fa-solid fa-shield-halved"></i> All reviews validated via digital rental contracts</span>
                    </div>

                    <div class="agg-middle">
                        <div class="rating-bar-row"><span>5 Stars</span><div class="r-track"><div class="r-fill" style="width: 84%"></div></div><span>84%</span></div>
                        <div class="rating-bar-row"><span>4 Stars</span><div class="r-track"><div class="r-fill" style="width: 15%"></div></div><span>15%</span></div>
                        <div class="rating-bar-row"><span>3 Stars</span><div class="r-track"><div class="r-fill" style="width: 1%"></div></div><span>1%</span></div>
                        <div class="rating-bar-row"><span>2 Stars</span><div class="r-track"><div class="r-fill" style="width: 0%"></div></div><span>&lt;0.5%</span></div>
                        <div class="rating-bar-row"><span>1 Star</span><div class="r-track"><div class="r-fill" style="width: 0%"></div></div><span>0%</span></div>
                    </div>

                    <div class="agg-right">
                        <div class="agg-metric"><i class="fa-regular fa-clock"></i><div><strong>99.4%</strong><span>On-Time Handover</span></div></div>
                        <div class="agg-metric"><i class="fa-solid fa-spray-can-sparkles"></i><div><strong>100%</strong><span>Sanitized &amp; Inspected</span></div></div>
                        <div class="agg-metric"><i class="fa-solid fa-arrow-rotate-right"></i><div><strong>98.7%</strong><span>Would Rent Again</span></div></div>
                    </div>
                </div>

                </div>
        </section>

        <section class="reviews-grid-section">
            <div class="container">
                <div class="reviews-grid">
                <?php if (!$reviews): ?><p>No reviews yet.</p><?php endif; ?>
                <?php foreach ($reviews as $r):
                    $initials = strtoupper(substr($r['reviewer_name'], 0, 1) . (strpos($r['reviewer_name'], ' ') !== false ? substr(strrchr($r['reviewer_name'], ' '), 1, 1) : ''));
                    $tag = ['corporate' => ['corporate-tag', 'Corporate'], 'self' => ['self-tag', 'Self-Drive'], 'verified' => ['self-tag', 'Verified']][$r['tag_type']];
                ?>
                    <div class="review-card">
                        <div class="rc-header">
                            <div class="rc-user">
                                <div class="avatar avatar-dark"><?= e($initials) ?></div>
                                <div><h4><?= e($r['reviewer_name']) ?></h4><span><?= e($r['reviewer_title']) ?></span></div>
                            </div>
                            <span class="rc-tag <?= $tag[0] ?>">&#9830; <?= $tag[1] ?></span>
                        </div>
                        <div class="rc-rating-date">
                            <div class="rc-stars"><?php for ($i = 0; $i < round($r['rating']); $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?></div>
                            <span><?= date('M j, Y', strtotime($r['review_date'])) ?></span>
                        </div>
                        <p class="rc-text">"<?= e($r['review_text']) ?>"</p>
                        <div class="rc-footer">
                            <span class="rc-loc"><i class="fa-solid fa-map-location-dot"></i> <?= e($r['location_tag']) ?></span>
                            <span class="rc-perk text-blue"><?= e($r['perk_tag']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <div class="reviews-pagination">
                    <div class="rp-info">Showing <strong><?= count($reviews) ?></strong> verified reviews</div>
                </div>
            </div>
        </section>

        <section class="reviews-cta-bar">
            <div class="container">
                <div class="rcta-box">
                    <div class="rcta-text">
                        <div class="rcta-badge"><i class="fa-solid fa-shield-halved"></i> 100% Comprehensive Collision &amp; Liability Insurance Included</div>
                        <h2>Ready to Experience DriveSphere?</h2>
                        <p>Explore our premium fleet or reserve your vehicle in minutes. Enjoy transparent rates and white-glove handover across Luzon and Visayas.</p>
                    </div>
                    <div class="rcta-buttons">
                        <a href="<?= BASE_URL ?>rental.php" class="btn btn-primary-light btn-large">Browse Rental Fleet <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="<?= $backHelp ?>" class="btn btn-outline-white btn-large"><i class="fa-regular fa-circle-left"></i> Back to Help</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer Section -->
    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="js/script.js"></script>
</body>
</html>