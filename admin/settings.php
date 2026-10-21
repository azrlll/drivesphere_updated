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
    }
    redirect('admin/settings.php');
}
$m = $merchant;
?>
<div class="crumb"><a href="<?= $A ?>merchant.php">&larr; Back to Merchant Overview</a></div>
<div style="max-width:760px;margin:0 auto">
<h1 class="t">Merchant Profile &amp; Corporate Settings</h1>
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
