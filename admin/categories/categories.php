<?php

include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../includes/header.php';
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
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

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

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Kategori Yönetimi</h2>
    <p class="text-gray-600">Yeni kategori ekleyebilir veya mevcut kategorileri yönetebilirsiniz.</p>
</div>

<!-- Kategori Ekleme / Güncelleme Formu -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4"><?= $edit_category ? 'Kategori Düzenle' : 'Yeni Kategori Ekle' ?></h3>
    <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label for="name" class="form-label"><?= $edit_category ? 'Kategori Adını Düzenle:' : 'Kategori Adı:' ?></label>
            <input
                type="text"
                name="name"
                id="name"
                required
                value="<?= $edit_category ? sanitize($edit_category['name']) : '' ?>"
                class="form-input">
        </div>

        <div class="md:col-span-2">
            <label for="description" class="form-label">Açıklama:</label>
            <textarea
                name="description"
                id="description"
                class="form-input"
                rows="3"><?= $edit_category ? sanitize($edit_category['description']) : '' ?></textarea>
        </div>

        <div class="md:col-span-2">
            <label for="image" class="form-label">Resim:</label>
            <input type="file" name="image" id="image" class="form-input">
            <?php if ($edit_category && $edit_category['image']): ?>
                <div class="mt-2">
                    <p class="text-sm text-gray-600 mb-2">Mevcut resim:</p>
                    <img src="../../uploads/<?= $edit_category['image'] ?>" alt="<?= sanitize($edit_category['name']) ?>" class="w-16 h-16 object-cover rounded">
                </div>
            <?php endif; ?>
        </div>

        <input type="hidden" name="edit_id" value="<?= $edit_category['id'] ?? '' ?>">

        <div class="md:col-span-2">
            <button type="submit" class="form-button">
                <i class="fas fa-<?= $edit_category ? 'sync' : 'plus' ?> mr-2"></i>
                <?= $edit_category ? 'Güncelle' : 'Kategori Ekle' ?>
            </button>
            <?php if ($edit_category): ?>
                <a href="categories.php" class="ml-2 form-button bg-gray-500 hover:bg-gray-600">
                    <i class="fas fa-times mr-2"></i> İptal
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Kategori listesi -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Kategoriler</h3>

        <!-- Search UI (AJAX) -->
        <form id="category-search-form" onsubmit="return false;" class="flex">
            <input
                type="search"
                id="category-search"
                name="keyword"
                placeholder="Kategori ara..."
                class="form-input"
                autocomplete="off">
        </form>
    </div>

    <!-- Spinner -->
    <div id="loading-spinner" class="hidden mb-6">
        <div class="flex justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kategori Adı</th>
                    <th>Açıklama</th>
                    <th>Resim</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody id="categories-tbody">
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">
                            Henüz kategori eklenmemiş.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= sanitize($cat['id']); ?></td>
                            <td><?= sanitize($cat['name']); ?></td>
                            <td><?= sanitize($cat['description']); ?></td>
                            <td>
                                <?php if ($cat['image']): ?>
                                    <img src="../../uploads/<?= $cat['image'] ?>" alt="<?= sanitize($cat['name']) ?>" class="w-16 h-16 object-cover rounded">
                                <?php else: ?>
                                    <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center text-gray-500">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="categories.php?edit=<?= urlencode($cat['id']); ?>" class="btn-edit mr-2">
                                    <i class="fas fa-edit"></i> Düzenle
                                </a>
                                <a href="categories.php?delete=<?= urlencode($cat['id']); ?>" class="btn-delete">
                                    <i class="fas fa-trash"></i> Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Sayfalama linkleri -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="categories.php?page=<?= $i ?>"
                        class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> border border-gray-300 first:rounded-l-md last:rounded-r-md">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script src="/MiniShop/assets/js/admin-categories-search.js"></script>
<?php include_once __DIR__ . '/../../includes/footer.php'; ?>