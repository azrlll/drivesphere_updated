<?php
$pageTitle = 'Manage Vehicles';
$nav = 'manage';
require_once __DIR__ . '/../includes/admin_header.php';

// Filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$catFilter = $_GET['cat'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$perPage = max(1, min(42, (int)($_GET['per_page'] ?? 6))); // Default 6 matching mockup

// Compute global status counts
$countAll = (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$countPosted = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE is_posted = 1")->fetchColumn();
$countNotPosted = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE is_posted = 0")->fetchColumn();
$countMaint = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance'")->fetchColumn();

// Build query
$where = ["1=1"];
$params = [];

if ($statusFilter === 'posted') {
    $where[] = "v.is_posted = 1";
} elseif ($statusFilter === 'not_posted') {
    $where[] = "v.is_posted = 0";
} elseif ($statusFilter === 'maintenance') {
    $where[] = "v.status = 'maintenance'";
}

if ($catFilter !== 'all' && $catFilter !== '') {
    $where[] = "(LOWER(c.slug) = :cat OR LOWER(v.category) = :cat)";
    $params['cat'] = strtolower($catFilter);
}

if ($q !== '') {
    $where[] = "(v.model LIKE :q OR v.brand LIKE :q OR v.make LIKE :q OR v.plate_number LIKE :q OR v.category LIKE :q)";
    $params['q'] = '%' . $q . '%';
}

$whereSql = implode(' AND ', $where);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles v LEFT JOIN categories c ON v.category_id=c.id WHERE $whereSql");
$countStmt->execute($params);
$totalVehicles = (int)$countStmt->fetchColumn();

// Pagination calculation
$totalPages = max(1, (int)ceil($totalVehicles / $perPage));
$page = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$offset = ($page - 1) * $perPage;

$sql = "SELECT v.*, c.name AS cat_name FROM vehicles v 
        LEFT JOIN categories c ON v.category_id=c.id 
        WHERE $whereSql 
        ORDER BY v.id ASC 
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $val) {
    $stmt->bindValue(':' . $k, $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$start = $totalVehicles > 0 ? ($offset + 1) : 0;
$end = min($offset + $perPage, $totalVehicles);
?>

<!-- 1. Header Section -->
<div class="manage-header-block">
  <h1 class="manage-head-title">Manage Vehicles</h1>
  <p class="manage-head-sub">Manage availability, maintenance intervals, daily billing tariffs, and live merchant catalog visibility.</p>
</div>

<!-- 2. Search & Filter Bar -->
<div class="manage-toolbar">
  <!-- Search input -->
  <div class="manage-search-wrap">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" name="q" id="manageSearchInput" class="manage-search-input" placeholder="Search by vehicle name, plate number, or category..." value="<?= e($q) ?>" autocomplete="off">
  </div>

  <!-- Status Tabs -->
  <div class="manage-status-tabs">
    <a href="manage.php?status=all&per_page=<?= $perPage ?>" class="manage-tab-btn <?= $statusFilter === 'all' ? 'active' : '' ?>">All (<?= $countAll ?>)</a>
    <a href="manage.php?status=posted&per_page=<?= $perPage ?>" class="manage-tab-btn <?= $statusFilter === 'posted' ? 'active' : '' ?>">Posted (<?= $countPosted ?>)</a>
    <a href="manage.php?status=not_posted&per_page=<?= $perPage ?>" class="manage-tab-btn <?= $statusFilter === 'not_posted' ? 'active' : '' ?>">Not Posted (<?= $countNotPosted ?>)</a>
    <a href="manage.php?status=maintenance&per_page=<?= $perPage ?>" class="manage-tab-btn tab-danger <?= $statusFilter === 'maintenance' ? 'active' : '' ?>"><span class="red-dot"></span> Under Maintenance (<?= $countMaint ?>)</a>
  </div>

  <!-- Category Quick Filter Dropdown -->
  <div style="position:relative;">
    <button type="button" class="btn-filter-icon" id="quickFilterBtn" title="Filter by Vehicle Category" aria-haspopup="true" aria-expanded="false">
      <i class="fa-solid fa-sliders"></i>
    </button>
    <div id="categoryFilterDropdown" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:210px;background:#fff;border:1px solid #cbd5e1;border-radius:10px;box-shadow:0 12px 28px rgba(15,23,42,0.15);z-index:100;padding:6px 0;">
      <div style="padding:6px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
        <span>Filter by Class</span>
        <?php if ($catFilter !== 'all'): ?><a href="manage.php?status=<?= urlencode($statusFilter) ?>&per_page=<?= $perPage ?>" style="font-size:11px;color:#0284c7;text-decoration:none;">Reset</a><?php endif; ?>
      </div>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=all&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'all' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">All Categories</a>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=suv&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'suv' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">SUV &amp; 4x4</a>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=sedan&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'sedan' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">Sedans &amp; Executive</a>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=van&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'van' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">Vans &amp; Shuttles</a>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=hatchback&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'hatchback' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">Compact Hatchbacks</a>
      <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=pickup&per_page=<?= $perPage ?>" style="display:block;padding:8px 14px;font-size:13px;font-weight:600;color:#0f172a;text-decoration:none;<?= $catFilter === 'pickup' ? 'background:#f0f9ff;color:#0284c7;' : '' ?>">4x4 Utility Pickups</a>
    </div>
  </div>

  <!-- + Add Vehicle Button -->
  <a href="<?= $A ?>vehicle_form.php" class="btn-add-vehicle" style="color: #ffffff !important;" title="Add a new vehicle to fleet">
    <i class="fa-solid fa-plus" style="color: #ffffff !important;"></i> Add Vehicle
  </a>
</div>

<!-- 3. Vehicles Grid (2 rows x 3 columns matching image) -->
<div class="manage-v-grid" id="manageFleetGrid">
  <?php if (empty($vehicles)): ?>
    <div style="grid-column: 1 / -1; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:48px 20px; text-align:center;">
      <i class="fa-solid fa-car-tunnel" style="font-size:42px; color:#94a3b8; margin-bottom:12px;"></i>
      <h3 style="font-size:18px; font-weight:800; color:#0f172a; margin-bottom:6px;">No vehicles match your criteria</h3>
      <p style="font-size:14px; color:#64748b; margin-bottom:18px;">Try clearing search keywords or view all active fleet units.</p>
      <a href="manage.php" class="btn-manage-add" style="display:inline-flex;">View All Vehicles</a>
    </div>
  <?php endif; ?>

  <?php foreach ($vehicles as $v): 
    $makeBrand = $v['brand'] ?: $v['make'];
    if (!empty($makeBrand) && !str_starts_with(strtolower($v['model']), strtolower($makeBrand))) {
        $fullName = $makeBrand . ' ' . $v['model'];
    } else {
        $fullName = $v['model'];
    }
    $isPosted = !empty($v['is_posted']);
    $tagClass = $v['tag_class'] ?: ($v['category'] . ' • ' . $v['transmission']);
    $plate = $v['plate_number'];
    $condStatus = $v['condition_status'] ?: 'Good Condition';
    $condType = $v['condition_type'] ?: 'good';
    $nextPms = $v['next_pms'] ?: '30,000 km';
    $fuelLevel = $v['fuel_level'] ?: '90% Full';
    $pmsIsBay = str_starts_with($nextPms, 'Finish');
    $pmsIsDue = (stripos($nextPms, 'Past Due') !== false || stripos($condStatus, 'Due') !== false);
  ?>
    <div class="manage-v-card" id="vcard-<?= $v['id'] ?>" data-search-text="<?= strtolower(e($fullName . ' ' . $plate . ' ' . $tagClass . ' ' . $v['category'])) ?>">
      <!-- Media Header with Real Badges -->
      <div class="manage-card-media">
        <img src="<?= e(vehicle_image_url($v['image_path'] ?: $v['image_url'])) ?>" alt="<?= e($fullName) ?>" class="manage-card-img" loading="lazy">
        
        <!-- Left Overlays: Category & Plate -->
        <div class="v-overlays-left">
          <span class="v-pill-class"><?= e($tagClass) ?></span>
          <span class="v-pill-plate"><?= e($plate) ?></span>
        </div>

        <!-- Right Overlay: Posted / Not Posted Badge -->
        <div class="v-overlays-right" id="vbadge-wrap-<?= $v['id'] ?>">
          <?php if ($isPosted): ?>
            <span class="v-badge-posted"><i class="fa-solid fa-globe"></i> Posted</span>
          <?php else: ?>
            <span class="v-badge-notposted"><i class="fa-solid fa-lock"></i> Not Posted</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Card Body -->
      <div class="manage-card-body">
        <div class="manage-v-head-row">
          <a href="<?= $A ?>vehicle_form.php?id=<?= $v['id'] ?>" class="manage-v-title" title="Edit <?= e($fullName) ?> Details">
            <?= e($fullName) ?>
          </a>
          <div class="manage-v-rate">
            ₱<?= number_format($v['daily_rate'], 0) ?>
            <small>/ day</small>
          </div>
        </div>

        <div class="manage-v-specs">
          <?= e(!empty($v['specs_text']) ? $v['specs_text'] : ($v['transmission'] . ' • ' . $v['fuel_type'] . ' • ' . (int)($v['seating_capacity'] ?: $v['seats']) . ' Seater')) ?>
        </div>

        <!-- 3-Column Metric Box -->
        <div class="manage-metrics-box">
          <div class="metric-col">
            <span class="metric-lbl">ODOMETER</span>
            <span class="metric-val"><?= number_format($v['odometer']) ?> km</span>
          </div>
          <div class="metric-col">
            <span class="metric-lbl">FUEL LEVEL</span>
            <span class="metric-val"><?= e($fuelLevel) ?></span>
          </div>
          <div class="metric-col">
            <span class="metric-lbl"><?= $pmsIsBay ? 'BAY STATUS' : 'NEXT PMS' ?></span>
            <span class="metric-val <?= $pmsIsDue ? 'val-danger' : ($pmsIsBay ? 'val-info' : '') ?>">
              <?= e($nextPms) ?>
            </span>
          </div>
        </div>

        <!-- Condition Status Pill -->
        <div class="v-cond-pill cond-<?= e($condType) ?>" id="vcond-<?= $v['id'] ?>">
          <?php if ($condType === 'info'): ?>
            <i class="fa-solid fa-wrench" style="font-size:10px;"></i>
          <?php else: ?>
            <span class="cond-dot"></span>
          <?php endif; ?>
          <span><?= e($condStatus) ?></span>
        </div>

        <!-- Actions Footer: Edit Info | Clock History | Post/Unpost -->
        <div class="manage-card-actions">
          <a href="<?= $A ?>vehicle_form.php?id=<?= $v['id'] ?>" class="btn-card-edit" title="Edit Vehicle Specs &amp; Photos">
            <i class="fa-solid fa-pencil"></i> Edit Info
          </a>

          <button type="button" class="btn-card-history" 
                  data-id="<?= $v['id'] ?>" 
                  data-name="<?= e($fullName) ?>" 
                  data-plate="<?= e($plate) ?>"
                  data-odo="<?= number_format($v['odometer']) ?> km"
                  data-pms="<?= e($nextPms) ?>"
                  data-fuel="<?= e($fuelLevel) ?>"
                  data-cond="<?= e($condStatus) ?>"
                  data-status="<?= e($v['status']) ?>"
                  title="Service &amp; Maintenance Log">
            <i class="fa-regular fa-clock"></i>
          </button>

          <div id="vbtn-wrap-<?= $v['id'] ?>" style="flex: 1; display: flex;">
            <?php if ($isPosted): ?>
              <button type="button" class="btn-card-unpost" data-id="<?= $v['id'] ?>" title="Temporarily hide vehicle from customer browse catalog">
                <i class="fa-solid fa-arrow-up-from-bracket" style="transform: rotate(180deg);"></i> Unpost
              </button>
            <?php else: ?>
              <button type="button" class="btn-card-postnow" data-id="<?= $v['id'] ?>" title="Publish vehicle to customer rental catalog">
                <i class="fa-solid fa-upload"></i> Post Now
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- 4. Pagination Bar (Matches Mockup) -->
<?php if ($totalVehicles > 0): ?>
  <div class="manage-pagination-bar">
    <div class="manage-page-info">
      <span>Showing <strong><?= $start ?>–<?= $end ?></strong> of <strong><?= $totalVehicles ?></strong> vehicles</span>
      <span>&bull;</span>
      <span>Per page:</span>
      <select id="perPageSelect" class="per-page-select" aria-label="Vehicles per page">
        <option value="6" <?= $perPage === 6 ? 'selected' : '' ?>>6</option>
        <option value="12" <?= $perPage === 12 ? 'selected' : '' ?>>12</option>
        <option value="24" <?= $perPage === 24 ? 'selected' : '' ?>>24</option>
        <option value="42" <?= $perPage === 42 ? 'selected' : '' ?>>42</option>
      </select>
    </div>

    <div class="manage-page-navs">
      <!-- Prev -->
      <?php if ($page <= 1): ?>
        <span class="manage-page-btn disabled"><i class="fa-solid fa-chevron-left"></i> Prev</span>
      <?php else: ?>
        <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=<?= urlencode($catFilter) ?>&q=<?= urlencode($q) ?>&per_page=<?= $perPage ?>&page=<?= $page - 1 ?>" class="manage-page-btn">
          <i class="fa-solid fa-chevron-left"></i> Prev
        </a>
      <?php endif; ?>

      <!-- Page Numbers with Ellipsis -->
      <?php
      $startPage = max(1, $page - 1);
      $endPage = min($totalPages, $page + 1);
      if ($page === 1) {
          $endPage = min($totalPages, 3);
      } elseif ($page === $totalPages) {
          $startPage = max(1, $totalPages - 2);
      }
      
      if ($startPage > 1) {
          echo '<a href="manage.php?status=' . urlencode($statusFilter) . '&cat=' . urlencode($catFilter) . '&q=' . urlencode($q) . '&per_page=' . $perPage . '&page=1" class="manage-page-btn">1</a>';
          if ($startPage > 2) echo '<span class="manage-page-ellipsis">...</span>';
      }

      for ($p = $startPage; $p <= $endPage; $p++):
      ?>
        <?php if ($p === $page): ?>
          <span class="manage-page-btn active"><?= $p ?></span>
        <?php else: ?>
          <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=<?= urlencode($catFilter) ?>&q=<?= urlencode($q) ?>&per_page=<?= $perPage ?>&page=<?= $p ?>" class="manage-page-btn"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php
      if ($endPage < $totalPages) {
          if ($endPage < $totalPages - 1) echo '<span class="manage-page-ellipsis">...</span>';
          echo '<a href="manage.php?status=' . urlencode($statusFilter) . '&cat=' . urlencode($catFilter) . '&q=' . urlencode($q) . '&per_page=' . $perPage . '&page=' . $totalPages . '" class="manage-page-btn">' . $totalPages . '</a>';
      }
      ?>

      <!-- Next -->
      <?php if ($page >= $totalPages): ?>
        <span class="manage-page-btn disabled">Next <i class="fa-solid fa-chevron-right"></i></span>
      <?php else: ?>
        <a href="manage.php?status=<?= urlencode($statusFilter) ?>&cat=<?= urlencode($catFilter) ?>&q=<?= urlencode($q) ?>&per_page=<?= $perPage ?>&page=<?= $page + 1 ?>" class="manage-page-btn">
          Next <i class="fa-solid fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- 5. Vehicle Service & Maintenance History Modal -->
<div class="manage-modal-backdrop" id="historyModalBackdrop" style="display:none;" role="dialog" aria-modal="true">
  <div class="manage-modal-box">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;padding:16px 20px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
      <div>
        <h3 id="histModalVehicleName" style="margin:0 0 4px;font-size:17px;font-weight:800;color:#0f172a;">Vehicle Service History</h3>
        <span id="histModalPlate" style="font-size:12px;color:#64748b;font-weight:700;">Plate: N/A</span>
      </div>
      <button type="button" id="histModalClose" style="background:none;border:none;font-size:18px;color:#94a3b8;cursor:pointer;padding:4px;" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div style="padding:20px;">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:18px;">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
          <span style="font-size:10px;font-weight:700;color:#64748b;display:block;text-transform:uppercase;margin-bottom:2px;">Current Odometer</span>
          <span id="histModalOdo" style="font-size:14px;font-weight:800;color:#0f172a;">--</span>
        </div>
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;">
          <span style="font-size:10px;font-weight:700;color:#64748b;display:block;text-transform:uppercase;margin-bottom:2px;">Next Scheduled PMS</span>
          <span id="histModalPms" style="font-size:14px;font-weight:800;color:#0284c7;">--</span>
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;display:block;margin-bottom:8px;">Recent Fleet Health Log</span>
        <div style="border-left:2px solid #0284c7;padding-left:14px;margin-bottom:10px;">
          <strong id="histModalCond" style="font-size:13px;color:#0f172a;display:block;">Condition Status</strong>
          <span style="font-size:12px;color:#64748b;">Verified by DriveSphere Fleet Mechanics Depot &bull; BGC Flagship Station</span>
        </div>
        <div style="border-left:2px solid #cbd5e1;padding-left:14px;">
          <strong style="font-size:13px;color:#0f172a;display:block;">Multi-Point Sanitization &amp; Fluid Inspection</strong>
          <span style="font-size:12px;color:#64748b;">100-Point Pre-Rental Diagnostic Safety Check Cleared</span>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:14px;border-top:1px solid #f1f5f9;">
        <a id="histModalMaintLink" href="maintenance.php" class="btn-card-edit" style="flex:initial;padding:8px 16px;">
          <i class="fa-solid fa-wrench"></i> Full Maintenance Hub
        </a>
        <a id="histModalEditLink" href="vehicle_form.php" class="btn-manage-add" style="padding:8px 16px;">
          <i class="fa-solid fa-pencil"></i> Edit Details
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 6. Floating Action Toast Notification -->
<div class="settings-toast success" id="manageToast">
  <i class="fa-solid fa-circle-check" style="font-size:16px;"></i>
  <span id="manageToastMessage">Vehicle published to catalog.</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toast helper
    const toast = document.getElementById('manageToast');
    const toastMsg = document.getElementById('manageToastMessage');
    let toastTimer = null;
    function showManageToast(msg, isSuccess = true) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = msg;
        toast.className = 'settings-toast ' + (isSuccess ? 'success' : 'error');
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // 2. Client-side Live Search + Server Enter Search
    const searchInput = document.getElementById('manageSearchInput');
    const cards = document.querySelectorAll('.manage-v-card');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            cards.forEach(card => {
                const text = card.getAttribute('data-search-text') || '';
                if (!q || text.includes(q)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const qVal = this.value.trim();
                const url = new URL(window.location.href);
                url.searchParams.set('q', qVal);
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }
        });
    }

    // 3. Category Filter Dropdown Toggle
    const filterBtn = document.getElementById('quickFilterBtn');
    const filterDrop = document.getElementById('categoryFilterDropdown');
    if (filterBtn && filterDrop) {
        filterBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = filterDrop.style.display === 'block';
            filterDrop.style.display = isOpen ? 'none' : 'block';
            filterBtn.setAttribute('aria-expanded', !isOpen);
        });

        document.addEventListener('click', function(e) {
            if (!filterBtn.contains(e.target) && !filterDrop.contains(e.target)) {
                filterDrop.style.display = 'none';
                filterBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // 4. Per Page Selector Handler
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', this.value);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    }

    // 5. Post / Unpost Toggle AJAX Handler
    document.addEventListener('click', function(e) {
        const unpostBtn = e.target.closest('.btn-card-unpost');
        const postBtn = e.target.closest('.btn-card-postnow');
        const targetBtn = unpostBtn || postBtn;

        if (targetBtn) {
            e.preventDefault();
            const vId = targetBtn.getAttribute('data-id');
            const isUnposting = !!unpostBtn;

            targetBtn.disabled = true;
            targetBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

            const formData = new FormData();
            formData.append('op', 'toggle_post');
            formData.append('id', vId);
            formData.append('csrf', '<?= csrf_token() ?>');

            fetch('<?= $A ?>action.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                targetBtn.disabled = false;
                if (data.ok) {
                    const badgeWrap = document.getElementById('vbadge-wrap-' + vId);
                    const btnWrap = document.getElementById('vbtn-wrap-' + vId);

                    if (data.is_posted) {
                        if (badgeWrap) badgeWrap.innerHTML = '<span class="v-badge-posted"><i class="fa-solid fa-globe"></i> Posted</span>';
                        if (btnWrap) btnWrap.innerHTML = '<button type="button" class="btn-card-unpost" data-id="' + vId + '" title="Temporarily hide vehicle from customer browse catalog"><i class="fa-solid fa-arrow-up-from-bracket" style="transform: rotate(180deg);"></i> Unpost</button>';
                        showManageToast('✓ Vehicle published to live customer catalog!');
                    } else {
                        if (badgeWrap) badgeWrap.innerHTML = '<span class="v-badge-notposted"><i class="fa-solid fa-lock"></i> Not Posted</span>';
                        if (btnWrap) btnWrap.innerHTML = '<button type="button" class="btn-card-postnow" data-id="' + vId + '" title="Publish vehicle to customer rental catalog"><i class="fa-solid fa-upload"></i> Post Now</button>';
                        showManageToast('Vehicle unpublished from customer catalog.');
                    }
                } else {
                    showManageToast('Failed to update status.', false);
                }
            })
            .catch(err => {
                targetBtn.disabled = false;
                showManageToast('Error updating vehicle.', false);
            });
        }
    });

    // 6. Service & Maintenance History Modal Handler
    const historyModal = document.getElementById('historyModalBackdrop');
    const modalClose = document.getElementById('histModalClose');
    const modalName = document.getElementById('histModalVehicleName');
    const modalPlate = document.getElementById('histModalPlate');
    const modalOdo = document.getElementById('histModalOdo');
    const modalPms = document.getElementById('histModalPms');
    const modalCond = document.getElementById('histModalCond');
    const modalMaintLink = document.getElementById('histModalMaintLink');
    const modalEditLink = document.getElementById('histModalEditLink');

    document.addEventListener('click', function(e) {
        const histBtn = e.target.closest('.btn-card-history');
        if (histBtn) {
            e.preventDefault();
            const id = histBtn.getAttribute('data-id');
            const name = histBtn.getAttribute('data-name');
            const plate = histBtn.getAttribute('data-plate');
            const odo = histBtn.getAttribute('data-odo');
            const pms = histBtn.getAttribute('data-pms');
            const cond = histBtn.getAttribute('data-cond');

            if (modalName) modalName.textContent = name;
            if (modalPlate) modalPlate.textContent = 'Plate: ' + plate;
            if (modalOdo) modalOdo.textContent = odo;
            if (modalPms) modalPms.textContent = pms;
            if (modalCond) modalCond.textContent = cond;
            if (modalMaintLink) modalMaintLink.href = 'maintenance.php?id=' + id;
            if (modalEditLink) modalEditLink.href = 'vehicle_form.php?id=' + id;

            if (historyModal) historyModal.style.display = 'flex';
        }
    });

    if (modalClose) {
        modalClose.addEventListener('click', function() {
            if (historyModal) historyModal.style.display = 'none';
        });
    }
    if (historyModal) {
        historyModal.addEventListener('click', function(e) {
            if (e.target === historyModal) historyModal.style.display = 'none';
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
