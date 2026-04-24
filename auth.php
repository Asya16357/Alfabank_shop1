<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$activeTab = 'login';
$errors = [];

$loggedUser = current_user($conn);
if ($loggedUser) {
    if (($loggedUser['role'] ?? 'user') === 'admin') {
        redirect_to('admin.php');
    }
    redirect_to('profile.php');
}

if (!empty($_SESSION['auth_error'])) {
    $errors[] = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $activeTab = 'register';

        $login = trim((string)($_POST['login'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($_POST['phone'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($login === '' || mb_strlen($login) < 3) {
            $errors[] = 'Логин должен содержать минимум 3 символа.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $login)) {
            $errors[] = 'Логин может содержать только латинские буквы, цифры и символ _.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный email.';
        }
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors[] = 'Введите корректный номер телефона.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не короче 6 символов.';
        }

        if (!$errors) {
            $stmt = $conn->prepare('SELECT id FROM users WHERE login=? LIMIT 1');
            $stmt->bind_param('s', $login);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($exists) {
                $errors[] = 'Такой логин уже существует.';
            }
        }

        if (!$errors) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'INSERT INTO users (login, email, phone, password_hash, role, is_blocked) VALUES (?, ?, ?, ?, "user", 0)'
            );
            $stmt->bind_param('ssss', $login, $email, $phone, $passwordHash);
            $ok = $stmt->execute();
            $stmt->close();

            if ($ok) {
                $_SESSION['auth_success'] = 'Регистрация успешна. Теперь выполните вход.';
                redirect_to('auth.php');
            }

            $errors[] = 'Не удалось зарегистрироваться. Проверьте поля и повторите попытку.';
        }
    }

    if (isset($_POST['login_btn'])) {
        $activeTab = 'login';
        $login = trim((string)($_POST['login'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($login === '' || $password === '') {
            $errors[] = 'Введите логин и пароль.';
        } else {
            $passCol = password_column($conn);
            $sql = "SELECT id, login, role, is_blocked, {$passCol} AS user_password FROM users WHERE login=? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $login);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user || !verify_user_password($conn, $user, $password)) {
                $errors[] = 'Неверный логин или пароль.';
            } elseif (!empty($user['is_blocked'])) {
                $errors[] = 'Ваш аккаунт заблокирован администратором.';
            } else {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                if (($_SESSION['role'] ?? 'user') === 'admin') {
                    redirect_to('admin.php');
                }
                redirect_to('profile.php');
            }
        }
    }
}

$success = $_SESSION['auth_success'] ?? '';
unset($_SESSION['auth_success']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Авторизация | Альфа-Банк</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #f3f5f8, #eceff3);
        }
        .auth-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }
        .auth-card {
            width: min(420px, 100%);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 16px 30px rgba(0, 0, 0, 0.08);
            padding: 24px;
        }
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .tabs button {
            flex: 1;
            border: 0;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            background: #eceff3;
        }
        .tabs button.active {
            background: #e30613;
            color: #fff;
        }
        .msg {
            border-radius: 8px;
            padding: 10px;
            margin: 0 0 12px;
            font-size: 14px;
        }
        .msg.error { background: #ffe8ea; color: #8a1d24; }
        .msg.ok { background: #e6f8eb; color: #1d6b37; }
        form { display: none; }
        form.active { display: block; }
        input, button[type="submit"] {
            width: 100%;
            margin: 8px 0;
            padding: 11px;
            border-radius: 8px;
            border: 1px solid #cfd6dd;
        }
        button[type="submit"] {
            border: 0;
            background: #e30613;
            color: #fff;
            cursor: pointer;
        }
        .link {
            text-align: center;
            margin-top: 8px;
            font-size: 14px;
        }
        .link a { color: #e30613; text-decoration: none; }
    </style>
    <script>
        function showTab(tab) {
            document.getElementById('tab-login').classList.toggle('active', tab === 'login');
            document.getElementById('tab-register').classList.toggle('active', tab === 'register');
            document.getElementById('form-login').classList.toggle('active', tab === 'login');
            document.getElementById('form-register').classList.toggle('active', tab === 'register');
        }
    </script>
</head>
<body onload="showTab('<?= $activeTab === 'register' ? 'register' : 'login' ?>')">
<div class="auth-wrap">
    <div class="auth-card">
        <div class="tabs">
            <button id="tab-login" type="button" onclick="showTab('login')">Вход</button>
            <button id="tab-register" type="button" onclick="showTab('register')">Регистрация</button>
        </div>

        <?php if ($success): ?>
            <div class="msg ok"><?= h($success) ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="msg error"><?= h($error) ?></div>
        <?php endforeach; ?>

        <form method="post" id="form-login">
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login_btn">Войти</button>
            <div class="link"><a href="reset_password.php">Забыли пароль?</a></div>
        </form>

        <form method="post" id="form-register">
            <input type="text" name="login" placeholder="Логин (латиница, цифры, _)" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="phone" placeholder="Телефон" required>
            <input type="password" name="password" placeholder="Пароль (минимум 6 символов)" required>
            <button type="submit" name="register">Зарегистрироваться</button>
        </form>
    </div>
</div>
</body>
</html>
