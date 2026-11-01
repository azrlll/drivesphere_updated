<?php
$pageTitle = 'Merchant Profile & Corporate Settings'; $nav = 'merchant';
require_once __DIR__ . '/../includes/admin_header.php';
$adminId = admin_user_id();
$me = $pdo->prepare("SELECT * FROM users WHERE id=?"); $me->execute([$adminId]); $me = $me->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('admin/settings.php');
    $action = $_POST['action'] ?? '';
    if ($action === 'merchant') {
        $name = trim($_POST['merchant_name'] ?? '');
        if ($name === '') flash_set('error', 'Merchant name is required.');
        else {
            $pdo->prepare("UPDATE merchant_profile SET merchant_name=?, contact_name=?, phone=?, email=?, address=? WHERE id=1")
                ->execute([$name, trim($_POST['contact_name']), trim($_POST['phone']), trim($_POST['email']), trim($_POST['address'])]);
            flash_set('success', 'Merchant profile saved.');
        }
    } elseif ($action === 'account') {
        $full = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? '');
        $dup = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?"); $dup->execute([$email, $me['id']]);
        if ($full === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) flash_set('error', 'Enter your name and a valid email.');
        elseif ($dup->fetch()) flash_set('error', 'That email is used by another account.');
        else {
            $p = explode(' ', $full, 2);
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?")->execute([$p[0], $p[1] ?? '-', $email, $me['id']]);
            $_SESSION['admin_user_name'] = $p[0] . ' ' . ($p[1] ?? '-');
            $_SESSION['admin_user_email'] = $email;
            if (($_SESSION['user_role'] ?? '') === 'admin') {
                $_SESSION['user_name'] = $p[0] . ' ' . ($p[1] ?? '-');
                $_SESSION['user_email'] = $email;
            }
            flash_set('success', 'Account details saved.');
        }
    } elseif ($action === 'password') {
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current_password'] ?? '', $me['password'])) flash_set('error', 'Current password is incorrect.');
        elseif (strlen($new) < 8 || $new !== ($_POST['confirm_password'] ?? '')) flash_set('error', 'New password must be 8+ characters and match the confirmation.');
        else { $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]); flash_set('success', 'Password changed.'); }
    } elseif ($action === 'scheduling_policy') {
        $ohStart = trim($_POST['office_hours_start'] ?? '08:00');
        $ohEnd   = trim($_POST['office_hours_end'] ?? '20:00');
        $advMin  = (float)($_POST['advance_min_hours'] ?? 48);
        $advMax  = (float)($_POST['advance_max_hours'] ?? 72);
        $leadMin = (float)($_POST['lead_time_min_hours'] ?? 1);

        $updSt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $updSt->execute(['office_hours_start', $ohStart]);
        $updSt->execute(['office_hours_end', $ohEnd]);
        $updSt->execute(['advance_min_hours', $advMin]);
        $updSt->execute(['advance_max_hours', $advMax]);
        $updSt->execute(['lead_time_min_hours', $leadMin]);

        flash_set('success', 'Fleet operating hours and reservation scheduling policy saved.');
    }
    redirect('admin/settings.php');
}
$m = $merchant;
$currOhStart = get_system_setting($pdo, 'office_hours_start', '08:00');
$currOhEnd   = get_system_setting($pdo, 'office_hours_end', '20:00');
$currAdvMin  = get_system_setting($pdo, 'advance_min_hours', '48');
$currAdvMax  = get_system_setting($pdo, 'advance_max_hours', '72');
$currLeadMin = get_system_setting($pdo, 'lead_time_min_hours', '1');
?>
<div class="crumb"><a href="<?= $A ?>merchant.php">&larr; Back to Merchant Overview</a></div>
<div style="max-width:760px;margin:0 auto">
<h1 class="t">Merchant Profile &amp; Corporate Settings</h1>

<!-- Fleet Scheduling Policy (Phase 2 & Phase 3) -->
<form method="post" class="card">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="scheduling_policy">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
    <div>
      <h3 style="margin:0 0 4px;">Fleet Operations &amp; Reservation Policy</h3>
      <p style="margin:0; font-size:12px; color:#64748b;">Configure official branch office hours and defense panel booking constraints.</p>
    </div>
    <span class="badge b-blue">DEFENSE PANEL POLICY</span>
  </div>

  <div class="row2">
    <div class="field">
      <label>Office Opening Time (PST)</label>
      <input type="time" name="office_hours_start" value="<?= e($currOhStart) ?>" required>
      <small style="font-size:11px; color:#64748b;">Standard: 08:00 AM PST</small>
    </div>
    <div class="field">
      <label>Office Closing Time (PST)</label>
      <input type="time" name="office_hours_end" value="<?= e($currOhEnd) ?>" required>
      <small style="font-size:11px; color:#64748b;">Standard: 08:00 PM PST</small>
    </div>
  </div>

  <div class="row2">
    <div class="field">
      <label>Minimum Advance Notice (Hours)</label>
      <input type="number" step="0.5" min="0" max="168" name="advance_min_hours" value="<?= e($currAdvMin) ?>" required>
      <small style="font-size:11px; color:#64748b;">Standard: 48 hours (Set to 0 to permit same-day rush reservations)</small>
    </div>
    <div class="field">
      <label>Maximum Advance Notice (Hours)</label>
      <input type="number" step="0.5" min="1" max="720" name="advance_max_hours" value="<?= e($currAdvMax) ?>" required>
      <small style="font-size:11px; color:#64748b;">Standard: 72 hours</small>
    </div>
  </div>

  <div class="field">
    <label>Minimum Lead Time Before Pickup (Hours)</label>
    <input type="number" step="0.25" min="0" max="24" name="lead_time_min_hours" value="<?= e($currLeadMin) ?>" required>
    <small style="font-size:11px; color:#64748b;">Standard: 1.0 hour minimum cutoff before scheduled vehicle collection</small>
  </div>

  <button class="btn-d">Save Scheduling Policy</button>
</form>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="merchant"><h3>Legal entity &amp; contact details</h3>
  <div class="field"><label>Merchant name</label><input name="merchant_name" required value="<?= e($m['merchant_name']) ?>"></div>
  <div class="row2"><div class="field"><label>Contact person</label><input name="contact_name" value="<?= e($m['contact_name'] ?? '') ?>"></div><div class="field"><label>Business phone</label><input name="phone" value="<?= e($m['phone'] ?? '') ?>"></div></div>
  <div class="field"><label>Business email</label><input type="email" name="email" value="<?= e($m['email'] ?? '') ?>"></div>
  <div class="field"><label>Address</label><input name="address" value="<?= e($m['address'] ?? '') ?>"></div>
  <button class="btn-d">Save merchant profile</button></form>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="account"><h3>My administrator account</h3>
  <div class="row2"><div class="field"><label>Full name</label><input name="full_name" required value="<?= e(trim($me['first_name'] . ' ' . ($me['last_name'] === '-' ? '' : $me['last_name']))) ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($me['email']) ?>"></div></div>
  <button class="btn-d">Save account</button></form>
<form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="password"><h3>Change password</h3>
  <div class="field"><label>Current password</label><input type="password" name="current_password" required></div>
  <div class="row2"><div class="field"><label>New password</label><input type="password" name="new_password" minlength="8" required></div><div class="field"><label>Confirm</label><input type="password" name="confirm_password" minlength="8" required></div></div>
  <button class="btn-d">Update password</button></form>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
