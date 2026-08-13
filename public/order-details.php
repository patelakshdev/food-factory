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

$steps = ['pending', 'confirmed', 'preparing', 'ready', 'completed'];
$currentStatus = $order['status'];
$currentStatusIndex = array_search($currentStatus, $steps);
if ($currentStatusIndex === false) {
    if ($currentStatus === 'out_for_delivery') $currentStatusIndex = 3;
    else $currentStatusIndex = -1;
}

require __DIR__ . '/../includes/header.php';
?>

<div class="section-wrap" style="max-width:960px;">
    <?php if (!empty($_GET['placed'])): ?>
        <div class="alert alert-success" style="margin-bottom:24px;">
            <span>🎉</span>
            <div>
                <strong>Order placed successfully!</strong><br>
                Order number: <strong><?= e($order['order_number']) ?></strong>. Thank you for choosing Food Factory!
            </div>
        </div>
    <?php endif; ?>

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
        <div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:32px;font-weight:800;color:var(--white);margin-bottom:4px;">
                Order #<?= e($order['order_number']) ?>
            </h2>
            <p style="color:var(--text-muted);font-size:14px;">Placed on <?= e(date('M d, Y • h:i A', strtotime($order['placed_at']))) ?></p>
        </div>
        <div>
            <span class="status-badge status-<?= e($order['status']) ?>" style="font-size:14px;padding:6px 16px;">
                <?= e(str_replace('_', ' ', $order['status'])) ?>
            </span>
        </div>
    </div>

    <!-- Order Progress Tracker -->
    <?php if ($order['status'] !== 'cancelled'): ?>
        <div class="admin-card" style="padding:28px 20px;overflow-x:auto;">
            <div class="order-progress">
                <?php
                $labels = [
                    'pending'   => 'Order Placed',
                    'confirmed' => 'Confirmed',
                    'preparing' => 'Preparing',
                    'ready'     => $order['order_type'] === 'delivery' ? 'Out for Delivery' : 'Ready',
                    'completed' => 'Delivered'
                ];
                foreach ($steps as $idx => $st):
                    $isDone = ($currentStatusIndex !== false && $idx <= $currentStatusIndex);
                    $isCurrent = ($currentStatusIndex === $idx);
                    $class = $isDone ? 'done' : '';
                    if ($isCurrent) $class .= ' current';
                ?>
                    <div class="progress-step <?= $class ?>">
                        <div class="progress-dot"><?= $isDone ? '✓' : ($idx + 1) ?></div>
                        <div class="progress-label"><?= e($labels[$st]) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-error" style="margin-bottom:24px;">
            <span>❌</span> This order has been cancelled.
        </div>
    <?php endif; ?>

    <!-- Items breakdown -->
    <div class="admin-card">
        <h3>Order Items</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th style="text-align:right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--white);"><?= e($it['item_name_snapshot']) ?></div>
                        <?php if ($it['variant_name_snapshot']): ?>
                            <div style="font-size:12px;color:var(--text-muted);"><?= e($it['variant_name_snapshot']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= money((float)$it['unit_price_snapshot']) ?></td>
                    <td style="text-align:right;font-weight:600;color:var(--white);"><?= money((float)$it['line_total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary" style="margin-top:20px;margin-left:auto;max-width:320px;">
            <div class="row"><span>Subtotal</span><span><?= money((float)$order['subtotal']) ?></span></div>
            <?php if ((float)$order['discount'] > 0): ?>
                <div class="row"><span>Discount</span><span style="color:#4ade80;">-<?= money((float)$order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="row"><span>Delivery Fee</span><span><?= money((float)$order['delivery_fee']) ?></span></div>
            <div class="row total"><span>Total Paid</span><span><?= money((float)$order['total']) ?></span></div>
        </div>
    </div>

    <!-- Details grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div class="admin-card" style="margin:0;">
            <h3>Delivery & Contact Info</h3>
            <p style="font-size:14px;color:var(--text-muted);margin-bottom:8px;">
                <strong style="color:var(--white);">Type:</strong> <?= e(ucfirst($order['order_type'])) ?>
            </p>
            <?php if ($order['delivery_address']): ?>
                <p style="font-size:14px;color:var(--text-muted);margin-bottom:8px;">
                    <strong style="color:var(--white);">Address:</strong> <?= e($order['delivery_address']) ?>
                </p>
            <?php endif; ?>
            <?php if ($order['table_number']): ?>
                <p style="font-size:14px;color:var(--text-muted);margin-bottom:8px;">
                    <strong style="color:var(--white);">Table Number:</strong> <?= e($order['table_number']) ?>
                </p>
            <?php endif; ?>
            <p style="font-size:14px;color:var(--text-muted);">
                <strong style="color:var(--white);">Payment Method:</strong> <?= e(strtoupper($order['payment_method'])) ?> (<?= e(ucfirst($order['payment_status'])) ?>)
            </p>
        </div>

        <div class="admin-card" style="margin:0;">
            <h3>Status History</h3>
            <ul class="order-timeline">
                <?php foreach ($history as $h): ?>
                    <li>
                        <div class="tl-dot"></div>
                        <div>
                            <div class="tl-status"><?= e(str_replace('_', ' ', $h['status'])) ?></div>
                            <div style="font-size:12px;color:var(--text-muted);"><?= e(date('M d, Y • h:i A', strtotime($h['created_at']))) ?></div>
                            <?php if ($h['note']): ?>
                                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?= e($h['note']) ?></div>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div style="text-align:center;margin-top:20px;">
        <a href="/public/orders.php" class="btn-outline">← Back to My Orders</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
