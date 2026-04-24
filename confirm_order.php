<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare('UPDATE orders SET status="Подтвержден" WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $changed = $stmt->affected_rows > 0;
    $stmt->close();

    if ($changed) {
        notify_order_status($conn, $id, 'Подтвержден');
    }
}

redirect_to('admin.php?status=Новый');
