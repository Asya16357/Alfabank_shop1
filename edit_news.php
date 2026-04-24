<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect_to('admin.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $isActive = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

    if ($title === '' || mb_strlen($title) < 3) {
        $errors[] = 'Введите корректный заголовок.';
    }
    if ($content === '' || mb_strlen($content) < 10) {
        $errors[] = 'Текст новости слишком короткий.';
    }

    if (!$errors) {
        $stmt = $conn->prepare('UPDATE news SET title=?, content=?, is_active=? WHERE id=?');
        $stmt->bind_param('ssii', $title, $content, $isActive, $id);
        $stmt->execute();
        $stmt->close();
        redirect_to('admin.php');
    }
}

$stmt = $conn->prepare('SELECT id, title, content, is_active FROM news WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$news) {
    redirect_to('admin.php');
}

include 'header.php';
?>
<div class="admin-container">
    <h2>Редактировать новость</h2>
    <?php foreach ($errors as $error): ?>
        <p class="admin-card"><?= h($error) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input type="text" name="title" value="<?= h($news['title']) ?>" required>
        <textarea name="content" rows="8" required><?= h($news['content']) ?></textarea>
        <select name="is_active">
            <option value="1" <?= (int)$news['is_active'] === 1 ? 'selected' : '' ?>>Опубликовано</option>
            <option value="0" <?= (int)$news['is_active'] === 0 ? 'selected' : '' ?>>Скрыто</option>
        </select>
        <button type="submit">Сохранить</button>
    </form>
</div>
<?php include 'footer.php'; ?>

