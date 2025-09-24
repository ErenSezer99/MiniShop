<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../layout/header.php';
require_admin();

// Rol değiştirme işlemi
if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_role = $_GET['role'] === 'admin' ? 'admin' : 'user';

    pg_prepare($dbconn, "update_user_role", "UPDATE users SET role = $1 WHERE id = $2");
    pg_execute($dbconn, "update_user_role", [$new_role, $user_id]);

    set_flash('Kullanıcı rolü başarıyla güncellendi!');
    redirect('users.php');
    exit;
}

// Kullanıcıları çek
pg_prepare($dbconn, "select_users", "SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$res_users = pg_execute($dbconn, "select_users", []);

$users = [];
while ($row = pg_fetch_assoc($res_users)) {
    $users[] = $row;
}
?>

<h2>Kullanıcılar</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Kullanıcı Adı</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Oluşturulma Tarihi</th>
        <th>İşlemler</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= sanitize($user['username']) ?></td>
            <td><?= sanitize($user['email']) ?></td>
            <td><?= sanitize($user['role']) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <?php if ($user['role'] === 'user'): ?>
                    <a href="users.php?id=<?= $user['id'] ?>&change_role=1&role=admin">Admin Yap</a> |
                    <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">Sil</a>
                <?php else: ?>
                    <a href="users.php?id=<?= $user['id'] ?>&change_role=1&role=user">User Yap</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php
include_once __DIR__ . '/../layout/footer.php';
?>