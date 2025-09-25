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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guest_name = sanitize($_POST['guest_name'] ?? '');
    $guest_email = sanitize($_POST['guest_email'] ?? '');
    $guest_address = sanitize($_POST['guest_address'] ?? '');

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
        set_flash("Sipariş oluşturulamadı, lütfen tekrar deneyin.", "error");
    }
}
?>

<h2>Ödeme Bilgileri</h2>

<?php if ($flash = get_flash()): ?>
    <p style="color:<?= $flash['type'] === 'error' ? 'red' : 'green' ?>; font-weight:bold;">
        <?= sanitize($flash['message']) ?>
    </p>
<?php endif; ?>

<?php if ($order_created): ?>
    <h3>Siparişiniz alındı! (#<?= $order_details['id'] ?>)</h3>

    <p><strong>Ad Soyad:</strong> <?= sanitize($order_details['guest_name']) ?></p>
    <p><strong>Email:</strong> <?= sanitize($order_details['guest_email']) ?></p>
    <p><strong>Adres:</strong> <?= sanitize($order_details['guest_address']) ?></p>

    <h4>Sipariş Detayları:</h4>
    <ul>
        <?php foreach ($order_details['items'] as $item): ?>
            <li><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?> = <?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</li>
        <?php endforeach; ?>
    </ul>
    <p><strong>Toplam: <?= number_format($order_details['total'], 2) ?> ₺</strong></p>

<?php elseif (empty($cart_items)): ?>
    <p>Sepetiniz boş.</p>
<?php else: ?>
    <form method="POST" action="">
        <p>
            <label>Ad Soyad:</label><br>
            <input type="text" name="guest_name" value="<?= is_logged_in() ? sanitize($_SESSION['user']['username']) : '' ?>" required>
        </p>
        <p>
            <label>Email:</label><br>
            <input type="email" name="guest_email" value="<?= is_logged_in() && isset($_SESSION['user']['email']) ? sanitize($_SESSION['user']['email']) : '' ?>" required>
        </p>
        <p>
            <label>Adres:</label><br>
            <textarea name="guest_address" required><?= is_logged_in() ? '' : '' ?></textarea>
        </p>

        <h3>Sipariş Özeti</h3>
        <ul>
            <?php foreach ($cart_items as $item): ?>
                <li><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?> = <?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Toplam: <?= number_format($total, 2) ?> ₺</strong></p>

        <button type="submit">Ödeme Yap</button>
    </form>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>