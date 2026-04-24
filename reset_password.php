<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim((string)($_POST['login'] ?? ''));

    if ($login === '') {
        $isError = true;
        $message = 'Введите логин.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE login=? LIMIT 1');
        $stmt->bind_param('s', $login);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $isError = true;
            $message = 'Пользователь не найден.';
        } else {
            $newPassword = (string)random_int(100000, 999999);
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $col = password_column($conn);
            $sql = "UPDATE users SET {$col}=? WHERE id=?";
            $upd = $conn->prepare($sql);
            $uid = (int)$user['id'];
            $upd->bind_param('si', $hash, $uid);
            $upd->execute();
            $upd->close();

            $message = 'Новый пароль: ' . $newPassword;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Восстановление пароля</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; }
        .container {
            width: min(360px, 92%);
            margin: 90px auto;
            background: #fff;
            padding: 22px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 { text-align: center; margin-top: 0; }
        input, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #e30613; color: #fff; border: 0; cursor: pointer; }
        .msg { margin-top: 10px; padding: 10px; border-radius: 6px; text-align: center; }
        .ok { background: #e9f7ef; color: #2e7d32; }
        .error { background: #f8d7da; color: #721c24; }
        a { color: #e30613; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h2>Восстановление пароля</h2>
    <?php if ($message): ?>
        <div class="msg <?= $isError ? 'error' : 'ok' ?>"><?= h($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="login" placeholder="Введите логин" required>
        <button type="submit">Сгенерировать новый пароль</button>
    </form>
    <p><a href="auth.php">← Назад</a></p>
</div>
</body>
</html>
