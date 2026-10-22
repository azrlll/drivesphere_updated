<?php if (empty($fullWidth)): ?>
</div><!-- /.p-wrap -->
<?php endif; ?>
</main>

<footer class="p-foot">
  <div class="p-wrap">
    <div class="p-foot-grid">
      <!-- Left: Logo & Tagline -->
      <div class="p-foot-brand">
        <a href="<?= BASE_URL ?>admin/dashboard.php" class="p-foot-logo">
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
        <a href="<?= BASE_URL ?>about.php">Our Story</a>
        <a href="<?= BASE_URL ?>admin/manage.php">Fleet Network</a>
      </div>

      <!-- Links 2: Legal & Compliance -->
      <div class="p-foot-col">
        <h4>LEGAL &amp; COMPLIANCE</h4>
        <a href="<?= BASE_URL ?>policies.php">Privacy Policy</a>
      </div>

      <!-- Links 3: Get Help -->
      <div class="p-foot-col">
        <h4>GET HELP</h4>
        <a href="<?= BASE_URL ?>admin/help.php">FAQs &amp; Guide</a>
        <a href="<?= BASE_URL ?>admin/notifications.php">Support Inquiries</a>
      </div>
    </div>

    <!-- Contact Details Bar -->
    <div class="p-foot-contact-bar">
      <div class="p-contact-item">
        <i class="fa-solid fa-phone"></i>
        <span>24/7 Hotline: <a href="tel:+63288883748" style="color:inherit;text-decoration:none;"><strong>(02) 8888-DRIVE</strong></a></span>
      </div>
      <div class="p-contact-item">
        <i class="fa-regular fa-envelope"></i>
        <span><a href="mailto:support@drivesphere.ph" style="color:inherit;text-decoration:none;">support@drivesphere.ph</a></span>
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

<script src="<?= BASE_URL ?>js/portal.js?v=<?= filemtime(__DIR__ . '/../js/portal.js') ?>"></script>
</body>
</html>
