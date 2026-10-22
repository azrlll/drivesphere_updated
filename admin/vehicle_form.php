<?php
$pageTitle = 'Vehicle'; $nav = 'manage';
require_once __DIR__ . '/../includes/admin_header.php';
$id = (int)($_GET['id'] ?? 0);
$v = [
    'category_id' => 1,
    'brand' => '',
    'make' => '',
    'model' => '',
    'year' => 2024,
    'category' => 'Sedan',
    'tag_label' => '',
    'plate_number' => '',
    'daily_rate' => '',
    'weekly_rate' => '',
    'transmission' => 'Automatic',
    'fuel_type' => 'Gasoline',
    'seating_capacity' => 5,
    'seats' => 5,
    'status' => 'available',
    'image_path' => '',
    'image_url' => '',
    'description' => '',
    'odometer' => 0
];

if ($id) {
    $st = $pdo->prepare("SELECT * FROM vehicles WHERE id=?"); $st->execute([$id]);
    $v = $st->fetch();
    if (!$v) { flash_set('error', 'Vehicle not found.'); redirect('admin/manage.php'); }
    if (empty($v['brand'])) $v['brand'] = $v['make'];
    if (empty($v['image_path'])) $v['image_path'] = $v['image_url'];
    if (empty($v['seating_capacity'])) $v['seating_capacity'] = $v['seats'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/manage.php');
    $brand = trim($_POST['brand'] ?? $_POST['make'] ?? '');
    $make = $brand;
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 2024);
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $category = trim($_POST['category'] ?? 'Sedan');
    $tagLabel = trim($_POST['tag_label'] ?? '');
    $plate = strtoupper(trim($_POST['plate_number'] ?? ''));
    $dailyRate = (float)($_POST['daily_rate'] ?? 0);
    $weeklyRate = $_POST['weekly_rate'] !== '' ? (float)$_POST['weekly_rate'] : null;
    $transmission = trim($_POST['transmission'] ?? 'Automatic');
    $fuelType = trim($_POST['fuel_type'] ?? 'Gasoline');
    $seats = (int)($_POST['seats'] ?? 5);
    $seatingCapacity = (int)($_POST['seating_capacity'] ?? $seats);
    $status = in_array($_POST['status'], ['available', 'maintenance', 'rented']) ? $_POST['status'] : 'available';
    $description = trim($_POST['description'] ?? '');
    $odometer = (int)($_POST['odometer'] ?? 0);
    $imagePath = trim($_POST['image_path'] ?? $_POST['image_url'] ?? '');

    // Handle uploaded file if present
    if (!empty($_FILES['vehicle_image']['name']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['vehicle_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($brand . '-' . $model));
            $filename = $slug . '-' . time() . '.' . $ext;
            $dest1 = __DIR__ . '/../assets/images/vehicles/' . $filename;
            $dest2 = __DIR__ . '/../images/vehicles/' . $filename;
            if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $dest1)) {
                @copy($dest1, $dest2);
                $imagePath = 'assets/images/vehicles/' . $filename;
            }
        }
    }
    $imageUrl = $imagePath;

    if ($brand === '' || $model === '' || $plate === '' || $dailyRate <= 0 || $seats < 1 || $imagePath === '') {
        flash_set('error', 'Please fill in brand, model, plate, a daily rate above 0, seats and an image path or upload.');
    } else {
        $dup = $pdo->prepare("SELECT id FROM vehicles WHERE plate_number=? AND id<>?"); $dup->execute([$plate, $id]);
        if ($dup->fetch()) { 
            flash_set('error', 'That plate number already exists.'); 
        } else {
            if ($id) {
                $upd = $pdo->prepare("UPDATE vehicles SET category_id=?, brand=?, make=?, model=?, year=?, category=?, tag_label=?, plate_number=?, daily_rate=?, image_path=?, weekly_rate=?, transmission=?, fuel_type=?, seating_capacity=?, seats=?, status=?, image_url=?, description=?, odometer=? WHERE id=?");
                $upd->execute([$categoryId, $brand, $make, $model, $year, $category, $tagLabel, $plate, $dailyRate, $imagePath, $weeklyRate, $transmission, $fuelType, $seatingCapacity, $seats, $status, $imageUrl, $description, $odometer, $id]);
            } else {
                $ins = $pdo->prepare("INSERT INTO vehicles (category_id, brand, make, model, year, category, tag_label, plate_number, daily_rate, image_path, weekly_rate, transmission, fuel_type, seating_capacity, seats, status, image_url, description, odometer) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $ins->execute([$categoryId, $brand, $make, $model, $year, $category, $tagLabel, $plate, $dailyRate, $imagePath, $weeklyRate, $transmission, $fuelType, $seatingCapacity, $seats, $status, $imageUrl, $description, $odometer]);
            }
            flash_set('success', 'Vehicle saved successfully.'); 
            redirect('admin/manage.php');
        }
    }
    $v = array_merge($v, $_POST);
}
flash_show();
$cats = $pdo->query("SELECT id, name, slug FROM categories ORDER BY id")->fetchAll();
$categoryOptions = ['Sedan', 'Hatchback', 'SUV', 'MPV', 'Van', 'Pickup'];
?>
<div class="crumb"><a href="<?= $A ?>manage.php">&larr; Back to Manage Vehicles</a></div>
<form method="post" enctype="multipart/form-data" class="card" style="max-width:820px"><?= csrf_field() ?>
  <h1 class="t"><?= $id ? 'Edit Vehicle' : 'Add Vehicle' ?></h1>

  <?php if (!empty($v['image_path'])): ?>
    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 16px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
      <img src="<?= e(vehicle_image_url($v['image_path'])) ?>" alt="Vehicle Photo" style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px;">
      <div>
        <strong>Current Vehicle Photo:</strong><br>
        <span style="font-size: 12px; color: #64748b;"><?= e($v['image_path']) ?></span>
      </div>
    </div>
  <?php endif; ?>

  <div class="row2">
    <div class="field"><label>Brand / Manufacturer</label><input name="brand" required value="<?= e($v['brand'] ?: $v['make']) ?>" placeholder="e.g. Toyota, Honda"></div>
    <div class="field"><label>Model</label><input name="model" required value="<?= e($v['model']) ?>" placeholder="e.g. Vios 1.5 G"></div>
    <div class="field"><label>Model Year</label><input type="number" min="2000" max="2030" name="year" required value="<?= (int)($v['year'] ?: 2024) ?>"></div>
    <div class="field"><label>Category Type</label>
      <select name="category" required>
        <?php foreach ($categoryOptions as $co): ?>
          <option value="<?= $co ?>" <?= (strcasecmp($v['category'] ?? '', $co) === 0) ? 'selected' : '' ?>><?= $co ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Category Registry</label>
      <select name="category_id">
        <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $v['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Tag Label (Optional)</label><input name="tag_label" value="<?= e($v['tag_label']) ?>" placeholder="e.g. 4x4 Flagship SUV"></div>
    <div class="field"><label>Philippine Plate Number</label><input name="plate_number" required value="<?= e($v['plate_number']) ?>" placeholder="e.g. NQM 5520"></div>
    <div class="field"><label>Status</label>
      <select name="status">
        <?php foreach (['available', 'maintenance', 'rented'] as $s): ?>
          <option value="<?= $s ?>" <?= $v['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Daily Rate (₱ PHP)</label><input type="number" step="0.01" min="1" name="daily_rate" required value="<?= e($v['daily_rate']) ?>"></div>
    <div class="field"><label>Weekly Rate (₱ PHP, Optional)</label><input type="number" step="0.01" min="0" name="weekly_rate" value="<?= e($v['weekly_rate']) ?>"></div>
    <div class="field"><label>Transmission</label><input name="transmission" required value="<?= e($v['transmission']) ?>" placeholder="Automatic or Manual"></div>
    <div class="field"><label>Fuel Type</label><input name="fuel_type" required value="<?= e($v['fuel_type']) ?>" placeholder="Gasoline, Diesel, or Hybrid"></div>
    <div class="field"><label>Seating Capacity</label><input type="number" min="1" max="30" name="seating_capacity" required value="<?= (int)($v['seating_capacity'] ?: $v['seats']) ?>"></div>
    <div class="field"><label>Odometer (km)</label><input type="number" min="0" name="odometer" value="<?= e($v['odometer']) ?>"></div>
  </div>

  <div class="field" style="margin-top: 8px;">
    <label>Upload Vehicle Image (Local Folder: /assets/images/vehicles/)</label>
    <input type="file" name="vehicle_image" accept="image/jpeg,image/png,image/webp">
  </div>

  <div class="field">
    <label>Or Local Image Path / Filename</label>
    <input name="image_path" required value="<?= e($v['image_path'] ?: $v['image_url']) ?>" placeholder="assets/images/vehicles/toyota-vios.jpg">
  </div>

  <div class="field"><label>Short Description</label><textarea name="description" rows="3"><?= e($v['description']) ?></textarea></div>
  <button class="btn-d">Save Vehicle</button> <a class="btn-o" href="<?= $A ?>manage.php">Cancel</a>
</form>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
