<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../layout/header.php';
require_admin();

// Rol değiştirme işlemi
if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_role = $_GET['role'] === 'admin' ? 'admin' : 'user';
    $stmt_role = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt_role->execute([':role' => $new_role, ':id' => $user_id]);

    set_flash('Kullanıcı rolü başarıyla güncellendi!');
    redirect('users.php');
    exit;
}

// Kullanıcıları çek
$sql = "SELECT id, username, email, role, created_at FROM users ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
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