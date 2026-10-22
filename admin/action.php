<?php
// One protected endpoint for admin button actions (approve, reject, dispatch, vehicle status/delete, ...).
// Every request must come from a logged-in ADMIN and carry a valid CSRF token.
require_once __DIR__ . '/../includes/functions.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    redirect('admin/rentals.php');
}
require_admin();
csrf_check('admin/rentals.php');
$pdo = getDBConnection();
$op  = $_POST['op'] ?? '';
$id  = (int)($_POST['id'] ?? 0);
$back = $_POST['back'] ?? 'admin/rentals.php';
if (strpos($back, 'admin/') !== 0 || strpos($back, '..') !== false) { $back = 'admin/rentals.php'; }   // only allow admin pages

function get_res($pdo, $id) {
    $st = $pdo->prepare("SELECT r.*, v.make, v.model, v.odometer FROM reservations r JOIN vehicles v ON r.vehicle_id = v.id WHERE r.id = ?");
    $st->execute([$id]);
    return $st->fetch();
}

switch ($op) {
    case 'approve':
        $r = get_res($pdo, $id);
        if (!$r || $r['status'] !== 'pending') { flash_set('error', 'Only pending requests can be approved.'); break; }
        if (!vehicle_is_available($pdo, $r['vehicle_id'], $r['pickup_date'], $r['return_date'], $r['id'])) {
            flash_set('error', 'Cannot approve: the vehicle is not available for those dates (maintenance or overlapping booking).'); break;
        }
        $pdo->prepare("UPDATE reservations SET status='reserved', approved_at=NOW() WHERE id=?")->execute([$id]);
        if ($r['user_id']) add_notification($pdo, $r['user_id'], 'customer', 'booking', 'Booking Confirmed — ' . $r['make'] . ' ' . $r['model'],
            'Your rental ' . $r['reference_code'] . ' has been approved by DriveSphere Fleet Command.', 'customer/receipt.php?ref=' . $r['reference_code']);
        flash_set('success', 'Request ' . $r['reference_code'] . ' approved and reserved.');
        break;

    case 'reject':
        $r = get_res($pdo, $id);
        $reason = trim($_POST['reason'] ?? '');
        if (!$r || $r['status'] !== 'pending') { flash_set('error', 'Only pending requests can be rejected.'); break; }
        $pdo->prepare("UPDATE reservations SET status='rejected', rejection_reason=? WHERE id=?")->execute([$reason !== '' ? $reason : 'Not available', $id]);
        if ($r['user_id']) add_notification($pdo, $r['user_id'], 'customer', 'booking', 'Rental request declined',
            $r['reference_code'] . ' was declined. Reason: ' . ($reason !== '' ? $reason : 'Not available'), 'customer/receipt.php?ref=' . $r['reference_code']);
        flash_set('success', 'Request ' . $r['reference_code'] . ' rejected.');
        break;

    case 'dispatch':   // reserved -> en route (vehicle leaves the hub)
        $r = get_res($pdo, $id);
        if (!$r || $r['status'] !== 'reserved') { flash_set('error', 'Only reserved rentals can be dispatched.'); break; }
        $pdo->prepare("UPDATE reservations SET status='en_route', odometer_out=? WHERE id=?")->execute([$r['odometer'], $id]);
        $pdo->prepare("UPDATE vehicles SET status='rented' WHERE id=?")->execute([$r['vehicle_id']]);
        if ($r['user_id']) add_notification($pdo, $r['user_id'], 'customer', 'booking', 'Vehicle dispatched', 'Your ' . $r['make'] . ' ' . $r['model'] . ' is ready and on the road. Safe travels!', 'customer/receipt.php?ref=' . $r['reference_code']);
        flash_set('success', 'Vehicle dispatched. Rental is now En Route.');
        break;

    case 'send_receipt':   // "Send To User"
        $r = get_res($pdo, $id);
        if ($r && $r['user_id']) {
            add_notification($pdo, $r['user_id'], 'customer', 'payment', 'Receipt available — ' . $r['reference_code'], 'Your official DriveSphere receipt is ready to view.', 'customer/receipt.php?ref=' . $r['reference_code']);
            flash_set('success', 'Receipt sent to the customer.');
        }
        break;

    case 'toggle_post':
        $st = $pdo->prepare("SELECT is_posted, make, model FROM vehicles WHERE id=?");
        $st->execute([$id]);
        $veh = $st->fetch();
        if ($veh) {
            $newVal = $veh['is_posted'] ? 0 : 1;
            $pdo->prepare("UPDATE vehicles SET is_posted=? WHERE id=?")->execute([$newVal, $id]);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true, 
                    'is_posted' => $newVal,
                    'label' => $newVal ? 'Posted' : 'Not Posted',
                    'btn_text' => $newVal ? 'Unpost' : 'Post Now',
                    'message' => 'Vehicle ' . ($newVal ? 'published to live catalog.' : 'unposted from catalog.')
                ]);
                exit;
            }
            flash_set('success', 'Vehicle ' . ($newVal ? 'published to live catalog.' : 'unposted from catalog.'));
        }
        break;

    case 'vehicle_status':
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['available', 'maintenance'], true)) {
            $busy = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE vehicle_id=? AND status='en_route'");
            $busy->execute([$id]);
            if ($busy->fetchColumn() > 0) { flash_set('error', 'This vehicle is currently rented out (En Route).'); break; }
            $pdo->prepare("UPDATE vehicles SET status=? WHERE id=?")->execute([$status, $id]);
            flash_set('success', 'Vehicle marked as ' . $status . '.');
        }
        break;

    case 'vehicle_delete':
        $n = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE vehicle_id=?");
        $n->execute([$id]);
        if ($n->fetchColumn() > 0) { flash_set('error', 'This vehicle has rental records and cannot be deleted. Mark it as Maintenance instead.'); break; }
        $pdo->prepare("DELETE FROM vehicles WHERE id=?")->execute([$id]);
        flash_set('success', 'Vehicle deleted.');
        break;

    case 'mark_read':
        $pdo->exec("UPDATE notifications SET is_read=1 WHERE audience='admin'");
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }
        flash_set('success', 'All notifications marked as read.');
        break;

    case 'notif_toggle':
        $nid = (int)($_POST['id'] ?? 0);
        $st = $pdo->prepare("SELECT is_read FROM notifications WHERE id=? AND audience='admin'");
        $st->execute([$nid]);
        $curr = $st->fetchColumn();
        if ($curr !== false) {
            $newVal = $curr ? 0 : 1;
            $pdo->prepare("UPDATE notifications SET is_read=? WHERE id=? AND audience='admin'")->execute([$newVal, $nid]);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'id' => $nid, 'is_read' => $newVal]);
                exit;
            }
            flash_set('success', $newVal ? 'Notification marked as read.' : 'Notification marked as unread.');
        }
        break;

    case 'notif_dismiss':
        $nid = (int)($_POST['id'] ?? 0);
        if ($nid > 0) {
            $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND audience='admin'")->execute([$nid]);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => true, 'id' => $nid]);
                exit;
            }
            flash_set('success', 'Notification dismissed.');
        }
        break;

    default:
        flash_set('error', 'Unknown action.');
}
redirect($back);
