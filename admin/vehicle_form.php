<?php
$pageTitle = 'Vehicle'; $nav = 'manage';
require_once __DIR__ . '/../includes/admin_header.php';
$id = (int)($_GET['id'] ?? 0);
$v = ['category_id' => 1, 'make' => '', 'model' => '', 'tag_label' => '', 'plate_number' => '', 'daily_rate' => '', 'weekly_rate' => '', 'transmission' => 'Automatic', 'fuel_type' => '', 'seats' => 5, 'status' => 'available', 'image_url' => '', 'description' => '', 'odometer' => 0];
if ($id) {
    $st = $pdo->prepare("SELECT * FROM vehicles WHERE id=?"); $st->execute([$id]);
    $v = $st->fetch();
    if (!$v) { flash_set('error', 'Vehicle not found.'); redirect('admin/manage.php'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/manage.php');
    $f = [(int)$_POST['category_id'], trim($_POST['make']), trim($_POST['model']), trim($_POST['tag_label']), strtoupper(trim($_POST['plate_number'])),
          (float)$_POST['daily_rate'], $_POST['weekly_rate'] !== '' ? (float)$_POST['weekly_rate'] : null, trim($_POST['transmission']),
          trim($_POST['fuel_type']), (int)$_POST['seats'], in_array($_POST['status'], ['available', 'maintenance', 'rented']) ? $_POST['status'] : 'available',
          trim($_POST['image_url']), trim($_POST['description']), (int)$_POST['odometer']];
    if ($f[1] === '' || $f[2] === '' || $f[4] === '' || $f[5] <= 0 || $f[9] < 1 || $f[11] === '') {
        flash_set('error', 'Please fill in make, model, plate, a daily rate above 0, seats and an image URL.');
    } else {
        $dup = $pdo->prepare("SELECT id FROM vehicles WHERE plate_number=? AND id<>?"); $dup->execute([$f[4], $id]);
        if ($dup->fetch()) { flash_set('error', 'That plate number already exists.'); }
        else {
            if ($id) { $pdo->prepare("UPDATE vehicles SET category_id=?,make=?,model=?,tag_label=?,plate_number=?,daily_rate=?,weekly_rate=?,transmission=?,fuel_type=?,seats=?,status=?,image_url=?,description=?,odometer=? WHERE id=?")->execute(array_merge($f, [$id])); }
            else     { $pdo->prepare("INSERT INTO vehicles (category_id,make,model,tag_label,plate_number,daily_rate,weekly_rate,transmission,fuel_type,seats,status,image_url,description,odometer) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($f); }
            flash_set('success', 'Vehicle saved.'); redirect('admin/manage.php');
        }
    }
    $v = array_merge($v, $_POST);
}
flash_show();   // show validation errors from this request
$cats = $pdo->query("SELECT id, name FROM categories")->fetchAll();
?>
<div class="crumb"><a href="<?= $A ?>manage.php">&larr; Back to Manage Vehicles</a></div>
<form method="post" class="card" style="max-width:820px"><?= csrf_field() ?>
  <h1 class="t"><?= $id ? 'Edit Vehicle' : 'Add Vehicle' ?></h1>
  <div class="row2">
    <div class="field"><label>Make</label><input name="make" required value="<?= e($v['make']) ?>"></div>
    <div class="field"><label>Model</label><input name="model" required value="<?= e($v['model']) ?>"></div>
    <div class="field"><label>Category</label><select name="category_id"><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $v['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Tag label</label><input name="tag_label" value="<?= e($v['tag_label']) ?>" placeholder="e.g. Executive VIP Van"></div>
    <div class="field"><label>Plate number</label><input name="plate_number" required value="<?= e($v['plate_number']) ?>"></div>
    <div class="field"><label>Status</label><select name="status"><?php foreach (['available', 'maintenance', 'rented'] as $s): ?><option <?= $v['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Daily rate (₱)</label><input type="number" step="0.01" min="1" name="daily_rate" required value="<?= e($v['daily_rate']) ?>"></div>
    <div class="field"><label>Weekly rate (₱, optional)</label><input type="number" step="0.01" min="0" name="weekly_rate" value="<?= e($v['weekly_rate']) ?>"></div>
    <div class="field"><label>Transmission</label><input name="transmission" required value="<?= e($v['transmission']) ?>" placeholder="Automatic (6-Speed)"></div>
    <div class="field"><label>Fuel type</label><input name="fuel_type" required value="<?= e($v['fuel_type']) ?>" placeholder="2.8L Diesel Turbo"></div>
    <div class="field"><label>Seats</label><input type="number" min="1" name="seats" required value="<?= e($v['seats']) ?>"></div>
    <div class="field"><label>Odometer (km)</label><input type="number" min="0" name="odometer" value="<?= e($v['odometer']) ?>"></div>
  </div>
  <div class="field"><label>Image URL</label><input name="image_url" required value="<?= e($v['image_url']) ?>" placeholder="https://..."></div>
  <div class="field"><label>Description</label><textarea name="description" rows="3"><?= e($v['description']) ?></textarea></div>
  <button class="btn-d">Save vehicle</button> <a class="btn-o" href="<?= $A ?>manage.php">Cancel</a>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
