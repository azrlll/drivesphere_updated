<?php
// Supported DriveSphere Fleet Hubs
$hubs = [
    'bgc' => [
        'name' => 'Metro Manila - BGC Hub (Active)',
        'sub' => 'BGC High Street Flagship Station • Taguig City',
        'badge' => 'Flagship Base',
        'icon' => 'fa-solid fa-building'
    ],
    'mnl' => [
        'name' => 'NAIA Airport Terminal 3 Hub',
        'sub' => 'Curbside Flight Arrival Key Handover • Pasay City',
        'badge' => '24/7 Airport',
        'icon' => 'fa-solid fa-plane-arrival'
    ],
    'makati' => [
        'name' => 'Makati CBD Hub (Ayala Center)',
        'sub' => 'Ayala One Transportation Center Bay 2 • Makati City',
        'badge' => 'Express Station',
        'icon' => 'fa-solid fa-city'
    ],
    'ortigas' => [
        'name' => 'Ortigas Center - Pasig Hub',
        'sub' => 'San Antonio Galleria Depot • Pasig City',
        'badge' => 'Fleet Base',
        'icon' => 'fa-solid fa-car-side'
    ],
    'crk' => [
        'name' => "Clark Int'l Airport Hub (Pampanga)",
        'sub' => 'Northern Luzon Airport Delivery Station • Clark FreePort',
        'badge' => 'Regional Hub',
        'icon' => 'fa-solid fa-plane'
    ],
    'ceb' => [
        'name' => 'Cebu City - Mactan Airport Hub',
        'sub' => 'Visayas Central Operations Fleet Base • Lapu-Lapu City',
        'badge' => 'Island Gateway',
        'icon' => 'fa-solid fa-plane-departure'
    ],
    'dvo' => [
        'name' => 'Davao City - Lanang Hub',
        'sub' => 'Mindanao Gateway Station • Davao City',
        'badge' => 'Express Hub',
        'icon' => 'fa-solid fa-map-pin'
    ]
];

$selectedHubKey = $_GET['hub'] ?? $_GET['location'] ?? 'bgc';
if (!isset($hubs[$selectedHubKey])) {
    $selectedHubKey = 'bgc';
}
$currentHub = $hubs[$selectedHubKey];
?>
<div class="location-chip-wrap" id="locationSelectorWrap">
    <div class="location-chip" id="locationSelector" role="button" tabindex="0" title="Click to choose DriveSphere Pickup Hub" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-location-dot" style="color: #0284c7;"></i>
        <span id="selectedHubText"><?= htmlspecialchars($currentHub['name'], ENT_QUOTES, 'UTF-8') ?></span>
        <i class="fa-solid fa-chevron-down loc-chevron" id="locChevron"></i>
    </div>
    <input type="hidden" name="hub" id="selectedHubInput" value="<?= htmlspecialchars($selectedHubKey, ENT_QUOTES, 'UTF-8') ?>">

    <div class="location-dropdown-panel" id="locationDropdownPanel" style="display: none;">
        <div class="loc-dropdown-header">
            <span class="loc-header-title"><i class="fa-solid fa-map-pin"></i> Select Pickup &amp; Fleet Hub</span>
            <span class="loc-header-badge"><?= count($hubs) ?> Available Hubs</span>
        </div>
        <div class="loc-hub-list">
            <?php foreach ($hubs as $k => $h): 
                $isActive = ($k === $selectedHubKey);
            ?>
                <div class="loc-hub-item <?= $isActive ? 'active-hub' : '' ?>" data-hub-key="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" data-hub-name="<?= htmlspecialchars($h['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="loc-hub-icon-box">
                        <i class="<?= htmlspecialchars($h['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>
                    <div class="loc-hub-details">
                        <div class="loc-hub-name-row">
                            <span class="loc-hub-title"><?= htmlspecialchars($h['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="loc-hub-badge"><?= htmlspecialchars($h['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <span class="loc-hub-sub"><?= htmlspecialchars($h['sub'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <i class="fa-solid fa-check loc-check-icon"></i>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
(function() {
    function initLocationSelector() {
        var trigger = document.getElementById('locationSelector');
        var panel = document.getElementById('locationDropdownPanel');
        var hubInput = document.getElementById('selectedHubInput');
        var hubText = document.getElementById('selectedHubText');
        var form = document.getElementById('browseSearchForm');

        if (!trigger || !panel) return;

        function toggleDropdown(open) {
            var isHidden = (panel.style.display === 'none' || panel.style.display === '');
            var shouldOpen = (typeof open === 'boolean') ? open : isHidden;
            if (shouldOpen) {
                panel.style.display = 'block';
                trigger.classList.add('active-open');
                trigger.setAttribute('aria-expanded', 'true');
            } else {
                panel.style.display = 'none';
                trigger.classList.remove('active-open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        }

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });

        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleDropdown();
            } else if (e.key === 'Escape') {
                toggleDropdown(false);
            }
        });

        var items = panel.querySelectorAll('.loc-hub-item');
        items.forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                var key = item.getAttribute('data-hub-key');
                var name = item.getAttribute('data-hub-name');

                if (hubInput) hubInput.value = key;
                if (hubText) hubText.textContent = name;

                items.forEach(function(el) { el.classList.remove('active-hub'); });
                item.classList.add('active-hub');

                toggleDropdown(false);

                if (form) {
                    form.submit();
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !panel.contains(e.target)) {
                toggleDropdown(false);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && panel.style.display !== 'none') {
                toggleDropdown(false);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLocationSelector);
    } else {
        initLocationSelector();
    }
})();
</script>
