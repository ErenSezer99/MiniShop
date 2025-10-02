<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

// --- Sayfalama ayarları ---
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam ürün sayısı
pg_prepare($dbconn, "count_products", "SELECT COUNT(*) AS total FROM products");
$res_total = pg_execute($dbconn, "count_products", []);
$row_total = pg_fetch_assoc($res_total);
$total_products = (int)$row_total['total'];
$total_pages = ceil($total_products / $limit);

// Ürünleri çek
pg_prepare($dbconn, "select_products", "
    SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
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

// Kullanıcının favori ürünleri
$user_favs = [];
if (is_logged_in()) {
    $user_id = current_user_id();
    pg_prepare($dbconn, "select_wishlist", "SELECT product_id FROM wishlist WHERE user_id = $1");
    $res_wishlist = pg_execute($dbconn, "select_wishlist", [$user_id]);
    while ($row = pg_fetch_assoc($res_wishlist)) {
        $user_favs[] = $row['product_id'];
    }
}
?>

<div class="flex justify-between items-center mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Ürünler</h2>

    <!-- Search UI (AJAX) -->
    <div class="products-header-actions">
        <div class="relative">
            <input
                type="search"
                id="product-search"
                name="keyword"
                placeholder="Ürün ara..."
                class="form-input"
                autocomplete="off"
                style="width: 250px;">
        </div>
        <a href="/MiniShop/cart/cart.php" class="btn-cart flex items-center">
            <i class="fas fa-shopping-cart mr-2"></i> Sepete Git
        </a>
    </div>
</div>

<!-- Spinner -->
<div id="loading-spinner" class="hidden mb-6">
    <div class="flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
</div>

<div class="products-grid" id="products-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <?php if ($product['image']): ?>
                <img src="/MiniShop/uploads/<?= sanitize($product['image']) ?>"
                    alt="<?= sanitize($product['name']) ?>"
                    class="product-img">
            <?php else: ?>
                <div class="bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center text-gray-500">
                    <i class="fas fa-image text-4xl"></i>
                </div>
            <?php endif; ?>

            <div class="product-info">
                <h3 class="product-name"><?= sanitize($product['name']) ?></h3>
                <p class="product-price"><?= number_format($product['price'], 2) ?> ₺</p>
                <p class="product-category">Kategori: <?= sanitize($product['category_name']) ?></p>
                <p class="product-desc"><?= sanitize($product['description']) ?></p>

                <form class="add-to-cart-form" data-product-id="<?= $product['id'] ?>">
                    <div class="cart-item-quantity">
                        <label for="quantity-<?= $product['id'] ?>" class="text-gray-700">Adet:</label>
                        <input type="number" id="quantity-<?= $product['id'] ?>" name="quantity" value="1" min="1" class="qty-input">
                    </div>
                    <button type="submit" class="btn-add-cart flex items-center justify-center">
                        <i class="fas fa-cart-plus mr-2"></i> Sepete Ekle
                    </button>
                </form>

                <!-- Favoriler Butonu -->
                <?php
                $isFav = in_array($product['id'], $user_favs);
                $favAction = $isFav ? 'remove' : 'add';
                $favIcon   = $isFav ? '♥' : '♡';
                $favTitle  = $isFav ? 'Favorilerden çıkar' : 'Favorilere ekle';
                ?>
                <button
                    class="btn-fav <?= $isFav ? 'active' : '' ?>"
                    data-product-id="<?= $product['id'] ?>"
                    data-action="<?= $favAction ?>"
                    title="<?= $favTitle ?>"><?= $favIcon ?></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($products)): ?>
    <div class="text-center py-12">
        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">Ürün Bulunamadı</h3>
        <p class="text-gray-500">Şu anda hiç ürün bulunmamaktadır.</p>
    </div>
<?php endif; ?>

<?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-6">
        <nav class="inline-flex rounded-md shadow">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="index.php?page=<?= $i ?>"
                    class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> border border-gray-300 first:rounded-l-md last:rounded-r-md">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
<?php endif; ?>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>
<script src="/MiniShop/assets/js/products-search.js"></script>