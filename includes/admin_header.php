<?php
// Admin dashboard layout (top). EVERY admin page includes this file, so every
// admin page checks for an administrator session before showing anything.
require_once __DIR__ . '/functions.php';
require_admin();
$pdo = getDBConnection();
$unread = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE audience='admin' AND is_read=0")->fetchColumn();
$merchant = $pdo->query("SELECT * FROM merchant_profile WHERE id=1")->fetch() ?: ['merchant_name' => 'DriveSphere Mobility'];
$nav = $nav ?? '';
$A = BASE_URL . 'admin/';
?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle ?? 'Admin') ?> - DriveSphere Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>css/portal.css">
</head>
<body class="portal">
<header class="p-nav"><div class="p-wrap">
  <a class="p-brand" href="<?= $A ?>dashboard.php"><img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere"><span><b>DriveSphere</b><small>Admin / Merchant Portal</small></span></a>
  <button class="p-burger" id="burger" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
  <nav class="p-links" id="navLinks">
    <a href="<?= $A ?>dashboard.php" class="<?= $nav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
    <a href="<?= $A ?>manage.php" class="<?= $nav === 'manage' ? 'active' : '' ?>">Manage</a>
    <a href="<?= $A ?>merchant.php" class="<?= $nav === 'merchant' ? 'active' : '' ?>">Merchant</a>
    <a href="<?= $A ?>rentals.php" class="<?= $nav === 'rentals' ? 'active' : '' ?>">Rentals</a>
  </nav>
  <div class="p-acc">
    <button class="p-acc-btn" id="accBtn" type="button"><?= e(admin_user_name()) ?> <span class="av"><?= e(strtoupper(substr(admin_user_name(), 0, 1))) ?></span></button>
    <div class="p-menu" id="accMenu">
      <div class="who"><b><?= e(admin_user_name()) ?></b><span><?= e(admin_user_email()) ?> &middot; Administrator</span></div>
      <a href="<?= $A ?>settings.php"><span><i class="fa-solid fa-gear"></i> Settings</span></a>
      <a href="<?= $A ?>notifications.php"><span><i class="fa-solid fa-bell"></i> Notifications</span><?php if ($unread): ?><span class="dot"><?= $unread ?></span><?php endif; ?></a>
      <a href="<?= $A ?>help.php"><span><i class="fa-solid fa-circle-question"></i> Help &amp; Support</span></a>
      <a class="out" href="<?= BASE_URL ?>admin/logout.php"><span><i class="fa-solid fa-right-from-bracket"></i> Log Out</span></a>
    </div>
  </div>
</div></header>
<main class="p-main"><div class="p-wrap">
<?php flash_show(); ?>
