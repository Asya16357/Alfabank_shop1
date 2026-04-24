<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';

$id = (int)($_GET['id'] ?? 0);
$currentAdminId = (int)$_SESSION['user_id'];

if ($id > 0 && $id !== $currentAdminId) {
    $stmt = $conn->prepare('UPDATE users SET is_blocked = IF(is_blocked=1, 0, 1) WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

redirect_to('admin.php');
