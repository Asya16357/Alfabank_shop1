<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare('SELECT id, title, content, published_at FROM news WHERE id=? AND is_active=1 LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'header.php';
?>
<div class="container news-detail">
    <?php if (!$news): ?>
        <h1>Новость не найдена</h1>
        <p><a href="news.php">← Назад к новостям</a></p>
    <?php else: ?>
        <h1><?= h($news['title']) ?></h1>
        <p class="date"><?= date('d.m.Y', strtotime((string)$news['published_at'])) ?></p>
        <div class="content"><?= nl2br(h($news['content'])) ?></div>
        <p><a href="news.php" class="back-link">← Назад к новостям</a></p>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
