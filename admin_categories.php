<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$cats = $conn->query('SELECT id, name FROM categories ORDER BY id DESC');
?>

<h1>Категории</h1>

<form method="post" action="add_category.php">
    <input type="text" name="name" placeholder="Название категории" required>
    <button type="submit">Добавить</button>
</form>

<?php while ($c = $cats->fetch_assoc()): ?>
<p>
    <?= h($c['name']) ?>
    <a href="delete_category.php?id=<?= (int)$c['id'] ?>" onclick="return confirm('Удалить категорию?')">Удалить</a>
</p>
<?php endwhile; ?>
<a href="admin.php">← Назад в админку</a>
<?php include 'footer.php'; ?>