<?php
$pageTitle = 'Notifications'; $nav = '';
require_once __DIR__ . '/../includes/admin_header.php';
$list = $pdo->query("SELECT * FROM notifications WHERE audience='admin' ORDER BY created_at DESC, id DESC LIMIT 50")->fetchAll();
$inq  = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap"><h1 class="t" style="margin:0">System &amp; Operational Notifications</h1>
  <form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="mark_read"><input type="hidden" name="back" value="admin/notifications.php"><button class="btn-o"><i class="fa-solid fa-check-double"></i> Mark all as read</button></form></div>
<div style="margin-top:16px">
<?php foreach ($list as $n): ?><div class="notif <?= $n['is_read'] ? '' : 'unread' ?>"><div class="ic"><i class="fa-solid fa-bell"></i></div>
  <div style="flex:1"><b><?= e($n['title']) ?></b> <small class="sub" style="float:right"><?= date('M j, g:i A', strtotime($n['created_at'])) ?></small><div class="sub"><?= e($n['message']) ?></div>
  <?php if ($n['link']): ?><a href="<?= BASE_URL . e($n['link']) ?>">Open &rarr;</a><?php endif; ?></div></div><?php endforeach; ?>
<?php if (!$list): ?><p class="sub">No notifications.</p><?php endif; ?></div>
<div class="card"><h2>Customer inquiries (from the Contact page)</h2><div class="tbl-wrap"><table class="tbl"><tr><th>Date</th><th>Name</th><th>Email</th><th>Topic</th><th>Message</th></tr>
<?php foreach ($inq as $i): ?><tr><td><?= date('M j, g:i A', strtotime($i['created_at'])) ?></td><td><?= e($i['full_name']) ?></td><td><?= e($i['email']) ?></td><td><?= e($i['category']) ?></td><td><?= e($i['message']) ?></td></tr><?php endforeach; ?>
<?php if (!$inq): ?><tr><td colspan="5">No inquiries yet.</td></tr><?php endif; ?></table></div></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
