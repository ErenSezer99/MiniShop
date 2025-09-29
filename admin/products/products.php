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

<!-- Ürün Ekleme Formu -->
<h2>Yeni Ürün Ekle</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Ürün Adı:</label>
    <input type="text" name="name" required><br>

    <label>Açıklama:</label>
    <textarea name="description" required></textarea><br>

    <label>Fiyat:</label>
    <input type="number" step="0.01" name="price" required><br>

    <label>Stok:</label>
    <input type="number" name="stock" required><br>

    <label>Kategori:</label>
    <select name="category_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Resim:</label>
    <input type="file" name="image"><br>

    <button type="submit" name="add_product">Ekle</button>
</form>

<!-- Ürün Listesi Tablosu -->
<h2>Ürünler</h2>

<!-- Search UI (AJAX) -->
<form id="product-search-form" onsubmit="return false;" style="margin-bottom:12px;">
    <input
        type="search"
        id="product-search"
        name="keyword"
        placeholder="Ürün ara (isim, açıklama)..."
        value="<?= sanitize($_GET['q'] ?? '') ?>"
        style="padding:6px; width:320px;"
        autocomplete="off">

    <select id="product-category-filter" name="category" style="padding:6px; margin-left:8px;">
        <option value="0">Tüm Kategoriler</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                <?= sanitize($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Spinner -->
<div id="loading-spinner" style="display:none; margin-bottom:12px;"></div>

<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ürün Adı</th>
            <th>Açıklama</th>
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
                <td><?= sanitize($product['description']) ?></td>
                <td><?= $product['price'] ?></td>
                <td><?= $product['stock'] ?></td>
                <td><?= sanitize($product['category_name']) ?></td>
                <td>
                    <?php if ($product['image']): ?>
                        <img src="../../uploads/<?= $product['image'] ?>" alt="<?= sanitize($product['name']) ?>" width="50">
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_product.php?id=<?= $product['id'] ?>">Düzenle</a>
                    <a href="delete_product.php?id=<?= $product['id'] ?>">Sil</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Sayfalama Linkleri (aynı eskisi gibi) -->
<div style="margin-top:15px;">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="products.php?page=<?= $i ?>"
                style="margin:0 5px; <?= $i == $page ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<script src="/MiniShop/assets/js/admin-products-search.js"></script>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>