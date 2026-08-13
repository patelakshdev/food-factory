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

<div class="section-wrap">
    <div style="margin-bottom:32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:32px;font-weight:800;color:var(--white);margin-bottom:4px;">📦 My Orders</h2>
            <p style="color:var(--text-muted);font-size:14px;">Track and manage your order history.</p>
        </div>
        <a href="/public/menu.php" class="btn-outline">Order Food</a>
    </div>

    <?php if (!$orders): ?>
        <div style="text-align:center;padding:80px 20px;background:var(--card-bg);border:1px solid var(--border);border-radius:20px;">
            <div style="font-size:64px;margin-bottom:20px;">📦</div>
            <h3 style="font-family:'Outfit',sans-serif;color:var(--white);font-size:22px;margin-bottom:10px;">No orders placed yet</h3>
            <p style="color:var(--text-muted);margin-bottom:24px;">Explore our menu and place your first order!</p>
            <a href="/public/menu.php" class="btn">Explore Menu</a>
        </div>
    <?php else: ?>
        <div class="orders-grid">
            <?php foreach ($orders as $o): ?>
                <div class="order-card">
                    <div class="order-card-info">
                        <div class="order-number">Order #<?= e($o['order_number']) ?></div>
                        <div class="order-meta">
                            <span>📅 <?= e(date('M d, Y • h:i A', strtotime($o['placed_at']))) ?></span>
                            <span>🚚 <?= e(ucfirst($o['order_type'])) ?></span>
                            <span>💳 <?= e(strtoupper($o['payment_method'])) ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="status-badge status-<?= e($o['status']) ?>">
                            <?= e(str_replace('_', ' ', $o['status'])) ?>
                        </span>
                    </div>
                    <div class="order-card-total">
                        <?= money((float)$o['total']) ?>
                    </div>
                    <div class="order-card-actions">
                        <a href="/public/order-details.php?order=<?= urlencode($o['order_number']) ?>" class="btn-sm">
                            View Details →
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
