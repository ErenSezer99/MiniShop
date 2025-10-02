<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

// Giriş kontrolü
if (!is_logged_in()) {
    set_flash("Favorilerinizi görüntülemek için giriş yapmalısınız.", "error");
    redirect('/MiniShop/auth/login.php');
}

$user_id = current_user_id();

// --- Sayfalama ayarları ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam favori sayısı
pg_prepare($dbconn, "count_wishlist", "SELECT COUNT(*) AS total FROM wishlist WHERE user_id = $1");
$res_total = pg_execute($dbconn, "count_wishlist", [$user_id]);
$row_total = pg_fetch_assoc($res_total);
$total_favs = (int)$row_total['total'];
$total_pages = ceil($total_favs / $limit);

// Favorileri çek
pg_prepare($dbconn, "select_wishlist", "
    SELECT w.product_id, p.name, p.price, p.image
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = $1
    ORDER BY w.created_at DESC
    LIMIT $2 OFFSET $3
");
$res_wishlist = pg_execute($dbconn, "select_wishlist", [$user_id, $limit, $offset]);

$wishlist_items = [];
while ($row = pg_fetch_assoc($res_wishlist)) {
    $wishlist_items[] = $row;
}
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Favorilerim</h2>
    <p class="text-gray-600">Favori ürünlerinizi bu sayfadan görüntüleyebilirsiniz.</p>
</div>

<div class="products-grid">
    <?php if (count($wishlist_items) === 0): ?>
        <div class="col-span-full text-center py-12">
            <i class="fas fa-heart text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-semibold text-gray-700 mb-2">Favori Ürününüz Yok</h3>
            <p class="text-gray-500 mb-6">Ürünleri favorilerinize ekleyerek daha sonra kolayca bulabilirsiniz.</p>
            <a href="/MiniShop/products/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i> Alışverişe Başla
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($wishlist_items as $item): ?>
            <div class="product-card">
                <?php if ($item['image']): ?>
                    <img src="/MiniShop/uploads/<?= sanitize($item['image']) ?>"
                        alt="<?= sanitize($item['name']) ?>"
                        class="product-img">
                <?php else: ?>
                    <div class="bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center text-gray-500">
                        <i class="fas fa-image text-4xl"></i>
                    </div>
                <?php endif; ?>
                <div class="product-info">
                    <h3 class="product-name"><?= sanitize($item['name']) ?></h3>
                    <p class="product-price"><?= number_format($item['price'], 2) ?> ₺</p>

                    <form class="add-to-cart-form" data-product-id="<?= $item['product_id'] ?>">
                        <div class="cart-item-quantity">
                            <label for="quantity-<?= $item['product_id'] ?>" class="text-gray-700">Adet:</label>
                            <input type="number" id="quantity-<?= $item['product_id'] ?>" name="quantity" value="1" min="1" class="qty-input">
                        </div>
                        <button type="submit" class="btn-add-cart flex items-center justify-center">
                            <i class="fas fa-cart-plus mr-2"></i> Sepete Ekle
                        </button>
                    </form>

                    <div class="flex justify-between items-center mt-4">
                        <button class="btn-fav active"
                            data-product-id="<?= $item['product_id'] ?>"
                            data-action="remove"
                            title="Favorilerden çıkar">♥</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Sayfalama -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=<?= $i ?>"
                class="<?= ($i == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> px-4 py-2 rounded-md transition duration-300">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>