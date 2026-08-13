<?php
declare(strict_types=1);

/** Finds or creates the active cart for the current user/session. */
function get_or_create_cart(): array
{
    $userId = current_user()['id'] ?? null;

    if ($userId) {
        $stmt = db()->prepare('SELECT * FROM carts WHERE user_id = :uid AND status = "active" LIMIT 1');
        $stmt->execute(['uid' => $userId]);
    } else {
        if (empty($_SESSION['cart_session_id'])) {
            $_SESSION['cart_session_id'] = bin2hex(random_bytes(16));
        }
        $stmt = db()->prepare('SELECT * FROM carts WHERE session_id = :sid AND status = "active" LIMIT 1');
        $stmt->execute(['sid' => $_SESSION['cart_session_id']]);
    }

    $cart = $stmt->fetch();
    if ($cart) {
        return $cart;
    }

    $insert = db()->prepare('INSERT INTO carts (user_id, session_id, status) VALUES (:uid, :sid, "active")');
    $insert->execute([
        'uid' => $userId,
        'sid' => $userId ? null : $_SESSION['cart_session_id'],
    ]);
    $id = (int)db()->lastInsertId();

    $stmt = db()->prepare('SELECT * FROM carts WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

/** Full cart contents with live item data joined in (never trusts stored prices). */
function get_cart_items_detailed(int $cartId): array
{
    $stmt = db()->prepare(
        'SELECT ci.id AS cart_item_id, ci.quantity, ci.addon_ids, ci.special_instructions,
                mi.id AS menu_item_id, mi.name, mi.price AS base_price, mi.image, mi.status,
                iv.id AS variant_id, iv.name AS variant_name, iv.price_delta
         FROM cart_items ci
         JOIN menu_items mi ON mi.id = ci.menu_item_id
         LEFT JOIN item_variants iv ON iv.id = ci.variant_id
         WHERE ci.cart_id = :cart_id
         ORDER BY ci.id ASC'
    );
    $stmt->execute(['cart_id' => $cartId]);
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $row) {
        $addonTotal = 0.0;
        $addonNames = [];
        if (!empty($row['addon_ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $row['addon_ids'])));
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $addonStmt = db()->prepare("SELECT id, name, price FROM addons WHERE id IN ($in) AND menu_item_id = ?");
                $addonStmt->execute([...$ids, $row['menu_item_id']]);
                foreach ($addonStmt->fetchAll() as $addon) {
                    $addonTotal += (float)$addon['price'];
                    $addonNames[] = $addon['name'];
                }
            }
        }

        $unitPrice = (float)$row['base_price'] + (float)($row['price_delta'] ?? 0) + $addonTotal;
        $lineTotal = $unitPrice * (int)$row['quantity'];

        $items[] = [
            'cart_item_id'   => (int)$row['cart_item_id'],
            'menu_item_id'   => (int)$row['menu_item_id'],
            'name'           => $row['name'],
            'variant_name'   => $row['variant_name'],
            'addon_names'    => $addonNames,
            'image'          => $row['image'],
            'quantity'       => (int)$row['quantity'],
            'unit_price'     => $unitPrice,
            'line_total'     => $lineTotal,
            'available'      => $row['status'] === 'active',
            'special_instructions' => $row['special_instructions'],
        ];
    }
    return $items;
}

function get_cart_subtotal(int $cartId): float
{
    $total = 0.0;
    foreach (get_cart_items_detailed($cartId) as $item) {
        $total += $item['line_total'];
    }
    return $total;
}

function get_cart_count(): int
{
    if (empty($_SESSION['user_id']) && empty($_SESSION['cart_session_id'])) {
        return 0;
    }
    $cart = get_or_create_cart();
    $stmt = db()->prepare('SELECT COALESCE(SUM(quantity),0) AS c FROM cart_items WHERE cart_id = :id');
    $stmt->execute(['id' => $cart['id']]);
    return (int)$stmt->fetch()['c'];
}

/** Validates & applies a coupon to a subtotal. Returns [discount, error]. */
function evaluate_coupon(string $code, float $subtotal, ?int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM coupons WHERE code = :code AND status = "active" LIMIT 1');
    $stmt->execute(['code' => $code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return [0.0, 'Invalid coupon code.', null];
    }
    $now = time();
    if ($coupon['starts_at'] && strtotime($coupon['starts_at']) > $now) {
        return [0.0, 'This coupon is not active yet.', null];
    }
    if ($coupon['ends_at'] && strtotime($coupon['ends_at']) < $now) {
        return [0.0, 'This coupon has expired.', null];
    }
    if ($subtotal < (float)$coupon['min_order']) {
        return [0.0, 'Order does not meet the minimum amount for this coupon.', null];
    }
    if ($coupon['usage_limit'] !== null && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
        return [0.0, 'This coupon has reached its usage limit.', null];
    }
    if ($userId && $coupon['per_user_limit'] !== null) {
        $used = db()->prepare('SELECT COUNT(*) AS c FROM coupon_redemptions WHERE coupon_id = :cid AND user_id = :uid');
        $used->execute(['cid' => $coupon['id'], 'uid' => $userId]);
        if ((int)$used->fetch()['c'] >= (int)$coupon['per_user_limit']) {
            return [0.0, 'You have already used this coupon.', null];
        }
    }
    if ($userId && $coupon['first_order_only']) {
        $orderCount = db()->prepare('SELECT COUNT(*) AS c FROM orders WHERE user_id = :uid');
        $orderCount->execute(['uid' => $userId]);
        if ((int)$orderCount->fetch()['c'] > 0) {
            return [0.0, 'This coupon is valid for first orders only.', null];
        }
    }

    $discount = match ($coupon['type']) {
        'percentage'    => $subtotal * ((float)$coupon['value'] / 100),
        'fixed'         => (float)$coupon['value'],
        'free_delivery' => 0.0, // handled separately as delivery fee waiver
        default         => 0.0,
    };
    if ($coupon['max_discount'] !== null) {
        $discount = min($discount, (float)$coupon['max_discount']);
    }
    $discount = min($discount, $subtotal);

    return [$discount, null, $coupon];
}
