<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);
$userId = (int)$user['id'];
$errors = [];

$cartStmt = $conn->prepare(
    'SELECT c.product_id, c.quantity, p.name, p.price, p.image_path, p.stock
     FROM cart c
     JOIN products p ON p.id = c.product_id
     WHERE c.user_id = ?'
);
$cartStmt->bind_param('i', $userId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();
$cartItems = [];
$total = 0;
while ($row = $cartResult->fetch_assoc()) {
    $row['sum'] = (int)$row['price'] * (int)$row['quantity'];
    $total += $row['sum'];
    $cartItems[] = $row;
}
$cartStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_order'])) {
    $selected = array_map('intval', $_POST['products'] ?? []);
    $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $comment = trim((string)($_POST['comment'] ?? ''));

    if (!$selected) {
        $errors[] = 'Выберите товары для оформления.';
    }
    if (strlen($phone) < 10 || strlen($phone) > 15) {
        $errors[] = 'Введите корректный номер телефона.';
    }
    if ($address === '' || mb_strlen($address) < 5) {
        $errors[] = 'Введите корректный адрес доставки.';
    }

    $selectedMap = array_flip($selected);
    $orderItems = [];
    foreach ($cartItems as $item) {
        $pid = (int)$item['product_id'];
        if (!isset($selectedMap[$pid])) {
            continue;
        }
        if ((int)$item['quantity'] > (int)$item['stock']) {
            $errors[] = 'Недостаточно товара на складе: ' . $item['name'];
        } else {
            $orderItems[] = $item;
        }
    }

    if (!$orderItems) {
        $errors[] = 'В корзине нет подходящих товаров для заказа.';
    }

    if (!$errors) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                'INSERT INTO orders (user_id, contact_phone, delivery_address, comment, status)
                 VALUES (?, ?, ?, ?, "Новый")'
            );
            $stmt->bind_param('isss', $userId, $phone, $address, $comment);
            $stmt->execute();
            $orderId = (int)$stmt->insert_id;
            $stmt->close();

            $itemStmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)');
            $stockStmt = $conn->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $deleteStmt = $conn->prepare('DELETE FROM cart WHERE user_id=? AND product_id=?');

            foreach ($orderItems as $item) {
                $pid = (int)$item['product_id'];
                $qty = (int)$item['quantity'];

                $itemStmt->bind_param('iii', $orderId, $pid, $qty);
                $itemStmt->execute();

                $stockStmt->bind_param('iii', $qty, $pid, $qty);
                $stockStmt->execute();
                if ($stockStmt->affected_rows < 1) {
                    throw new RuntimeException('Ошибка списания товара со склада');
                }

                $deleteStmt->bind_param('ii', $userId, $pid);
                $deleteStmt->execute();
            }

            $itemStmt->close();
            $stockStmt->close();
            $deleteStmt->close();

            $conn->commit();
            redirect_to('profile.php');
        } catch (Throwable $e) {
            $conn->rollback();
            $errors[] = 'Не удалось оформить заказ. Попробуйте снова.';
        }
    }
}

include 'header.php';
?>

<div class="order-container">
    <div class="order-left">
        <h2>Оформление заказа</h2>
        <?php foreach ($errors as $error): ?>
            <p class="order-msg order-error"><?= h($error) ?></p>
        <?php endforeach; ?>

        <form method="post">
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <input class="order-check" type="checkbox" name="products[]" value="<?= (int)$item['product_id'] ?>">
                    <img src="<?= h($item['image_path']) ?>" alt="">
                    <div class="order-info">
                        <h4><?= h($item['name']) ?></h4>
                        <p>Количество: <?= (int)$item['quantity'] ?></p>
                        <p>В наличии: <?= (int)$item['stock'] ?></p>
                    </div>
                    <div class="order-price"><?= (int)$item['sum'] ?> руб.</div>
                </div>
            <?php endforeach; ?>

            <div class="order-right">
                <h3>Данные для доставки</h3>
                <p class="total-price"><?= (int)$total ?> руб.</p>
                <input type="text" name="phone" placeholder="Телефон" required>
                <input type="text" name="address" placeholder="Адрес доставки" required>
                <textarea name="comment" placeholder="Комментарий"></textarea>
                <button class="order-btn" type="submit" name="make_order">Оформить заказ</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[name="products[]"]');
    const totalBlock = document.querySelector('.total-price');
    const rows = document.querySelectorAll('.order-item');

    function updateTotal() {
        let total = 0;
        rows.forEach((row) => {
            const checkbox = row.querySelector('input[name="products[]"]');
            if (!checkbox || !checkbox.checked) return;
            const text = row.querySelector('.order-price')?.textContent || '0';
            total += parseInt(text.replace(/\D+/g, ''), 10) || 0;
        });
        if (totalBlock) totalBlock.textContent = total + ' руб.';
    }

    checkboxes.forEach((box) => box.addEventListener('change', updateTotal));
});
</script>

<?php include 'footer.php'; ?>
