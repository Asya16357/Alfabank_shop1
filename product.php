<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
include 'header.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? LIMIT 1'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo '<p>Товар не найден</p>';
    include 'footer.php';
    exit();
}
?>

<div class="product-container">
    <div class="product-image">
        <img src="<?= h($product['image_path']) ?>" alt="">
    </div>
    <div class="product-info">
        <h1><?= h($product['name']) ?></h1>
        <p class="category">Категория: <?= h($product['category_name'] ?? 'Без категории') ?></p>
        <p class="description"><?= h($product['description']) ?></p>
        <p class="price"><?= (int)$product['price'] ?> ₽</p>
        <p class="stock">В наличии: <?= (int)$product['stock'] ?></p>

        <?php if ((int)$product['stock'] > 0): ?>
            <form method="get" action="cart.php" class="cart-form">
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="return" value="<?= h($_SERVER['REQUEST_URI'] ?? 'catalog.php') ?>">
                <label>Количество:</label>
                <input type="number" name="qty" value="1" min="1" max="<?= (int)$product['stock'] ?>">
                <button type="submit" class="btn-cart">Добавить в корзину</button>
            </form>
        <?php else: ?>
            <p class="out">Нет в наличии</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

