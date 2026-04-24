<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);
$userId = (int)$user['id'];

$stmt = $conn->prepare('DELETE FROM cart WHERE user_id=?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

redirect_to('cart_view.php');
