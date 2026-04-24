<?php
session_start();
include 'db.php';
include 'header.php';

// 🏢 компания
$company_result = $conn->query("SELECT * FROM company_info LIMIT 1");
$company = $company_result ? $company_result->fetch_assoc() : null;

// 🔥 хиты продаж
$hits_result = $conn->query("
    SELECT * FROM products
    ORDER BY stock DESC
    LIMIT 5
");

$hits = [];
if ($hits_result) {
    while ($row = $hits_result->fetch_assoc()) {
        $hits[] = $row;
    }
}

// 📰 новости
$news_result = $conn->query("
    SELECT * FROM news
    WHERE is_active = 1
    ORDER BY published_at DESC
    LIMIT 3
");

$news = [];
if ($news_result) {
    while ($row = $news_result->fetch_assoc()) {
        $news[] = $row;
    }
}
?>
<h1>Добро пожаловать в Альфа Банк</h1>


<div class="container">

    <!-- СЛАЙДЕР -->
    <section class="hero-slider">

        <input type="radio" name="slide" id="s1" checked>
        <input type="radio" name="slide" id="s2">

        <div class="slides">
            <div class="slide">
                <div class="slide-content">
                    <img src="img/sl.jpg">
                </div>
            </div>

            <div class="slide">
                <div class="slide-content">
                    <img src="img/sl1.jpg">
                </div>
            </div>
        </div>

        <div class="arrows">
            <label for="s1" class="prev">❮</label>
            <label for="s2" class="next">❯</label>
        </div>

    </section>

    <!-- О КОМПАНИИ -->
    <section class="about">
        <h2><?= $company['company_name'] ?? 'О сервисе' ?></h2>
        <p>
            <?= $company['description'] ?? 'Информация о компании отсутствует' ?>
        </p>
    </section>

    <!-- ПРЕИМУЩЕСТВА -->
    <section class="features">

        <div class="feature">
            <h3>💳 Удобные платежи</h3>
            <p>Оплачивайте услуги быстро и безопасно</p>
        </div>

        <div class="feature">
            <h3>⚡ Мгновенные операции</h3>
            <p>Все действия выполняются за секунды</p>
        </div>

        <div class="feature">
            <h3>🔒 Надежность</h3>
            <p>Ваши данные защищены</p>
        </div>

    </section>

    <!-- ХИТЫ ПРОДАЖ -->
    <section class="hits">
        <h2>🔥 Хиты продаж</h2>

        <div class="products">

            <?php if (!empty($hits)): ?>
                <?php foreach ($hits as $product): ?>
                    <div class="card">

                        <img src="<?= $product['image_path'] ?>" width="150">

                        <h3><?= $product['name'] ?></h3>

                        <p><?= $product['price'] ?> ₽</p>

                        <a href="product.php?id=<?= $product['id'] ?>">
                            Подробнее
                        </a>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Товаров пока нет</p>
            <?php endif; ?>

        </div>
    </section>

    <!-- НОВОСТИ -->
    <section class="news">
        <h2>📰 Новости и акции</h2>

        <?php if (!empty($news)): ?>
            <?php foreach ($news as $n): ?>
                <div class="news-item">

                    <h3><?= $n['title'] ?></h3>

                    <p>
                        <?= mb_substr($n['content'], 0, 120) ?>...
                    </p>

                    <a href="news_detail.php?id=<?= $n['id'] ?>">
                        Читать далее
                    </a>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Новостей пока нет</p>
        <?php endif; ?>

    </section>

</div>

<footer class="footer">
    © 2026 <?= $company['company_name'] ?? 'Альфа-Банк' ?>. Учебный проект.
</footer>

</body>
</html>