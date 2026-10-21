<?php
$pageTitle = 'Help'; $nav = 'help';
require_once __DIR__ . '/../includes/customer_header.php';
$faqs = [
 ['How do I reserve a vehicle?', 'Open Browse or Dashboard, choose a vehicle and your dates, then press "Request to Book". Our fleet team reviews it and you get a notification when it is approved.'],
 ['When am I charged?', 'Nothing is charged when you send a request. The total (rental + 12% VAT) is settled after your vehicle is returned. A refundable security deposit of ₱5,000 is held during the rental.'],
 ['Can I cancel a booking?', 'Yes. Open the booking in Rental History and press "Cancel booking" while it is still pending or reserved.'],
 ['Where can I see my receipts?', 'Rental History lists every booking. Each one has a receipt, and returned rentals also show a return record and final charges.'],
 ['What documents do I need?', 'A valid Philippine LTO driver\'s license (at least 21 years old) and a valid ID at pick-up.'],
];
?>
<div class="hero"><h1>How can we help?</h1><p>Answers to common questions, or reach our support team.</p></div>
<div class="split">
  <div><?php foreach ($faqs as $f): ?><div class="card"><h3><?= e($f[0]) ?></h3><p class="sub" style="margin:0"><?= e($f[1]) ?></p></div><?php endforeach; ?></div>
  <div>
    <div class="card dark"><h3>Need immediate help?</h3><p>Our 24/7 roadside and dispatch desk is ready.</p><a class="btn-b btn-full" href="tel:+63288883748"><i class="fa-solid fa-phone"></i> (02) 8888-DRIVE</a></div>
    <div class="card"><h3>Send us a message</h3><p class="sub">Questions about a booking or billing?</p><a class="btn-d btn-full" href="<?= BASE_URL ?>contact.php">Contact support</a></div>
    <div class="card"><h3>Policies</h3><a class="btn-o btn-full" href="<?= BASE_URL ?>policies.php">View rental policies</a></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
