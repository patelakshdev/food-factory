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

    // Idempotency: reuse a key stored in session for this checkout attempt so a
    // double-submit (double click / back button) cannot create two orders.
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

            // Re-fetch cart items INSIDE the transaction and recompute everything
            // from the database. Nothing from the client-submitted totals is trusted.
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
                if ($couponErr) {
                    $discount = 0.0;
                    $couponRow = null;
                }
            }

            $deliveryFeeSetting = (float)(db()->query("SELECT setting_value FROM settings WHERE setting_key='delivery_fee'")->fetchColumn() ?: 40);
            $freeDeliveryAbove = (float)(db()->query("SELECT setting_value FROM settings WHERE setting_key='free_delivery_above'")->fetchColumn() ?: 499);

            $deliveryFee = 0.0;
            if ($orderType === 'delivery') {
                $deliveryFee = $subtotal >= $freeDeliveryAbove ? 0.0 : $deliveryFeeSetting;
                if ($couponRow && $couponRow['type'] === 'free_delivery') {
                    $deliveryFee = 0.0;
                }
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
                'order_number' => $orderNumber,
                'user_id' => $user['id'] ?? null,
                'guest_name' => $user ? null : $_POST['guest_name'],
                'guest_email' => $user ? null : $_POST['guest_email'],
                'guest_phone' => $user ? null : $_POST['guest_phone'],
                'order_type' => $orderType,
                'payment_method' => $_POST['payment_method'] ?? 'cod',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'coupon_id' => $couponRow['id'] ?? null,
                'delivery_address' => $orderType === 'delivery' ? $_POST['delivery_address'] : null,
                'table_number' => $orderType === 'dine_in' ? $_POST['table_number'] : null,
                'notes' => $_POST['notes'] ?? null,
                'idempotency_key' => $idempotencyKey,
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, menu_item_id, item_name_snapshot, variant_name_snapshot,
                    unit_price_snapshot, quantity, line_total, special_instructions)
                 VALUES (:order_id, :menu_item_id, :name, :variant, :unit_price, :qty, :line_total, :instr)'
            );
            foreach ($freshItems as $it) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'menu_item_id' => $it['menu_item_id'],
                    'name' => $it['name'],
                    'variant' => $it['variant_name'],
                    'unit_price' => $it['unit_price'],
                    'qty' => $it['quantity'],
                    'line_total' => $it['line_total'],
                    'instr' => $it['special_instructions'],
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

            // Close out the cart.
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

<section class="section-wrap">
    <h2>Checkout</h2>

    <?php if ($errors): ?>
        <div class="alert alert-error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
        <form method="post">
            <?= csrf_field() ?>

            <div class="order-type-toggle">
                <label><input type="radio" name="order_type" value="delivery" checked><span>Delivery</span></label>
                <label><input type="radio" name="order_type" value="pickup"><span>Pickup</span></label>
                <label><input type="radio" name="order_type" value="dine_in"><span>Dine-in</span></label>
            </div>

            <?php if (!$user): ?>
                <input type="text" name="guest_name" placeholder="Full Name" value="<?= old('guest_name') ?>" required>
                <input type="email" name="guest_email" placeholder="Email" value="<?= old('guest_email') ?>" required>
                <input type="tel" name="guest_phone" placeholder="Phone" value="<?= old('guest_phone') ?>" required>
                <p style="font-size:13px;color:#888;">Have an account? <a href="/public/login.php">Log in</a> for faster checkout & order history.</p>
            <?php endif; ?>

            <textarea name="delivery_address" rows="3" placeholder="Delivery address (required for delivery orders)"></textarea>
            <input type="text" name="table_number" placeholder="Table number (dine-in only)">
            <textarea name="notes" rows="2" placeholder="Any special instructions?"></textarea>

            <select name="payment_method">
                <option value="cod">Cash on Delivery / Pay at Counter</option>
                <option value="card">Card</option>
                <option value="upi">UPI</option>
            </select>

            <button type="submit" class="btn">Place Order</button>
        </form>

        <div class="admin-card">
            <h3>Order Summary</h3>
            <?php foreach ($items as $it): ?>
                <div class="cart-summary row">
                    <span><?= e($it['name']) ?> × <?= (int)$it['quantity'] ?></span>
                    <span><?= money($it['line_total']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="cart-summary">
                <div class="row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                <?php if ($discount > 0): ?><div class="row"><span>Discount</span><span>-<?= money($discount) ?></span></div><?php endif; ?>
                <p style="font-size:12px;color:#888;">Delivery fee is added for delivery orders and calculated at order time.</p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
