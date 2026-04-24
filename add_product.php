<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
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
            'INSERT INTO products (name, description, image_path, price, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssiii', $name, $description, $imagePath, $price, $stock, $categoryId);
        $stmt->execute();
        $stmt->close();
        redirect_to('admin.php');
    }
}

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');
include 'header.php';
?>
<div class="admin-container">
    <h2>Добавить товар</h2>
    <?php foreach ($errors as $error): ?>
        <p class="admin-card"><?= h($error) ?></p>
    <?php endforeach; ?>
    <form method="post">
        <input type="text" name="name" placeholder="Название" required>
        <textarea name="description" rows="5" placeholder="Описание"></textarea>
        <input type="text" name="image_path" placeholder="Путь к изображению (например images/card1.jpg)">
        <input type="number" name="price" placeholder="Цена" min="0" required>
        <input type="number" name="stock" placeholder="Количество" min="0" required>
        <select name="category_id" required>
            <option value="">Выберите категорию</option>
            <?php while ($c = $categories->fetch_assoc()): ?>
                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Добавить</button>
    </form>
</div>
<?php include 'footer.php'; ?>
