<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$products = $conn->query(
    'SELECT p.id, p.name, p.price, p.stock, c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.id DESC'
);
?>

<h1>Товары</h1>
<a href="add_product.php">Добавить товар</a>

<table>
<tr>
    <th>ID</th>
    <th>Название</th>
    <th>Цена</th>
    <th>Склад</th>
    <th>Категория</th>
    <th>Действия</th>
</tr>

<?php while ($p = $products->fetch_assoc()): ?>
<tr>
    <td><?= (int)$p['id'] ?></td>
    <td><?= h($p['name']) ?></td>
    <td><?= (int)$p['price'] ?> ₽</td>
    <td><?= (int)$p['stock'] ?></td>
    <td><?= h($p['category_name'] ?? '—') ?></td>
    <td>
        <a href="edit_product.php?id=<?= (int)$p['id'] ?>">Редактировать</a>
        <a href="delete_product.php?id=<?= (int)$p['id'] ?>" onclick="return confirm('Удалить товар?')">Удалить</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
<a href="admin.php">← Назад в админку</a>
<?php include 'footer.php'; ?>