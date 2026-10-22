<?php
$pageTitle = 'Notifications';
$nav = '';
require_once __DIR__ . '/../includes/customer_header.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check('customer/notifications.php');
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE audience='customer' AND user_id = ?")->execute([$me['id']]);
    $_SESSION['customer_notifications_read'] = true;
    redirect('customer/notifications.php');
}

$tab = $_GET['type'] ?? 'all';
$isAllRead = !empty($_SESSION['customer_notifications_read']);

// Base notifications definition
$rawNotifications = [
    [
        'id' => 1,
        'type' => 'booking',
        'icon' => 'fa-solid fa-check',
        'active_bg' => '#059669',
        'title' => 'Booking Confirmed — Toyota Fortuner LTD 2.8L',
        'time' => 'Today, 10:45 AM',
        'initially_unread' => true,
        'desc' => 'Your rental reservation for Nov 14 – 17, 2025 has been approved by DriveSphere Fleet Command.',
        'tag_icon' => 'fa-solid fa-ticket',
        'tag_text' => '#DS-BOK-99214',
        'action_label' => 'View Booking Voucher &rarr;',
        'action_link' => $C . 'receipt.php?ref=DS-RN-2026-0001',
        'action_external' => false
    ],
    [
        'id' => 2,
        'type' => 'booking',
        'icon' => 'fa-regular fa-clock',
        'active_bg' => '#0284c7',
        'title' => 'Upcoming Pickup Reminder',
        'time' => 'Yesterday, 3:30 PM',
        'initially_unread' => true,
        'desc' => 'Your vehicle will be ready for pickup tomorrow at 9:00 AM at the BGC Flagship Station.',
        'tag_icon' => 'fa-solid fa-location-dot',
        'tag_text' => 'Station Bay 04 &bull; High Street South',
        'action_label' => 'Get Directions &rarr;',
        'action_link' => 'https://maps.google.com/?q=BGC+High+Street+Taguig',
        'action_external' => true
    ],
    [
        'id' => 3,
        'type' => 'payment',
        'icon' => 'fa-regular fa-credit-card',
        'active_bg' => '#0ea5e9',
        'title' => 'Payment Received — ₱14,112.00',
        'time' => 'Nov 12, 2025',
        'initially_unread' => true,
        'desc' => 'Your payment via GCash has been successfully processed and verified. Receipt #DS-REC-88419 is now available.',
        'tag_icon' => 'fa-solid fa-receipt',
        'tag_text' => 'GCash Ref: 9028-118-2940',
        'action_label' => 'Download Invoice (PDF) &rarr;',
        'action_link' => $C . 'receipt.php?ref=DS-RN-2026-0001',
        'action_external' => false
    ],
    [
        'id' => 4,
        'type' => 'security',
        'icon' => 'fa-solid fa-shield',
        'active_bg' => '#1e293b',
        'title' => 'Refundable Security Deposit Notice',
        'time' => 'Nov 12, 2025',
        'initially_unread' => false,
        'desc' => 'A standard pre-authorization hold of ₱5,000.00 is active and will be released upon vehicle return.',
        'tag_icon' => 'fa-solid fa-lock',
        'tag_text' => 'Automatic Release within 24–48h post-inspection',
        'action_label' => '',
        'action_link' => '',
        'action_external' => false
    ]
];

// Determine final dynamic state based on whether user marked all as read
$notifications = [];
foreach ($rawNotifications as $item) {
    $unread = $isAllRead ? false : $item['initially_unread'];
    $iconBg = $isAllRead ? '#94a3b8' : $item['active_bg'];

    $notifications[] = [
        'id' => $item['id'],
        'type' => $item['type'],
        'icon' => $item['icon'],
        'icon_bg' => $iconBg,
        'title' => $item['title'],
        'time' => $item['time'],
        'unread' => $unread,
        'desc' => $item['desc'],
        'tag_icon' => $item['tag_icon'],
        'tag_text' => $item['tag_text'],
        'action_label' => $item['action_label'],
        'action_link' => $item['action_link'],
        'action_external' => $item['action_external']
    ];
}

// Tab filtering
$filtered = $notifications;
if ($tab === 'booking') {
    $filtered = array_filter($notifications, fn($n) => $n['type'] === 'booking');
} elseif ($tab === 'payment') {
    $filtered = array_filter($notifications, fn($n) => $n['type'] === 'payment');
} elseif ($tab === 'security') {
    $filtered = array_filter($notifications, fn($n) => $n['type'] === 'security');
}
?>

<div class="notif-container">
    <!-- Top Bar Breadcrumb -->
    <div class="settings-top-bar" style="margin-bottom: 18px;">
        <a href="<?= $C ?>dashboard.php" class="settings-back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Main Notifications Card -->
    <div class="notif-main-card">
        <!-- Header Row -->
        <div class="notif-header">
            <div class="notif-header-left">
                <div class="notif-bell-icon">
                    <i class="fa-regular fa-bell"></i>
                </div>
                <h1 class="notif-title">Notifications</h1>
                <?php if ($isAllRead): ?>
                    <span class="notif-count-pill" style="background:#f1f5f9; color:#64748b; border-color:#e2e8f0;">0 New Alerts</span>
                <?php else: ?>
                    <span class="notif-count-pill">3 New Alerts</span>
                <?php endif; ?>
            </div>

            <form method="post" id="markAllReadForm" style="margin: 0;">
                <?= csrf_field() ?>
                <button type="submit" class="btn-mark-all" id="btnMarkAll">
                    <i class="fa-solid fa-check-double"></i> Mark all as read
                </button>
            </form>
        </div>

        <!-- Filter Tabs -->
        <div class="notif-tabs">
            <a href="?type=all" class="notif-tab <?= $tab === 'all' ? 'active' : '' ?>">All Alerts (4)</a>
            <a href="?type=booking" class="notif-tab <?= $tab === 'booking' ? 'active' : '' ?>">Bookings (2)</a>
            <a href="?type=payment" class="notif-tab <?= $tab === 'payment' ? 'active' : '' ?>">Payments (1)</a>
            <a href="?type=security" class="notif-tab <?= $tab === 'security' ? 'active' : '' ?>">Security &amp; Deposit (1)</a>
        </div>

        <!-- Notifications List -->
        <div class="notif-list" id="notifListContainer">
            <?php foreach ($filtered as $n): ?>
                <div class="notif-card-item <?= $n['unread'] ? 'unread' : '' ?>">
                    <!-- Round Icon (Turns grey #94a3b8 when marked as read) -->
                    <div class="notif-item-icon" style="background-color: <?= e($n['icon_bg']) ?>;">
                        <i class="<?= e($n['icon']) ?>"></i>
                    </div>

                    <!-- Item Content -->
                    <div class="notif-item-body">
                        <div class="notif-item-top">
                            <span class="notif-item-title"><?= e($n['title']) ?></span>
                            <span class="notif-item-time">
                                <?= e($n['time']) ?>
                                <?php if ($n['unread']): ?>
                                    <span class="notif-dot-blue"></span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <p class="notif-item-desc"><?= e($n['desc']) ?></p>

                        <div class="notif-item-sub">
                            <span class="notif-tag-pill">
                                <i class="<?= e($n['tag_icon']) ?>"></i> <?= $n['tag_text'] ?>
                            </span>

                            <?php if ($n['action_label'] && $n['action_link']): ?>
                                <a href="<?= e($n['action_link']) ?>" class="notif-action-link" <?= $n['action_external'] ? 'target="_blank" rel="noopener"' : '' ?>>
                                    <?= $n['action_label'] ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom 2 Quick Action Boxes -->
    <div class="notif-bottom-grid">
        <a href="tel:+63288883748" class="notif-info-box">
            <div class="notif-info-icon">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h4 class="notif-info-title">Need Immediate Dispatch?</h4>
                <p class="notif-info-sub">Fleet Command hotline 24/7 active</p>
            </div>
        </a>

        <a href="<?= $C ?>help.php" class="notif-info-box">
            <div class="notif-info-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
                <h4 class="notif-info-title">Pickup Station Status</h4>
                <p class="notif-info-sub">BGC Station open &bull; Bay 04 clear</p>
            </div>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const markForm = document.getElementById('markAllReadForm');
    if (markForm) {
        markForm.addEventListener('submit', function () {
            // Turn all circular icons grey immediately
            document.querySelectorAll('.notif-item-icon').forEach(function (el) {
                el.style.backgroundColor = '#94a3b8';
            });
            // Remove/hide all blue unread dots
            document.querySelectorAll('.notif-dot-blue').forEach(function (dot) {
                dot.style.display = 'none';
            });
            // Remove unread border highlight
            document.querySelectorAll('.notif-card-item').forEach(function (card) {
                card.classList.remove('unread');
            });
            // Update counter pill to 0 New Alerts in muted grey
            const pill = document.querySelector('.notif-count-pill');
            if (pill) {
                pill.textContent = '0 New Alerts';
                pill.style.background = '#f1f5f9';
                pill.style.color = '#64748b';
                pill.style.borderColor = '#e2e8f0';
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
