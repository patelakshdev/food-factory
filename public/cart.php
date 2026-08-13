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
        flash('success', 'Coupon applied.');
    }
    redirect('/public/cart.php');
}

require __DIR__ . '/../includes/header.php';
?>
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">

<section class="section-wrap">
    <h2>Your Cart</h2>

    <?php if (!$items): ?>
        <p>Your cart is empty. <a href="/public/menu.php">Browse the menu</a> to get started.</p>
    <?php else: ?>
        <table class="cart-table" id="cart-table">
            <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">
                    <td>
                        <?= e($it['name']) ?>
                        <?php if ($it['variant_name']): ?> (<?= e($it['variant_name']) ?>)<?php endif; ?>
                        <?php if (!$it['available']): ?><br><small style="color:#b3261e">No longer available</small><?php endif; ?>
                    </td>
                    <td><?= money($it['unit_price']) ?></td>
                    <td>
                        <input type="number" min="0" max="20" value="<?= (int)$it['quantity'] ?>"
                               class="cart-qty" data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">
                    </td>
                    <td class="line-total"><?= money($it['line_total']) ?></td>
                    <td><button type="button" class="btn-sm secondary remove-line" data-cart-item-id="<?= (int)$it['cart_item_id'] ?>">Remove</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post" style="margin-bottom:20px;max-width:360px;">
            <?= csrf_field() ?>
            <input type="text" name="coupon_code" placeholder="Coupon code" value="<?= e($_SESSION['applied_coupon'] ?? '') ?>">
            <button type="submit" name="apply_coupon" value="1" class="btn-sm">Apply Coupon</button>
        </form>

        <div class="cart-summary">
            <div class="row"><span>Subtotal</span><span id="subtotal-value"><?= money($subtotal) ?></span></div>
            <?php if ($discount > 0): ?>
                <div class="row"><span>Discount</span><span>-<?= money($discount) ?></span></div>
            <?php endif; ?>
            <div class="row total"><span>Estimated Total*</span><span><?= money(max(0, $subtotal - $discount)) ?></span></div>
            <p style="font-size:12px;color:#888;">*Delivery fee and final total are calculated at checkout.</p>
        </div>

        <div style="text-align:right;margin-top:16px;">
            <a href="/public/checkout.php" class="btn">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</section>

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
        }
    }

    document.querySelectorAll('.cart-qty').forEach(input => {
        input.addEventListener('change', () => updateLine(input.dataset.cartItemId, parseInt(input.value, 10)));
    });
    document.querySelectorAll('.remove-line').forEach(btn => {
        btn.addEventListener('click', () => updateLine(btn.dataset.cartItemId, 0));
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
