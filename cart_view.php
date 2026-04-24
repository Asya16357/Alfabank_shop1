<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$user = require_auth($conn);
$userId = (int)$user['id'];

$cartStmt = $conn->prepare(
    'SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image_path, p.stock
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

$favStmt = $conn->prepare(
    'SELECT p.id, p.name, p.price, p.image_path
     FROM favorites f
     JOIN products p ON p.id = f.product_id
     WHERE f.user_id = ?
     ORDER BY f.id DESC'
);
$favStmt->bind_param('i', $userId);
$favStmt->execute();
$favorites = $favStmt->get_result();

include 'header.php';
?>

<div class="cart-container">
    <h2>Корзина</h2>

    <?php if (!$cartItems): ?>
        <p>Корзина пуста.</p>
    <?php endif; ?>

    <?php foreach ($cartItems as $row): ?>
        <div class="cart-item">
            <a href="delete_cart.php?id=<?= (int)$row['id'] ?>" class="delete">✖</a>
            <a href="favorite.php?id=<?= (int)$row['product_id'] ?>" class="fav">❤</a>
            <img src="<?= h($row['image_path']) ?>" class="cart-img" alt="">
            <div class="cart-info">
                <h3><?= h($row['name']) ?></h3>
                <p>В наличии: <?= (int)$row['stock'] ?></p>
            </div>

            <form method="post" action="update_cart.php" class="qty-form">
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <button name="action" value="minus" type="submit">-</button>
                <span class="qty"><?= (int)$row['quantity'] ?></span>
                <button name="action" value="plus" type="submit">+</button>
            </form>

            <div class="price"><?= (int)$row['sum'] ?> ₽</div>
        </div>
    <?php endforeach; ?>

    <div class="cart-bottom">
        <h3>Итого: <?= (int)$total ?> ₽</h3>
        <div>
            <a href="clear_cart.php" class="btn gray">Очистить</a>
            <a href="order.php" class="btn red">Оформить заказ</a>
        </div>
    </div>

    <h2>Избранное</h2>
    <div class="favorites-section">
        <div class="favorites-grid">
            <?php while ($row = $favorites->fetch_assoc()): ?>
                <div class="fav-card">
                    <a href="favorite.php?id=<?= (int)$row['id'] ?>" class="fav-btn">❤</a>
                    <img src="<?= h($row['image_path']) ?>" alt="">
                    <h4><?= h($row['name']) ?></h4>
                    <p><?= (int)$row['price'] ?> ₽</p>
                    <a href="cart.php?id=<?= (int)$row['id'] ?>" class="fav-cart">В корзину</a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="cart-bottom">
            <div><a href="cl_fav.php" class="btn gray">Очистить избранное</a></div>
        </div>
    </div>
</div>

<?php
$favStmt->close();
include 'footer.php';
?>

