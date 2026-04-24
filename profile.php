<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);

if (($user['role'] ?? 'user') === 'admin') {
    redirect_to('admin.php');
}

$errors = [];
$success = '';
$userId = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $login = trim((string)($_POST['login'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));

    if ($login === '' || mb_strlen($login) < 3) {
        $errors[] = 'Логин должен содержать минимум 3 символа.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
        $errors[] = 'Логин может содержать только латинские буквы, цифры и _.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email.';
    }
    if ($phone !== '' && (strlen($phone) < 10 || strlen($phone) > 15)) {
        $errors[] = 'Некорректный номер телефона.';
    }

    if (!$errors) {
        $checkStmt = $conn->prepare('SELECT id FROM users WHERE login = ? AND id <> ? LIMIT 1');
        $checkStmt->bind_param('si', $login, $userId);
        $checkStmt->execute();
        $exists = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($exists) {
            $errors[] = 'Этот логин уже занят.';
        } else {
            $stmt = $conn->prepare('UPDATE users SET login=?, email=?, phone=? WHERE id=?');
            $stmt->bind_param('sssi', $login, $email, $phone, $userId);
            $stmt->execute();
            $stmt->close();
            $success = 'Данные профиля обновлены.';
            $user = current_user($conn, true);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId > 0) {
        $stmt = $conn->prepare('UPDATE orders SET status="Отменен" WHERE id=? AND user_id=? AND status="Новый"');
        $stmt->bind_param('ii', $orderId, $userId);
        $stmt->execute();
        $stmt->close();
        $success = 'Заказ отменен.';
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

$countStmt = $conn->prepare('SELECT COUNT(*) AS c FROM orders WHERE user_id=?');
$countStmt->bind_param('i', $userId);
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['c'];
$countStmt->close();
$totalPages = max(1, (int)ceil($totalRows / $limit));

$stmt = $conn->prepare(
    'SELECT id, created_at, status, delivery_address, contact_phone FROM orders WHERE user_id=? ORDER BY id DESC LIMIT ? OFFSET ?'
);
$stmt->bind_param('iii', $userId, $limit, $offset);
$stmt->execute();
$orders = $stmt->get_result();

include 'header.php';
?>

<div class="profile-container">
    <h2>Личный кабинет</h2>

    <?php foreach ($errors as $error): ?>
        <p class="profile-msg profile-error"><?= h($error) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p class="profile-msg profile-ok"><?= h($success) ?></p>
    <?php endif; ?>

    <div class="profile-box">
        <h3>Мои данные</h3>
        <form method="post">
            <input type="text" name="login" value="<?= h($user['login']) ?>" required>
            <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" placeholder="Email">
            <input type="text" name="phone" value="<?= h($user['phone'] ?? '') ?>" placeholder="Телефон">
            <button type="submit" name="save_profile">Сохранить</button>
        </form>
    </div>

    <div class="orders-box">
        <h3>История заказов</h3>
        <?php while ($order = $orders->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <b>Заказ №<?= (int)$order['id'] ?></b>
                    <span class="status"><?= h($order['status']) ?></span>
                </div>
                <p>Дата: <?= h($order['created_at'] ?? '') ?></p>
                <p>Адрес: <?= h($order['delivery_address'] ?? '') ?></p>
                <p>Телефон: <?= h($order['contact_phone'] ?? '') ?></p>

                <?php
                $itemsStmt = $conn->prepare(
                    'SELECT p.name, oi.quantity FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?'
                );
                $orderId = (int)$order['id'];
                $itemsStmt->bind_param('i', $orderId);
                $itemsStmt->execute();
                $items = $itemsStmt->get_result();
                ?>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <p><?= h($item['name']) ?> x <?= (int)$item['quantity'] ?></p>
                <?php endwhile; ?>
                <?php $itemsStmt->close(); ?>

                <?php if (($order['status'] ?? '') === 'Новый'): ?>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <button type="submit" name="cancel_order" class="cancel-btn">Отменить заказ</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="profile.php?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
include 'footer.php';
?>

