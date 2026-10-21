<?php
$pageTitle = 'Account Settings'; $nav = '';
require_once __DIR__ . '/../includes/customer_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('customer/settings.php');
    if (($_POST['action'] ?? '') === 'profile') {
        $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? ''); $lic = trim($_POST['license_number'] ?? '');
        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?"); $dup->execute([$email, $me['id']]);
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) flash_set('error', 'Enter your full name and a valid email.');
        elseif ($dup->fetch()) flash_set('error', 'That email is used by another account.');
        else {
            $parts = explode(' ', $name, 2);
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, license_number=? WHERE id=?")
                ->execute([$parts[0], $parts[1] ?? '-', $email, $phone, $lic, $me['id']]);
            $_SESSION['user_name'] = $parts[0] . ' ' . ($parts[1] ?? '-'); $_SESSION['user_email'] = $email;
            flash_set('success', 'Your profile was updated.');
        }
    } elseif (($_POST['action'] ?? '') === 'password') {
        $new = $_POST['new_password'] ?? '';
        if (!password_verify($_POST['current_password'] ?? '', $me['password'])) flash_set('error', 'Current password is incorrect.');
        elseif (strlen($new) < 8) flash_set('error', 'New password must be at least 8 characters.');
        elseif ($new !== ($_POST['confirm_password'] ?? '')) flash_set('error', 'New passwords do not match.');
        else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            flash_set('success', 'Your password was changed.');
        }
    }
    redirect('customer/settings.php');
}
?>
<div class="crumb"><a href="<?= $C ?>dashboard.php">&larr; Back to Dashboard</a></div>
<div style="max-width:760px;margin:0 auto">
  <h1 class="t">Account Settings</h1><p class="sub">Update your personal profile details and credentials.</p>
  <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <h3>Profile</h3>
    <div class="field"><label>Full name</label><input name="full_name" required value="<?= e(trim($me['first_name'] . ' ' . ($me['last_name'] === '-' ? '' : $me['last_name']))) ?>"></div>
    <div class="row2">
      <div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($me['email']) ?>"></div>
      <div class="field"><label>Mobile phone</label><input name="phone" value="<?= e($me['phone']) ?>"></div>
    </div>
    <div class="field"><label>Driver's license no.</label><input name="license_number" value="<?= e($me['license_number']) ?>"></div>
    <button class="btn-d">Save changes</button>
  </form>
  <form method="post" class="card"><?= csrf_field() ?><input type="hidden" name="action" value="password">
    <h3>Change password</h3>
    <div class="field"><label>Current password</label><input type="password" name="current_password" required></div>
    <div class="row2">
      <div class="field"><label>New password</label><input type="password" name="new_password" minlength="8" required></div>
      <div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" minlength="8" required></div>
    </div>
    <button class="btn-d">Update password</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
