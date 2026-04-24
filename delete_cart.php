<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);

$cartId = (int)($_GET['id'] ?? 0);
if ($cartId > 0) {
    $stmt = $conn->prepare('DELETE FROM cart WHERE id=? AND user_id=?');
    $userId = (int)$user['id'];
    $stmt->bind_param('ii', $cartId, $userId);
    $stmt->execute();
    $stmt->close();
}

redirect_to('cart_view.php');
