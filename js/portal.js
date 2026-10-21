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
      });
    }
  }
});
