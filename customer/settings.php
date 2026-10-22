<?php
require_once __DIR__ . '/../includes/functions.php';
require_customer();
$pdo = getDBConnection();
$customerId = customer_user_id();

$stMe = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stMe->execute([$customerId]);
$me = $stMe->fetch();

if (!$me) {
    logout_customer();
    flash_set('error', 'Please log in to your account.');
    redirect('login.php');
}

// Handle AJAX updates or full form POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check('customer/settings.php');

    $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    $name   = trim($_POST['full_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $phoneVerified = !empty($_POST['phone_verified']) ? 1 : 0;
    $emailVerified = !empty($_POST['email_verified']) ? 1 : 0;

    // Duplicate email validation excluding current user
    $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $dup->execute([$email, $me['id']]);

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Please provide a valid full name and email address.']);
            exit;
        }
        flash_set('error', 'Please provide a valid full name and email address.');
    } elseif ($dup->fetch()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'That email address is already in use by another account.']);
            exit;
        }
        flash_set('error', 'That email address is already in use by another account.');
    } else {
        $parts = explode(' ', $name, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '-';

        // Check avatar handling
        $avatarPath = $me['avatar'] ?? null;
        if (!empty($_POST['remove_avatar']) && $_POST['remove_avatar'] === '1') {
            if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
                @unlink(__DIR__ . '/../' . $avatarPath);
            }
            $avatarPath = null;
        }

        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar_file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $dir = __DIR__ . '/../uploads/avatars';
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                // If previous avatar exists, clean it up
                if ($avatarPath && file_exists(__DIR__ . '/../' . $avatarPath)) {
                    @unlink(__DIR__ . '/../' . $avatarPath);
                }
                $newFileName = 'avatar_' . $me['id'] . '_' . time() . '.' . $ext;
                $dest = $dir . '/' . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $avatarPath = 'uploads/avatars/' . $newFileName;
                }
            }
        }

        // Update MySQL record
        try {
            $up = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, avatar=?, phone_verified=?, email_verified=? WHERE id=?");
            $up->execute([$firstName, $lastName, $email, $phone, $avatarPath, $phoneVerified, $emailVerified, $me['id']]);
        } catch (PDOException $ex) {
            $up = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, phone=?, avatar=? WHERE id=?");
            $up->execute([$firstName, $lastName, $email, $phone, $avatarPath, $me['id']]);
        }

        $_SESSION['user_name'] = $firstName . ($lastName !== '-' ? ' ' . $lastName : '');
        $_SESSION['user_email'] = $email;
        if ($avatarPath) {
            $_SESSION['user_avatar'] = $avatarPath;
        } else {
            unset($_SESSION['user_avatar']);
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'user_name' => $_SESSION['user_name'],
                'avatar' => $avatarPath,
                'avatar_url' => $avatarPath ? (BASE_URL . $avatarPath . '?v=' . time()) : ''
            ]);
            exit;
        }

        flash_set('success', 'Your account settings have been saved successfully.');
        redirect('customer/settings.php');
    }
}

// Include customer layout header (HTML output begins here)
$pageTitle = 'Account Settings';
$nav = '';
require_once __DIR__ . '/../includes/customer_header.php';

// User display values from database
$userFullName = trim(($me['first_name'] ?? '') . ' ' . (($me['last_name'] ?? '') === '-' ? '' : ($me['last_name'] ?? '')));
$userPhone = $me['phone'] ?? '+63 917 123 4567';
$userEmail = $me['email'] ?? 'carlos@drivesphere.ph';
$phoneVerified = !isset($me['phone_verified']) || (int)$me['phone_verified'] === 1;
$emailVerified = !isset($me['email_verified']) || (int)$me['email_verified'] === 1;

// Avatar state: empty by default unless user has chosen and saved an image
$hasAvatar = !empty($me['avatar']) && file_exists(__DIR__ . '/../' . $me['avatar']);
$avatarUrl = $hasAvatar ? (BASE_URL . ltrim($me['avatar'], '/') . '?v=' . filemtime(__DIR__ . '/../' . $me['avatar'])) : '';
$avatarFileName = $hasAvatar ? basename($me['avatar']) : 'No image selected yet';
$avatarMeta = $hasAvatar ? 'Custom photo uploaded • Active' : 'No file chosen • PNG or JPG max 4MB';
?>

<!-- Top Breadcrumbs Bar -->
<div class="settings-top-bar">
    <a href="<?= $C ?>dashboard.php" class="settings-back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Account Profile
    </a>
</div>

<!-- Account Settings Main Card -->
<div class="settings-main-card">
    <h1 class="settings-header-title">Account Settings</h1>
    <p class="settings-header-sub">Update your personal profile details, verified credentials, and avatar.</p>

    <form method="post" action="<?= $C ?>settings.php" enctype="multipart/form-data" id="settingsForm">
        <?= csrf_field() ?>
        <input type="hidden" name="remove_avatar" id="removeAvatarInput" value="0">
        <input type="hidden" name="phone_verified" id="phoneVerifiedInput" value="<?= $phoneVerified ? '1' : '0' ?>">
        <input type="hidden" name="email_verified" id="emailVerifiedInput" value="<?= $emailVerified ? '1' : '0' ?>">

        <!-- Row 1: Full Name -->
        <div class="settings-row">
            <div class="settings-row-label">
                <h4>Full Name</h4>
                <p>Official government ID name</p>
            </div>
            <div class="settings-input-group">
                <div class="settings-input-box">
                    <input type="text" name="full_name" id="inputFullName" value="<?= e($userFullName) ?>" required autocomplete="name" placeholder="Enter your full name">
                    <i class="fa-regular fa-user"></i>
                </div>
                <button type="button" class="badge-done" id="btnDoneName" title="Confirm and save your name">
                    <i class="fa-solid fa-check"></i> <span id="doneNameText">Done</span>
                </button>
            </div>
        </div>

        <!-- Row 2: Phone Number -->
        <div class="settings-row">
            <div class="settings-row-label">
                <h4>Phone Number</h4>
                <p>SMS reservation notifications</p>
            </div>
            <div class="settings-input-group">
                <div class="settings-input-box">
                    <input type="text" name="phone" id="inputPhone" value="<?= e($userPhone) ?>" required autocomplete="tel" placeholder="+63 9XX XXX XXXX">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <button type="button" class="<?= $phoneVerified ? 'badge-verified' : 'badge-action-unverified' ?>" id="btnVerifyPhone" title="Click to verify or view status">
                    <i class="fa-solid <?= $phoneVerified ? 'fa-circle-check' : 'fa-shield-halved' ?>" id="phoneVerifyIcon"></i> 
                    <span id="phoneVerifyText"><?= $phoneVerified ? 'Verified' : 'Verify Now' ?></span>
                </button>
            </div>
        </div>

        <!-- Row 3: Profile Picture (Starts with NO image selected yet by default) -->
        <div class="settings-row">
            <div class="settings-row-label">
                <h4>Profile Picture</h4>
                <p>PNG or JPG max 4MB size</p>
            </div>
            <div class="settings-avatar-card">
                <!-- Circular Avatar Preview Box (Clickable to trigger upload) -->
                <div class="settings-avatar-preview" id="avatarPreviewBox" style="cursor:pointer;" title="Click to choose a photo">
                    <?php if ($hasAvatar): ?>
                        <img src="<?= $avatarUrl ?>" alt="Profile Preview" class="settings-avatar-img" id="avatarPreviewImg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <div id="avatarPlaceholder" style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#e0f2fe;border-radius:50%;color:#0284c7;">
                            <i class="fa-regular fa-user" style="font-size:24px;"></i>
                        </div>
                    <?php else: ?>
                        <img src="" alt="Profile Preview" class="settings-avatar-img" id="avatarPreviewImg" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <div id="avatarPlaceholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e0f2fe;border-radius:50%;color:#0284c7;">
                            <i class="fa-regular fa-user" style="font-size:24px;"></i>
                        </div>
                    <?php endif; ?>
                    <span class="settings-cam-badge"><i class="fa-solid fa-camera"></i></span>
                </div>

                <div class="settings-avatar-info">
                    <div class="settings-avatar-filename" id="avatarFileName"><?= e($avatarFileName) ?></div>
                    <div class="settings-avatar-meta" id="avatarMeta"><?= e($avatarMeta) ?></div>
                    <div class="settings-avatar-btns">
                        <label class="btn-photo-change" style="cursor:pointer;">
                            <i class="fa-solid fa-upload"></i> <span id="btnPhotoText"><?= $hasAvatar ? 'Change Photo' : 'Choose Photo' ?></span>
                            <input type="file" name="avatar_file" id="avatarFileInput" accept="image/png,image/jpeg,image/webp" style="display:none">
                        </label>
                        <button type="button" class="btn-photo-remove" id="btnRemovePhoto" style="<?= $hasAvatar ? '' : 'display:none;' ?>">Remove</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 4: Contact Email -->
        <div class="settings-row">
            <div class="settings-row-label">
                <h4>Contact Email</h4>
                <p>Primary billing &amp; key deliveries</p>
            </div>
            <div class="settings-input-group">
                <div class="settings-input-box">
                    <input type="email" name="email" id="inputEmail" value="<?= e($userEmail) ?>" required autocomplete="email" placeholder="name@domain.com">
                    <i class="fa-regular fa-envelope"></i>
                </div>
                <button type="button" class="<?= $emailVerified ? 'badge-verified-green' : 'badge-action-unverified' ?>" id="btnVerifyEmail" title="Click to verify or view status">
                    <i class="fa-solid <?= $emailVerified ? 'fa-circle-check' : 'fa-paper-plane' ?>" id="emailVerifyIcon"></i> 
                    <span id="emailVerifyText"><?= $emailVerified ? 'Verified' : 'Verify Email' ?></span>
                </button>
            </div>
        </div>

        <!-- Footer Actions Bar -->
        <div class="settings-card-foot" style="justify-content: flex-end;">
            <div class="settings-foot-actions">
                <a href="<?= $C ?>dashboard.php" class="btn-settings-cancel">Cancel</a>
                <button type="submit" class="btn-settings-save" id="btnSaveChanges">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Interactive Verification Modal (Phone / Email OTP Verification) -->
<div class="settings-modal-backdrop" id="verifyModalBackdrop" style="display:none;">
    <div class="settings-modal-box" id="verifyModalBox">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div id="modalIconBox" style="width:42px;height:42px;border-radius:10px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 id="modalTitle" style="font-size:17px;font-weight:700;color:#0f172a;margin:0 0 2px;">Verification Status</h3>
                    <span id="modalTarget" style="font-size:12px;color:#64748b;">+63 917 123 4567</span>
                </div>
            </div>
            <button type="button" id="modalCloseBtn" style="background:none;border:none;color:#94a3b8;font-size:18px;cursor:pointer;padding:4px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="modalBodyText" style="font-size:13px;color:#475569;line-height:1.55;margin-bottom:20px;">
            Your credentials are authenticated and verified.
        </div>

        <!-- OTP Code Input Row (shown when re-verifying or unverified) -->
        <div id="otpInputRow" style="margin-bottom:20px;display:none;">
            <label style="display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px;letter-spacing:0.5px;">ENTER 4-DIGIT CODE</label>
            <div style="display:flex;gap:10px;">
                <input type="text" id="otpCodeInput" maxlength="6" placeholder="2026" style="flex:1;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:15px;letter-spacing:3px;font-weight:700;text-align:center;">
                <button type="button" id="btnQuickVerify" style="background:#f1f5f9;border:1px solid #cbd5e1;border-radius:8px;padding:0 14px;font-size:12px;font-weight:600;color:#0f172a;cursor:pointer;">
                    <i class="fa-solid fa-bolt" style="color:#0284c7;"></i> Auto-Fill
                </button>
            </div>
            <span style="display:block;font-size:11px;color:#94a3b8;margin-top:6px;">Demo Hint: Enter <strong>2026</strong> or press Auto-Fill.</span>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" id="modalCancelBtn" style="background:#f1f5f9;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;color:#475569;cursor:pointer;">Close</button>
            <button type="button" id="modalActionBtn" style="background:#0284c7;border:none;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;color:#ffffff;cursor:pointer;">
                Confirm Verification
            </button>
        </div>
    </div>
</div>

<!-- Floating Toast Feedback Banner -->
<div class="settings-toast success" id="settingsToast">
    <i class="fa-solid fa-circle-check" style="font-size:16px;"></i>
    <span id="toastMessage">Changes saved.</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // Toast Notification Helper
    // -------------------------------------------------------------
    const toast = document.getElementById('settingsToast');
    const toastMsg = document.getElementById('toastMessage');
    let toastTimer = null;
    function showToast(message, isSuccess = true) {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        toast.className = 'settings-toast ' + (isSuccess ? 'success' : '');
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // -------------------------------------------------------------
    // 1. Full Name "Done" Interactive Button
    // -------------------------------------------------------------
    const btnDoneName = document.getElementById('btnDoneName');
    const inputFullName = document.getElementById('inputFullName');
    const doneNameText = document.getElementById('doneNameText');

    if (btnDoneName && inputFullName) {
        btnDoneName.addEventListener('click', function () {
            const val = inputFullName.value.trim();
            if (val === '') {
                showToast('Please enter your full name.', false);
                inputFullName.focus();
                return;
            }
            
            // Visual Done animation
            btnDoneName.style.transform = 'scale(0.94)';
            doneNameText.textContent = 'Saved!';
            btnDoneName.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Saved!</span>';
            btnDoneName.style.background = '#d1fae5';
            btnDoneName.style.borderColor = '#10b981';

            showToast('Full Name confirmed as "' + val + '".');

            setTimeout(() => {
                btnDoneName.style.transform = '';
                btnDoneName.innerHTML = '<i class="fa-solid fa-check"></i> <span id="doneNameText">Done</span>';
                btnDoneName.style.background = '#ecfdf5';
                btnDoneName.style.borderColor = '#a7f3d0';
            }, 1800);
        });

        inputFullName.addEventListener('input', function () {
            btnDoneName.innerHTML = '<i class="fa-solid fa-check"></i> <span id="doneNameText">Done</span>';
            btnDoneName.style.background = '#ecfdf5';
            btnDoneName.style.borderColor = '#a7f3d0';
        });
    }

    // -------------------------------------------------------------
    // 2. Profile Picture Upload & Live Header Synchronization
    // -------------------------------------------------------------
    const fileInput = document.getElementById('avatarFileInput');
    const previewImg = document.getElementById('avatarPreviewImg');
    const placeholder = document.getElementById('avatarPlaceholder');
    const previewBox = document.getElementById('avatarPreviewBox');
    const fileNameEl = document.getElementById('avatarFileName');
    const metaEl = document.getElementById('avatarMeta');
    const btnPhotoText = document.getElementById('btnPhotoText');
    const btnRemove = document.getElementById('btnRemovePhoto');
    const removeInput = document.getElementById('removeAvatarInput');
    const navCustomerAvatar = document.getElementById('navCustomerAvatar');
    const menuCustomerAvatar = document.getElementById('menuCustomerAvatar');

    function updateNavAvatars(imgUrl) {
        if (imgUrl) {
            if (navCustomerAvatar) {
                navCustomerAvatar.innerHTML = '<img src="' + imgUrl + '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
            }
            if (menuCustomerAvatar) {
                menuCustomerAvatar.innerHTML = '<img src="' + imgUrl + '" alt="Profile" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
            }
        } else {
            if (navCustomerAvatar) {
                navCustomerAvatar.innerHTML = '<svg viewBox="0 0 32 32" fill="none" style="width:100%;height:100%;display:block;"><circle cx="16" cy="16" r="16" fill="#0284c7"/><circle cx="16" cy="12" r="5.5" fill="#ffffff"/><path d="M6 28c0-5.523 4.477-10 10-10s10 4.477 10 10" fill="#ffffff"/></svg>';
            }
            if (menuCustomerAvatar) {
                menuCustomerAvatar.innerHTML = '<svg viewBox="0 0 40 40" fill="none" style="width:100%;height:100%;display:block;border-radius:50%;"><circle cx="20" cy="20" r="20" fill="#e0f2fe"/><circle cx="20" cy="15" r="7" fill="#0284c7"/><path d="M7 36c0-7.18 5.82-13 13-13s13 5.82 13 13" fill="#0284c7"/></svg>';
            }
        }
    }

    // Click on avatar circle triggers file picker
    if (previewBox && fileInput) {
        previewBox.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 4 * 1024 * 1024) {
                    showToast('Selected file exceeds 4MB limit. Please choose a smaller photo.', false);
                    fileInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (evt) {
                    const dataUrl = evt.target.result;
                    previewImg.src = dataUrl;
                    previewImg.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';

                    fileNameEl.textContent = file.name;
                    metaEl.textContent = 'Selected: ' + (file.size / (1024 * 1024)).toFixed(1) + ' MB • Ready to save';
                    if (btnPhotoText) btnPhotoText.textContent = 'Change Photo';
                    if (btnRemove) btnRemove.style.display = 'inline-block';
                    if (removeInput) removeInput.value = '0';

                    // Update header avatar in real-time as soon as photo is selected!
                    updateNavAvatars(dataUrl);
                    showToast('Photo chosen! Nav header avatar preview updated.');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function (e) {
            e.stopPropagation();
            if (fileInput) fileInput.value = '';
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
            if (placeholder) placeholder.style.display = 'flex';

            fileNameEl.textContent = 'No image selected yet';
            metaEl.textContent = 'No file chosen • PNG or JPG max 4MB';
            if (btnPhotoText) btnPhotoText.textContent = 'Choose Photo';
            btnRemove.style.display = 'none';
            if (removeInput) removeInput.value = '1';

            // Reset header icon back to default
            updateNavAvatars(null);
            showToast('Image removed. Nav header avatar restored to default.');
        });
    }

    // Form submit handler with AJAX enhancement + instant feedback
    const settingsForm = document.getElementById('settingsForm');
    const btnSaveChanges = document.getElementById('btnSaveChanges');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const origHtml = btnSaveChanges.innerHTML;
            btnSaveChanges.disabled = true;
            btnSaveChanges.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

            const formData = new FormData(settingsForm);
            formData.append('ajax', '1');

            fetch(settingsForm.action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSaveChanges.disabled = false;
                btnSaveChanges.innerHTML = origHtml;
                if (data.success) {
                    showToast('✓ ' + (data.message || 'Changes saved successfully!'));
                    if (data.avatar_url) {
                        updateNavAvatars(data.avatar_url);
                        if (btnPhotoText) btnPhotoText.textContent = 'Change Photo';
                        if (btnRemove) btnRemove.style.display = 'inline-block';
                    } else if (removeInput && removeInput.value === '1') {
                        updateNavAvatars(null);
                    }
                } else {
                    showToast(data.error || 'Failed to save changes.', false);
                }
            })
            .catch(err => {
                // If fetch fails for any reason, perform standard HTML submission
                settingsForm.submit();
            });
        });
    }

    // -------------------------------------------------------------
    // 3. Phone & Email Verification Interactive Modal System
    // -------------------------------------------------------------
    const modalBackdrop = document.getElementById('verifyModalBackdrop');
    const modalBox = document.getElementById('verifyModalBox');
    const modalTitle = document.getElementById('modalTitle');
    const modalTarget = document.getElementById('modalTarget');
    const modalBodyText = document.getElementById('modalBodyText');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    const modalActionBtn = document.getElementById('modalActionBtn');
    const otpInputRow = document.getElementById('otpInputRow');
    const otpCodeInput = document.getElementById('otpCodeInput');
    const btnQuickVerify = document.getElementById('btnQuickVerify');

    const inputPhone = document.getElementById('inputPhone');
    const inputEmail = document.getElementById('inputEmail');
    const btnVerifyPhone = document.getElementById('btnVerifyPhone');
    const btnVerifyEmail = document.getElementById('btnVerifyEmail');
    const phoneVerifiedInput = document.getElementById('phoneVerifiedInput');
    const emailVerifiedInput = document.getElementById('emailVerifiedInput');
    const phoneVerifyText = document.getElementById('phoneVerifyText');
    const emailVerifyText = document.getElementById('emailVerifyText');
    const phoneVerifyIcon = document.getElementById('phoneVerifyIcon');
    const emailVerifyIcon = document.getElementById('emailVerifyIcon');

    let currentModalType = null; // 'phone' or 'email'

    function openModal(type) {
        currentModalType = type;
        modalBackdrop.style.display = 'flex';
        void modalBackdrop.offsetWidth;
        modalBackdrop.classList.add('show');

        if (type === 'phone') {
            const num = inputPhone.value.trim() || 'Mobile Number';
            modalTitle.textContent = 'Phone Verification';
            modalTarget.textContent = num;
            
            const isVerified = phoneVerifiedInput.value === '1';
            if (isVerified) {
                modalBodyText.innerHTML = '<strong>' + num + '</strong> is fully verified on your account for SMS instant booking updates, vehicle handover authentication, and emergency 24/7 dispatch.';
                otpInputRow.style.display = 'none';
                modalActionBtn.textContent = 'Re-Verify Phone';
                modalActionBtn.style.background = '#0284c7';
            } else {
                modalBodyText.innerHTML = 'Enter the 4-digit SMS verification code sent to <strong>' + num + '</strong> to activate instant self-drive approvals.';
                otpInputRow.style.display = 'block';
                otpCodeInput.value = '';
                otpCodeInput.placeholder = '2026';
                modalActionBtn.textContent = 'Confirm OTP Code';
                modalActionBtn.style.background = '#059669';
                setTimeout(() => otpCodeInput.focus(), 200);
            }
        } else if (type === 'email') {
            const em = inputEmail.value.trim() || 'Email Address';
            modalTitle.textContent = 'Email Verification';
            modalTarget.textContent = em;

            const isVerified = emailVerifiedInput.value === '1';
            if (isVerified) {
                modalBodyText.innerHTML = '<strong>' + em + '</strong> is verified on your DriveSphere account for official digital rental agreements, payment receipts, and security alerts.';
                otpInputRow.style.display = 'none';
                modalActionBtn.textContent = 'Re-Verify Email';
                modalActionBtn.style.background = '#059669';
            } else {
                modalBodyText.innerHTML = 'Enter the 4-digit security code sent to <strong>' + em + '</strong> to verify your primary billing email.';
                otpInputRow.style.display = 'block';
                otpCodeInput.value = '';
                otpCodeInput.placeholder = '8888';
                modalActionBtn.textContent = 'Confirm Email Code';
                modalActionBtn.style.background = '#059669';
                setTimeout(() => otpCodeInput.focus(), 200);
            }
        }
    }

    function closeModal() {
        modalBackdrop.classList.remove('show');
        setTimeout(() => {
            modalBackdrop.style.display = 'none';
        }, 220);
    }

    if (btnVerifyPhone) {
        btnVerifyPhone.addEventListener('click', () => openModal('phone'));
    }
    if (btnVerifyEmail) {
        btnVerifyEmail.addEventListener('click', () => openModal('email'));
    }
    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
    if (modalCancelBtn) modalCancelBtn.addEventListener('click', closeModal);
    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) closeModal();
        });
    }

    if (btnQuickVerify) {
        btnQuickVerify.addEventListener('click', function () {
            otpCodeInput.value = currentModalType === 'phone' ? '2026' : '8888';
        });
    }

    if (modalActionBtn) {
        modalActionBtn.addEventListener('click', function () {
            if (currentModalType === 'phone') {
                if (modalActionBtn.textContent.includes('Re-Verify')) {
                    phoneVerifiedInput.value = '0';
                    modalBodyText.innerHTML = 'An SMS code has been sent to <strong>' + inputPhone.value + '</strong>. Enter code below:';
                    otpInputRow.style.display = 'block';
                    otpCodeInput.value = '';
                    otpCodeInput.placeholder = '2026';
                    modalActionBtn.textContent = 'Confirm OTP Code';
                    modalActionBtn.style.background = '#059669';
                    otpCodeInput.focus();
                    return;
                }

                phoneVerifiedInput.value = '1';
                btnVerifyPhone.className = 'badge-verified';
                phoneVerifyText.textContent = 'Verified';
                phoneVerifyIcon.className = 'fa-solid fa-circle-check';

                closeModal();
                showToast('✓ Phone number (' + inputPhone.value + ') successfully verified!');
            } else if (currentModalType === 'email') {
                if (modalActionBtn.textContent.includes('Re-Verify')) {
                    emailVerifiedInput.value = '0';
                    modalBodyText.innerHTML = 'A security code has been sent to <strong>' + inputEmail.value + '</strong>. Enter code below:';
                    otpInputRow.style.display = 'block';
                    otpCodeInput.value = '';
                    otpCodeInput.placeholder = '8888';
                    modalActionBtn.textContent = 'Confirm Email Code';
                    modalActionBtn.style.background = '#059669';
                    otpCodeInput.focus();
                    return;
                }

                emailVerifiedInput.value = '1';
                btnVerifyEmail.className = 'badge-verified-green';
                emailVerifyText.textContent = 'Verified';
                emailVerifyIcon.className = 'fa-solid fa-circle-check';

                closeModal();
                showToast('✓ Email address (' + inputEmail.value + ') successfully verified!');
            }
        });
    }

    if (inputPhone) {
        inputPhone.addEventListener('input', function () {
            phoneVerifiedInput.value = '0';
            btnVerifyPhone.className = 'badge-action-unverified';
            phoneVerifyText.textContent = 'Verify Now';
            phoneVerifyIcon.className = 'fa-solid fa-shield-halved';
        });
    }
    if (inputEmail) {
        inputEmail.addEventListener('input', function () {
            emailVerifiedInput.value = '0';
            btnVerifyEmail.className = 'badge-action-unverified';
            emailVerifyText.textContent = 'Verify Email';
            emailVerifyIcon.className = 'fa-solid fa-paper-plane';
        });
    }
});
</script>

<?php require __DIR__ . '/../includes/customer_footer.php'; ?>
