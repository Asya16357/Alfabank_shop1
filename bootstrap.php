<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $url): void
{
    header('Location: ' . $url);
    exit();
}

function db_has_column(mysqli $conn, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $tableEsc = $conn->real_escape_string($table);
    $columnEsc = $conn->real_escape_string($column);
    $sql = "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$columnEsc}'";
    $result = $conn->query($sql);
    $cache[$key] = $result && $result->num_rows > 0;

    return $cache[$key];
}

function password_column(mysqli $conn): string
{
    return db_has_column($conn, 'users', 'password_hash') ? 'password_hash' : 'password';
}

function current_user(mysqli $conn, bool $refresh = false): ?array
{
    static $cached = null;
    static $loaded = false;

    if ($refresh) {
        $cached = null;
        $loaded = false;
    }

    if ($loaded) {
        return $cached;
    }
    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $userId = (int)$_SESSION['user_id'];
    $passCol = password_column($conn);
    $sql = "SELECT id, login, email, phone, role, is_blocked, {$passCol} AS user_password FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['role']);
        return null;
    }

    if (!empty($user['is_blocked'])) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['auth_error'] = 'Ваш аккаунт заблокирован администратором.';
        return null;
    }

    $_SESSION['role'] = $user['role'] ?? 'user';
    $cached = $user;
    return $cached;
}

function require_auth(mysqli $conn): array
{
    $user = current_user($conn);
    if (!$user) {
        redirect_to('auth.php');
    }

    return $user;
}

function require_admin(mysqli $conn): array
{
    $user = require_auth($conn);
    if (($user['role'] ?? 'user') !== 'admin') {
        redirect_to('index.php');
    }

    return $user;
}

function is_admin_user(mysqli $conn): bool
{
    $user = current_user($conn);
    return (bool)$user && ($user['role'] ?? 'user') === 'admin';
}

function verify_user_password(mysqli $conn, array $user, string $plainPassword): bool
{
    $hash = (string)($user['user_password'] ?? '');
    if ($hash === '') {
        return false;
    }

    if (password_verify($plainPassword, $hash)) {
        return true;
    }

    if (password_column($conn) === 'password' && hash_equals($hash, $plainPassword)) {
        return true;
    }

    return false;
}

function notify_order_status(mysqli $conn, int $orderId, string $status): void
{
    $stmt = $conn->prepare(
        'SELECT o.id, u.email, u.login
         FROM orders o
         JOIN users u ON u.id = o.user_id
         WHERE o.id = ? LIMIT 1'
    );
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $email = trim((string)($order['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $subject = 'Изменение статуса заказа #' . $orderId;
    $message = "Здравствуйте, {$order['login']}!\nСтатус вашего заказа #{$orderId} изменен на: {$status}.";
    $headers = "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail($email, $subject, $message, $headers);
}
