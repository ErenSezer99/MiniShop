<?php

include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../layout/header.php';
require_admin();

// Düzenlenecek kategori bilgisi
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);

    // Güvenli prepared statement
    $result = pg_prepare($dbconn, "select_category", "SELECT * FROM categories WHERE id = $1");
    $res = pg_execute($dbconn, "select_category", [$edit_id]);
    $edit_category = pg_fetch_assoc($res);
}

// POST işlemleri: ekleme veya güncelleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    // Resim yükleme fonksiyonu
    $image = upload_image('image');

    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : null;

    if (!empty($name)) {
        if ($edit_id) {
            pg_prepare($dbconn, "update_category", "UPDATE categories SET name=$1, description=$2, image=$3 WHERE id=$4");
            pg_execute($dbconn, "update_category", [$name, $description, $image, $edit_id]);

            set_flash("Kategori başarıyla güncellendi.", "success");
            redirect("categories.php");
        } else {
            pg_prepare($dbconn, "insert_category", "INSERT INTO categories (name, description, image) VALUES ($1, $2, $3)");
            pg_execute($dbconn, "insert_category", [$name, $description, $image]);

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
    $delete_id = intval($_GET['delete']);
    pg_prepare($dbconn, "delete_category", "DELETE FROM categories WHERE id=$1");
    pg_execute($dbconn, "delete_category", [$delete_id]);

    set_flash("Kategori başarıyla silindi.", "success");
    redirect("categories.php");
}

// Sayfalama ayarları
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam kategori sayısı
$res_total = pg_query($dbconn, "SELECT COUNT(*) FROM categories");
$total_categories = pg_fetch_result($res_total, 0, 0);
$total_pages = ceil($total_categories / $limit);

// Mevcut kategorileri çek (limitli)
pg_prepare($dbconn, "select_categories", "SELECT * FROM categories ORDER BY id DESC LIMIT $1 OFFSET $2");
$res_categories = pg_execute($dbconn, "select_categories", [$limit, $offset]);

$categories = [];
while ($row = pg_fetch_assoc($res_categories)) {
    $categories[] = $row;
}

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
    <form method="post" enctype="multipart/form-data">
        <label for="name"><?= $edit_category ? 'Kategori Adını Düzenle:' : 'Yeni Kategori:' ?></label>
        <input type="text" name="name" id="name" required value="<?= $edit_category ? sanitize($edit_category['name']) : '' ?>"><br>

        <label for="description">Açıklama:</label>
        <textarea name="description" id="description"><?= $edit_category ? sanitize($edit_category['description']) : '' ?></textarea><br>

        <label for="image">Resim:</label>
        <input type="file" name="image" id="image"><br>

        <input type="hidden" name="edit_id" value="<?= $edit_category['id'] ?? '' ?>">
        <button type="submit"><?= $edit_category ? 'Güncelle' : 'Ekle' ?></button>
    </form>

    <!-- Kategori listesi -->
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Kategori Adı</th>
            <th>Açıklama</th>
            <th>Resim</th>
            <th>İşlemler</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= sanitize($cat['id']); ?></td>
                <td><?= sanitize($cat['name']); ?></td>
                <td><?= sanitize($cat['description']); ?></td>
                <td>
                    <?php if ($cat['image']): ?>
                        <img src="../../uploads/<?= $cat['image'] ?>" alt="<?= sanitize($cat['name']) ?>" width="50">
                    <?php endif; ?>
                </td>
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