<?php
// Public website navbar (shared by every public page).
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$navLinks = [
    'index.php'  => 'Home',
    'rental.php' => 'Rental',
    'fleet.php'  => 'Fleet',
    'about.php'  => 'About',
    'help.php'   => 'Help'
];

// Determine the active link so the user always knows which page/section they are on
$activePage = $currentPage;
if ($currentPage === 'book.php') {
    $activePage = 'rental.php';
} elseif (in_array($currentPage, ['policies.php', 'contact.php'])) {
    $activePage = 'help.php';
}
?>
<header class="main-header">
<div class="container header-container">
    <a href="<?= BASE_URL ?>index.php" class="logo">
        <span class="logo-box">
            <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
        </span>
        <span class="logo-text">DriveSphere</span>
    </a>

    <nav class="main-nav">
        <?php foreach ($navLinks as $file => $label): ?>
            <a href="<?= BASE_URL . $file ?>" class="<?= $activePage === $file ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="header-actions">
        <!-- Account Avatar Capsule & Dropdown Modal (matches Public_Dropdown.png and Figma Mockup) -->
        <div class="account-wrapper">
            <button id="accountTrigger" class="user-avatar-capsule" type="button" aria-expanded="false" aria-label="User Account Menu">
                <span class="capsule-avatar-circle">
                    <svg viewBox="0 0 32 32" fill="none" class="avatar-svg" style="width:100%;height:100%;display:block;">
                        <circle cx="16" cy="16" r="16" fill="#cbd5e1"/>
                        <circle cx="16" cy="12" r="5.5" fill="#8a99ad"/>
                        <path d="M6 28c0-5.523 4.477-10 10-10s10 4.477 10 10" fill="#8a99ad"/>
                    </svg>
                </span>
                <i class="fa-solid fa-chevron-down chevron-icon"></i>
            </button>

            <div id="accountDropdown" class="account-dropdown">
                <div class="dropdown-brand-header">
                    <span class="brand-logo-box">
                        <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
                    </span>
                    <span class="brand-name">DriveSphere Mobility</span>
                </div>

                <div class="user-profile-summary">
                    <div class="avatar-wrapper">
                        <div class="user-large-avatar-ring">
                            <svg viewBox="0 0 64 64" fill="none" class="avatar-svg-large" style="width:100%;height:100%;display:block;border-radius:50%;">
                                <circle cx="32" cy="32" r="32" fill="#cbd5e1"/>
                                <circle cx="32" cy="24" r="11" fill="#8a99ad"/>
                                <path d="M12 56c0-11.046 8.954-20 20-20s20 8.954 20 20" fill="#8a99ad"/>
                            </svg>
                        </div>
                        <span class="status-dot-online"></span>
                    </div>
                    <h3 class="user-display-name">User Preview</h3>
                </div>

                <div class="menu-section-label">ACCOUNT</div>
                <ul class="dropdown-menu-list">
                    <li><a href="<?= BASE_URL ?>login.php?redirect=customer/settings.php" class="dropdown-item">
                        <div class="item-left"><i class="fa-solid fa-gear item-icon"></i><span class="item-text">Settings</span></div>
                        <i class="fa-solid fa-chevron-right item-arrow"></i></a></li>
                    <li><a href="<?= BASE_URL ?>login.php?redirect=customer/notifications.php" class="dropdown-item">
                        <div class="item-left"><i class="fa-regular fa-bell item-icon"></i><span class="item-text">Notifications</span></div>
                        <i class="fa-solid fa-chevron-right item-arrow"></i></a></li>
                    <li><a href="<?= BASE_URL ?>help.php" class="dropdown-item">
                        <div class="item-left"><i class="fa-regular fa-circle-question item-icon"></i><span class="item-text">Help &amp; Support</span></div>
                        <i class="fa-solid fa-chevron-right item-arrow"></i></a></li>
                </ul>

                <div class="dropdown-logout-wrapper">
                    <a href="<?= BASE_URL ?>login.php" class="logout-link"><i class="fa-solid fa-arrow-right-to-bracket"></i><span>Log In</span></a>
                </div>
            </div>
        </div>
    </div>
</div>
</header>
