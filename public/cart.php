<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Your Cart - Food Factory';

$cart = get_or_create_cart();
$items = get_cart_items_detailed((int)$cart['id']);
$subtotal = array_sum(array_column($items, 'line_total'));

$couponError = null;
$discount = 0.0;
if (!empty($_SESSION['applied_coupon'])) {
    [$discount, $couponError] = evaluate_coupon($_SESSION['applied_coupon'], $subtotal, current_user()['id'] ?? null);
    if ($couponError) {
        unset($_SESSION['applied_coupon']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    csrf_verify();
    $code = strtoupper(trim((string)$_POST['coupon_code']));
    [$discount, $couponError] = evaluate_coupon($code, $subtotal, current_user()['id'] ?? null);
    if ($couponError) {
        flash('error', $couponError);
    } else {
        $_SESSION['applied_coupon'] = $code;
        flash('success', 'Coupon applied successfully!');
    }
    redirect('/public/cart.php');
}

require __DIR__ . '/../includes/header.php';
?>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">

<div class="section-wrap" style="max-width:1200px;">
    <!-- Page header -->
    <div style="margin-bottom:32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:32px;font-weight:800;color:var(--white);margin-bottom:4px;">🛒 Your Cart</h2>
            <p style="color:var(--text-muted);font-size:14px;"><?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> in your cart</p>
        </div>
        <a href="/public/menu.php" class="btn-outline">← Continue Shopping</a>
    </div>

    <?php if (!$items): ?>
        <!-- Empty cart -->
        <div style="text-align:center;padding:100px 20px;background:var(--card-bg);border:1px solid var(--border);border-radius:20px;">
            <div style="font-size:72px;margin-bottom:24px;">🛒</div>
            <h3 style="font-family:'Outfit',sans-serif;color:var(--white);font-size:24px;margin-bottom:12px;">Your cart is empty</h3>
            <p style="color:var(--text-muted);margin-bottom:28px;">Looks like you haven't added anything yet. Explore our menu!</p>
            <a href="/public/menu.php" class="btn">Browse Menu</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <!-- Cart items panel -->
            <div class="cart-items-panel">
                <div class="cart-panel-header">Items</div>
                <table class="cart-table" id="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">
                            <td>
                                <div class="cart-item-name"><?= e($it['name']) ?></div>
                                <?php if ($it['variant_name']): ?>
                                    <div class="cart-item-variant"><?= e($it['variant_name']) ?></div>
                                <?php endif; ?>
                                <?php if (!$it['available']): ?>
                                    <div style="color:#f87171;font-size:11px;margin-top:3px;">⚠️ No longer available</div>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-muted);"><?= money($it['unit_price']) ?></td>
                            <td>
                                <input type="number" min="0" max="20" value="<?= (int)$it['quantity'] ?>"
                                       class="cart-qty" data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">
                            </td>
                            <td class="line-total" style="font-weight:700;color:var(--white);"><?= money($it['line_total']) ?></td>
                            <td>
                                <button type="button" class="btn-sm danger remove-line" data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">
                                    🗑
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary sidebar -->
            <div class="cart-summary-panel">
                <h3>Order Summary</h3>

                <!-- Coupon -->
                <form method="post" style="margin-bottom:20px;">
                    <?= csrf_field() ?>
                    <div class="coupon-form">
                        <input type="text" name="coupon_code" placeholder="Coupon code"
                               value="<?= e($_SESSION['applied_coupon'] ?? '') ?>">
                        <button type="submit" name="apply_coupon" value="1" class="btn-sm">Apply</button>
                    </div>
                </form>

                <div class="cart-summary">
                    <div class="row"><span>Subtotal</span><span id="subtotal-value"><?= money($subtotal) ?></span></div>
                    <?php if ($discount > 0): ?>
                        <div class="row"><span>🎟️ Coupon discount</span><span style="color:#4ade80;">-<?= money($discount) ?></span></div>
                    <?php endif; ?>
                    <div class="row"><span>Delivery fee</span><span style="color:var(--text-muted);font-size:12px;">Calculated at checkout</span></div>
                    <div class="row total"><span>Estimated Total</span><span><?= money(max(0, $subtotal - $discount)) ?></span></div>
                </div>
                <p class="cart-note">* Final total including delivery fee will be shown at checkout.</p>
                <a href="/public/checkout.php" class="btn cart-cta" style="width:100%;justify-content:center;border-radius:8px;">
                    Proceed to Checkout →
                </a>
                <a href="/public/menu.php" class="btn-outline" style="width:100%;justify-content:center;margin-top:10px;border-radius:8px;text-align:center;">
                    + Add More Items
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function updateLine(id, qty) {
        const res = await fetch('/api/cart/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_item_id: id, quantity: qty, csrf_token: csrf }),
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Update failed.', 'error');
        }
    }

    document.querySelectorAll('.cart-qty').forEach(input => {
        input.addEventListener('change', () => updateLine(input.dataset.cartItemId, parseInt(input.value, 10)));
    });
    document.querySelectorAll('.remove-line').forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Remove this item?')) updateLine(btn.dataset.cartItemId, 0);
        });
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
