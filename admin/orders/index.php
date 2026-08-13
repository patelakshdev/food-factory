<?php
require_once __DIR__ . '/../../config/app.php';
require_permission('orders.view');
$pageTitle = 'Orders';

// Valid forward status transitions. Prevents e.g. jumping straight from
// pending to completed, or reviving a cancelled order.
const ORDER_TRANSITIONS = [
    'pending'          => ['confirmed', 'cancelled'],
    'confirmed'        => ['preparing', 'cancelled'],
    'preparing'        => ['ready', 'cancelled'],
    'ready'            => ['out_for_delivery', 'completed', 'cancelled'],
    'out_for_delivery' => ['completed', 'cancelled'],
    'completed'        => [],
    'cancelled'        => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    csrf_verify();
    require_permission('orders.update');

    $orderId = (int)$_POST['order_id'];
    $newStatus = (string)$_POST['status'];

    $stmt = db()->prepare('SELECT status FROM orders WHERE id = :id');
    $stmt->execute(['id' => $orderId]);
    $current = $stmt->fetchColumn();

    if ($current === false) {
        flash('error', 'Order not found.');
    } elseif (!in_array($newStatus, ORDER_TRANSITIONS[$current] ?? [], true)) {
        flash('error', "Cannot move an order from '{$current}' to '{$newStatus}'.");
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE orders SET status = :s WHERE id = :id')
                ->execute(['s' => $newStatus, 'id' => $orderId]);
            $pdo->prepare('INSERT INTO order_status_history (order_id, status, changed_by) VALUES (:oid, :s, :uid)')
                ->execute(['oid' => $orderId, 's' => $newStatus, 'uid' => current_user()['id']]);
            $pdo->commit();
            audit_log(current_user()['id'], 'order.status_update', 'order', $orderId, "→ {$newStatus}");
            flash('success', "Order updated to {$newStatus}.");
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[ORDER STATUS UPDATE ERROR] ' . $e->getMessage());
            flash('error', 'Could not update the order. Please try again.');
        }
    }
    redirect('/admin/orders/index.php');
}

$statusFilter = $_GET['status'] ?? '';
$sql = 'SELECT * FROM orders';
$params = [];
if ($statusFilter !== '') {
    $sql .= ' WHERE status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY placed_at DESC LIMIT 100';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require __DIR__ . '/../../includes/admin-header.php';
?>

<div class="admin-card">
    <form method="get" style="margin-bottom:16px;">
        <select name="status" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (array_keys(ORDER_TRANSITIONS) as $s): ?>
                <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $s)) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <table class="admin-table">
        <thead><tr><th>Order #</th><th>Type</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th><?php if (user_can('orders.update')): ?><th>Update</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr id="order-<?= (int)$o['id'] ?>">
                <td><a href="/public/order-details.php?order=<?= urlencode($o['order_number']) ?>" target="_blank"><?= e($o['order_number']) ?></a></td>
                <td><?= e(str_replace('_', ' ', $o['order_type'])) ?></td>
                <td><?= e($o['guest_name'] ?: ('User #' . $o['user_id'])) ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= e(str_replace('_', ' ', $o['status'])) ?></span></td>
                <td><?= e($o['placed_at']) ?></td>
                <?php if (user_can('orders.update')): ?>
                <td>
                    <?php $next = ORDER_TRANSITIONS[$o['status']] ?? []; ?>
                    <?php if ($next): ?>
                        <form method="post" style="display:flex;gap:6px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                            <select name="status">
                                <?php foreach ($next as $n): ?><option value="<?= e($n) ?>"><?= e(str_replace('_', ' ', $n)) ?></option><?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" value="1" class="btn-sm">Update</button>
                        </form>
                    <?php else: ?>
                        <em>Final</em>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../includes/admin-footer.php'; ?>
