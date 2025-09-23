<?php

include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../layout/header.php';
require_admin();

// Düzenlenecek kategori bilgisi
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// POST işlemleri: ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $edit_id = $_POST['edit_id'] ?? null;

    if (!empty($name)) {
        if ($edit_id) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $edit_id]);

            set_flash("Kategori başarıyla güncellendi.", "success");
            redirect("categories.php");
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);

            set_flash("Kategori başarıyla eklendi.", "success");
            redirect("categories.php");
        }
    } else {
        set_flash("Kategori adı boş olamaz.", "error");
        redirect("categories.php");
    }
}

// Kategori silme işlemi
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$delete_id]);

    set_flash("Kategori başarıyla silindi.", "success");
    redirect("categories.php");
}

// Sayfalama ayarları
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam kategori sayısı
$total_stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$total_categories = $total_stmt->fetchColumn();
$total_pages = ceil($total_categories / $limit);

// Mevcut kategorileri çek (limitli)
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h2>Kategoriler</h2>

    <?php
    $flash = get_flash();
    if ($flash) {
        echo "<p style='color:" . ($flash['type'] === 'success' ? 'green' : 'red') . ";'>" . sanitize($flash['message']) . "</p>";
    }
    ?>

    <!-- Kategori ekleme / güncelleme formu -->
    <form method="post">
        <label for="name"><?= $edit_category ? 'Kategori Adını Düzenle:' : 'Yeni Kategori:' ?></label>
        <input type="text" name="name" id="name" required value="<?= $edit_category ? sanitize($edit_category['name']) : '' ?>">
        <input type="hidden" name="edit_id" value="<?= $edit_category['id'] ?? '' ?>">
        <button type="submit"><?= $edit_category ? 'Güncelle' : 'Ekle' ?></button>
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
                <td><?= sanitize($cat['id']); ?></td>
                <td><?= sanitize($cat['name']); ?></td>
                <td><?= sanitize($cat['created_at']); ?></td>
                <td>
                    <a href="categories.php?edit=<?= urlencode($cat['id']); ?>">Düzenle</a>
                    <a href="categories.php?delete=<?= urlencode($cat['id']); ?>" onclick="return confirm('Bu kategoriyi silmek istediğinize emin misiniz?')">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Sayfalama linkleri -->
    <div style="margin-top:15px;">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="categories.php?page=<?= $i ?>"
                    style="margin:0 5px; <?= $i == $page ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/../layout/footer.php'; ?>