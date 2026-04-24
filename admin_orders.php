<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$status = (string)($_GET['status'] ?? 'Новый');
$allowedStatuses = ['Новый', 'Подтвержден', 'Отменен'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'Новый';
}

$ordersStmt = $conn->prepare(
    'SELECT o.id, o.created_at, o.status, u.login
     FROM orders o
     JOIN users u ON u.id = o.user_id
     WHERE o.status = ?
     ORDER BY o.id DESC'
);
$ordersStmt->bind_param('s', $status);
$ordersStmt->execute();
$orders = $ordersStmt->get_result();
?>

<h1>Заказы</h1>

<div class="status-nav">
    <a href="?status=Новый">Новые</a>
    <a href="?status=Подтвержден">Подтвержденные</a>
    <a href="?status=Отменен">Отмененные</a>
</div>

<?php while ($o = $orders->fetch_assoc()): ?>
<div class="admin-card">
    <p><b>Заказ #<?= (int)$o['id'] ?></b></p>
    <p>Пользователь: <?= h($o['login']) ?></p>
    <p>Дата: <?= h($o['created_at']) ?></p>
    <p>Статус: <?= h($o['status']) ?></p>

    <a href="confirm_order.php?id=<?= (int)$o['id'] ?>">Подтвердить</a>
    <a href="cancel_order.php?id=<?= (int)$o['id'] ?>">Отменить</a>
</div>
<?php endwhile; ?>
<a href="admin.php">← Назад в админку</a>
<?php
$ordersStmt->close();
include 'footer.php';
?>