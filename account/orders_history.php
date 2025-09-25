<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';

if (!is_logged_in()) {
    set_flash("Lütfen sipariş geçmişinizi görmek için giriş yapın.", "error");
    redirect('/MiniShop/auth/login.php');
}

$user_id = current_user_id();

// Kullanıcının siparişlerini çek
pg_prepare($dbconn, "get_user_orders", "
    SELECT id, total_amount, status, created_at
    FROM orders
    WHERE user_id=$1
    ORDER BY created_at DESC
");
$res_orders = pg_execute($dbconn, "get_user_orders", [$user_id]);

$orders = [];
while ($row = pg_fetch_assoc($res_orders)) {
    // Tarihi okunabilir formata çevir
    $row['formatted_date'] = date('d-m-Y H:i', strtotime($row['created_at']));
    $orders[] = $row;
}

pg_prepare($dbconn, "get_order_items", "
    SELECT oi.quantity, oi.price, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=$1
");

$status_map = [
    'pending'    => 'Beklemede',
    'processing' => 'İşlemde',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal'
];
?>

<h2>Sipariş Geçmişiniz</h2>

<?php if (empty($orders)): ?>
    <p>Henüz siparişiniz yok.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <h3>Sipariş #<?= $order['id'] ?> (<?= $status_map[$order['status']] ?? $order['status'] ?>)</h3>
            <p><strong>Tarih:</strong> <?= $order['formatted_date'] ?></p>
            <p><strong>Toplam Tutar:</strong> <?= number_format($order['total_amount'], 2) ?> ₺</p>

            <h4>Ürünler:</h4>
            <ul>
                <?php
                $res_items = pg_execute($dbconn, "get_order_items", [$order['id']]);
                while ($item = pg_fetch_assoc($res_items)):
                ?>
                    <li><?= sanitize($item['name']) ?> × <?= $item['quantity'] ?> = <?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>