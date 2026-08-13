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

$cartItemId = (int)($input['cart_item_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);
$cart = get_or_create_cart();

if ($quantity <= 0) {
    $stmt = db()->prepare('DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id');
    $stmt->execute(['id' => $cartItemId, 'cart_id' => $cart['id']]);
} else {
    $quantity = min(20, $quantity);
    $stmt = db()->prepare('UPDATE cart_items SET quantity = :q WHERE id = :id AND cart_id = :cart_id');
    $stmt->execute(['q' => $quantity, 'id' => $cartItemId, 'cart_id' => $cart['id']]);
}

$items = get_cart_items_detailed((int)$cart['id']);
$subtotal = array_sum(array_column($items, 'line_total'));

echo json_encode([
    'success'  => true,
    'subtotal' => round($subtotal, 2),
    'cart_count' => get_cart_count(),
]);
