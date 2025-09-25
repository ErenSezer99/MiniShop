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
?>

<h2>Ürünler</h2>

<div class="cart-header">
    <a href="/MiniShop/cart/cart.php" class="btn-cart">Sepete Git 🛒</a>
</div>

<div class="products-grid">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <?php if ($product['image']): ?>
                <img src="/MiniShop/uploads/<?= sanitize($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" class="product-img">
            <?php endif; ?>

            <h3 class="product-name"><?= sanitize($product['name']) ?></h3>
            <p class="product-price"><?= number_format($product['price'], 2) ?> ₺</p>
            <p class="product-category">Kategori: <?= sanitize($product['category_name']) ?></p>
            <p class="product-desc"><?= sanitize($product['description']) ?></p>

            <form class="add-to-cart-form" data-product-id="<?= $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" class="qty-input">
                <button type="submit" class="btn-add-cart">Sepete Ekle</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<script src="/MiniShop/assets/js/main.js"></script>
<script src="/MiniShop/assets/js/cart.js"></script>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>