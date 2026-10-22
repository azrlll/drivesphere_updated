// Small helpers for the customer + admin dashboards.
document.addEventListener('DOMContentLoaded', function () {
  // Account dropdown
  var btn = document.getElementById('accBtn'), menu = document.getElementById('accMenu');
  if (btn && menu) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var isOpen = menu.classList.toggle('open');
      btn.classList.toggle('active', isOpen);
    });

    menu.addEventListener('click', function (e) {
      e.stopPropagation();
    });

    document.addEventListener('click', function () {
      menu.classList.remove('open');
      btn.classList.remove('active');
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        menu.classList.remove('open');
        btn.classList.remove('active');
      }
    });
  }

  // Mobile menu
  var burger = document.getElementById('burger'), links = document.getElementById('navLinks');
  if (burger && links) burger.addEventListener('click', function () { links.classList.toggle('open'); });

  // Modals: <button data-open="id"> ... <button data-close>
  document.querySelectorAll('[data-open]').forEach(function (b) {
    b.addEventListener('click', function () { document.getElementById(b.dataset.open).classList.add('open'); });
  });
  document.querySelectorAll('[data-close]').forEach(function (b) {
    b.addEventListener('click', function () { b.closest('.modal').classList.remove('open'); });
  });
  document.querySelectorAll('.modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('open'); });
  });

  // Confirm before risky actions: <form data-confirm="Are you sure?">
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
  });

  // Print buttons
  document.querySelectorAll('[data-print]').forEach(function (b) { b.addEventListener('click', function () { window.print(); }); });

  // Filter Modal Controller (Matches Image 1)
  var filterBackdrop = document.getElementById('filterModalBackdrop');
  var closeFilterBtn = document.getElementById('closeFilterModal');
  var filterTriggers = document.querySelectorAll('#filterToggleBtn, [data-open="filterModal"], .filter-chip-btn');

  var openFilter = function () {
    if (filterBackdrop) filterBackdrop.classList.add('open');
    if (typeof updateRangeSlider === 'function') updateRangeSlider();
  };
  var closeFilter = function () {
    if (filterBackdrop) filterBackdrop.classList.remove('open');
  };

  filterTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (e) {
      e.preventDefault();
      openFilter();
    });
  });

  if (closeFilterBtn) {
    closeFilterBtn.addEventListener('click', function (e) {
      e.preventDefault();
      closeFilter();
    });
  }

  if (filterBackdrop) {
    filterBackdrop.addEventListener('click', function (e) {
      if (e.target === filterBackdrop) {
        closeFilter();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && filterBackdrop.classList.contains('open')) {
        closeFilter();
      }
    });

    // Type pills selection
    var typePills = document.querySelectorAll('#typePills .filter-pill');
    var typeInput = document.getElementById('filterTypeInput');
    typePills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        typePills.forEach(function (p) { p.classList.remove('active-blue'); });
        pill.classList.add('active-blue');
        if (typeInput) typeInput.value = pill.dataset.type || 'all';
      });
    });

    // Transmission pills selection
    var transPills = document.querySelectorAll('#transPills .filter-pill');
    var transInput = document.getElementById('filterTransInput');
    transPills.forEach(function (pill) {
      pill.addEventListener('click', function () {
        transPills.forEach(function (p) { p.classList.remove('active-navy'); });
        pill.classList.add('active-navy');
        if (transInput) transInput.value = pill.dataset.trans || 'all';
      });
    });

    // ==========================================
    // Range Slider Controller (Slidable & Functional)
    // ==========================================
    var minSlider = document.getElementById('rangeMinSlider');
    var maxSlider = document.getElementById('rangeMaxSlider');
    var sliderFill = document.getElementById('sliderFill');
    var minDisplay = document.getElementById('minPriceDisplay');
    var maxDisplay = document.getElementById('maxPriceDisplay');
    var minSelect = document.getElementById('filterMinPrice');
    var maxSelect = document.getElementById('filterMaxPrice');
    var sliderContainer = document.getElementById('rangeSliderContainer');

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
        var label = '₱ ' + Number(val).toLocaleString() + (isMax && val >= 10000 ? '+' : '');
        var opt = new Option(label, valStr, true, true);
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

    function updateRangeSlider() {
      if (!minSlider || !maxSlider || !sliderFill) return;
      var minVal = parseInt(minSlider.value, 10);
      var maxVal = parseInt(maxSlider.value, 10);
      var minLimit = parseInt(minSlider.min, 10) || 1000;
      var maxLimit = parseInt(minSlider.max, 10) || 10000;

      if (minVal >= maxVal) {
        minVal = maxVal - 250;
        minSlider.value = minVal;
      }

      var leftPct = ((minVal - minLimit) / (maxLimit - minLimit)) * 100;
      var widthPct = ((maxVal - minVal) / (maxLimit - minLimit)) * 100;

      sliderFill.style.left = leftPct + '%';
      sliderFill.style.width = widthPct + '%';

      if (minDisplay) minDisplay.textContent = '₱ ' + Number(minVal).toLocaleString();
      if (maxDisplay) maxDisplay.textContent = '₱ ' + Number(maxVal).toLocaleString() + (maxVal >= 10000 ? '+' : '');

      syncSelectWithVal(minSelect, minVal, false);
      syncSelectWithVal(maxSelect, maxVal, true);
    }

    if (minSlider && maxSlider) {
      // Bring active thumb forward on interaction so both thumbs are always easily grabbable
      if (sliderContainer) {
        sliderContainer.addEventListener('mousemove', function (e) {
          var rect = sliderContainer.getBoundingClientRect();
          var x = e.clientX - rect.left;
          var pct = Math.max(0, Math.min(1, x / rect.width));
          var hoverVal = minLimit + pct * (maxLimit - minLimit);
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
      minSlider.addEventListener('mousedown', function () { minSlider.style.zIndex = '10'; maxSlider.style.zIndex = '3'; });
      minSlider.addEventListener('touchstart', function () { minSlider.style.zIndex = '4'; maxSlider.style.zIndex = '3'; });
      maxSlider.addEventListener('mousedown', function () { maxSlider.style.zIndex = '4'; minSlider.style.zIndex = '3'; });
      maxSlider.addEventListener('touchstart', function () { maxSlider.style.zIndex = '4'; minSlider.style.zIndex = '3'; });

      minSlider.addEventListener('input', function () {
        var minVal = parseInt(minSlider.value, 10);
        var maxVal = parseInt(maxSlider.value, 10);
        if (minVal >= maxVal - 250) {
          minSlider.value = maxVal - 250;
        }
        updateRangeSlider();
      });

      maxSlider.addEventListener('input', function () {
        var minVal = parseInt(minSlider.value, 10);
        var maxVal = parseInt(maxSlider.value, 10);
        if (maxVal <= minVal + 250) {
          maxSlider.value = minVal + 250;
        }
        updateRangeSlider();
      });

      if (minSelect) {
        minSelect.addEventListener('change', function () {
          var v = parseInt(this.value, 10) || 1000;
          var maxV = parseInt(maxSlider.value, 10);
          if (v >= maxV - 250) {
            v = maxV - 250;
            this.value = v;
          }
          minSlider.value = v;
          updateRangeSlider();
        });
      }

      if (maxSelect) {
        maxSelect.addEventListener('change', function () {
          var v = parseInt(this.value, 10) || 10000;
          var minV = parseInt(minSlider.value, 10);
          if (v <= minV + 250) {
            v = minV + 250;
            this.value = v;
          }
          maxSlider.value = v;
          updateRangeSlider();
        });
      }

      if (sliderContainer) {
        sliderContainer.addEventListener('click', function (e) {
          if (e.target.classList.contains('range-slider-input')) return;
          var rect = this.getBoundingClientRect();
          var clickX = e.clientX - rect.left;
          var pct = Math.max(0, Math.min(1, clickX / rect.width));
          var minLimit = parseInt(minSlider.min, 10) || 1000;
          var maxLimit = parseInt(minSlider.max, 10) || 10000;
          var clickedVal = Math.round((minLimit + pct * (maxLimit - minLimit)) / 250) * 250;
          var currentMin = parseInt(minSlider.value, 10);
          var currentMax = parseInt(maxSlider.value, 10);

          if (Math.abs(clickedVal - currentMin) < Math.abs(clickedVal - currentMax)) {
            if (clickedVal < currentMax - 250) {
              minSlider.value = clickedVal;
            }
          } else {
            if (clickedVal > currentMin + 250) {
              maxSlider.value = clickedVal;
            }
          }
          updateRangeSlider();
        });
      }

      updateRangeSlider();
    }

    // Reset filters
    var resetBtn = document.getElementById('resetFiltersBtn');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (typePills.length) {
          typePills.forEach(function (p) { p.classList.remove('active-blue'); });
          typePills[0].classList.add('active-blue');
          if (typeInput) typeInput.value = 'all';
        }
        if (transPills.length) {
          transPills.forEach(function (p) { p.classList.remove('active-navy'); });
          transPills[0].classList.add('active-navy');
          if (transInput) transInput.value = 'all';
        }
        var minP = document.getElementById('filterMinPrice');
        var maxP = document.getElementById('filterMaxPrice');
        var brand = document.getElementById('filterBrand');
        if (minP) minP.value = '1500';
        if (maxP) maxP.value = '6000';
        if (brand) brand.value = 'all';
        if (minSlider && maxSlider) {
          minSlider.value = 1500;
          maxSlider.value = 6000;
          updateRangeSlider();
        }
      });
    }
  }

  // ==========================================
  // Location Hub Dropdown Selector
  // ==========================================
  var locTrigger = document.getElementById('locationSelector');
  var locPanel = document.getElementById('locationDropdownPanel');
  var locHubInput = document.getElementById('selectedHubInput');
  var locHubText = document.getElementById('selectedHubText');
  var searchForm = document.getElementById('browseSearchForm');

  if (locTrigger && locPanel) {
    function toggleLocDropdown(open) {
      var isHidden = (locPanel.style.display === 'none' || locPanel.style.display === '');
      var shouldOpen = (typeof open === 'boolean') ? open : isHidden;
      if (shouldOpen) {
        locPanel.style.display = 'block';
        locTrigger.classList.add('active-open');
        locTrigger.setAttribute('aria-expanded', 'true');
      } else {
        locPanel.style.display = 'none';
        locTrigger.classList.remove('active-open');
        locTrigger.setAttribute('aria-expanded', 'false');
      }
    }

    locTrigger.addEventListener('click', function (e) {
      e.stopPropagation();
      toggleLocDropdown();
    });

    locTrigger.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleLocDropdown();
      } else if (e.key === 'Escape') {
        toggleLocDropdown(false);
      }
    });

    var hubItems = locPanel.querySelectorAll('.loc-hub-item');
    hubItems.forEach(function (item) {
      item.addEventListener('click', function (e) {
        e.stopPropagation();
        var key = item.getAttribute('data-hub-key');
        var name = item.getAttribute('data-hub-name');

        if (locHubInput) locHubInput.value = key;
        if (locHubText) locHubText.textContent = name;

        hubItems.forEach(function (el) { el.classList.remove('active-hub'); });
        item.classList.add('active-hub');

        toggleLocDropdown(false);

        if (searchForm) {
          searchForm.submit();
        }
      });
    });

    document.addEventListener('click', function (e) {
      if (!locTrigger.contains(e.target) && !locPanel.contains(e.target)) {
        toggleLocDropdown(false);
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && locPanel.style.display !== 'none') {
        toggleLocDropdown(false);
      }
    });
  }
});
