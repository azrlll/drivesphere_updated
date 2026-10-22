<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, brand, model, image_path, image_url FROM vehicles WHERE id <= 6");
while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $r['id'] . ': ' . $r['brand'] . ' ' . $r['model'] . ' -> path=' . $r['image_path'] . ' url=' . $r['image_url'] . "\n";
}
