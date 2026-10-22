<?php
// Public "Rent Now / Vehicle Details" fallback endpoint.
// Always direct to customer login page with target vehicle redirect.
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['vehicle'] ?? $_GET['id'] ?? 0);
$params = $_GET;
unset($params['vehicle']);
if ($id > 0) {
    $params['id'] = $id;
}
$cleanParams = [];
foreach ($params as $k => $v) {
    $k = preg_replace('/^amp;/', '', $k);
    $cleanParams[$k] = $v;
}
$qs = http_build_query($cleanParams);
$target = 'customer/vehicle.php' . ($qs !== '' ? '?' . $qs : '');

redirect('login.php?redirect=' . urlencode($target));
