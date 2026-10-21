<?php
// Shows one vehicle card. $v = row from vehicles (+ optional 'free' flag), $qs = extra query string (dates).
function render_vehicle_card($v, $qs = '') {
    $free = isset($v['free']) ? $v['free'] : ($v['status'] === 'available');
    $link = BASE_URL . 'customer/vehicle.php?id=' . (int)$v['id'] . ($qs !== '' ? '&' . $qs : '');
    ?>
    <div class="vcard">
        <div class="img" style="background-image:url('<?= e($v['image_url']) ?>')">
            <?= $free ? '<span class="badge b-green">Available</span>' : '<span class="badge b-red">' . ($v['status'] === 'maintenance' ? 'Maintenance' : 'Unavailable') . '</span>' ?>
        </div>
        <div class="body">
            <div class="meta"><?= e($v['category_name'] ?? '') ?> &middot; <?= e($v['tag_label']) ?></div>
            <h3><?= e($v['make'] . ' ' . $v['model']) ?></h3>
            <div class="meta"><i class="fa-solid fa-gear"></i> <?= e($v['transmission']) ?> &middot; <i class="fa-solid fa-users"></i> <?= (int)$v['seats'] ?> seats &middot; <?= e($v['fuel_type']) ?></div>
            <div class="foot">
                <div class="price"><?= money($v['daily_rate']) ?> <small>/ day</small></div>
                <a class="btn-d btn-sm" href="<?= $link ?>"><?= $free ? 'Reserve' : 'View' ?></a>
            </div>
        </div>
    </div>
    <?php
}
