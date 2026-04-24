<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$newOrders = (int)$conn->query('SELECT COUNT(*) AS c FROM orders WHERE status="Новый"')->fetch_assoc()['c'];
$productsCount = (int)$conn->query('SELECT COUNT(*) AS c FROM products')->fetch_assoc()['c'];
$usersCount = (int)$conn->query('SELECT COUNT(*) AS c FROM users')->fetch_assoc()['c'];
?>

<div class="admin-container">
    <h1>Админ-панель</h1>

    <div class="metrics">
        <div>Новые заказы: <?= $newOrders ?></div>
        <div>Товары: <?= $productsCount ?></div>
        <div>Пользователи: <?= $usersCount ?></div>
    </div>

    <ul>
        <li><a href="admin_products.php">Товары</a></li>
        <li><a href="admin_orders.php">Заказы</a></li>
        <li><a href="admin_users.php">Пользователи</a></li>
        <li><a href="admin_categories.php">Категории</a></li>
        <li><a href="admin_news.php">Новости</a></li>
    </ul>
</div>

<?php include 'footer.php'; ?>