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

    <form method="get" action="<?= BASE_URL ?>customer/browse.php" id="filterModalForm">
      <div class="filter-modal-body">
        <!-- 1. Vehicle Type -->
        <div>
          <label class="filter-group-lbl">Vehicle Type</label>
          <div class="pill-options-grid" id="typePills">
            <button type="button" class="filter-pill active-blue" data-type="all">All</button>
            <button type="button" class="filter-pill" data-type="sedan">Sedan</button>
            <button type="button" class="filter-pill" data-type="suv">SUV</button>
            <button type="button" class="filter-pill" data-type="van">MPV / Van</button>
            <button type="button" class="filter-pill" data-type="crossover">Crossover</button>
            <button type="button" class="filter-pill" data-type="pickup">Pickup</button>
          </div>
          <input type="hidden" name="type" id="filterTypeInput" value="all">
        </div>

        <!-- 2. Transmission -->
        <div>
          <label class="filter-group-lbl">Transmission</label>
          <div class="pill-options-grid" id="transPills" style="grid-template-columns: repeat(3, 1fr);">
            <button type="button" class="filter-pill active-navy" data-trans="all">All</button>
            <button type="button" class="filter-pill" data-trans="Automatic">Automatic</button>
            <button type="button" class="filter-pill" data-trans="Manual">Manual</button>
          </div>
          <input type="hidden" name="transmission" id="filterTransInput" value="all">
        </div>

        <!-- 3. Price Range -->
        <div>
          <div class="filter-group-lbl">
            <span>Price Range</span>
            <small>Per Day (PHP)</small>
          </div>
          <div class="price-inputs-row">
            <div class="price-input-box">
              <span class="price-box-lbl">Min Price</span>
              <select name="min_price" id="filterMinPrice">
                <option value="1000">₱ 1,000</option>
                <option value="1500" selected>₱ 1,500</option>
                <option value="2000">₱ 2,000</option>
                <option value="3000">₱ 3,000</option>
                <option value="4000">₱ 4,000</option>
              </select>
            </div>
            <div class="price-input-box">
              <span class="price-box-lbl">Max Price</span>
              <select name="max_price" id="filterMaxPrice">
                <option value="3500">₱ 3,500</option>
                <option value="5000">₱ 5,000</option>
                <option value="6000" selected>₱ 6,000</option>
                <option value="8000">₱ 8,000</option>
                <option value="10000">₱ 10,000+</option>
              </select>
            </div>
          </div>
          <div class="range-slider-wrap">
            <div class="range-bar-track">
              <div class="range-bar-fill" id="sliderFill"></div>
            </div>
            <div class="range-endpoints">
              <span>₱1,000</span>
              <span>₱3,500</span>
              <span>₱10,000+</span>
            </div>
          </div>
        </div>

        <!-- 4. Brand -->
        <div>
          <label class="filter-group-lbl">Brand</label>
          <div class="brand-select-box">
            <select name="brand" id="filterBrand">
              <option value="all">All Brands (Toyota, Honda, Mitsubishi, Ford...)</option>
              <option value="Toyota">Toyota</option>
              <option value="Honda">Honda</option>
              <option value="Mitsubishi">Mitsubishi</option>
              <option value="Ford">Ford</option>
              <option value="Mazda">Mazda</option>
              <option value="Hyundai">Hyundai</option>
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
