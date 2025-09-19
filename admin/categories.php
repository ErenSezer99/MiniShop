<?php

include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';
require_admin();

// Kategori silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = sanitize($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$delete_id]);
    set_flash("Kategori başarıyla silindi.", "success");
    redirect('categories.php');
}

// Kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        $success = "Kategori başarıyla eklendi.";
    } else {
        $error = "Kategori adı boş olamaz.";
    }
}

// Mevcut kategorileri çek
$stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2>Kategoriler</h2>

    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <!-- Kategori ekleme formu -->
    <form method="post">
        <label for="name">Yeni Kategori:</label>
        <input type="text" name="name" id="name" required>
        <button type="submit">Ekle</button>
    </form>

    <!-- Kategori listesi -->
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Kategori Adı</th>
            <th>Oluşturulma Tarihi</th>
            <th>İşlemler</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['id']); ?></td>
                <td><?= htmlspecialchars($cat['name']); ?></td>
                <td><?= htmlspecialchars($cat['created_at']); ?></td>
                <td>
                    <a href="categories.php?delete_id=<?= htmlspecialchars($cat['id']); ?>" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>