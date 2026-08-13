<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/app.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input') ?: '[]', true) ?? [];

if (!hash_equals($_SESSION['_csrf'] ?? '', (string)($input['csrf_token'] ?? ''))) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Session expired, please refresh the page.']);
    exit;
}

if (!rate_limit('cart_add', 60, 60)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests, please slow down.']);
    exit;
}

$menuItemId = (int)($input['menu_item_id'] ?? 0);
$variantId = !empty($input['variant_id']) ? (int)$input['variant_id'] : null;
$quantity = max(1, min(20, (int)($input['quantity'] ?? 1)));
$addonIds = array_filter(array_map('intval', (array)($input['addon_ids'] ?? [])));

$stmt = db()->prepare("SELECT * FROM menu_items WHERE id = :id AND status = 'active' LIMIT 1");
$stmt->execute(['id' => $menuItemId]);
$item = $stmt->fetch();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'This item is unavailable.']);
    exit;
}

if ($variantId) {
    $v = db()->prepare('SELECT id FROM item_variants WHERE id = :id AND menu_item_id = :mid AND status = "active"');
    $v->execute(['id' => $variantId, 'mid' => $menuItemId]);
    if (!$v->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid item option selected.']);
        exit;
    }
}

$cart = get_or_create_cart();

// Merge quantity if an identical line already exists.
$addonKey = $addonIds ? implode(',', $addonIds) : null;
$existing = db()->prepare(
    'SELECT id, quantity FROM cart_items
     WHERE cart_id = :cart_id AND menu_item_id = :item_id
       AND variant_id <=> :variant_id AND addon_ids <=> :addon_ids'
);
$existing->execute([
    'cart_id' => $cart['id'], 'item_id' => $menuItemId,
    'variant_id' => $variantId, 'addon_ids' => $addonKey,
]);
$row = $existing->fetch();

if ($row) {
    $upd = db()->prepare('UPDATE cart_items SET quantity = quantity + :q WHERE id = :id');
    $upd->execute(['q' => $quantity, 'id' => $row['id']]);
} else {
    $ins = db()->prepare(
        'INSERT INTO cart_items (cart_id, menu_item_id, variant_id, addon_ids, quantity)
         VALUES (:cart_id, :item_id, :variant_id, :addon_ids, :quantity)'
    );
    $ins->execute([
        'cart_id' => $cart['id'], 'item_id' => $menuItemId,
        'variant_id' => $variantId, 'addon_ids' => $addonKey, 'quantity' => $quantity,
    ]);
}

echo json_encode(['success' => true, 'cart_count' => get_cart_count()]);
