<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);

$cartId = (int)($_POST['id'] ?? 0);
$action = (string)($_POST['action'] ?? '');
$userId = (int)$user['id'];

if ($cartId <= 0 || !in_array($action, ['plus', 'minus'], true)) {
    redirect_to('cart_view.php');
}

$stmt = $conn->prepare(
    'SELECT c.id, c.product_id, c.quantity, p.stock
     FROM cart c
     JOIN products p ON p.id = c.product_id
     WHERE c.id = ? AND c.user_id = ?
     LIMIT 1'
);
$stmt->bind_param('ii', $cartId, $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    redirect_to('cart_view.php');
}

$quantity = (int)$row['quantity'];
$stock = (int)$row['stock'];

if ($action === 'plus' && $quantity < $stock) {
    $quantity++;
}
if ($action === 'minus' && $quantity > 1) {
    $quantity--;
}

$stmt = $conn->prepare('UPDATE cart SET quantity=? WHERE id=? AND user_id=?');
$stmt->bind_param('iii', $quantity, $cartId, $userId);
$stmt->execute();
$stmt->close();

redirect_to('cart_view.php');
