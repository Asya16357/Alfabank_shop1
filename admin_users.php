<?php
declare(strict_types=1);

require_once __DIR__ . '/admin_check.php';
include 'header.php';

$users = $conn->query('SELECT id, login, email, phone, role, is_blocked FROM users ORDER BY id DESC');
?>

<h1>Пользователи</h1>

<table>
<tr>
    <th>ID</th>
    <th>Логин</th>
    <th>Email</th>
    <th>Телефон</th>
    <th>Роль</th>
    <th>Статус</th>
    <th>Действия</th>
</tr>

<?php while ($u = $users->fetch_assoc()): ?>
<tr>
    <td><?= (int)$u['id'] ?></td>
    <td><?= h($u['login']) ?></td>
    <td><?= h($u['email'] ?? '') ?></td>
    <td><?= h($u['phone'] ?? '') ?></td>
    <td><?= h($u['role']) ?></td>
    <td><?= (int)$u['is_blocked'] === 1 ? 'Заблокирован' : 'Активен' ?></td>
    <td>
        <form method="post" action="update_user.php" class="inline-form">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <select name="role">
                <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <button type="submit">OK</button>
        </form>

        <a href="block_user.php?id=<?= (int)$u['id'] ?>">
            <?= (int)$u['is_blocked'] === 1 ? 'Разблокировать' : 'Заблокировать' ?>
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>
<a href="admin.php">← Назад в админку</a>
<?php include 'footer.php'; ?>