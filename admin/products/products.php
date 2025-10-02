<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../includes/header.php';
require_admin();

// Ürün ekleme işlemi
if (isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Resim yükleme
    $image = upload_image('image');

    // Veritabanına ekle
    pg_prepare($dbconn, "insert_product", "
    INSERT INTO products (name, description, price, stock, category_id, image, created_at)
    VALUES ($1, $2, $3, $4, $5, $6, NOW())
    ");
    pg_execute($dbconn, "insert_product", [$name, $description, $price, $stock, $category_id, $image]);

    set_flash('Ürün başarıyla eklendi!');
    redirect('products.php');
    exit;
}

// --- Sayfalama ayarları ---
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam ürün sayısı
$res_total = pg_query($dbconn, "SELECT COUNT(*) FROM products");
$total_products = pg_fetch_result($res_total, 0, 0);
$total_pages = ceil($total_products / $limit);

// Ürünleri çekme sorgusu (limitli)
pg_prepare($dbconn, "select_products", "
SELECT p.id, p.name, p.description, p.price, p.stock, p.image, c.name AS category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
ORDER BY p.id DESC
LIMIT $1 OFFSET $2
");
$res_products = pg_execute($dbconn, "select_products", [$limit, $offset]);

$products = [];
while ($row = pg_fetch_assoc($res_products)) {
    $products[] = $row;
}

// Kategorileri çek
pg_prepare($dbconn, "select_categories", "SELECT id, name FROM categories ORDER BY name ASC");
$res_categories = pg_execute($dbconn, "select_categories", []);
$categories = [];
while ($row = pg_fetch_assoc($res_categories)) {
    $categories[] = $row;
}
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Ürün Yönetimi</h2>
    <p class="text-gray-600">Yeni ürün ekleyebilir veya mevcut ürünleri yönetebilirsiniz.</p>
</div>

<!-- Ürün Ekleme Formu -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Yeni Ürün Ekle</h3>
    <form action="" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="name" class="form-label">Ürün Adı:</label>
            <input type="text" id="name" name="name" required class="form-input">
        </div>

        <div>
            <label for="price" class="form-label">Fiyat:</label>
            <input type="number" step="0.01" id="price" name="price" required class="form-input">
        </div>

        <div>
            <label for="stock" class="form-label">Stok:</label>
            <input type="number" id="stock" name="stock" required class="form-input">
        </div>

        <div>
            <label for="category_id" class="form-label">Kategori:</label>
            <select id="category_id" name="category_id" required class="form-input">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="md:col-span-2">
            <label for="description" class="form-label">Açıklama:</label>
            <textarea id="description" name="description" required class="form-input" rows="3"></textarea>
        </div>

        <div class="md:col-span-2">
            <label for="image" class="form-label">Resim:</label>
            <input type="file" id="image" name="image" class="form-input">
        </div>

        <div class="md:col-span-2">
            <button type="submit" name="add_product" class="form-button">
                <i class="fas fa-plus mr-2"></i> Ürün Ekle
            </button>
        </div>
    </form>
</div>

<!-- Ürün Listesi -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-800">Ürünler</h3>

        <!-- Search UI (AJAX) -->
        <form id="product-search-form" onsubmit="return false;" class="flex">
            <input
                type="search"
                id="product-search"
                name="keyword"
                placeholder="Ürün ara..."
                value="<?= sanitize($_GET['q'] ?? '') ?>"
                class="form-input mr-2"
                autocomplete="off">

            <select id="product-category-filter" name="category" class="form-input">
                <option value="0">Tüm Kategoriler</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
                    <th>Ürün Adı</th>
                    <th>Fiyat</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Resim</th>
                    <th>İşlemler</th>
                </tr>
            </thead>

            <tbody id="products-tbody">
                <?php foreach ($products as $product): ?>
                    <tr data-product-id="<?= $product['id'] ?>">
                        <td><?= $product['id'] ?></td>
                        <td><?= sanitize($product['name']) ?></td>
                        <td><?= number_format($product['price'], 2) ?> ₺</td>
                        <td><?= $product['stock'] ?></td>
                        <td><?= sanitize($product['category_name']) ?></td>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="../../uploads/<?= $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" class="w-16 h-16 object-cover rounded">
                            <?php else: ?>
                                <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center text-gray-500">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-edit mr-2">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn-delete">
                                <i class="fas fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sayfalama Linkleri -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="products.php?page=<?= $i ?>"
                        class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> border border-gray-300 first:rounded-l-md last:rounded-r-md">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script src="/MiniShop/assets/js/admin-products-search.js"></script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>