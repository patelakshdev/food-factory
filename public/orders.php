<?php
require_once __DIR__ . '/../config/app.php';
require_login();
$pageTitle = 'My Orders - Food Factory';

$user = current_user();
$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = :uid ORDER BY placed_at DESC');
$stmt->execute(['uid' => $user['id']]);
$orders = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section class="section-wrap orders-list">
    <h2>My Orders</h2>
    <?php if (!$orders): ?>
        <p>You haven't placed any orders yet. <a href="/public/menu.php">Browse the menu</a>.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Order #</th><th>Date</th><th>Items Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?= e($o['order_number']) ?></td>
                    <td><?= e($o['placed_at']) ?></td>
                    <td><?= money((float)$o['total']) ?></td>
                    <td><span class="status-badge status-<?= e($o['status']) ?>"><?= e(str_replace('_', ' ', $o['status'])) ?></span></td>
                    <td><a href="/public/order-details.php?order=<?= urlencode($o['order_number']) ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
