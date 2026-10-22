<?php if (empty($fullWidth)): ?>
</div><!-- /.p-wrap -->
<?php endif; ?>
</main>

<footer class="p-foot">
  <div class="p-wrap">
    <div class="p-foot-grid">
      <!-- Left: Logo & Tagline -->
      <div class="p-foot-brand">
        <a href="<?= BASE_URL ?>customer/dashboard.php" class="p-foot-logo">
          <span class="p-foot-logo-box">
            <img src="<?= BASE_URL ?>images/DriveSphere_logo.jpg" alt="DriveSphere Logo">
          </span>
          <span class="p-foot-logo-text">DriveSphere</span>
        </a>
        <p class="p-foot-desc">Your Premier Philippine Vehicle Rental &amp; Smart Fleet Management Platform</p>
      </div>

      <!-- Links 1: About Us -->
      <div class="p-foot-col">
        <h4>ABOUT US</h4>
        <a href="<?= BASE_URL ?>customer/about.php">Our Story</a>
        <a href="<?= BASE_URL ?>customer/browse.php">Fleet Network</a>
      </div>

      <!-- Links 2: Legal & Compliance -->
      <div class="p-foot-col">
        <h4>LEGAL &amp; COMPLIANCE</h4>
        <a href="<?= BASE_URL ?>customer/policies.php">Privacy Policy</a>
      </div>

      <!-- Links 3: Get Help -->
      <div class="p-foot-col">
        <h4>GET HELP</h4>
        <a href="<?= BASE_URL ?>customer/help.php">FAQs</a>
        <a href="<?= BASE_URL ?>customer/contact.php">Booking Support</a>
      </div>
    </div>

    <!-- Contact Details Bar -->
    <div class="p-foot-contact-bar">
      <div class="p-contact-item">
        <i class="fa-solid fa-phone"></i>
        <span>24/7 Hotline: <strong>(02) 8888-DRIVE</strong></span>
      </div>
      <div class="p-contact-item">
        <i class="fa-regular fa-envelope"></i>
        <span>support@drivesphere.ph</span>
      </div>
      <div class="p-contact-item">
        <i class="fa-solid fa-location-dot"></i>
        <span>BGC, Taguig City, Philippines</span>
      </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="p-foot-bot">
      <span>&copy; 2026 DriveSphere Philippines Inc. All rights reserved.</span>
    </div>
  </div>
</footer>

<?php include __DIR__ . '/filter_modal.php'; ?>

<script src="<?= BASE_URL ?>js/portal.js?v=<?= filemtime(__DIR__ . '/../js/portal.js') ?>"></script>
</body>
</html>
