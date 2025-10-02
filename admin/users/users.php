<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

// Rol değiştirme işlemi
if (isset($_GET['change_role']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $new_role = $_GET['role'] === 'admin' ? 'admin' : 'user';

    pg_prepare($dbconn, "update_user_role", "UPDATE users SET role = $1 WHERE id = $2");
    pg_execute($dbconn, "update_user_role", [$new_role, $user_id]);

    // Eğer kendi rolünü değiştiriyorsa session'ı temizle ve logout yap
    if ($user_id == ($_SESSION['user']['id'] ?? 0)) {
        session_unset();
        session_destroy();
        redirect('/MiniShop/auth/login.php');
        exit;
    }

    set_flash('Kullanıcı rolü başarıyla güncellendi!');
    redirect('users.php');
    exit;
}

include_once __DIR__ . '/../../includes/header.php';

// Kullanıcıları çek
pg_prepare($dbconn, "select_users", "SELECT id, username, email, role, created_at FROM users ORDER BY id DESC");
$res_users = pg_execute($dbconn, "select_users", []);

$users = [];
while ($row = pg_fetch_assoc($res_users)) {
    // Tarihi okunabilir formata çevir
    $row['formatted_date'] = date('d-m-Y H:i', strtotime($row['created_at']));
    $users[] = $row;
}
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Kullanıcı Yönetimi</h2>
    <p class="text-gray-600">Kullanıcıları yönetebilir ve rollerini değiştirebilirsiniz.</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Oluşturulma Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= sanitize($user['username']) ?></td>
                        <td><?= sanitize($user['email']) ?></td>
                        <td>
                            <span class="px-2 py-1 rounded <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                <?= sanitize($user['role']) ?>
                            </span>
                        </td>
                        <td><?= $user['formatted_date'] ?></td>
                        <td>
                            <?php if ($user['role'] === 'user'): ?>
                                <a href="users.php?id=<?= $user['id'] ?>&change_role=1&role=admin" class="btn-edit mr-2">
                                    <i class="fas fa-user-shield"></i> Admin Yap
                                </a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn-delete">
                                    <i class="fas fa-trash"></i> Sil
                                </a>
                            <?php else: ?>
                                <a href="users.php?id=<?= $user['id'] ?>&change_role=1&role=user" class="btn-edit">
                                    <i class="fas fa-user"></i> User Yap
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>