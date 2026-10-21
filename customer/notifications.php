<?php
$pageTitle = 'Notifications'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('customer/notifications.php');
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE audience='customer' AND user_id = ?")->execute([$me['id']]);
    flash_set('success', 'All notifications marked as read.');
    redirect('customer/notifications.php');
}
$types = ['all' => 'All Alerts', 'booking' => 'Bookings', 'payment' => 'Payments', 'security' => 'Security & Deposit'];
$t = $_GET['type'] ?? 'all';
if (!isset($types[$t])) $t = 'all';
$sql = "SELECT * FROM notifications WHERE audience='customer' AND user_id = ?" . ($t !== 'all' ? " AND type = ?" : "") . " ORDER BY created_at DESC, id DESC";
$st = $pdo->prepare($sql); $st->execute($t !== 'all' ? [$me['id'], $t] : [$me['id']]);
$list = $st->fetchAll();
$icons = ['booking' => 'fa-calendar-check', 'payment' => 'fa-receipt', 'security' => 'fa-shield-halved', 'fleet' => 'fa-car'];
?>
<div class="crumb"><a href="<?= $C ?>dashboard.php">&larr; Back to Dashboard</a></div>
<div class="card" style="max-width:820px;margin:0 auto">
  <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
    <h1 class="t" style="margin:0">Notifications <?php if ($unread): ?><span class="badge b-blue"><?= $unread ?> new</span><?php endif; ?></h1>
    <form method="post"><?= csrf_field() ?><button class="btn-o btn-sm"><i class="fa-solid fa-check-double"></i> Mark all as read</button></form>
  </div>
  <div class="tabs" style="margin-top:14px"><?php foreach ($types as $k => $l): ?><a class="<?= $t === $k ? 'on' : '' ?>" href="?type=<?= $k ?>"><?= e($l) ?></a><?php endforeach; ?></div>
  <?php if (!$list): ?><p class="sub">No notifications here.</p><?php endif; ?>
  <?php foreach ($list as $n): ?>
    <div class="notif <?= $n['is_read'] ? '' : 'unread' ?>">
      <div class="ic"><i class="fa-solid <?= $icons[$n['type']] ?? 'fa-bell' ?>"></i></div>
      <div style="flex:1"><b><?= e($n['title']) ?></b> <small class="sub" style="float:right"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></small>
        <div class="sub" style="margin:2px 0 6px"><?= e($n['message']) ?></div>
        <?php if ($n['link']): ?><a href="<?= BASE_URL . e($n['link']) ?>">View details &rarr;</a><?php endif; ?></div>
    </div>
  <?php endforeach; ?>
  <p class="sub">Need help? <a href="<?= $C ?>help.php">Visit Help</a> or call (02) 8888-DRIVE.</p>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
