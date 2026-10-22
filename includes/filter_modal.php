<?php
// Filter Modal matching Image 1
?>
<div class="modal-backdrop" id="filterModalBackdrop" role="dialog" aria-modal="true" aria-labelledby="filterModalTitle">
  <div class="filter-dialog">
    <div class="filter-modal-head">
      <h3 class="filter-modal-title" id="filterModalTitle">
        <i class="fa-solid fa-sliders"></i> Filter
      </h3>
      <button class="filter-close-x" id="closeFilterModal" type="button" aria-label="Close Filter">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <?php 
    $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
    $filterAction = ($currentScript === 'browse.php') ? (BASE_URL . 'customer/browse.php') : (BASE_URL . 'customer/dashboard.php');
    
    $activeType = $_GET['type'] ?? 'all';
    $activeTrans = $_GET['transmission'] ?? 'all';
    $activeMinPrice = !empty($_GET['min_price']) ? (int)$_GET['min_price'] : 1500;
    $activeMaxPrice = !empty($_GET['max_price']) ? (int)$_GET['max_price'] : 6000;
    $activeBrand = $_GET['brand'] ?? 'all';
    $activeHub = $_GET['hub'] ?? $_GET['location'] ?? 'bgc';
    $activeQ = $_GET['q'] ?? '';

    // Calculate percentage fill for initial track styling
    $minFillPct = max(0, min(100, (($activeMinPrice - 1000) / 9000) * 100));
    $widthFillPct = max(0, min(100, (($activeMaxPrice - $activeMinPrice) / 9000) * 100));
    ?>
    <form method="get" action="<?= $filterAction ?>" id="filterModalForm">
      <?php if (!empty($activeHub)): ?>
        <input type="hidden" name="hub" value="<?= htmlspecialchars($activeHub, ENT_QUOTES, 'UTF-8') ?>">
      <?php endif; ?>
      <?php if (!empty($activeQ)): ?>
        <input type="hidden" name="q" value="<?= htmlspecialchars($activeQ, ENT_QUOTES, 'UTF-8') ?>">
      <?php endif; ?>

      <div class="filter-modal-body">
        <!-- 1. Vehicle Type -->
        <div>
          <label class="filter-group-lbl">Vehicle Type</label>
          <div class="pill-options-grid" id="typePills">
            <?php
            $types = [
                'all' => 'All',
                'sedan' => 'Sedan',
                'suv' => 'SUV',
                'van' => 'MPV / Van',
                'crossover' => 'Crossover',
                'pickup' => 'Pickup'
            ];
            foreach ($types as $tKey => $tLabel):
                $isSel = ($activeType === $tKey);
            ?>
              <button type="button" class="filter-pill <?= $isSel ? 'active-blue' : '' ?>" data-type="<?= $tKey ?>"><?= $tLabel ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="type" id="filterTypeInput" value="<?= htmlspecialchars($activeType, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- 2. Transmission -->
        <div>
          <label class="filter-group-lbl">Transmission</label>
          <div class="pill-options-grid" id="transPills" style="grid-template-columns: repeat(3, 1fr);">
            <?php
            $transList = [
                'all' => 'All',
                'Automatic' => 'Automatic',
                'Manual' => 'Manual'
            ];
            foreach ($transList as $trKey => $trLabel):
                $isSel = (strcasecmp($activeTrans, $trKey) === 0);
            ?>
              <button type="button" class="filter-pill <?= $isSel ? 'active-navy' : '' ?>" data-trans="<?= $trKey ?>"><?= $trLabel ?></button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="transmission" id="filterTransInput" value="<?= htmlspecialchars($activeTrans, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- 3. Price Range (Functional & Slidable matching Image) -->
        <div>
          <div class="filter-group-lbl">
            <span>Price Range</span>
            <small style="color: #0284c7; font-weight: 600;">Per Day (PHP)</small>
          </div>
          <div class="price-inputs-row">
            <div class="price-input-box">
              <span class="price-box-lbl">Min Price</span>
              <div class="price-select-wrap">
                <span class="price-display-val" id="minPriceDisplay">₱ <?= number_format($activeMinPrice) ?></span>
                <select name="min_price" id="filterMinPrice" class="price-select-overlay" aria-label="Select Min Price">
                  <?php 
                    $minPresets = [1000, 1250, 1500, 1750, 2000, 2250, 2500, 2750, 3000, 3500, 4000, 4500, 5000];
                    if (!in_array($activeMinPrice, $minPresets)) { $minPresets[] = $activeMinPrice; sort($minPresets); }
                    foreach ($minPresets as $pVal):
                  ?>
                    <option value="<?= $pVal ?>" <?= $activeMinPrice === $pVal ? 'selected' : '' ?>>₱ <?= number_format($pVal) ?></option>
                  <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down select-chevron"></i>
              </div>
            </div>
            <div class="price-input-box">
              <span class="price-box-lbl">Max Price</span>
              <div class="price-select-wrap">
                <span class="price-display-val" id="maxPriceDisplay">₱ <?= number_format($activeMaxPrice) ?><?= $activeMaxPrice >= 10000 ? '+' : '' ?></span>
                <select name="max_price" id="filterMaxPrice" class="price-select-overlay" aria-label="Select Max Price">
                  <?php 
                    $maxPresets = [2000, 2500, 3000, 3500, 4000, 4500, 5000, 5500, 6000, 6500, 7000, 7500, 8000, 8500, 9000, 9500, 10000];
                    if (!in_array($activeMaxPrice, $maxPresets)) { $maxPresets[] = $activeMaxPrice; sort($maxPresets); }
                    foreach ($maxPresets as $pVal):
                  ?>
                    <option value="<?= $pVal ?>" <?= $activeMaxPrice === $pVal ? 'selected' : '' ?>>₱ <?= number_format($pVal) ?><?= $pVal >= 10000 ? '+' : '' ?></option>
                  <?php endforeach; ?>
                </select>
                <i class="fa-solid fa-chevron-down select-chevron"></i>
              </div>
            </div>
          </div>
          <div class="range-slider-wrap">
            <div class="range-slider-container" id="rangeSliderContainer">
              <div class="range-bar-track" id="rangeBarTrack">
                <div class="range-bar-fill" id="sliderFill" style="left: <?= number_format($minFillPct, 2, '.', '') ?>%; width: <?= number_format($widthFillPct, 2, '.', '') ?>%;"></div>
              </div>
              <input type="range" class="range-slider-input" id="rangeMinSlider" min="1000" max="10000" step="250" value="<?= $activeMinPrice ?>" aria-label="Minimum Daily Price">
              <input type="range" class="range-slider-input" id="rangeMaxSlider" min="1000" max="10000" step="250" value="<?= $activeMaxPrice ?>" aria-label="Maximum Daily Price">
            </div>
            <div class="range-endpoints">
              <span>₱1,000</span>
              <span>₱5,500</span>
              <span>₱10,000+</span>
            </div>
          </div>
        </div>

        <!-- 4. Brand -->
        <div>
          <label class="filter-group-lbl">Brand</label>
          <div class="brand-select-box">
            <select name="brand" id="filterBrand">
              <option value="all" <?= $activeBrand === 'all' ? 'selected' : '' ?>>All Brands (Toyota, Honda, Mitsubishi, Ford...)</option>
              <option value="Toyota" <?= $activeBrand === 'Toyota' ? 'selected' : '' ?>>Toyota</option>
              <option value="Honda" <?= $activeBrand === 'Honda' ? 'selected' : '' ?>>Honda</option>
              <option value="Mitsubishi" <?= $activeBrand === 'Mitsubishi' ? 'selected' : '' ?>>Mitsubishi</option>
              <option value="Ford" <?= $activeBrand === 'Ford' ? 'selected' : '' ?>>Ford</option>
              <option value="Mazda" <?= $activeBrand === 'Mazda' ? 'selected' : '' ?>>Mazda</option>
              <option value="Hyundai" <?= $activeBrand === 'Hyundai' ? 'selected' : '' ?>>Hyundai</option>
            </select>
            <i class="fa-solid fa-chevron-down select-chevron"></i>
          </div>
        </div>

        <!-- 5. Actions -->
        <div>
          <button type="submit" class="btn-apply-filters">
            <i class="fa-solid fa-check"></i> Apply Filters
          </button>
          <button type="button" class="btn-reset-filters" id="resetFiltersBtn">
            Reset All Filters
          </button>
        </div>
      </div>

      <div class="filter-modal-foot">
        <div>
          <span class="status-dot-green"></span> Filtering across 42 Fleet Hubs
        </div>
        <div>
          DriveSphere v2.4
        </div>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
    function initFilterModalScripts() {
        var backdrop = document.getElementById('filterModalBackdrop');
        var closeBtn = document.getElementById('closeFilterModal');
        var toggleBtn = document.getElementById('filterToggleBtn');
        var toggleBtn2 = document.getElementById('filterToggleBtn2');
        var minSlider = document.getElementById('rangeMinSlider');
        var maxSlider = document.getElementById('rangeMaxSlider');
        var sliderFill = document.getElementById('sliderFill');
        var minDisplay = document.getElementById('minPriceDisplay');
        var maxDisplay = document.getElementById('maxPriceDisplay');
        var minSelect = document.getElementById('filterMinPrice');
        var maxSelect = document.getElementById('filterMaxPrice');
        var sliderContainer = document.getElementById('rangeSliderContainer');
        var resetBtn = document.getElementById('resetFiltersBtn');
        var typePills = document.querySelectorAll('#typePills .filter-pill');
        var typeInput = document.getElementById('filterTypeInput');
        var transPills = document.querySelectorAll('#transPills .filter-pill');
        var transInput = document.getElementById('filterTransInput');
        var brandSelect = document.getElementById('filterBrand');

        var MIN_LIMIT = 1000;
        var MAX_LIMIT = 10000;
        var STEP_GAP = 250;

        function openModal() {
            if (backdrop) {
                backdrop.classList.add('open');
                backdrop.classList.add('active');
            }
            updateSliderPositions();
        }

        function closeModal() {
            if (backdrop) {
                backdrop.classList.remove('open');
                backdrop.classList.remove('active');
            }
        }

        if (toggleBtn) toggleBtn.addEventListener('click', openModal);
        if (toggleBtn2) toggleBtn2.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backdrop) {
            backdrop.addEventListener('click', function(e) {
                if (e.target === backdrop) closeModal();
            });
        }

        // Pill interactions
        if (typePills.length && typeInput) {
            typePills.forEach(function(pill) {
                pill.addEventListener('click', function() {
                    typePills.forEach(function(p) { p.classList.remove('active-blue'); });
                    pill.classList.add('active-blue');
                    typeInput.value = pill.getAttribute('data-type');
                });
            });
        }

        if (transPills.length && transInput) {
            transPills.forEach(function(pill) {
                pill.addEventListener('click', function() {
                    transPills.forEach(function(p) { p.classList.remove('active-navy'); });
                    pill.classList.add('active-navy');
                    transInput.value = pill.getAttribute('data-trans');
                });
            });
        }

        function formatPHP(val, isMax) {
            return '₱ ' + Number(val).toLocaleString() + (isMax && val >= MAX_LIMIT ? '+' : '');
        }

        function syncSelectWithVal(selectEl, val, isMax) {
            if (!selectEl) return;
            var valStr = String(val);
            var exists = false;
            for (var i = 0; i < selectEl.options.length; i++) {
                if (selectEl.options[i].value === valStr) {
                    selectEl.selectedIndex = i;
                    exists = true;
                    break;
                }
            }
            if (!exists) {
                var opt = new Option(formatPHP(val, isMax), valStr, true, true);
                var inserted = false;
                for (var j = 0; j < selectEl.options.length; j++) {
                    if (parseInt(selectEl.options[j].value, 10) > val) {
                        selectEl.add(opt, selectEl.options[j]);
                        inserted = true;
                        break;
                    }
                }
                if (!inserted) selectEl.add(opt);
                selectEl.value = valStr;
            }
        }

        function updateSliderPositions(activeHandle) {
            if (!minSlider || !maxSlider || !sliderFill) return;
            var minVal = parseInt(minSlider.value, 10);
            var maxVal = parseInt(maxSlider.value, 10);

            if (activeHandle === 'min' && minVal >= maxVal - STEP_GAP) {
                minVal = maxVal - STEP_GAP;
                minSlider.value = minVal;
            } else if (activeHandle === 'max' && maxVal <= minVal + STEP_GAP) {
                maxVal = minVal + STEP_GAP;
                maxSlider.value = maxVal;
            } else if (minVal >= maxVal) {
                minVal = maxVal - STEP_GAP;
                minSlider.value = minVal;
            }

            var leftPct = ((minVal - MIN_LIMIT) / (MAX_LIMIT - MIN_LIMIT)) * 100;
            var widthPct = ((maxVal - minVal) / (MAX_LIMIT - MIN_LIMIT)) * 100;

            sliderFill.style.left = leftPct + '%';
            sliderFill.style.width = widthPct + '%';

            if (minDisplay) minDisplay.textContent = formatPHP(minVal, false);
            if (maxDisplay) maxDisplay.textContent = formatPHP(maxVal, true);

            syncSelectWithVal(minSelect, minVal, false);
            syncSelectWithVal(maxSelect, maxVal, true);
        }

        if (minSlider && maxSlider) {
            // Live 60fps drag listeners
            minSlider.addEventListener('input', function() {
                updateSliderPositions('min');
            });

            maxSlider.addEventListener('input', function() {
                updateSliderPositions('max');
            });

            // Handle hovering so user can seamlessly grab either handle when close
            if (sliderContainer) {
                sliderContainer.addEventListener('mousemove', function(e) {
                    var rect = sliderContainer.getBoundingClientRect();
                    var x = e.clientX - rect.left;
                    var pct = Math.max(0, Math.min(1, x / rect.width));
                    var hoverVal = MIN_LIMIT + pct * (MAX_LIMIT - MIN_LIMIT);
                    var minVal = parseInt(minSlider.value, 10);
                    var maxVal = parseInt(maxSlider.value, 10);

                    if (Math.abs(hoverVal - minVal) < Math.abs(hoverVal - maxVal)) {
                        minSlider.style.zIndex = '5';
                        maxSlider.style.zIndex = '3';
                    } else {
                        maxSlider.style.zIndex = '5';
                        minSlider.style.zIndex = '3';
                    }
                });
            }

            minSlider.addEventListener('mousedown', function() { minSlider.style.zIndex = '10'; maxSlider.style.zIndex = '3'; });
            maxSlider.addEventListener('mousedown', function() { maxSlider.style.zIndex = '10'; minSlider.style.zIndex = '3'; });
            minSlider.addEventListener('touchstart', function() { minSlider.style.zIndex = '10'; maxSlider.style.zIndex = '3'; }, { passive: true });
            maxSlider.addEventListener('touchstart', function() { maxSlider.style.zIndex = '10'; minSlider.style.zIndex = '3'; }, { passive: true });

            if (minSelect) {
                minSelect.addEventListener('change', function() {
                    var v = parseInt(this.value, 10) || MIN_LIMIT;
                    var maxV = parseInt(maxSlider.value, 10);
                    if (v >= maxV - STEP_GAP) {
                        v = maxV - STEP_GAP;
                        this.value = v;
                    }
                    minSlider.value = v;
                    updateSliderPositions();
                });
            }

            if (maxSelect) {
                maxSelect.addEventListener('change', function() {
                    var v = parseInt(this.value, 10) || MAX_LIMIT;
                    var minV = parseInt(minSlider.value, 10);
                    if (v <= minV + STEP_GAP) {
                        v = minV + STEP_GAP;
                        this.value = v;
                    }
                    maxSlider.value = v;
                    updateSliderPositions();
                });
            }

            if (sliderContainer) {
                sliderContainer.addEventListener('click', function(e) {
                    if (e.target.classList.contains('range-slider-input')) return;
                    var rect = this.getBoundingClientRect();
                    var clickX = e.clientX - rect.left;
                    var pct = Math.max(0, Math.min(1, clickX / rect.width));
                    var clickedVal = Math.round((MIN_LIMIT + pct * (MAX_LIMIT - MIN_LIMIT)) / STEP_GAP) * STEP_GAP;
                    var curMin = parseInt(minSlider.value, 10);
                    var curMax = parseInt(maxSlider.value, 10);

                    if (Math.abs(clickedVal - curMin) < Math.abs(clickedVal - curMax)) {
                        if (clickedVal < curMax - STEP_GAP) {
                            minSlider.value = clickedVal;
                            updateSliderPositions('min');
                        }
                    } else {
                        if (clickedVal > curMin + STEP_GAP) {
                            maxSlider.value = clickedVal;
                            updateSliderPositions('max');
                        }
                    }
                });
            }

            // Reset handler
            if (resetBtn) {
                resetBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (typePills.length) {
                        typePills.forEach(function(p) { p.classList.remove('active-blue'); });
                        typePills[0].classList.add('active-blue');
                        if (typeInput) typeInput.value = 'all';
                    }
                    if (transPills.length) {
                        transPills.forEach(function(p) { p.classList.remove('active-navy'); });
                        transPills[0].classList.add('active-navy');
                        if (transInput) transInput.value = 'all';
                    }
                    if (brandSelect) brandSelect.value = 'all';

                    minSlider.value = 1500;
                    maxSlider.value = 6000;
                    updateSliderPositions();
                });
            }

            updateSliderPositions();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilterModalScripts);
    } else {
        initFilterModalScripts();
    }
})();
</script>
