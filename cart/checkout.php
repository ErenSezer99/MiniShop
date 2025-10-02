<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

$cart_items = [];
$total = 0;

// Sepeti al
if (is_logged_in()) {
    $user_id = current_user_id();
    pg_prepare($dbconn, "get_cart", "
        SELECT c.product_id, c.quantity, p.name, p.price
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
    $user_id = null;
    if (!empty($_SESSION['cart'])) {
        $product_ids = array_keys($_SESSION['cart']);
        $params = $product_ids;

        $sql = "SELECT id, name, price FROM products WHERE id IN (" . implode(',', array_map(fn($i) => '$' . $i, range(1, count($params)))) . ")";
        pg_prepare($dbconn, "get_guest_cart", $sql);
        $res = pg_execute($dbconn, "get_guest_cart", $params);

        while ($row = pg_fetch_assoc($res)) {
            $row['quantity'] = $_SESSION['cart'][$row['id']];
            $cart_items[] = $row;
            $total += $row['price'] * $row['quantity'];
        }
    }
}

// POST işlemi
$order_created = false;
$order_details = [];
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = sanitize($_POST['guest_name'] ?? '');
    $guest_email = sanitize($_POST['guest_email'] ?? '');
    $guest_address = sanitize($_POST['guest_address'] ?? '');

    // Email doğrulama
    if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Geçersiz e-posta adresi.";
    } else {
        $order_id = create_order($user_id, $cart_items, $total, $guest_name, $guest_email, $guest_address);

        if ($order_id) {
            // Sepeti temizle
            if (!is_logged_in()) {
                unset($_SESSION['cart']);
            } else {
                pg_prepare($dbconn, "clear_cart", "DELETE FROM cart WHERE user_id=$1");
                pg_execute($dbconn, "clear_cart", [$user_id]);
            }

            $order_created = true;
            $order_details = [
                'id' => $order_id,
                'guest_name' => $guest_name,
                'guest_email' => $guest_email,
                'guest_address' => $guest_address,
                'total' => $total,
                'items' => $cart_items
            ];
        } else {
            $error_message = "Sipariş oluşturulamadı, lütfen tekrar deneyin.";
        }
    }
}
?>

<h2 class="text-3xl font-bold text-gray-800 mb-8">Ödeme Bilgileri</h2>

<?php if ($error_message): ?>
    <div class="mb-6">
        <div class="bg-red-100 border-red-400 text-red-700 border px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= sanitize($error_message) ?></span>
        </div>
    </div>
<?php elseif ($flash = get_flash()): ?>
    <div class="mb-6">
        <div class="<?= $flash['type'] === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700' ?> border px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= sanitize($flash['message']) ?></span>
        </div>
    </div>
<?php endif; ?>

<?php if ($order_created): ?>
    <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-800">Siparişiniz Alındı!</h3>
            <p class="text-gray-600">Sipariş numaranız: #<?= $order_details['id'] ?></p>
        </div>

        <div class="border-t border-b border-gray-200 py-6 mb-6">
            <h4 class="text-xl font-semibold mb-4">Sipariş Detayları</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h5 class="font-medium text-gray-700 mb-2">Müşteri Bilgileri</h5>
                    <p><strong>Ad Soyad:</strong> <?= sanitize($order_details['guest_name']) ?></p>
                    <p><strong>Email:</strong> <?= sanitize($order_details['guest_email']) ?></p>
                    <p><strong>Adres:</strong> <?= sanitize($order_details['guest_address']) ?></p>
                </div>
            </div>

            <h5 class="font-medium text-gray-700 mb-2">Sipariş İçeriği</h5>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Adet</th>
                            <th>Fiyat</th>
                            <th>Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details['items'] as $item): ?>
                            <tr>
                                <td><?= sanitize($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'], 2) ?> ₺</td>
                                <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-right">
            <p class="text-xl font-bold">Toplam: <?= number_format($order_details['total'], 2) ?> ₺</p>
        </div>

        <div class="mt-8 text-center">
            <a href="/MiniShop/products/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i> Alışverişe Devam Et
            </a>
        </div>
    </div>
<?php elseif (empty($cart_items)): ?>
    <div class="text-center py-12">
        <i class="fas fa-shopping-cart text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">Sepetiniz Boş</h3>
        <p class="text-gray-500 mb-6">Ödeme yapabilmeniz için sepetinizde ürün olmalıdır.</p>
        <a href="/MiniShop/products/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-shopping-bag mr-2"></i> Alışverişe Devam Et
        </a>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Teslimat Bilgileri</h3>

            <form method="POST" action="" class="space-y-6" id="checkout-form">
                <div>
                    <label for="guest_name" class="form-label">Ad Soyad:</label>
                    <input
                        type="text"
                        id="guest_name"
                        name="guest_name"
                        value="<?= is_logged_in() ? sanitize($_SESSION['user']['username']) : '' ?>"
                        required
                        class="form-input">
                </div>

                <div>
                    <label for="guest_email" class="form-label">Email:</label>
                    <input
                        type="email"
                        id="guest_email"
                        name="guest_email"
                        value="<?= is_logged_in() && isset($_SESSION['user']['email']) ? sanitize($_SESSION['user']['email']) : '' ?>"
                        required
                        class="form-input">
                </div>

                <div>
                    <label for="guest_address" class="form-label">Adres:</label>
                    <textarea
                        id="guest_address"
                        name="guest_address"
                        required
                        class="form-input"
                        rows="4"></textarea>
                </div>

                <div>
                    <button type="submit" class="form-button w-full">
                        <i class="fas fa-credit-card mr-2"></i> Ödeme Yap
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Sipariş Özeti</h3>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Adet</th>
                            <th>Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?= sanitize($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 mt-4 pt-4">
                <div class="flex justify-between text-xl font-bold">
                    <span>Toplam:</span>
                    <span><?= number_format($total, 2) ?> ₺</span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>