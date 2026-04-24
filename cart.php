<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);

$productId = (int)($_GET['id'] ?? 0);
$quantity = max(1, (int)($_GET['qty'] ?? 1));
$return = (string)($_GET['return'] ?? 'catalog.php');

if ($productId <= 0) {
    redirect_to('catalog.php');
}

$stmt = $conn->prepare('SELECT id, stock FROM products WHERE id=? LIMIT 1');
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['cart_error'] = 'Товар не найден.';
    redirect_to('catalog.php');
}

$stock = (int)$product['stock'];
if ($stock < 1) {
    $_SESSION['cart_error'] = 'Товара нет в наличии.';
    redirect_to($return);
}

$userId = (int)$user['id'];
$stmt = $conn->prepare('SELECT id, quantity FROM cart WHERE user_id=? AND product_id=? LIMIT 1');
$stmt->bind_param('ii', $userId, $productId);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

$newQty = $quantity;
if ($existing) {
    $newQty = (int)$existing['quantity'] + $quantity;
}

if ($newQty > $stock) {
    $newQty = $stock;
}

if ($existing) {
    $stmt = $conn->prepare('UPDATE cart SET quantity=? WHERE id=?');
    $cartId = (int)$existing['id'];
    $stmt->bind_param('ii', $newQty, $cartId);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)');
    $stmt->bind_param('iii', $userId, $productId, $newQty);
    $stmt->execute();
    $stmt->close();
}

redirect_to($return);
