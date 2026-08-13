<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Order Details - Food Factory';

$orderNumber = trim((string)($_GET['order'] ?? ''));
$user = current_user();

if ($orderNumber === '') {
    redirect('/public/orders.php');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE order_number = :num LIMIT 1');
$stmt->execute(['num' => $orderNumber]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    require __DIR__ . '/../includes/error-404.php';
    exit;
}

// Access control: owner, matching guest session, or logged-in admin/staff.
$isOwner = ($user && (int)$order['user_id'] === (int)$user['id']);
$isGuestJustPlaced = (!empty($_GET['placed']) && empty($order['user_id']));
if (!$isOwner && !$isGuestJustPlaced && !is_admin_role()) {
    http_response_code(403);
    require __DIR__ . '/../includes/error-403.php';
    exit;
}

$itemsStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :id');
$itemsStmt->execute(['id' => $order['id']]);
$items = $itemsStmt->fetchAll();

$historyStmt = db()->prepare('SELECT * FROM order_status_history WHERE order_id = :id ORDER BY created_at ASC');
$historyStmt->execute(['id' => $order['id']]);
$history = $historyStmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section class="section-wrap">
    <?php if (!empty($_GET['placed'])): ?>
        <div class="alert alert-success">Your order has been placed! Order number: <strong><?= e($order['order_number']) ?></strong></div>
    <?php endif; ?>

    <h2>Order #<?= e($order['order_number']) ?></h2>
    <p>Status: <span class="status-badge status-<?= e($order['status']) ?>"><?= e(str_replace('_', ' ', $order['status'])) ?></span></p>

    <div class="admin-card">
        <h3>Items</h3>
        <table class="admin-table">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Line Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['item_name_snapshot']) ?><?php if ($it['variant_name_snapshot']): ?> (<?= e($it['variant_name_snapshot']) ?>)<?php endif; ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= money((float)$it['unit_price_snapshot']) ?></td>
                    <td><?= money((float)$it['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-summary">
            <div class="row"><span>Subtotal</span><span><?= money((float)$order['subtotal']) ?></span></div>
            <div class="row"><span>Discount</span><span>-<?= money((float)$order['discount']) ?></span></div>
            <div class="row"><span>Delivery Fee</span><span><?= money((float)$order['delivery_fee']) ?></span></div>
            <div class="row total"><span>Total</span><span><?= money((float)$order['total']) ?></span></div>
        </div>
    </div>

    <div class="admin-card">
        <h3>Order Timeline</h3>
        <ul>
            <?php foreach ($history as $h): ?>
                <li><?= e(str_replace('_', ' ', $h['status'])) ?> — <?= e($h['created_at']) ?><?php if ($h['note']): ?> (<?= e($h['note']) ?>)<?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
