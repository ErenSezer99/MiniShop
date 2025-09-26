<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

$cart_items = [];
$total = 0;

if (is_logged_in()) {
    $user_id = current_user_id();
    pg_prepare($dbconn, "get_cart", "
        SELECT c.product_id, c.quantity, p.name, p.price, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id=$1
    ");
    $res = pg_execute($dbconn, "get_cart", [$user_id]);
    while ($row = pg_fetch_assoc($res)) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
} else {
    // session tabanlı
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '$1'));
        $idx = 1;
        $params = [];
        foreach ($product_ids as $pid) $params[] = $pid;

        // Her ürünün detayını çek
        $sql = "SELECT id, name, price, image FROM products WHERE id IN (" . implode(',', array_map(fn($i) => '$' . $i, range(1, count($params)))) . ")";
        pg_prepare($dbconn, "get_guest_cart", $sql);
        $res = pg_execute($dbconn, "get_guest_cart", $params);

        while ($row = pg_fetch_assoc($res)) {
            $row['quantity'] = $_SESSION['cart'][$row['id']];
            $cart_items[] = $row;
            $total += $row['price'] * $row['quantity'];
        }
    }
}
?>

<h2>Sepetiniz</h2>

<?php if (empty($cart_items)): ?>
    <p>Sepetiniz boş.</p>
<?php else: ?>
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Ürün</th>
                <th>Fiyat</th>
                <th>Adet</th>
                <th>Toplam</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr data-product-id="<?= $item['product_id'] ?? $item['id'] ?>">
                    <td><?= sanitize($item['name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?> ₺</td>
                    <td>
                        <input type="number" class="cart-qty" value="<?= $item['quantity'] ?>" min="1" style="width:50px;">
                    </td>
                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</td>
                    <td>
                        <button class="remove-cart">Sil</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="text-align:right; font-weight:bold;">Toplam: <?= number_format($total, 2) ?> ₺</p>

    <!-- Ödeme Yap Butonu -->
    <div style="margin-top:20px; text-align:right;">
        <a href="checkout.php" class="btn-checkout" style="padding:8px 15px; background:#28a745; color:#fff; text-decoration:none; border-radius:4px;">Ödeme Yap</a>
    </div>
<?php endif; ?>

<script src="/MiniShop/assets/js/cart.js"></script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>