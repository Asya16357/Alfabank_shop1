<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$result = $conn->query('SELECT * FROM company_info LIMIT 1');
$company = $result ? $result->fetch_assoc() : null;

include 'header.php';
?>
<div class="container about-page">
    <h1>О компании</h1>

    <div class="about-block">
        <h2><?= h($company['company_name'] ?? 'Альфа Маркет') ?></h2>
        <p><?= h($company['description'] ?? 'Информация о компании пока не заполнена.') ?></p>
    </div>

    <div class="about-block">
        <h2>Наша миссия</h2>
        <p>Сделать онлайн-покупки простыми, безопасными и удобными для каждого клиента.</p>
    </div>

    <div class="about-block contacts">
        <h2>Контакты</h2>
        <p><b>Адрес:</b> <?= h($company['address'] ?? 'г. Москва') ?></p>
        <p><b>Телефон:</b> <?= h($company['phone'] ?? '+7 (000) 000-00-00') ?></p>
        <p><b>Email:</b> <?= h($company['email'] ?? 'info@mail.ru') ?></p>
    </div>
</div>

<?php include 'footer.php'; ?>
