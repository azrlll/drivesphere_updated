<?php
// Customer dashboard layout (top). Set $pageTitle and $nav before including.
require_once __DIR__ . '/functions.php';
require_customer();
$pdo = getDBConnection();
$customerId = customer_user_id();
$me = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$me->execute([$customerId]);
$me = $me->fetch();
if (!$me) { logout_customer(); redirect('login.php'); }
$unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE audience='customer' AND user_id=? AND is_read=0");
$unread->execute([$me['id']]);
$unread = (int)$unread->fetchColumn();
$nav = $nav ?? '';
$C = BASE_URL . 'customer/';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Customer') ?> - DriveSphere</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>css/portal.css">
</head>
<body class="portal">
<header class="p-nav">
  <div class="p-wrap">
    <a class="p-brand" href="<?= $C ?>dashboard.php">
      <span class="p-brand-logo">
        <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
      </span>
      <span class="p-brand-text">
        <b>DriveSphere</b>
        <small>CUSTOMER RENTAL</small>
      </span>
    </a>

    <button class="p-burger" id="burger" aria-label="Menu" type="button"><i class="fa-solid fa-bars"></i></button>

    <nav class="p-links" id="navLinks">
      <a href="<?= $C ?>dashboard.php" class="<?= $nav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= $C ?>browse.php" class="<?= $nav === 'browse' ? 'active' : '' ?>">Browse</a>
      <a href="<?= $C ?>about.php" class="<?= $nav === 'about' ? 'active' : '' ?>">About</a>
      <a href="<?= $C ?>help.php" class="<?= $nav === 'help' ? 'active' : '' ?>">Help</a>
    </nav>

    <div class="p-acc">
      <button class="p-acc-btn" id="accBtn" type="button" aria-expanded="false" aria-label="Customer Account Menu">
        <span>Account</span>
        <span class="av">
          <svg viewBox="0 0 32 32" fill="none" style="width:100%;height:100%;display:block;">
            <circle cx="16" cy="16" r="16" fill="#0284c7"/>
            <circle cx="16" cy="12" r="5.5" fill="#ffffff"/>
            <path d="M6 28c0-5.523 4.477-10 10-10s10 4.477 10 10" fill="#ffffff"/>
          </svg>
        </span>
        <i class="fa-solid fa-chevron-down p-chevron" id="accChevron"></i>
      </button>

      <div class="p-menu" id="accMenu">
        <div class="who">
          <div class="who-avatar">
            <svg viewBox="0 0 40 40" fill="none" style="width:100%;height:100%;display:block;border-radius:50%;">
              <circle cx="20" cy="20" r="20" fill="#e0f2fe"/>
              <circle cx="20" cy="15" r="7" fill="#0284c7"/>
              <path d="M7 36c0-7.18 5.82-13 13-13s13 5.82 13 13" fill="#0284c7"/>
            </svg>
          </div>
          <div class="who-info">
            <b><?= e($me['first_name'] . ' ' . $me['last_name']) ?></b>
            <span><?= e($me['email']) ?></span>
          </div>
        </div>

        <div class="p-menu-list">
          <a href="<?= $C ?>settings.php" class="p-item">
            <span class="p-item-left"><i class="fa-solid fa-gear"></i> Settings</span>
          </a>
          <a href="<?= $C ?>notifications.php" class="p-item">
            <span class="p-item-left"><i class="fa-regular fa-bell"></i> Notifications</span>
            <span class="p-toggle-pill <?= $unread ? 'has-badge' : '' ?>">
              <span class="p-toggle-knob"></span>
            </span>
          </a>
          <a href="<?= $C ?>history.php" class="p-item">
            <span class="p-item-left"><i class="fa-solid fa-clock-rotate-left"></i> My Rental History</span>
          </a>
        </div>

        <div class="p-menu-divider"></div>

        <a class="out p-item" href="<?= BASE_URL ?>actions/logout.php">
          <span class="p-item-left"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</span>
        </a>
      </div>
    </div>
  </div>
</header>
<main class="p-main">
<div class="p-wrap">
<?php flash_show(); ?>
