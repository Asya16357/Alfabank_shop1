<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '' || mb_strlen($title) < 3) {
        $errors[] = 'Введите корректный заголовок.';
    }
    if ($content === '' || mb_strlen($content) < 10) {
        $errors[] = 'Текст новости слишком короткий.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO news (title, content, published_at, is_active) VALUES (?, ?, NOW(), ?)');
        $stmt->bind_param('ssi', $title, $content, $isActive);
        $stmt->execute();
        $stmt->close();
        redirect_to('admin.php');
    }
}

include 'header.php';
?>
<div class="admin-container">
    <h2>Добавить новость</h2>
    <?php foreach ($errors as $error): ?>
        <p class="admin-card"><?= h($error) ?></p>
    <?php endforeach; ?>
    <form method="post">
        <input type="text" name="title" placeholder="Заголовок" required>
        <textarea name="content" placeholder="Текст новости" rows="8" required></textarea>
        <label><input type="checkbox" name="is_active" checked> Опубликовать сразу</label>
        <button type="submit">Сохранить</button>
    </form>
</div>
<?php include 'footer.php'; ?>
