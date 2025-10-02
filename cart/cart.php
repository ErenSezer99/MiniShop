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

<h2 class="text-3xl font-bold text-gray-800 mb-8">Sepetiniz</h2>

<?php if (empty($cart_items)): ?>
    <div class="text-center py-12">
        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">Sepetiniz Boş</h3>
        <p class="text-gray-500 mb-6">Sepetinizde henüz ürün bulunmamaktadır.</p>
        <a href="/MiniShop/products/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-shopping-bag mr-2"></i> Alışverişe Devam Et
        </a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-1/2">Ürün</th>
                        <th>Fiyat</th>
                        <th>Adet</th>
                        <th>Toplam</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr data-product-id="<?= $item['product_id'] ?? $item['id'] ?>">
                            <td>
                                <div class="cart-item">
                                    <?php if ($item['image']): ?>
                                        <img src="/MiniShop/uploads/<?= $item['image'] ?>"
                                            alt="<?= sanitize($item['name']) ?>"
                                            class="cart-item-image">
                                    <?php else: ?>
                                        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-20 h-20 flex items-center justify-center text-gray-500">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="cart-item-details">
                                        <div class="font-medium"><?= sanitize($item['name']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="cart-item-price"><?= number_format($item['price'], 2) ?> ₺</td>
                            <td>
                                <div class="cart-item-quantity">
                                    <button class="decrease-qty bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-2 rounded-l">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number"
                                        class="cart-qty border-t border-b border-gray-300 text-center w-16"
                                        value="<?= $item['quantity'] ?>"
                                        min="1">
                                    <button class="increase-qty bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-2 rounded-r">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="cart-item-total"><?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</td>
                            <td>
                                <button class="remove-cart bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">
                    Toplam: <span class="cart-total"><?= number_format($total, 2) ?> ₺</span>
                </div>
                <a href="checkout.php" class="btn-checkout bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded inline-flex items-center">
                    <i class="fas fa-credit-card mr-2"></i> Ödeme Yap
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="/MiniShop/assets/js/cart.js"></script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>