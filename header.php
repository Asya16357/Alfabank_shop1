<?php
require_once __DIR__ . '/bootstrap.php';
$headerUser = current_user($conn);
$isAdmin = (bool)$headerUser && (($headerUser['role'] ?? 'user') === 'admin');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Альфа-Банк</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="header">
    <div class="logo">
        <div class="logo-a">A</div>
        <span>Альфа-Банк</span>
    </div>

    <nav class="nav">
        <a href="index.php">Главная</a>
        <a href="catalog.php">Каталог</a>
        <a href="about.php">О компании</a>
        <a href="news.php">Новости</a>
        <?php if ($headerUser): ?>
            <a href="cart_view.php">Корзина</a>
        <?php endif; ?>
    </nav>

    <div class="user-menu">
        <?php if ($headerUser): ?>
            <span class="hello">Здравствуйте, <?= h($headerUser['login']) ?></span>
            <?php if ($isAdmin): ?>
                <a href="admin.php">Админ-панель</a>
            <?php else: ?>
                <a href="profile.php">Личный кабинет</a>
            <?php endif; ?>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="auth.php" class="login-btn">Войти</a>
        <?php endif; ?>
    </div>
</header>
