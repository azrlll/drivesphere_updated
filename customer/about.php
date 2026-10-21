<?php
$pageTitle = 'About'; $nav = 'about';
require_once __DIR__ . '/../includes/customer_header.php';
?>
<div class="hero"><h1>About DriveSphere</h1><p>The Philippines' premier vehicle rental and smart fleet management platform.</p></div>
<div class="grid3">
  <div class="card"><h3><i class="fa-solid fa-shield-halved"></i> Safe &amp; insured</h3><p class="sub">Every vehicle is inspected, sanitized and covered by comprehensive insurance before it reaches you.</p></div>
  <div class="card"><h3><i class="fa-solid fa-location-dot"></i> Island-wide hubs</h3><p class="sub">Pick up at BGC, Makati, Ortigas, NAIA and Clark. Return to the hub that suits your trip.</p></div>
  <div class="card"><h3><i class="fa-solid fa-headset"></i> 24/7 support</h3><p class="sub">Roadside assistance and booking support are one call away at (02) 8888-DRIVE.</p></div>
</div>
<div class="card"><h2>Our story</h2><p>DriveSphere started as a small fleet in Taguig and grew into a platform where customers reserve vehicles online and fleet managers track every rental from request to return. Our team of fleet managers, inspectors and support agents keeps each trip simple and transparent.</p>
<a class="btn-d" href="<?= $C ?>browse.php">Browse the fleet</a> <a class="btn-o" href="<?= $C ?>help.php">Get help</a></div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
