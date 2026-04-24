<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$news = $conn->query('SELECT id, title, is_active, published_at FROM news ORDER BY published_at DESC');
?>

<h1>Новости</h1>
<a href="add_news.php">Добавить новость</a>

<?php while ($n = $news->fetch_assoc()): ?>
<div class="admin-card">
    <h3><?= h($n['title']) ?></h3>
    <p>Статус: <?= (int)$n['is_active'] === 1 ? 'Опубликовано' : 'Скрыто' ?></p>

    <a href="edit_news.php?id=<?= (int)$n['id'] ?>">Редактировать</a>
    <a href="delete_news.php?id=<?= (int)$n['id'] ?>" onclick="return confirm('Удалить новость?')">Удалить</a>
</div>
<?php endwhile; ?>
<a href="admin.php">← Назад в админку</a>
<?php include 'footer.php'; ?>