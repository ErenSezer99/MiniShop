<?php
include_once __DIR__ . '/../includes/header.php';
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

// Giriş kontrolü
if (!is_logged_in()) {
    set_flash("Favorilerinizi görüntülemek için giriş yapmalısınız.", "error");
    redirect('/MiniShop/login.php');
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

<h2>Favorilerim</h2>

<div class="products-grid">
    <?php if (count($wishlist_items) === 0): ?>
        <p>Henüz favori olarak eklenmiş ürün yok.</p>
    <?php else: ?>
        <?php foreach ($wishlist_items as $item): ?>
            <div class="product-card">
                <?php if ($item['image']): ?>
                    <img src="/MiniShop/uploads/<?= sanitize($item['image']) ?>"
                        alt="<?= sanitize($item['name']) ?>"
                        class="product-img">
                <?php endif; ?>
                <h3 class="product-name"><?= sanitize($item['name']) ?></h3>
                <p class="product-price"><?= number_format($item['price'], 2) ?> ₺</p>

                <button class="btn-fav"
                    data-product-id="<?= $item['product_id'] ?>"
                    data-action="remove"
                    title="Favorilerden çıkar">♥</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Sayfalama -->
<div class="pagination">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=<?= $i ?>"
                class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>