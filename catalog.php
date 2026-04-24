<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
include 'header.php';

$category = max(0, (int)($_GET['category'] ?? 0));
$sort = (string)($_GET['sort'] ?? 'new');
$q = trim((string)($_GET['q'] ?? ''));
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

$conditions = [];
$types = '';
$params = [];

if ($category > 0) {
    $conditions[] = 'p.category_id = ?';
    $types .= 'i';
    $params[] = $category;
}
if ($q !== '') {
    $normalizedQuery = mb_strtolower(preg_replace('/\s+/', ' ', $q));
    $normalizedQuery = str_replace('ё', 'е', $normalizedQuery);
    $conditions[] = '(LOWER(REPLACE(p.name, "ё", "е")) LIKE ? OR LOWER(REPLACE(p.description, "ё", "е")) LIKE ?)';
    $like = '%' . $normalizedQuery . '%';
    $types .= 'ss';
    $params[] = $like;
    $params[] = $like;
}
if ($minPrice > 0) {
    $conditions[] = 'p.price >= ?';
    $types .= 'i';
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $conditions[] = 'p.price <= ?';
    $types .= 'i';
    $params[] = $maxPrice;
}

$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
$orderSql = 'p.id DESC';
if ($sort === 'cheap') {
    $orderSql = 'p.price ASC';
} elseif ($sort === 'expensive') {
    $orderSql = 'p.price DESC';
}

$countSql = "SELECT COUNT(*) AS total FROM products p {$whereSql}";
$countStmt = $conn->prepare($countSql);
if ($types !== '') {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = (int)$countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();
$totalPages = max(1, (int)ceil($totalRows / $limit));

$sql = "
    SELECT p.id, p.name, p.price, p.image_path, p.stock
    FROM products p
    {$whereSql}
    ORDER BY {$orderSql}
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$bindTypes = $types . 'ii';
$bindParams = $params;
$bindParams[] = $limit;
$bindParams[] = $offset;
$stmt->bind_param($bindTypes, ...$bindParams);
$stmt->execute();
$products = $stmt->get_result();

$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');

$favIds = [];
$user = current_user($conn);
if ($user) {
    $uid = (int)$user['id'];
    $favStmt = $conn->prepare('SELECT product_id FROM favorites WHERE user_id=?');
    $favStmt->bind_param('i', $uid);
    $favStmt->execute();
    $favRes = $favStmt->get_result();
    while ($fr = $favRes->fetch_assoc()) {
        $favIds[(int)$fr['product_id']] = true;
    }
    $favStmt->close();
}
?>

<div class="catalog-container">
    <form class="filters-form" method="get">
        <input type="text" name="q" placeholder="Поиск по названию или описанию" value="<?= h($q) ?>">
        <select name="category">
            <option value="0">Все категории</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= (int)$cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= h($cat['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="number" name="min_price" placeholder="Цена от" min="0" value="<?= $minPrice > 0 ? (int)$minPrice : '' ?>">
        <input type="number" name="max_price" placeholder="Цена до" min="0" value="<?= $maxPrice > 0 ? (int)$maxPrice : '' ?>">
        <select name="sort">
            <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Сначала новые</option>
            <option value="cheap" <?= $sort === 'cheap' ? 'selected' : '' ?>>Сначала дешевые</option>
            <option value="expensive" <?= $sort === 'expensive' ? 'selected' : '' ?>>Сначала дорогие</option>
        </select>
        <button type="submit">Применить</button>
    </form>

    <div class="catalog">
        <?php while ($row = $products->fetch_assoc()): ?>
            <div class="product-card">
                <?php if ($user): ?>
                    <a href="favorite.php?id=<?= (int)$row['id'] ?>" class="fav <?= isset($favIds[(int)$row['id']]) ? 'active' : '' ?>">❤</a>
                <?php else: ?>
                    <a href="auth.php" class="fav">❤</a>
                <?php endif; ?>
                <img src="<?= h($row['image_path']) ?>" alt="">
                <h3><?= h($row['name']) ?></h3>
                <p class="price"><?= (int)$row['price'] ?> ₽</p>
                <p class="stock">Остаток: <?= (int)$row['stock'] ?></p>
                <div class="actions">
                    <a href="product.php?id=<?= (int)$row['id'] ?>">Подробнее</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&category=<?= $category ?>&sort=<?= h($sort) ?>&q=<?= urlencode($q) ?>&min_price=<?= $minPrice ?>&max_price=<?= $maxPrice ?>"
                   class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
include 'footer.php';
?>

