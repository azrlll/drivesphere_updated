<?php
$pageTitle = 'Help & Support'; $nav = '';
require_once __DIR__ . '/../includes/admin_header.php';
?>
<h1 class="t">Help &amp; Support</h1><p class="sub">Quick guide for fleet administrators.</p>
<div class="grid2">
  <div class="card"><h3>Rental workflow</h3><ol style="padding-left:18px;margin:0"><li><b>Requests</b>: approve or reject new customer requests.</li><li><b>Rentals</b>: dispatch a reserved vehicle (it becomes En Route).</li><li><b>Record Return</b>: enter odometer, fuel and notes.</li><li><b>Damage &amp; Final Charges</b>: add repair charges or discounts, then confirm settlement.</li></ol></div>
  <div class="card"><h3>Fleet tips</h3><p>Use <a href="<?= $A ?>manage.php">Manage</a> to add vehicles, edit rates, or send a vehicle to maintenance so customers cannot book it. A vehicle with rental records cannot be deleted.</p><p>Technical support: <b>support@drivesphere.ph</b> &middot; (02) 8888-DRIVE</p></div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
