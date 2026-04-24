<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect_to('admin.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $imagePath = trim((string)($_POST['image_path'] ?? ''));
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Название товара слишком короткое.';
    }
    if ($price < 0) {
        $errors[] = 'Цена не может быть отрицательной.';
    }
    if ($stock < 0) {
        $errors[] = 'Остаток не может быть отрицательным.';
    }
    if ($categoryId <= 0) {
        $errors[] = 'Выберите категорию.';
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            'UPDATE products SET name=?, description=?, image_path=?, price=?, stock=?, category_id=? WHERE id=?'
        );
        $stmt->bind_param('sssiiii', $name, $description, $imagePath, $price, $stock, $categoryId, $id);
        $stmt->execute();
        $stmt->close();
        redirect_to('admin.php');
    }
}

$stmt = $conn->prepare('SELECT * FROM products WHERE id=? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$product) {
    redirect_to('admin.php');
}

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');
include 'header.php';
?>
<div class="admin-container">
    <h2>Редактирование товара</h2>
    <?php foreach ($errors as $error): ?>
        <p class="admin-card"><?= h($error) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input type="text" name="name" value="<?= h($product['name']) ?>" required>
        <textarea name="description" rows="5"><?= h($product['description'] ?? '') ?></textarea>
        <input type="text" name="image_path" value="<?= h($product['image_path'] ?? '') ?>">
        <input type="number" name="price" min="0" value="<?= (int)$product['price'] ?>" required>
        <input type="number" name="stock" min="0" value="<?= (int)$product['stock'] ?>" required>
        <select name="category_id" required>
            <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$product['category_id'] ? 'selected' : '' ?>>
                    <?= h($c['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Сохранить изменения</button>
    </form>

    <p><a href="admin.php">Назад в админку</a></p>
</div>
<?php include 'footer.php'; ?>

