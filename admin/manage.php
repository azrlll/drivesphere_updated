<?php
$pageTitle = 'Manage Vehicles'; $nav = 'manage';
require_once __DIR__ . '/../includes/admin_header.php';
$q = trim($_GET['q'] ?? ''); $status = $_GET['status'] ?? 'all';
$sql = "SELECT v.*, c.name AS cat FROM vehicles v JOIN categories c ON v.category_id=c.id WHERE 1=1"; $args = [];
if ($q !== '') { $sql .= " AND CONCAT(v.make,' ',v.model,' ',v.plate_number) LIKE ?"; $args[] = "%$q%"; }
if (in_array($status, ['available', 'rented', 'maintenance'])) { $sql .= " AND v.status = ?"; $args[] = $status; }
$st = $pdo->prepare($sql . " ORDER BY v.id DESC"); $st->execute($args); $list = $st->fetchAll();
$counts = $pdo->query("SELECT status, COUNT(*) n FROM vehicles GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
  <div><h1 class="t">Manage Vehicles</h1><p class="sub">Add, edit, and control the availability of every vehicle in the fleet.</p></div>
  <div class="act"><a class="btn-o" href="<?= $A ?>customers.php"><i class="fa-solid fa-users"></i> Customer records</a><a class="btn-d" href="<?= $A ?>vehicle_form.php"><i class="fa-solid fa-plus"></i> Add Vehicle</a></div>
</div>
<div class="tabs">
  <a class="<?= $status === 'all' ? 'on' : '' ?>" href="<?= $A ?>manage.php">All (<?= array_sum($counts) ?>)</a>
  <a class="<?= $status === 'available' ? 'on' : '' ?>" href="?status=available">Available (<?= $counts['available'] ?? 0 ?>)</a>
  <a class="<?= $status === 'rented' ? 'on' : '' ?>" href="?status=rented">Rented (<?= $counts['rented'] ?? 0 ?>)</a>
  <a class="<?= $status === 'maintenance' ? 'on' : '' ?>" href="?status=maintenance">Maintenance (<?= $counts['maintenance'] ?? 0 ?>)</a>
</div>
<form method="get" class="card" style="display:flex;gap:10px"><input name="q" value="<?= e($q) ?>" placeholder="Search by make, model or plate..." style="flex:1;padding:10px 12px;border:1px solid var(--line);border-radius:10px"><button class="btn-b">Search</button></form>
<div class="vgrid">
<?php foreach ($list as $v): ?>
  <div class="vcard">
    <div class="img" style="background-image:url('<?= e($v['image_url']) ?>')"><span class="badge <?= $v['status'] === 'available' ? 'b-green' : ($v['status'] === 'rented' ? 'b-blue' : 'b-amber') ?>"><?= e(ucfirst($v['status'])) ?></span></div>
    <div class="body">
      <div class="meta"><?= e($v['cat']) ?> &middot; <?= e($v['plate_number']) ?></div>
      <h3><?= e($v['make'] . ' ' . $v['model']) ?></h3>
      <div class="meta"><?= e($v['transmission']) ?> &middot; <?= (int)$v['seats'] ?> seats &middot; <?= number_format($v['odometer']) ?> km</div>
      <div class="price"><?= money($v['daily_rate']) ?> <small>/ day</small></div>
      <div class="act foot">
        <a class="btn-o btn-sm" href="<?= $A ?>vehicle_form.php?id=<?= (int)$v['id'] ?>">Edit</a>
        <?php if ($v['status'] !== 'rented'): ?>
        <form method="post" action="<?= $A ?>action.php"><?= csrf_field() ?><input type="hidden" name="op" value="vehicle_status"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><input type="hidden" name="back" value="admin/manage.php">
          <input type="hidden" name="status" value="<?= $v['status'] === 'maintenance' ? 'available' : 'maintenance' ?>">
          <button class="btn-o btn-sm"><?= $v['status'] === 'maintenance' ? 'Mark available' : 'Send to maintenance' ?></button></form>
        <form method="post" action="<?= $A ?>action.php" data-confirm="Delete this vehicle permanently?"><?= csrf_field() ?><input type="hidden" name="op" value="vehicle_delete"><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><input type="hidden" name="back" value="admin/manage.php"><button class="btn-r btn-sm">Delete</button></form>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
<?php if (!$list): ?><p>No vehicles found.</p><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
