<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);

$userId = (int)$user['id'];
$productId = (int)($_GET['id'] ?? 0);
$back = $_SERVER['HTTP_REFERER'] ?? 'catalog.php';

if ($productId > 0) {
    $stmt = $conn->prepare('SELECT id FROM favorites WHERE user_id=? AND product_id=? LIMIT 1');
    $stmt->bind_param('ii', $userId, $productId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        $stmt = $conn->prepare('DELETE FROM favorites WHERE user_id=? AND product_id=?');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare('INSERT INTO favorites (user_id, product_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $userId, $productId);
        $stmt->execute();
        $stmt->close();
    }
}

redirect_to($back);
