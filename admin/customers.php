<?php
$pageTitle = 'Customer Records'; $nav = 'manage';
require_once __DIR__ . '/../includes/admin_header.php';
$q = trim($_GET['q'] ?? '');
$sql = "SELECT u.*, COUNT(r.id) AS rentals, COALESCE(SUM(CASE WHEN r.status IN ('reserved','en_route','returned','completed') THEN COALESCE(r.final_total, r.total_price) END),0) AS spent
        FROM users u LEFT JOIN reservations r ON r.user_id = u.id WHERE u.role = 'customer'";
$args = [];
if ($q !== '') { $sql .= " AND CONCAT(u.first_name,' ',u.last_name,' ',u.email) LIKE ?"; $args[] = "%$q%"; }
$st = $pdo->prepare($sql . " GROUP BY u.id ORDER BY u.created_at DESC"); $st->execute($args); $list = $st->fetchAll();
?>
<div class="crumb"><a href="<?= $A ?>manage.php">&larr; Back to Manage Vehicles</a></div>
<h1 class="t">Customer Records</h1><p class="sub"><?= count($list) ?> registered customer(s).</p>
<form method="get" class="card" style="display:flex;gap:10px"><input name="q" value="<?= e($q) ?>" placeholder="Search by name or email..." style="flex:1;padding:10px 12px;border:1px solid var(--line);border-radius:10px"><button class="btn-b">Search</button></form>
<div class="card tbl-wrap"><table class="tbl"><tr><th>Customer</th><th>Email</th><th>Phone</th><th>License</th><th>Rentals</th><th>Total booked</th><th>Joined</th><th></th></tr>
<?php foreach ($list as $u): ?><tr><td><b><?= e($u['first_name'] . ' ' . $u['last_name']) ?></b></td><td><?= e($u['email']) ?></td><td><?= e($u['phone'] ?: '—') ?></td><td><?= e($u['license_number'] ?: '—') ?></td>
<td><?= (int)$u['rentals'] ?></td><td><?= money($u['spent']) ?></td><td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
<td><a class="btn-o btn-sm" href="<?= $A ?>rentals.php?customer=<?= (int)$u['id'] ?>&tab=all">View rentals</a></td></tr><?php endforeach; ?>
<?php if (!$list): ?><tr><td colspan="8">No customers found.</td></tr><?php endif; ?></table></div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
