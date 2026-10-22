<?php
require_once __DIR__ . '/../includes/functions.php';
$pdo = getDBConnection();
$fleet = (int)$pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$rented = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='rented'")->fetchColumn();
$maint = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='maintenance'")->fetchColumn();
$avail = $fleet - $rented - $maint;
$pending = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn();
$overdue = $pdo->query("SELECT r.*, v.make, v.model FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id WHERE r.status='en_route' AND r.return_date < NOW()")->fetchAll();
$toSettle = $pdo->query("SELECT r.*, v.make, v.model FROM reservations r JOIN vehicles v ON r.vehicle_id=v.id WHERE r.status='returned'")->fetchAll();
$vehicles = $pdo->query("SELECT id, make, model, image_url, daily_rate FROM vehicles LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

echo "Fleet: $fleet, Rented: $rented, Maint: $maint, Avail: $avail, Pending: $pending\n";
echo "Overdue: " . count($overdue) . ", Settle: " . count($toSettle) . "\n";
echo json_encode($vehicles, JSON_PRETTY_PRINT);
