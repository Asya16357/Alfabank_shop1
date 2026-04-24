<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare('DELETE FROM products WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

redirect_to('admin.php');
