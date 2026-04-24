<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';

$id = (int)($_POST['id'] ?? 0);
$role = (string)($_POST['role'] ?? 'user');
$allowed = ['user', 'admin'];

if ($id > 0 && in_array($role, $allowed, true)) {
    if ($id === (int)$_SESSION['user_id'] && $role !== 'admin') {
        redirect_to('admin.php');
    }

    $stmt = $conn->prepare('UPDATE users SET role=? WHERE id=?');
    $stmt->bind_param('si', $role, $id);
    $stmt->execute();
    $stmt->close();
}

redirect_to('admin.php');
