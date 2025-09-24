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

<!-- Sepete Git butonu -->
<div style="text-align:right; margin-bottom:15px;">
    <a href="/MiniShop/cart/cart.php" style="
        padding:8px 12px;
        background:#28a745;
        color:white;
        text-decoration:none;
        border-radius:4px;
        font-weight:bold;
    ">
        Sepete Git 🛒
    </a>
</div>

<div style="display:flex; flex-wrap:wrap; gap:20px;">
    <?php foreach ($products as $product): ?>
        <div style="
            border:1px solid #ccc;
            padding:15px;
            width:220px;
            text-align:center;
            border-radius:6px;
            box-shadow: 1px 1px 5px rgba(0,0,0,0.1);
        ">
            <?php if ($product['image']): ?>
                <img src="/MiniShop/uploads/<?= sanitize($product['image']) ?>"
                    alt="<?= sanitize($product['name']) ?>"
                    width="150" style="margin-bottom:10px;">
            <?php endif; ?>

            <h3 style="margin:5px 0;"><?= sanitize($product['name']) ?></h3>
            <p style="margin:3px 0; font-weight:bold; color:#007bff;">
                <?= number_format($product['price'], 2) ?> ₺
            </p>
            <p style="margin:2px 0; font-size:0.9em; color:#555;">
                Kategori: <?= sanitize($product['category_name']) ?>
            </p>
            <p style="margin:5px 0; font-size:0.85em; color:#333;">
                <?= sanitize($product['description']) ?>
            </p>

            <!-- Sepete ekleme formu -->
            <form action="/MiniShop/cart/add_to_cart.php" method="post">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" style="width:50px; margin-top:5px;">
                <button type="submit" style="
                    margin-top:5px;
                    padding:5px 10px;
                    background:#28a745;
                    color:white;
                    border:none;
                    border-radius:4px;
                    cursor:pointer;
                ">Sepete Ekle</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<!-- Sayfalama -->
<div style="margin-top:20px; text-align:center;">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?page=<?= $i ?>"
                style="
                   margin:0 5px;
                   padding:5px 10px;
                   border-radius:4px;
                   text-decoration:none;
                   background: <?= ($i == $page) ? '#007bff' : '#f0f0f0' ?>;
                   color: <?= ($i == $page) ? 'white' : 'black' ?>;
               ">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>