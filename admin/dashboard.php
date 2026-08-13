<?php
require_once __DIR__ . '/../config/app.php';
require_permission('dashboard.view');
$pageTitle = 'Dashboard';

$todayOrders = db()->query("SELECT COUNT(*) c FROM orders WHERE DATE(placed_at) = CURDATE()")->fetch()['c'];
$todayRevenue = db()->query("SELECT COALESCE(SUM(total),0) t FROM orders WHERE DATE(placed_at) = CURDATE() AND status != 'cancelled'")->fetch()['t'];
$pendingOrders = db()->query("SELECT COUNT(*) c FROM orders WHERE status IN ('pending','confirmed','preparing')")->fetch()['c'];
$pendingReservations = db()->query("SELECT COUNT(*) c FROM reservations WHERE status = 'pending'")->fetch()['c'];
$pendingReviews = db()->query("SELECT COUNT(*) c FROM reviews WHERE status = 'pending'")->fetch()['c'];
$newMessages = db()->query("SELECT COUNT(*) c FROM contact_messages WHERE status = 'new'")->fetch()['c'];

$recentOrders = db()->query(
    "SELECT * FROM orders ORDER BY placed_at DESC LIMIT 8"
)->fetchAll();

require __DIR__ . '/../includes/admin-header.php';
?>

<div class="kpi-grid">
    <div class="kpi-card"><div class="value"><?= (int)$todayOrders ?></div><div class="label">Orders Today</div></div>
    <div class="kpi-card"><div class="value"><?= money((float)$todayRevenue) ?></div><div class="label">Revenue Today</div></div>
    <div class="kpi-card"><div class="value"><?= (int)$pendingOrders ?></div><div class="label">Active Orders</div></div>
    <div class="kpi-card"><div class="value"><?= (int)$pendingReservations ?></div><div class="label">Pending Reservations</div></div>
    <div class="kpi-card"><div class="value"><?= (int)$pendingReviews ?></div><div class="label">Reviews to Moderate</div></div>
    <div class="kpi-card"><div class="value"><?= (int)$newMessages ?></div><div class="label">New Messages</div></div>
</div>

<div class="admin-card">
    <h3>Recent Orders</h3>
    <table class="admin-table">
        <thead><tr><th>Order #</th><th>Type</th><th>Total</th><th>Status</th><th>Placed</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
            <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e(str_replace('_', ' ', $o['order_type'])) ?></td>
                <td><?= money((float)$o['total']) ?></td>
                <td><span class="status-badge status-<?= e($o['status']) ?>"><?= e(str_replace('_', ' ', $o['status'])) ?></span></td>
                <td><?= e($o['placed_at']) ?></td>
                <td><a href="/admin/orders/index.php?highlight=<?= (int)$o['id'] ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
