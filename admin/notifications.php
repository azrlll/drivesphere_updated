<?php
$pageTitle = 'Notifications'; $nav = '';
require_once __DIR__ . '/../includes/admin_header.php';

$allNotifs = $pdo->query("SELECT * FROM notifications WHERE audience='admin' ORDER BY created_at DESC, id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
$inq       = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

// Count unread
$unreadCount = 0;
foreach ($allNotifs as $n) {
    if (!$n['is_read']) $unreadCount++;
}

// Split into Needs Attention (unread / action required) and Activity Feed (all timeline events)
$needsAttention = [];
$activityFeed = [];

foreach ($allNotifs as $n) {
    if (!$n['is_read']) {
        $needsAttention[] = $n;
    }
    $activityFeed[] = $n;
}

// Helper to determine icon, badges, and action labels cleanly
function get_notif_meta($n) {
    $t = strtolower($n['title'] . ' ' . $n['message']);
    $type = $n['type'] ?: 'system';
    
    $icon = 'fa-bell';
    $urgency = 'Urgent';
    $category = 'System';
    $ctaText = 'Open &rarr;';
    $secondaryText = 'Dismiss';
    $tag1 = 'Operations';
    $tag2 = 'Fleet Command';

    if (str_contains($t, 'maintenance') || str_contains($t, 'shop') || str_contains($t, 'brake') || str_contains($t, 'oil')) {
        $icon = 'fa-wrench';
        $urgency = 'Urgent';
        $category = 'Fleet Ops';
        $ctaText = 'Schedule Shop &rarr;';
        $secondaryText = 'Quick View';
        $tag1 = 'Bay Rapid Service';
        $tag2 = 'Turnaround Priority';
    } elseif (str_contains($t, 'authorization') || str_contains($t, 'booking') || str_contains($t, 'requested')) {
        $icon = 'fa-file-circle-check';
        $urgency = 'Pending';
        $category = 'Booking';
        $ctaText = 'Approve Now &rarr;';
        $secondaryText = 'Details';
        $tag1 = 'Instant Booking';
        $tag2 = 'Driver Verified';
    } elseif (str_contains($t, 'damage') || str_contains($t, 'claim') || str_contains($t, 'settlement')) {
        $icon = 'fa-shield-halved';
        $urgency = 'Claim';
        $category = 'Damage Audit';
        $ctaText = 'Review Claim &rarr;';
        $secondaryText = 'Audit Log';
        $tag1 = 'Turn-in Inspection';
        $tag2 = 'Deposit Escrow';
    } elseif (str_contains($t, 'kyc') || str_contains($t, 'identity') || str_contains($t, 'license')) {
        $icon = 'fa-id-card';
        $urgency = 'KYC';
        $category = 'Compliance';
        $ctaText = 'Validate KYC &rarr;';
        $secondaryText = 'Profile';
        $tag1 = 'Government ID';
        $tag2 = 'LTO Verification';
    } elseif (str_contains($t, 'inquiry') || str_contains($t, 'contact') || str_contains($t, 'message')) {
        $icon = 'fa-envelope-open-text';
        $urgency = 'Inquiry';
        $category = 'Customer Care';
        $ctaText = 'View Message &rarr;';
        $secondaryText = 'Archive';
        $tag1 = 'Public Portal';
        $tag2 = 'Direct Message';
    }

    // Extract reference if present
    if (preg_match('/(DS-[A-Z0-9-]+)/', $n['title'] . ' ' . $n['message'], $m)) {
        $tag1 = 'Ref #' . $m[1];
    }

    return [
        'icon' => $icon,
        'urgency' => $urgency,
        'category' => $category,
        'ctaText' => $ctaText,
        'secondaryText' => $secondaryText,
        'tag1' => $tag1,
        'tag2' => $tag2,
    ];
}
?>

<div class="adm-notif-wrap">
  <!-- Top Header Row -->
  <div class="adm-notif-header-row">
    <h1 class="adm-notif-main-title">System &amp; Operational Notifications</h1>
    <div class="adm-notif-header-tools">
      <button type="button" class="adm-btn-refresh-circle" id="btnRefreshPage" title="Refresh notification feed" aria-label="Refresh feed">
        <i class="fa-solid fa-rotate-right"></i>
      </button>

      <form method="post" action="<?= $A ?>action.php" id="formMarkAllRead" style="margin:0">
        <?= csrf_field() ?>
        <input type="hidden" name="op" value="mark_read">
        <input type="hidden" name="back" value="admin/notifications.php">
        <button type="submit" class="adm-btn-mark-all-pill" id="btnMarkAllRead" title="Mark all notifications as read">
          <i class="fa-solid fa-check-double" style="color:#0284c7"></i>
          <span>Mark All As Read</span>
        </button>
      </form>
    </div>
  </div>

  <!-- Search and Filter Bar -->
  <div class="adm-notif-filter-bar">
    <div class="adm-notif-search-container">
      <i class="fa-solid fa-magnifying-glass adm-notif-search-icon"></i>
      <input type="text" id="notifSearchInput" class="adm-notif-search-input" placeholder="Search notifications by keyword, customer name, plate or booking ref..." autocomplete="off">
    </div>

    <div class="adm-notif-pills-group" id="notifFilterPills">
      <button type="button" class="adm-notif-pill-btn active" data-filter="all">All</button>
      <button type="button" class="adm-notif-pill-btn" data-filter="unread">
        <span class="adm-dot-indicator-red"></span>
        <span id="pillUnreadLabel">Unread (<?= $unreadCount ?>)</span>
      </button>
      <button type="button" class="adm-notif-pill-btn" data-filter="booking">Bookings</button>
      <button type="button" class="adm-notif-pill-btn" data-filter="fleet">Fleet &amp; Maintenance</button>
      <button type="button" class="adm-notif-pill-btn" data-filter="inquiry">Inquiries</button>
    </div>
  </div>

  <!-- Section 1: Needs Attention -->
  <section class="adm-notif-section-block" id="sectionNeedsAttention">
    <div class="adm-notif-sec-header">
      <h2 class="adm-notif-sec-heading">Needs Attention</h2>
      <p class="adm-notif-sec-sub">Operational alerts requiring immediate administrative action</p>
    </div>

    <div class="adm-attn-cards-stack" id="attnCardsStack">
      <?php if ($needsAttention): ?>
        <?php foreach ($needsAttention as $idx => $n): 
          $meta = get_notif_meta($n);
          $isOlder = ($idx >= 3);
          $targetUrl = $n['link'] ? (str_starts_with($n['link'], 'http') ? $n['link'] : BASE_URL . ltrim($n['link'], '/')) : '#';
        ?>
          <div class="adm-attn-card-box <?= $isOlder ? 'adm-older-attn-item' : '' ?>" 
               id="notif-card-<?= (int)$n['id'] ?>"
               style="<?= $isOlder ? 'display:none;' : '' ?>"
               data-id="<?= (int)$n['id'] ?>"
               data-type="<?= e($n['type'] ?: 'fleet') ?>"
               data-read="<?= (int)$n['is_read'] ?>"
               data-search="<?= e(strtolower($n['title'] . ' ' . $n['message'] . ' ' . $meta['tag1'] . ' ' . $meta['tag2'])) ?>">
            
            <div class="adm-attn-card-icon-box">
              <i class="fa-solid <?= e($meta['icon']) ?>"></i>
            </div>

            <div class="adm-attn-card-body">
              <div class="adm-attn-card-title-line">
                <span class="adm-attn-card-title"><?= e($n['title']) ?></span>
                <span class="adm-pill-urgent"><?= e($meta['urgency']) ?></span>
                <span class="adm-pill-category"><?= e($meta['category']) ?></span>
              </div>
              <p class="adm-attn-card-msg"><?= e($n['message']) ?></p>
              <div class="adm-attn-card-meta-line">
                <span class="adm-attn-card-time">
                  <i class="fa-regular fa-clock"></i> <?= date('M j, g:i A', strtotime($n['created_at'])) ?>
                </span>
                <span class="adm-attn-card-chip"><?= e($meta['tag1']) ?></span>
                <span class="adm-attn-card-chip"><?= e($meta['tag2']) ?></span>
              </div>
            </div>

            <div class="adm-attn-card-actions">
              <button type="button" class="adm-btn-card-sec btn-dismiss-card" data-id="<?= (int)$n['id'] ?>" title="Dismiss alert">
                <?= e($meta['secondaryText']) ?>
              </button>
              
              <?php if ($n['link']): ?>
                <a href="<?= e($targetUrl) ?>" class="adm-btn-card-pri" title="Go to record">
                  <span><?= $meta['ctaText'] ?></span>
                </a>
              <?php endif; ?>

              <button type="button" class="adm-btn-card-check btn-toggle-read <?= $n['is_read'] ? 'is-checked' : '' ?>" data-id="<?= (int)$n['id'] ?>" title="Mark as read / unread" aria-label="Toggle read status">
                <i class="fa-solid fa-square-check"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="adm-attn-card-box" style="background:#f8fafc;border-style:dashed;">
          <div class="adm-attn-card-icon-box" style="background:#ecfdf5;color:#10b981;">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div class="adm-attn-card-body">
            <span class="adm-attn-card-title" style="color:#059669">All operational alerts resolved</span>
            <p class="adm-attn-card-msg" style="margin:2px 0 0">There are no urgent maintenance tasks or pending reservations requiring review at this time.</p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (count($needsAttention) > 3): ?>
      <button type="button" class="adm-btn-load-older-pill" id="btnToggleOlderAttn" data-expanded="false">
        <span>Load Older Telemetry Logs (30 Days)</span> <i class="fa-solid fa-chevron-down" style="margin-left:6px;font-size:11px;"></i>
      </button>
    <?php endif; ?>
  </section>

  <!-- Section 2: System Activity Feed -->
  <section class="adm-notif-section-block" id="sectionActivityFeed">
    <div class="adm-notif-sec-header">
      <h2 class="adm-notif-sec-heading">System Activity Feed</h2>
      <p class="adm-notif-sec-sub">(Ordered by latest timestamp)</p>
    </div>

    <div class="adm-feed-cards-stack" id="feedCardsStack">
      <?php if ($activityFeed): ?>
        <?php foreach ($activityFeed as $idx => $n): 
          $meta = get_notif_meta($n);
          $isOlder = ($idx >= 3);
          $targetUrl = $n['link'] ? (str_starts_with($n['link'], 'http') ? $n['link'] : BASE_URL . ltrim($n['link'], '/')) : '#';
        ?>
          <div class="adm-feed-card-box <?= $isOlder ? 'adm-older-feed-item' : '' ?>" 
               id="feed-card-<?= (int)$n['id'] ?>"
               style="<?= $isOlder ? 'display:none;' : '' ?>"
               data-id="<?= (int)$n['id'] ?>"
               data-type="<?= e($n['type'] ?: 'system') ?>"
               data-read="<?= (int)$n['is_read'] ?>"
               data-search="<?= e(strtolower($n['title'] . ' ' . $n['message'] . ' ' . $meta['tag1'] . ' ' . $meta['tag2'])) ?>">
            
            <div class="adm-feed-card-icon-box">
              <i class="fa-solid <?= e($meta['icon']) ?>"></i>
            </div>

            <div class="adm-feed-card-body">
              <h3 class="adm-feed-card-title"><?= e($n['title']) ?></h3>
              <p class="adm-feed-card-msg"><?= e($n['message']) ?></p>
              <div class="adm-feed-card-pills-row">
                <span class="adm-feed-pill-badge"><?= e($meta['category']) ?></span>
                <span class="adm-feed-pill-badge"><?= e($meta['tag1']) ?></span>
              </div>
            </div>

            <div class="adm-feed-card-right">
              <span class="adm-feed-datetime"><?= strtoupper(date('M j, g:i A', strtotime($n['created_at']))) ?></span>
              
              <button type="button" class="adm-btn-card-check btn-toggle-read <?= $n['is_read'] ? 'is-checked' : '' ?>" data-id="<?= (int)$n['id'] ?>" title="Toggle read" aria-label="Toggle read">
                <i class="fa-solid fa-square-check"></i>
              </button>

              <?php if ($n['link']): ?>
                <a href="<?= e($targetUrl) ?>" class="adm-feed-cta-link" title="Open record">
                  <span>Open &rarr;</span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="sub">No notifications.</p>
      <?php endif; ?>
    </div>

    <?php if (count($activityFeed) > 3): ?>
      <button type="button" class="adm-btn-load-older-pill" id="btnToggleOlderFeed" data-expanded="false">
        <span>Load Older Telemetry Logs (30 Days)</span> <i class="fa-solid fa-chevron-down" style="margin-left:6px;font-size:11px;"></i>
      </button>
    <?php endif; ?>
  </section>

  <!-- Section 3: Customer Inquiries (Preserves all existing data & texts 1:1) -->
  <section class="adm-inquiries-card" id="inquiriesSection">
    <h2 class="adm-inquiries-title">Customer inquiries (from the Contact page)</h2>
    <div class="tbl-wrap">
      <table class="tbl" id="inquiriesTable">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Topic</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inq as $i): ?>
            <tr data-search="<?= e(strtolower($i['full_name'] . ' ' . $i['email'] . ' ' . $i['category'] . ' ' . $i['message'])) ?>">
              <td style="white-space:nowrap;color:#64748b"><?= date('M j, g:i A', strtotime($i['created_at'])) ?></td>
              <td><b><?= e($i['full_name']) ?></b></td>
              <td><a href="mailto:<?= e($i['email']) ?>" style="color:#0284c7;text-decoration:none"><?= e($i['email']) ?></a></td>
              <td><span class="adm-pill-category"><?= e(ucfirst($i['category'])) ?></span></td>
              <td style="color:#334155"><?= e($i['message']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$inq): ?>
            <tr><td colspan="5" style="text-align:center;color:#64748b;padding:24px 0">No inquiries yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('notifSearchInput');
  const filterPills = document.querySelectorAll('#notifFilterPills .adm-notif-pill-btn');
  const attnCards = document.querySelectorAll('.adm-attn-card-box');
  const feedCards = document.querySelectorAll('.adm-feed-card-box');
  const inqRows = document.querySelectorAll('#inquiriesTable tbody tr');

  let activeFilter = 'all';
  let searchTerm = '';

  // 1. Refresh Feed Button
  const btnRefresh = document.getElementById('btnRefreshPage');
  if (btnRefresh) {
    btnRefresh.addEventListener('click', function() {
      btnRefresh.style.transform = 'rotate(360deg)';
      setTimeout(() => { window.location.reload(); }, 250);
    });
  }

  // 2. Client-side Search and Filter Engine
  function applyFilters() {
    // Filter Needs Attention
    attnCards.forEach(card => {
      const type = card.getAttribute('data-type') || '';
      const isRead = card.getAttribute('data-read') === '1';
      const text = card.getAttribute('data-search') || '';
      const isOlder = card.classList.contains('adm-older-attn-item');
      const isOlderExpanded = document.getElementById('btnToggleOlderAttn') && document.getElementById('btnToggleOlderAttn').getAttribute('data-expanded') === 'true';

      let matchesFilter = true;
      if (activeFilter === 'unread') {
        matchesFilter = !isRead;
      } else if (activeFilter === 'booking') {
        matchesFilter = (type === 'booking');
      } else if (activeFilter === 'fleet') {
        matchesFilter = (type === 'fleet' || type === 'maintenance');
      } else if (activeFilter === 'inquiry') {
        matchesFilter = (type === 'inquiry' || text.includes('inquiry'));
      }

      let matchesSearch = true;
      if (searchTerm !== '') {
        matchesSearch = text.includes(searchTerm);
      }

      let matchesExpanded = !isOlder || isOlderExpanded || searchTerm !== '' || activeFilter !== 'all';

      if (matchesFilter && matchesSearch && matchesExpanded) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });

    // Filter Activity Feed
    feedCards.forEach(card => {
      const type = card.getAttribute('data-type') || '';
      const isRead = card.getAttribute('data-read') === '1';
      const text = card.getAttribute('data-search') || '';
      const isOlder = card.classList.contains('adm-older-feed-item');
      const isOlderExpanded = document.getElementById('btnToggleOlderFeed') && document.getElementById('btnToggleOlderFeed').getAttribute('data-expanded') === 'true';

      let matchesFilter = true;
      if (activeFilter === 'unread') {
        matchesFilter = !isRead;
      } else if (activeFilter === 'booking') {
        matchesFilter = (type === 'booking');
      } else if (activeFilter === 'fleet') {
        matchesFilter = (type === 'fleet' || type === 'maintenance');
      } else if (activeFilter === 'inquiry') {
        matchesFilter = (type === 'inquiry' || text.includes('inquiry'));
      }

      let matchesSearch = true;
      if (searchTerm !== '') {
        matchesSearch = text.includes(searchTerm);
      }

      let matchesExpanded = !isOlder || isOlderExpanded || searchTerm !== '' || activeFilter !== 'all';

      if (matchesFilter && matchesSearch && matchesExpanded) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });

    // Filter Inquiries Table
    if (inqRows.length > 0) {
      inqRows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (searchTerm === '' || text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
  }

  // Filter Pills Click Handler
  filterPills.forEach(pill => {
    pill.addEventListener('click', function() {
      filterPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      activeFilter = pill.getAttribute('data-filter') || 'all';

      // If inquiry filter clicked, smooth scroll to inquiries section
      if (activeFilter === 'inquiry') {
        const inqSec = document.getElementById('inquiriesSection');
        if (inqSec) inqSec.scrollIntoView({ behavior: 'smooth' });
      }

      applyFilters();
    });
  });

  // Search Input Handler
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      searchTerm = searchInput.value.trim().toLowerCase();
      applyFilters();
    });
  }

  // 3. Toggle Older Telemetry Logs (Needs Attention)
  const btnToggleOlderAttn = document.getElementById('btnToggleOlderAttn');
  if (btnToggleOlderAttn) {
    btnToggleOlderAttn.addEventListener('click', function() {
      const isExpanded = btnToggleOlderAttn.getAttribute('data-expanded') === 'true';
      btnToggleOlderAttn.setAttribute('data-expanded', !isExpanded);
      btnToggleOlderAttn.querySelector('span').textContent = isExpanded 
        ? 'Load Older Telemetry Logs (30 Days)' 
        : 'Collapse Older Telemetry Logs';
      btnToggleOlderAttn.querySelector('i').style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
      applyFilters();
    });
  }

  // 4. Toggle Older Telemetry Logs (Activity Feed)
  const btnToggleOlderFeed = document.getElementById('btnToggleOlderFeed');
  if (btnToggleOlderFeed) {
    btnToggleOlderFeed.addEventListener('click', function() {
      const isExpanded = btnToggleOlderFeed.getAttribute('data-expanded') === 'true';
      btnToggleOlderFeed.setAttribute('data-expanded', !isExpanded);
      btnToggleOlderFeed.querySelector('span').textContent = isExpanded 
        ? 'Load Older Telemetry Logs (30 Days)' 
        : 'Collapse Older Telemetry Logs';
      btnToggleOlderFeed.querySelector('i').style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
      applyFilters();
    });
  }

  // 5. Checkbox Button: Toggle Read / Unread Status via AJAX
  const csrfToken = document.querySelector('input[name="csrf"]') ? document.querySelector('input[name="csrf"]').value : '';

  document.querySelectorAll('.btn-toggle-read').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = btn.getAttribute('data-id');
      if (!id) return;

      const fd = new FormData();
      fd.append('op', 'notif_toggle');
      fd.append('id', id);
      fd.append('csrf', csrfToken);
      fd.append('back', 'admin/notifications.php');

      // Optimistic UI toggle
      const isCurrentlyChecked = btn.classList.contains('is-checked');
      btn.classList.toggle('is-checked', !isCurrentlyChecked);

      // Find all elements with this notif ID (both in Needs Attention and Activity Feed)
      document.querySelectorAll(`[data-id="${id}"]`).forEach(el => {
        if (el.classList.contains('adm-attn-card-box')) {
          el.setAttribute('data-read', isCurrentlyChecked ? '0' : '1');
          el.classList.toggle('is-read-muted', !isCurrentlyChecked);
        }
        if (el.classList.contains('adm-feed-card-box')) {
          el.setAttribute('data-read', isCurrentlyChecked ? '0' : '1');
        }
        const check = el.querySelector('.btn-toggle-read');
        if (check) check.classList.toggle('is-checked', !isCurrentlyChecked);
      });

      fetch('<?= $A ?>action.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      }).then(r => r.json()).catch(() => {});
    });
  });

  // 6. Dismiss Button on Attention Cards
  document.querySelectorAll('.btn-dismiss-card').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const id = btn.getAttribute('data-id');
      const card = document.getElementById(`notif-card-${id}`);
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        setTimeout(() => { card.style.display = 'none'; }, 200);
      }

      const fd = new FormData();
      fd.append('op', 'notif_dismiss');
      fd.append('id', id);
      fd.append('csrf', csrfToken);
      fd.append('back', 'admin/notifications.php');

      fetch('<?= $A ?>action.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
      }).catch(() => {});
    });
  });
});
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
