<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;

$countRes = $conn->query('SELECT COUNT(*) AS total FROM news WHERE is_active=1');
$totalRows = (int)$countRes->fetch_assoc()['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$stmt = $conn->prepare('SELECT id, title, content, published_at FROM news WHERE is_active=1 ORDER BY published_at DESC LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';
?>
<div class="container">
    <h1>Новости и акции</h1>
    <?php while ($news = $result->fetch_assoc()): ?>
        <div class="news-card">
            <h3><?= h($news['title']) ?></h3>
            <p><?= h(mb_substr(strip_tags((string)$news['content']), 0, 120)) ?>...</p>
            <a href="news_detail.php?id=<?= (int)$news['id'] ?>">Читать далее →</a>
        </div>
    <?php endwhile; ?>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="news.php?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$stmt->close();
include 'footer.php';
?>
