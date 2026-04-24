<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';

$name = trim((string)($_POST['name'] ?? ''));
if ($name !== '') {
    $stmt = $conn->prepare('INSERT INTO categories (name) VALUES (?)');
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $stmt->close();
}

redirect_to('admin.php');
