<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = 'Checkout - Food Factory';

$cart = get_or_create_cart();
$items = get_cart_items_detailed((int)$cart['id']);

if (!$items) {
    flash('error', 'Your cart is empty.');
    redirect('/public/cart.php');
}

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (!rate_limit('checkout_submit', 10, 60)) {
        flash('error', 'Too many attempts. Please wait a moment and try again.');
        redirect('/public/checkout.php');
    }

    $v = new Validator($_POST);
    $orderType = $_POST['order_type'] ?? 'delivery';
    $v->inArray('order_type', ['delivery', 'pickup', 'dine_in'], 'Order type');

    if (!$user) {
        $v->required('guest_name', 'Name')->maxLength('guest_name', 120, 'Name')
          ->required('guest_email', 'Email')->email('guest_email')
          ->required('guest_phone', 'Phone')->phone('guest_phone');
    }
    if ($orderType === 'delivery') {
        $v->required('delivery_address', 'Delivery address')->maxLength('delivery_address', 500, 'Delivery address');
    }
    if ($orderType === 'dine_in') {
        $v->required('table_number', 'Table number');
    }
    $v->inArray('payment_method', ['cod', 'card', 'upi', 'wallet'], 'Payment method');

    if (empty($_SESSION['checkout_idempotency_key'])) {
        $_SESSION['checkout_idempotency_key'] = bin2hex(random_bytes(16));
    }
    $idempotencyKey = $_SESSION['checkout_idempotency_key'];

    if ($v->fails()) {
        $errors = $v->errors();
    } else {
        $pdo = db();
        try {
            $pdo->beginTransaction();

            $freshItems = get_cart_items_detailed((int)$cart['id']);
            $freshItems = array_values(array_filter($freshItems, fn($i) => $i['available']));

            if (!$freshItems) {
                throw new RuntimeException('Your cart no longer has any available items.');
            }

            $subtotal = array_sum(array_column($freshItems, 'line_total'));

            $discount = 0.0;
            $couponRow = null;
            if (!empty($_SESSION['applied_coupon'])) {
                [$discount, $couponErr, $couponRow] = evaluate_coupon(
                    $_SESSION['applied_coupon'], $subtotal, $user['id'] ?? null
                );
                if ($couponErr) { $discount = 0.0; $couponRow = null; }
            }

            $deliveryFeeSetting = (float)(db()->query("SELECT setting_value FROM settings WHERE setting_key='delivery_fee'")->fetchColumn() ?: 40);
            $freeDeliveryAbove  = (float)(db()->query("SELECT setting_value FROM settings WHERE setting_key='free_delivery_above'")->fetchColumn() ?: 499);

            $deliveryFee = 0.0;
            if ($orderType === 'delivery') {
                $deliveryFee = $subtotal >= $freeDeliveryAbove ? 0.0 : $deliveryFeeSetting;
                if ($couponRow && $couponRow['type'] === 'free_delivery') $deliveryFee = 0.0;
            }

            $total = max(0, $subtotal - $discount) + $deliveryFee;
            $orderNumber = generate_order_number();

            $stmt = $pdo->prepare(
                'INSERT INTO orders (order_number, user_id, guest_name, guest_email, guest_phone, order_type,
                    status, payment_method, payment_status, subtotal, discount, delivery_fee, total, coupon_id,
                    delivery_address, table_number, notes, idempotency_key)
                 VALUES (:order_number, :user_id, :guest_name, :guest_email, :guest_phone, :order_type,
                    "pending", :payment_method, "unpaid", :subtotal, :discount, :delivery_fee, :total, :coupon_id,
                    :delivery_address, :table_number, :notes, :idempotency_key)'
            );
            $stmt->execute([
                'order_number'     => $orderNumber,
                'user_id'          => $user['id'] ?? null,
                'guest_name'       => $user ? null : $_POST['guest_name'],
                'guest_email'      => $user ? null : $_POST['guest_email'],
                'guest_phone'      => $user ? null : $_POST['guest_phone'],
                'order_type'       => $orderType,
                'payment_method'   => $_POST['payment_method'] ?? 'cod',
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'delivery_fee'     => $deliveryFee,
                'total'            => $total,
                'coupon_id'        => $couponRow['id'] ?? null,
                'delivery_address' => $orderType === 'delivery' ? $_POST['delivery_address'] : null,
                'table_number'     => $orderType === 'dine_in' ? $_POST['table_number'] : null,
                'notes'            => $_POST['notes'] ?? null,
                'idempotency_key'  => $idempotencyKey,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, menu_item_id, item_name_snapshot, variant_name_snapshot,
                    unit_price_snapshot, quantity, line_total, special_instructions)
                 VALUES (:order_id, :menu_item_id, :name, :variant, :unit_price, :qty, :line_total, :instr)'
            );
            foreach ($freshItems as $it) {
                $itemStmt->execute([
                    'order_id'   => $orderId,
                    'menu_item_id' => $it['menu_item_id'],
                    'name'       => $it['name'],
                    'variant'    => $it['variant_name'],
                    'unit_price' => $it['unit_price'],
                    'qty'        => $it['quantity'],
                    'line_total' => $it['line_total'],
                    'instr'      => $it['special_instructions'],
                ]);
            }

            $histStmt = $pdo->prepare(
                'INSERT INTO order_status_history (order_id, status, changed_by, note) VALUES (:oid, "pending", :uid, "Order placed")'
            );
            $histStmt->execute(['oid' => $orderId, 'uid' => $user['id'] ?? null]);

            if ($couponRow) {
                $pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = :id')->execute(['id' => $couponRow['id']]);
                $pdo->prepare('INSERT INTO coupon_redemptions (coupon_id, user_id, order_id) VALUES (:cid, :uid, :oid)')
                    ->execute(['cid' => $couponRow['id'], 'uid' => $user['id'] ?? null, 'oid' => $orderId]);
            }

            $pdo->prepare('UPDATE carts SET status = "converted" WHERE id = :id')->execute(['id' => $cart['id']]);
            $pdo->commit();

            unset($_SESSION['applied_coupon'], $_SESSION['checkout_idempotency_key']);
            redirect('/public/order-details.php?order=' . $orderNumber . '&placed=1');
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[CHECKOUT ERROR] ' . $e->getMessage());
            flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'We could not place your order. Please try again.');
            redirect('/public/checkout.php');
        }
    }
}

$subtotal = array_sum(array_column($items, 'line_total'));
$discount = 0.0;
if (!empty($_SESSION['applied_coupon'])) {
    [$discount] = evaluate_coupon($_SESSION['applied_coupon'], $subtotal, $user['id'] ?? null);
}

require __DIR__ . '/../includes/header.php';
?>

<div class="section-wrap" style="max-width:1200px;">
    <div style="margin-bottom:28px;">
        <h2 style="font-family:'Outfit',sans-serif;font-size:32px;font-weight:800;color:var(--white);margin-bottom:4px;">Checkout</h2>
        <p style="color:var(--text-muted);font-size:14px;">Complete your order details below.</p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:20px;">
            <span>⚠️</span>
            <div><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
        </div>
    <?php endif; ?>

    <div class="checkout-grid">
        <!-- Left: Order form -->
        <div>
            <!-- Order type -->
            <div class="checkout-panel">
                <h3>📦 Delivery Method</h3>
                <form method="post" id="checkout-form">
                    <?= csrf_field() ?>
                    <div class="order-type-toggle" id="order-type-toggle">
                        <label id="lbl-delivery">
                            <input type="radio" name="order_type" value="delivery" checked>
                            <span>🚚 Delivery</span>
                        </label>
                        <label id="lbl-pickup">
                            <input type="radio" name="order_type" value="pickup">
                            <span>🏪 Pickup</span>
                        </label>
                        <label id="lbl-dine">
                            <input type="radio" name="order_type" value="dine_in">
                            <span>🍽️ Dine-In</span>
                        </label>
                    </div>

                    <!-- Guest fields -->
                    <?php if (!$user): ?>
                        <div style="background:rgba(255,193,7,0.07);border:1px solid rgba(255,193,7,0.2);border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                            <p style="font-size:13px;color:var(--gold);margin-bottom:4px;">💡 Have an account?
                                <a href="/public/login.php" style="color:var(--accent);font-weight:600;">Sign in</a> for faster checkout & order history.
                            </p>
                        </div>
                        <input type="text" name="guest_name" placeholder="Full Name" value="<?= old('guest_name') ?>" required>
                        <input type="email" name="guest_email" placeholder="Email" value="<?= old('guest_email') ?>" required>
                        <input type="tel" name="guest_phone" placeholder="Phone" value="<?= old('guest_phone') ?>" required>
                    <?php else: ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--dark3);border:1px solid var(--border);border-radius:8px;margin-bottom:16px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#ff8c00);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:15px;">
                                <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600;color:var(--white);font-size:14px;"><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></div>
                                <div style="font-size:12px;color:var(--text-muted);"><?= e($user['email']) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Delivery address -->
                    <div id="delivery-address-wrap">
                        <textarea name="delivery_address" rows="3" placeholder="📍 Full delivery address (street, area, city)"></textarea>
                    </div>
                    <!-- Table number -->
                    <div id="table-number-wrap" style="display:none;">
                        <input type="text" name="table_number" placeholder="Table number">
                    </div>
                    <!-- Notes -->
                    <textarea name="notes" rows="2" placeholder="🗒️ Special instructions (optional)"></textarea>
            </div><!-- end checkout-panel -->

            <!-- Payment method -->
            <div class="checkout-panel" style="margin-top:20px;">
                <h3>💳 Payment Method</h3>
                <div class="payment-methods" id="payment-options">
                    <div class="payment-method-card active" data-pm="cod" onclick="selectPM(this,'cod')">
                        <div class="pm-icon">💵</div>
                        <div class="pm-label">Cash / Counter</div>
                    </div>
                    <div class="payment-method-card" data-pm="upi" onclick="selectPM(this,'upi')">
                        <div class="pm-icon">📲</div>
                        <div class="pm-label">UPI</div>
                    </div>
                    <div class="payment-method-card" data-pm="card" onclick="selectPM(this,'card')">
                        <div class="pm-icon">💳</div>
                        <div class="pm-label">Card</div>
                    </div>
                </div>
                <input type="hidden" name="payment_method" id="payment_method" value="cod">

                    <button type="submit" class="btn" style="width:100%;justify-content:center;border-radius:8px;font-size:16px;padding:16px;">
                        🎉 Place Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Order summary -->
        <div class="checkout-summary">
            <h3>🧾 Order Summary</h3>
            <?php foreach ($items as $it): ?>
                <div class="summary-item">
                    <span><?= e($it['name']) ?> × <?= (int)$it['quantity'] ?></span>
                    <span><?= money($it['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-item"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
            <?php if ($discount > 0): ?>
                <div class="summary-item"><span>🎟️ Discount</span><span style="color:#4ade80;">-<?= money($discount) ?></span></div>
            <?php endif; ?>
            <div class="summary-item" id="delivery-fee-row"><span>Delivery Fee</span><span id="delivery-fee-val" style="color:var(--text-muted);font-size:12px;">Calculated at order</span></div>
            <div class="summary-total">
                <span>Total</span>
                <span><?= money(max(0, $subtotal - $discount)) ?>+</span>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">Delivery fee added for delivery orders at time of order.</p>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-muted);">
                    🔒 Secure checkout — your info is safe
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Payment method selection
function selectPM(el, val) {
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('payment_method').value = val;
}

// Order type toggle — show/hide address or table fields
document.querySelectorAll('input[name="order_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const addrWrap = document.getElementById('delivery-address-wrap');
        const tableWrap = document.getElementById('table-number-wrap');
        const deliveryFeeRow = document.getElementById('delivery-fee-row');
        addrWrap.style.display  = this.value === 'delivery' ? '' : 'none';
        tableWrap.style.display = this.value === 'dine_in' ? '' : 'none';
        deliveryFeeRow.style.display = this.value === 'delivery' ? '' : 'none';
        // Update labels
        document.querySelectorAll('.order-type-toggle label').forEach(l => l.classList.remove('selected'));
        this.closest('label').classList.add('selected');
    });
});
// Set initial selected label
document.querySelector('.order-type-toggle input:checked')?.closest('label')?.classList.add('selected');
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
