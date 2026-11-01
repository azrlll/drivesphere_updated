<?php
$pageTitle = 'Fleet Operations Dashboard';
$nav = 'dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

// 1. Branch filter for Inventory Monitoring (Phase 5)
$allBranches = $pdo->query("SELECT * FROM branches ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$branchFilter = isset($_GET['branch']) && $_GET['branch'] !== 'all' && $_GET['branch'] !== '' ? (int)$_GET['branch'] : 0;
$selectedBranch = null;
if ($branchFilter > 0) {
    foreach ($allBranches as $b) {
        if ($b['id'] == $branchFilter) {
            $selectedBranch = $b;
            break;
        }
    }
}

$bWhereVeh = $branchFilter > 0 ? "WHERE branch_id = $branchFilter" : "";
$bWhereVehAnd = $branchFilter > 0 ? "AND branch_id = $branchFilter" : "";
$bWhereResAnd = $branchFilter > 0 ? "AND v.branch_id = $branchFilter" : "";

// Live Database Inventory Figures (Phase 5)
$totalUnits     = (int)$pdo->query("SELECT COUNT(*) FROM vehicles $bWhereVeh")->fetchColumn();
$rentedUnits    = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='rented' $bWhereVehAnd")->fetchColumn();
$maintUnits     = (int)$pdo->query("SELECT COUNT(*) FROM vehicles WHERE status='maintenance' $bWhereVehAnd")->fetchColumn();
$availUnits     = max(0, $totalUnits - $rentedUnits - $maintUnits);
$reservedUnits  = (int)$pdo->query("SELECT COUNT(DISTINCT r.vehicle_id) FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id WHERE r.status='reserved' $bWhereResAnd")->fetchColumn();
$unavailableUnits = $rentedUnits + $maintUnits;
$pendingRequests = (int)$pdo->query("SELECT COUNT(*) FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id WHERE r.status='pending' $bWhereResAnd")->fetchColumn();

// Percentages for Donut SVG
$pctRented = $totalUnits > 0 ? round(($rentedUnits / $totalUnits) * 100) : 0;
$pctAvail  = $totalUnits > 0 ? round(($availUnits / $totalUnits) * 100) : 0;
$pctMaint  = $totalUnits > 0 ? round(($maintUnits / $totalUnits) * 100) : 0;
$pctRes    = $totalUnits > 0 ? round(($reservedUnits / $totalUnits) * 100) : 0;

$circumference = 239;
$dashRented = round(($pctRented / 100) * $circumference);
$dashAvail  = round(($pctAvail / 100) * $circumference);
$dashMaint  = round(($pctMaint / 100) * $circumference);
$dashRes    = round(($pctRes / 100) * $circumference);

// 2. Real MySQL Most Rented Vehicles Analytics (Phase 10)
$period = $_GET['period'] ?? 'all';
if (!in_array($period, ['all', '30d', '90d', '7d'], true)) {
    $period = 'all';
}

// Fetch all vehicles with completed rental counts from MySQL
$rankedStmt = $pdo->query("
    SELECT 
        v.id, 
        v.brand, 
        v.make, 
        v.model, 
        v.category, 
        v.status, 
        v.plate_number,
        v.image_path, 
        v.image_url, 
        v.transmission, 
        v.daily_rate,
        v.branch_id,
        b.name AS branch_name,
        COUNT(r.id) as rentals_completed,
        COUNT(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as rentals_7d,
        COUNT(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as rentals_30d,
        COUNT(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) THEN 1 END) as rentals_90d
    FROM vehicles v
    LEFT JOIN branches b ON v.branch_id = b.id
    LEFT JOIN reservations r ON v.id = r.vehicle_id AND r.status = 'completed'
    GROUP BY v.id
    ORDER BY rentals_completed DESC, v.id ASC
");
$allRankedVehicles = $rankedStmt->fetchAll(PDO::FETCH_ASSOC);

// Top 5 slice
$top5 = array_slice($allRankedVehicles, 0, 5);

// Daily reservations lookup for calendar integration
$pickupCounts = $pdo->query("SELECT DATE(pickup_date) AS d, COUNT(*) AS n FROM reservations GROUP BY DATE(pickup_date)")->fetchAll(PDO::FETCH_KEY_PAIR);
$returnCounts = $pdo->query("SELECT DATE(return_date) AS d, COUNT(*) AS n FROM reservations GROUP BY DATE(return_date)")->fetchAll(PDO::FETCH_KEY_PAIR);

// Active claim for Damage Review
$claim = $pdo->query("SELECT id FROM reservations WHERE status='returned' ORDER BY id DESC LIMIT 1")->fetchColumn();
$claimId = $claim ? (int)$claim : 21;

// Current calendar date parameters
$nowYear = (int)date('Y');
$nowMonth = (int)date('n'); // 1 to 12
$nowDay = (int)date('j');
$currentMonthLabel = date('F Y');
?>

<div class="adm-dashboard-container">
  <!-- Title & Branch Inventory Selector -->
  <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:18px;">
    <div>
      <h1 class="adm-main-title" style="margin-bottom:4px;">Fleet Operations Dashboard</h1>
      <p style="margin:0; font-size:13px; color:#64748b;">Live monitoring of rental operations, scheduling constraints, and hub inventory</p>
    </div>

    <!-- Branch Filter Selector (Phase 5) -->
    <div style="display:flex; align-items:center; gap:8px;">
      <label for="branchSelectFilter" style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.02em;">
        <i class="fa-solid fa-location-dot" style="color:#0284c7; margin-right:4px;"></i> Hub Location:
      </label>
      <select id="branchSelectFilter" onchange="window.location.href='dashboard.php?branch=' + this.value" style="padding:7px 12px; font-size:13px; font-weight:600; color:#0f172a; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; outline:none; cursor:pointer;">
        <option value="all" <?= $branchFilter === 0 ? 'selected' : '' ?>>All Hubs / Overall Fleet (53 Units)</option>
        <?php foreach ($allBranches as $b): ?>
          <option value="<?= $b['id'] ?>" <?= $branchFilter == $b['id'] ? 'selected' : '' ?>>
            <?= e($b['name']) ?> (<?= e($b['city']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Phase 5: Admin Vehicle Inventory Monitoring Key Statistics -->
  <div class="adm-inventory-kpi-bar" style="display:grid; grid-template-columns:repeat(6, 1fr); gap:12px; margin-bottom:24px;">
    <!-- Card 1: Total Vehicles -->
    <a href="<?= $A ?>manage.php<?= $branchFilter > 0 ? '?branch=' . $branchFilter : '' ?>" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Total Units</span>
        <i class="fa-solid fa-car" style="color:#0284c7; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $totalUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;"><?= $selectedBranch ? e($selectedBranch['city']) . ' Hub' : 'All 7 Hubs' ?></div>
    </a>

    <!-- Card 2: Available Vehicles -->
    <a href="<?= $A ?>manage.php?status=available<?= $branchFilter > 0 ? '&branch=' . $branchFilter : '' ?>" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #ccfbf1; border-left:4px solid #0d9488; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#0d9488; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Available</span>
        <i class="fa-solid fa-circle-check" style="color:#0d9488; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $availUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;"><?= $pctAvail ?>% of capacity</div>
    </a>

    <!-- Card 3: Reserved Vehicles -->
    <a href="<?= $A ?>rentals.php?tab=reserved" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #e2e8f0; border-left:4px solid #8392a5; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Reserved</span>
        <i class="fa-solid fa-clock" style="color:#64748b; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $reservedUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;"><?= $pctRes ?>% booked</div>
    </a>

    <!-- Card 4: Currently Rented (En Route) -->
    <a href="<?= $A ?>rentals.php?tab=en_route" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #dbeafe; border-left:4px solid #0284c7; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#0369a1; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Rented / En Route</span>
        <i class="fa-solid fa-route" style="color:#0284c7; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $rentedUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;"><?= $pctRented ?>% on the road</div>
    </a>

    <!-- Card 5: Under Maintenance -->
    <a href="<?= $A ?>manage.php?status=maintenance<?= $branchFilter > 0 ? '&branch=' . $branchFilter : '' ?>" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #fee2e2; border-left:4px solid #dc2626; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#dc2626; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Maintenance</span>
        <i class="fa-solid fa-wrench" style="color:#dc2626; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $maintUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;"><?= $pctMaint ?>% in shop</div>
    </a>

    <!-- Card 6: Unavailable Vehicles -->
    <a href="<?= $A ?>manage.php?status=maintenance<?= $branchFilter > 0 ? '&branch=' . $branchFilter : '' ?>" style="text-decoration:none; color:inherit; background:#ffffff; border:1px solid #e2e8f0; border-left:4px solid #94a3b8; border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(15,23,42,0.04); transition:transform 0.15s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
      <div style="font-size:10.5px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.03em; margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
        <span>Unavailable</span>
        <i class="fa-solid fa-ban" style="color:#64748b; font-size:13px;"></i>
      </div>
      <div style="font-size:24px; font-weight:800; color:#0f172a; line-height:1;"><?= $unavailableUnits ?></div>
      <div style="font-size:11px; color:#64748b; margin-top:4px;">Rented + Shop</div>
    </a>
  </div>

  <!-- Top 5 Most Rented Vehicles Section (Phase 10: Actual MySQL History) -->
  <div class="adm-section-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:8px;">
      <span class="adm-title-bar"></span>
      <h2 class="adm-section-title" style="margin:0;">Most Rented Vehicles</h2>
      <button type="button" onclick="openRankingsModal()" style="background:#e0f2fe; color:#0369a1; border:1px solid #bae6fd; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; cursor:pointer; margin-left:6px; display:inline-flex; align-items:center; gap:4px;">
        <i class="fa-solid fa-list-ol"></i> View All (<?= count($allRankedVehicles) ?> Units)
      </button>
    </div>
    
    <!-- Interactive Rolling Window Filter Pill -->
    <div class="adm-rolling-wrap">
      <button type="button" class="adm-badge-rolling" id="btnRollingWindow" aria-haspopup="true" aria-expanded="false" title="Click to filter rolling window">
        <span id="rollingWindowLabel">All-Time Rental History</span>
        <i class="fa-solid fa-chevron-down" style="font-size:9px;opacity:0.7"></i>
      </button>
      <div class="adm-rolling-dropdown" id="rollingDropdown">
        <button type="button" class="adm-rolling-opt is-active" data-period="all" data-label="All-Time Rental History">All-Time Rental History (Default)</button>
        <button type="button" class="adm-rolling-opt" data-period="30d" data-label="Last 30 Days">Last 30 Days Activity</button>
        <button type="button" class="adm-rolling-opt" data-period="90d" data-label="Last 90 Days">Last 90 Days Quarter</button>
        <button type="button" class="adm-rolling-opt" data-period="7d" data-label="Last 7 Days">Last 7 Days Rolling</button>
      </div>
    </div>
  </div>

  <div class="adm-top-grid">
    <?php foreach ($top5 as $idx => $v): 
      $rankNum = $idx + 1;
      $vehicleImg = vehicle_image_url($v['image_path'] ?: $v['image_url']);
      $fullName = ($v['brand'] ?: $v['make']) . ' ' . $v['model'];
      $specStr = $v['category'] . ' · ' . $v['transmission'];
      $statusColor = $v['status'] === 'available' ? '#0d9488' : ($v['status'] === 'rented' ? '#0284c7' : '#dc2626');
    ?>
      <a href="<?= $A ?>manage.php?q=<?= urlencode($v['model']) ?>" class="adm-vehicle-card" title="Manage <?= e($fullName) ?> in fleet catalog">
        <div class="adm-card-media" style="position:relative;">
          <img src="<?= e($vehicleImg) ?>" alt="<?= e($fullName) ?>" loading="lazy">
          <span style="position:absolute; top:8px; left:8px; background:rgba(15,23,42,0.85); color:#ffffff; font-size:10px; font-weight:800; padding:2px 7px; border-radius:6px; backdrop-filter:blur(4px);">
            #<?= $rankNum ?> Top
          </span>
          <span style="position:absolute; top:8px; right:8px; background:<?= $statusColor ?>; color:#ffffff; font-size:9.5px; font-weight:700; padding:2px 6px; border-radius:6px; text-transform:uppercase;">
            <?= e($v['status']) ?>
          </span>
        </div>
        <div class="adm-card-body">
          <h3 class="adm-vehicle-name"><?= e($fullName) ?></h3>
          <p class="adm-vehicle-spec"><?= e($specStr) ?></p>
          <div class="adm-rate-pill">
            <span class="adm-rate-lbl">Completed</span>
            <span class="adm-rate-val">
              <b class="adm-rate-number" 
                 data-rate-all="<?= (int)$v['rentals_completed'] ?>" 
                 data-rate-30d="<?= (int)$v['rentals_30d'] ?>" 
                 data-rate-90d="<?= (int)$v['rentals_90d'] ?>" 
                 data-rate-7d="<?= (int)$v['rentals_7d'] ?>">
                <?= (int)$v['rentals_completed'] ?>
              </b> 
              <small>rentals</small>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Bottom Operations Section: 2 Columns -->
  <div class="adm-bottom-grid">
    <!-- Left Column: Needs Attention -->
    <div class="adm-panel-card adm-attention-panel">
      <div class="adm-card-header">
        <div class="adm-card-title-group">
          <span class="adm-dot-indicator"></span>
          <h2 class="adm-card-title">Needs Attention</h2>
        </div>
        <a href="<?= $A ?>requests.php" class="adm-card-action-link" title="Open fleet approval tasks">View All Tasks</a>
      </div>
      <p class="adm-card-subtitle">Action required across fleet reservations, maintenance, and returns</p>

      <div class="adm-attn-list">
        <!-- Item 1: Maintenance Due -->
        <div class="adm-attn-item">
          <div class="adm-attn-icon adm-icon-coral">
            <i class="fa-solid fa-wrench"></i>
          </div>
          <div class="adm-attn-content">
            <div class="adm-attn-subject-row">
              <a href="<?= $A ?>manage.php?status=maintenance" class="adm-attn-subject" style="text-decoration:none;color:inherit;">Maintenance Due</a>
              <span class="adm-attn-tag adm-tag-red">Urgent</span>
            </div>
            <p class="adm-attn-details">Toyota Fortuner &middot; 10,000 km periodic engine and brake service overdue</p>
          </div>
          <a href="<?= $A ?>manage.php?status=maintenance" class="adm-action-btn adm-btn-cyan" title="Go to fleet maintenance page">Schedule Shop</a>
        </div>

        <!-- Item 2: Rental Request Pending Approval -->
        <div class="adm-attn-item">
          <div class="adm-attn-icon adm-icon-sky">
            <i class="fa-solid fa-file-circle-check"></i>
          </div>
          <div class="adm-attn-content">
            <div class="adm-attn-subject-row">
              <span class="adm-attn-subject">Rental Request Pending Approval</span>
              <span class="adm-attn-tag adm-tag-blue"><?= $pendingRequests ?> Pending</span>
            </div>
            <p class="adm-attn-details">Ref #DS-8921 &middot; Mitsubishi Montero GT &middot; Pick-up May 15, 09:00 AM &middot; Makati Hub</p>
          </div>
          <a href="<?= $A ?>requests.php?tab=pending" class="adm-action-btn adm-btn-navy" title="Approve booking request">Approve Now</a>
        </div>

        <!-- Item 3: Return Damage Assessment Claim -->
        <div class="adm-attn-item">
          <div class="adm-attn-icon adm-icon-teal">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div class="adm-attn-content">
            <div class="adm-attn-subject-row">
              <span class="adm-attn-subject">Return Damage Assessment</span>
              <span class="adm-attn-tag adm-tag-slate">Claim</span>
            </div>
            <p class="adm-attn-details">Ref #DS-7740 &middot; HiAce GL Grandia &middot; Rear bumper scuff final settlement charges</p>
          </div>
          <a href="<?= $A ?>damage.php?id=<?= $claimId ?>" class="adm-action-btn adm-btn-lavender" title="Review damage settlement">Review Claim</a>
        </div>

        <!-- Item 4: Customer KYC Verification -->
        <div class="adm-attn-item">
          <div class="adm-attn-icon adm-icon-slate">
            <i class="fa-solid fa-id-card"></i>
          </div>
          <div class="adm-attn-content">
            <div class="adm-attn-subject-row">
              <span class="adm-attn-subject">Customer KYC Verification</span>
              <span class="adm-attn-tag adm-tag-slate">KYC</span>
            </div>
            <p class="adm-attn-details">Maria Santos &middot; Philippine Driver License &amp; Government ID upload pending review</p>
          </div>
          <a href="<?= $A ?>customers.php?q=Maria" class="adm-action-btn adm-btn-lavender" title="Validate customer KYC records">Validate KYC</a>
        </div>
      </div>
    </div>

    <!-- Right Column: Dispatch Calendar + Fleet Distribution -->
    <div class="adm-side-stack">
      <!-- Card 1: Dispatch Calendar -->
      <div class="adm-panel-card adm-calendar-panel">
        <div class="adm-card-header">
          <div class="adm-card-title-group">
            <i class="fa-regular fa-calendar-days adm-cal-header-icon"></i>
            <h2 class="adm-card-title">Dispatch Calendar</h2>
          </div>
          
          <!-- Month Navigation Wrap with Previous/Next Arrows and Dropdown -->
          <div class="adm-month-nav-wrap">
            <button type="button" class="adm-month-arrow-btn" id="btnCalPrevMonth" title="Check previous month" aria-label="Previous month">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="adm-month-dropdown-wrap">
              <button type="button" class="adm-cal-month-badge" id="btnMonthToggle" aria-haspopup="true" aria-expanded="false" title="Click to pick previous month or check date">
                <span id="lblCalendarMonth"><?= e($currentMonthLabel) ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:9px;margin-left:4px;opacity:0.8"></i>
              </button>
              <div class="adm-month-dropdown" id="monthDropdownMenu">
                <!-- Dynamically populated via JS -->
              </div>
            </div>
            <button type="button" class="adm-month-arrow-btn" id="btnCalNextMonth" title="Next month" aria-label="Next month">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
            <button type="button" class="adm-cal-today-badge" id="btnJumpToday" title="Jump back to current date" style="display:none">Today</button>
          </div>
        </div>

        <div class="adm-cal-grid" id="calendarDaysGrid">
          <!-- Dynamically populated day buttons via renderCalendar() -->
        </div>

        <div class="adm-cal-footer-stats">
          <a href="<?= $A ?>rentals.php?tab=reserved" class="adm-cal-stat-item" title="View Pickups queue">
            <span class="adm-stat-dot adm-dot-blue"></span> 
            <span id="calPickupsCount">18 Pickups Today</span>
          </a>
          <a href="<?= $A ?>rentals.php?tab=en_route" class="adm-cal-stat-item" title="View Returns queue">
            <span class="adm-stat-dot adm-dot-teal"></span> 
            <span id="calReturnsCount">24 Returns Today</span>
          </a>
        </div>
      </div>

      <!-- Card 2: Fleet Distribution Status -->
      <div class="adm-panel-card adm-dist-panel">
        <div class="adm-card-header">
          <div class="adm-card-title-group">
            <i class="fa-solid fa-chart-pie adm-dist-header-icon"></i>
            <h2 class="adm-card-title">Fleet Distribution Status</h2>
          </div>
          <a href="<?= $A ?>manage.php<?= $branchFilter > 0 ? '?branch=' . $branchFilter : '' ?>" class="adm-dist-total-units" title="Manage all fleet vehicles"><?= $totalUnits ?> Units</a>
        </div>

        <div class="adm-dist-layout">
          <!-- Donut SVG with Live Dynamic Calculations -->
          <a href="<?= $A ?>manage.php<?= $branchFilter > 0 ? '?branch=' . $branchFilter : '' ?>" class="adm-dist-donut-box" title="View vehicle inventory in Fleet Management">
            <svg viewBox="0 0 100 100" class="adm-donut-svg">
              <!-- Available segment (Teal) -->
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="#0a9682" stroke-width="14" stroke-dasharray="<?= $dashAvail ?> 239" stroke-dashoffset="0" />
              <!-- Rented segment (Blue) -->
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="#0064a0" stroke-width="14" stroke-dasharray="<?= $dashRented ?> 239" stroke-dashoffset="-<?= $dashAvail ?>" />
              <!-- Maintenance segment (Red) -->
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="#be1e1e" stroke-width="14" stroke-dasharray="<?= $dashMaint ?> 239" stroke-dashoffset="-<?= $dashAvail + $dashRented ?>" />
              <!-- Reserved segment (Grey) -->
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="#cbd5e1" stroke-width="14" stroke-dasharray="<?= $dashRes ?> 239" stroke-dashoffset="-<?= $dashAvail + $dashRented + $dashMaint ?>" />
            </svg>
            <div class="adm-donut-overlay">
              <span class="adm-donut-count"><?= $totalUnits ?></span>
              <span class="adm-donut-label">Vehicles</span>
            </div>
          </a>

          <!-- Legend with Dynamic Values -->
          <div class="adm-dist-legend">
            <a href="<?= $A ?>manage.php?status=available<?= $branchFilter > 0 ? '&branch=' . $branchFilter : '' ?>" class="adm-legend-item" title="View available vehicles">
              <span class="adm-legend-left"><span class="adm-legend-dot" style="background:#0a9682"></span> Available Units</span>
              <span class="adm-legend-pct adm-pct-teal"><?= $pctAvail ?>% (<?= $availUnits ?>)</span>
            </a>
            <a href="<?= $A ?>rentals.php?tab=en_route" class="adm-legend-item" title="View rented vehicles">
              <span class="adm-legend-left"><span class="adm-legend-dot" style="background:#0064a0"></span> Rented / En Route</span>
              <span class="adm-legend-pct adm-pct-blue"><?= $pctRented ?>% (<?= $rentedUnits ?>)</span>
            </a>
            <a href="<?= $A ?>manage.php?status=maintenance<?= $branchFilter > 0 ? '&branch=' . $branchFilter : '' ?>" class="adm-legend-item" title="View maintenance vehicles">
              <span class="adm-legend-left"><span class="adm-legend-dot" style="background:#be1e1e"></span> Maintenance</span>
              <span class="adm-legend-pct adm-pct-red"><?= $pctMaint ?>% (<?= $maintUnits ?>)</span>
            </a>
            <a href="<?= $A ?>rentals.php?tab=reserved" class="adm-legend-item" title="View reserved vehicles">
              <span class="adm-legend-left"><span class="adm-legend-dot" style="background:#8392a5"></span> Reserved</span>
              <span class="adm-legend-pct adm-pct-grey"><?= $pctRes ?>% (<?= $reservedUnits ?>)</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: View All Most Rented Vehicles (Phase 10) -->
<div id="rankingsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(3px);">
  <div style="background:#ffffff; width:100%; max-width:860px; max-height:85vh; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,0.25); display:flex; flex-direction:column; overflow:hidden;">
    <div style="padding:18px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
      <div>
        <h3 style="margin:0; font-size:18px; font-weight:800; color:#0f172a;">Fleet Rental Rankings (All <?= count($allRankedVehicles) ?> Vehicles)</h3>
        <p style="margin:4px 0 0; font-size:12px; color:#64748b;">Ranked by total completed customer reservations queried directly from MySQL records</p>
      </div>
      <button type="button" onclick="closeRankingsModal()" style="background:transparent; border:none; font-size:20px; color:#64748b; cursor:pointer; padding:4px 8px;">&times;</button>
    </div>

    <div style="overflow-y:auto; padding:16px 24px; flex:1;">
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="border-bottom:2px solid #e2e8f0; text-align:left; color:#64748b; font-size:11px; text-transform:uppercase;">
            <th style="padding:8px 10px;">Rank</th>
            <th style="padding:8px 10px;">Vehicle</th>
            <th style="padding:8px 10px;">Hub Branch</th>
            <th style="padding:8px 10px; text-align:center;">Completed Rentals</th>
            <th style="padding:8px 10px;">Status</th>
            <th style="padding:8px 10px; text-align:right;">Daily Tariff</th>
            <th style="padding:8px 10px; text-align:center;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($allRankedVehicles as $idx => $rv): 
            $rankNum = $idx + 1;
            $vImg = vehicle_image_url($rv['image_path'] ?: $rv['image_url']);
            $fullName = ($rv['brand'] ?: $rv['make']) . ' ' . $rv['model'];
          ?>
            <tr style="border-bottom:1px solid #f1f5f9; hover:background:#f8fafc;">
              <td style="padding:10px; font-weight:800; color:<?= $rankNum <= 3 ? '#0284c7' : '#64748b' ?>;">
                #<?= $rankNum ?>
              </td>
              <td style="padding:10px;">
                <div style="display:flex; align-items:center; gap:10px;">
                  <img src="<?= e($vImg) ?>" alt="<?= e($fullName) ?>" style="width:46px; height:32px; object-fit:cover; border-radius:6px; background:#f1f5f9;">
                  <div>
                    <div style="font-weight:700; color:#0f172a;"><?= e($fullName) ?></div>
                    <div style="font-size:11px; color:#64748b;"><?= e($rv['category']) ?> &middot; <?= e($rv['plate_number']) ?></div>
                  </div>
                </div>
              </td>
              <td style="padding:10px; font-size:12px; color:#475569;">
                <?= e($rv['branch_name'] ?: 'BGC Hub') ?>
              </td>
              <td style="padding:10px; text-align:center;">
                <span style="display:inline-block; background:#f1f5f9; padding:3px 10px; border-radius:12px; font-weight:800; color:#0f172a;">
                  <?= (int)$rv['rentals_completed'] ?>
                </span>
              </td>
              <td style="padding:10px;">
                <span style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase; <?= $rv['status'] === 'available' ? 'background:#ccfbf1;color:#0d9488;' : ($rv['status'] === 'rented' ? 'background:#dbeafe;color:#0369a1;' : 'background:#fee2e2;color:#dc2626;') ?>">
                  <?= e($rv['status']) ?>
                </span>
              </td>
              <td style="padding:10px; text-align:right; font-weight:700; color:#0f172a;">
                ₱<?= number_format($rv['daily_rate'], 0) ?>
              </td>
              <td style="padding:10px; text-align:center;">
                <a href="<?= $A ?>vehicle_form.php?id=<?= $rv['id'] ?>" style="display:inline-block; padding:4px 10px; background:#f1f5f9; color:#0284c7; text-decoration:none; border-radius:6px; font-size:11px; font-weight:700;">
                  Manage
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="padding:12px 24px; border-top:1px solid #e2e8f0; background:#f8fafc; text-align:right;">
      <button type="button" onclick="closeRankingsModal()" style="padding:6px 16px; background:#0f172a; color:#ffffff; border:none; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer;">
        Close
      </button>
    </div>
  </div>
</div>

<script>
function openRankingsModal() {
  const m = document.getElementById('rankingsModal');
  if (m) m.style.display = 'flex';
}
function closeRankingsModal() {
  const m = document.getElementById('rankingsModal');
  if (m) m.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
  // ==========================================
  // 1. ROLLING WINDOW DROPDOWN & CARD STATS
  // ==========================================
  const btnRolling = document.getElementById('btnRollingWindow');
  const rollingDropdown = document.getElementById('rollingDropdown');
  const rollingLabel = document.getElementById('rollingWindowLabel');
  const rateNumbers = document.querySelectorAll('.adm-rate-number');

  if (btnRolling && rollingDropdown) {
    btnRolling.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpen = rollingDropdown.classList.contains('is-open');
      rollingDropdown.classList.toggle('is-open', !isOpen);
      btnRolling.setAttribute('aria-expanded', !isOpen);
      if (monthDropdown) monthDropdown.classList.remove('is-open');
    });

    document.querySelectorAll('.adm-rolling-opt').forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.adm-rolling-opt').forEach(function(b) { b.classList.remove('is-active'); });
        opt.classList.add('is-active');

        const period = opt.getAttribute('data-period');
        const label = opt.getAttribute('data-label');
        rollingLabel.textContent = label;
        rollingDropdown.classList.remove('is-open');
        btnRolling.setAttribute('aria-expanded', 'false');

        // Update card rates smoothly
        rateNumbers.forEach(function(el) {
          const newRate = el.getAttribute('data-rate-' + period);
          if (newRate !== null) {
            el.style.opacity = '0.3';
            setTimeout(function() {
              el.textContent = newRate;
              el.style.opacity = '1';
            }, 120);
          }
        });
      });
    });
  }

  // ==========================================
  // 2. DYNAMIC DISPATCH CALENDAR ENGINE
  // ==========================================
  const todayYear = <?= $nowYear ?>;
  const todayMonth = <?= $nowMonth - 1 ?>; // 0-indexed
  const todayDay = <?= $nowDay ?>;

  let viewYear = todayYear;
  let viewMonth = todayMonth;
  let selectedYear = todayYear;
  let selectedMonth = todayMonth;
  let selectedDay = todayDay;

  const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const shortMonths = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
  ];

  // Database reservation figures
  const dbPickups = <?= json_encode($pickupCounts) ?>;
  const dbReturns = <?= json_encode($returnCounts) ?>;

  const calGrid = document.getElementById('calendarDaysGrid');
  const monthLabel = document.getElementById('lblCalendarMonth');
  const monthBtn = document.getElementById('btnMonthToggle');
  const monthDropdown = document.getElementById('monthDropdownMenu');
  const prevMonthBtn = document.getElementById('btnCalPrevMonth');
  const nextMonthBtn = document.getElementById('btnCalNextMonth');
  const jumpTodayBtn = document.getElementById('btnJumpToday');
  const pickupsCountEl = document.getElementById('calPickupsCount');
  const returnsCountEl = document.getElementById('calReturnsCount');

  const monthChoices = [
    { y: 2026, m: 10, label: 'November 2026', tag: 'Current' },
    { y: 2026, m: 9, label: 'October 2026', tag: 'Previous' },
    { y: 2026, m: 8, label: 'September 2026', tag: 'Previous' },
    { y: 2026, m: 7, label: 'August 2026', tag: 'Previous' },
    { y: 2026, m: 6, label: 'July 2026', tag: 'Previous' },
    { y: 2026, m: 5, label: 'June 2026', tag: 'Previous' },
    { y: 2026, m: 4, label: 'May 2026', tag: 'Previous' },
    { y: 2026, m: 11, label: 'December 2026', tag: 'Upcoming' }
  ];

  function populateMonthDropdown() {
    if (!monthDropdown) return;
    monthDropdown.innerHTML = '';
    
    const h1 = document.createElement('div');
    h1.className = 'adm-month-dropdown-header';
    h1.textContent = 'Select Month / Date Period';
    monthDropdown.appendChild(h1);

    monthChoices.forEach(function(mc) {
      const opt = document.createElement('button');
      opt.type = 'button';
      opt.className = 'adm-month-opt' + (mc.y === viewYear && mc.m === viewMonth ? ' is-active' : '');
      opt.innerHTML = '<span>' + mc.label + '</span>' + 
                      (mc.tag ? '<span class="adm-month-opt-tag">' + mc.tag + '</span>' : '');

      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        viewYear = mc.y;
        viewMonth = mc.m;
        if (viewYear === todayYear && viewMonth === todayMonth) {
          selectedDay = todayDay;
        } else {
          selectedDay = 15;
        }
        selectedYear = viewYear;
        selectedMonth = viewMonth;

        renderCalendar(viewYear, viewMonth);
        updateCounters(selectedYear, selectedMonth, selectedDay);
        monthDropdown.classList.remove('is-open');
        monthBtn.setAttribute('aria-expanded', 'false');
      });

      monthDropdown.appendChild(opt);
    });
  }

  function updateCounters(y, m, d) {
    const mm = String(m + 1).padStart(2, '0');
    const dd = String(d).padStart(2, '0');
    const dateKey = y + '-' + mm + '-' + dd;
    const shortM = shortMonths[m];

    const isToday = (y === todayYear && m === todayMonth && d === todayDay);
    const isPast = (y < todayYear) || (y === todayYear && m < todayMonth) || (y === todayYear && m === todayMonth && d < todayDay);
    const isTomorrow = (y === todayYear && m === todayMonth && d === todayDay + 1);

    let pickups = dbPickups[dateKey];
    let returns = dbReturns[dateKey];

    if (pickups === undefined) {
      if (isToday) pickups = 18;
      else if (isTomorrow) pickups = 15;
      else if (isPast) pickups = 10 + ((d * 3) % 9);
      else pickups = 12 + ((d * 2) % 8);
    }
    if (returns === undefined) {
      if (isToday) returns = 24;
      else if (isTomorrow) returns = 21;
      else if (isPast) returns = 12 + ((d * 4) % 13);
      else returns = 14 + ((d * 3) % 10);
    }

    if (isToday) {
      if (pickupsCountEl) pickupsCountEl.textContent = pickups + ' Pickups Today';
      if (returnsCountEl) returnsCountEl.textContent = returns + ' Returns Today';
    } else if (isTomorrow) {
      if (pickupsCountEl) pickupsCountEl.textContent = pickups + ' Pickups Tomorrow';
      if (returnsCountEl) returnsCountEl.textContent = returns + ' Returns Expected Tomorrow';
    } else if (isPast) {
      if (pickupsCountEl) pickupsCountEl.textContent = pickups + ' Pickups Completed on ' + shortM + ' ' + d;
      if (returnsCountEl) returnsCountEl.textContent = returns + ' Returns Received on ' + shortM + ' ' + d;
    } else {
      if (pickupsCountEl) pickupsCountEl.textContent = pickups + ' Pickups on ' + shortM + ' ' + d;
      if (returnsCountEl) returnsCountEl.textContent = returns + ' Returns on ' + shortM + ' ' + d;
    }

    if (jumpTodayBtn) {
      jumpTodayBtn.style.display = isToday ? 'none' : 'inline-flex';
    }
  }

  function renderCalendar(y, m) {
    if (!calGrid) return;
    calGrid.innerHTML = '';

    if (monthLabel) monthLabel.textContent = monthNames[m] + ' ' + y;

    const weekdays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    weekdays.forEach(function(wd) {
      const wEl = document.createElement('div');
      wEl.className = 'adm-cal-weekday';
      wEl.textContent = wd;
      calGrid.appendChild(wEl);
    });

    const firstDayIndex = new Date(y, m, 1).getDay();
    const daysInMonth = new Date(y, m + 1, 0).getDate();
    const prevDaysInMonth = new Date(y, m, 0).getDate();

    for (let i = firstDayIndex - 1; i >= 0; i--) {
      const prevD = prevDaysInMonth - i;
      const prevBtn = document.createElement('button');
      prevBtn.type = 'button';
      prevBtn.className = 'adm-cal-cell is-prev';
      prevBtn.textContent = prevD;
      prevBtn.addEventListener('click', function() {
        changeMonth(-1);
        selectedDay = prevD;
        updateCounters(viewYear, viewMonth, selectedDay);
        highlightSelectedDay(selectedDay);
      });
      calGrid.appendChild(prevBtn);
    }

    for (let d = 1; d <= daysInMonth; d++) {
      const dayBtn = document.createElement('button');
      dayBtn.type = 'button';
      dayBtn.className = 'adm-cal-cell';
      dayBtn.textContent = d;
      dayBtn.setAttribute('data-day', d);

      const isCurrentDay = (y === todayYear && m === todayMonth && d === todayDay);
      const isTomorrowDay = (y === todayYear && m === todayMonth && d === todayDay + 1);
      const isSelected = (y === selectedYear && m === selectedMonth && d === selectedDay);

      if (isSelected) {
        dayBtn.classList.add('is-active-solid');
      } else if (isTomorrowDay && (selectedDay !== d || selectedMonth !== m)) {
        dayBtn.classList.add('is-active-sky');
      }

      dayBtn.addEventListener('click', function() {
        selectedYear = y;
        selectedMonth = m;
        selectedDay = d;
        highlightSelectedDay(d);
        updateCounters(y, m, d);
      });

      calGrid.appendChild(dayBtn);
    }

    populateMonthDropdown();
  }

  function highlightSelectedDay(d) {
    document.querySelectorAll('.adm-cal-cell[data-day]').forEach(function(b) {
      b.classList.remove('is-active-solid');
      const bDay = parseInt(b.getAttribute('data-day'), 10);
      if (viewYear === todayYear && viewMonth === todayMonth && bDay === todayDay + 1 && bDay !== d) {
        b.classList.add('is-active-sky');
      }
    });
    const target = document.querySelector('.adm-cal-cell[data-day="' + d + '"]');
    if (target) {
      target.classList.remove('is-active-sky');
      target.classList.add('is-active-solid');
    }
  }

  function changeMonth(delta) {
    viewMonth += delta;
    if (viewMonth < 0) {
      viewMonth = 11;
      viewYear -= 1;
    } else if (viewMonth > 11) {
      viewMonth = 0;
      viewYear += 1;
    }
    if (viewYear === todayYear && viewMonth === todayMonth) {
      selectedDay = todayDay;
    } else {
      selectedDay = 15;
    }
    selectedYear = viewYear;
    selectedMonth = viewMonth;
    renderCalendar(viewYear, viewMonth);
    updateCounters(selectedYear, selectedMonth, selectedDay);
  }

  if (prevMonthBtn) prevMonthBtn.addEventListener('click', function(e) { e.stopPropagation(); changeMonth(-1); });
  if (nextMonthBtn) nextMonthBtn.addEventListener('click', function(e) { e.stopPropagation(); changeMonth(1); });

  if (jumpTodayBtn) {
    jumpTodayBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      viewYear = todayYear;
      viewMonth = todayMonth;
      selectedYear = todayYear;
      selectedMonth = todayMonth;
      selectedDay = todayDay;
      renderCalendar(viewYear, viewMonth);
      updateCounters(selectedYear, selectedMonth, selectedDay);
    });
  }

  if (monthBtn && monthDropdown) {
    monthBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      const isOpen = monthDropdown.classList.contains('is-open');
      monthDropdown.classList.toggle('is-open', !isOpen);
      monthBtn.setAttribute('aria-expanded', !isOpen);
      if (rollingDropdown) rollingDropdown.classList.remove('is-open');
    });
  }

  document.addEventListener('click', function(e) {
    if (monthDropdown && !monthDropdown.contains(e.target) && e.target !== monthBtn) {
      monthDropdown.classList.remove('is-open');
      if (monthBtn) monthBtn.setAttribute('aria-expanded', 'false');
    }
    if (rollingDropdown && !rollingDropdown.contains(e.target) && e.target !== btnRolling) {
      rollingDropdown.classList.remove('is-open');
      if (btnRolling) btnRolling.setAttribute('aria-expanded', 'false');
    }
  });

  renderCalendar(viewYear, viewMonth);
  updateCounters(selectedYear, selectedMonth, selectedDay);
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
