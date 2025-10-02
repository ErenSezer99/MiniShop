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

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Sipariş Geçmişim</h2>
    <p class="text-gray-600">Geçmiş siparişlerinizi bu sayfadan görüntüleyebilirsiniz.</p>
</div>

<?php if (empty($orders)): ?>
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <i class="fas fa-shopping-bag text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-2xl font-semibold text-gray-700 mb-2">Henüz Siparişiniz Yok</h3>
        <p class="text-gray-500 mb-6">Alışveriş yaparak ilk siparişinizi verebilirsiniz.</p>
        <a href="/MiniShop/products/index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-shopping-bag mr-2"></i> Alışverişe Başla
        </a>
    </div>
<?php else: ?>
    <div class="space-y-6">
        <?php foreach ($orders as $order): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Sipariş #<?= $order['id'] ?></h3>
                            <p class="text-gray-600"><?= $order['formatted_date'] ?></p>
                        </div>
                        <div class="mt-2 md:mt-0">
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                <?= $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $order['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '' ?>
                                <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>">
                                <?= $status_map[$order['status']] ?? $order['status'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-lg font-bold text-gray-800">Toplam Tutar: <span class="text-blue-600"><?= number_format($order['total_amount'], 2) ?> ₺</span></p>
                    </div>
                </div>
                
                <div class="p-6 bg-gray-50">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Sipariş Ürünleri</h4>
                    <div class="space-y-3">
                        <?php
                        $res_items = pg_execute($dbconn, "get_order_items", [$order['id']]);
                        while ($item = pg_fetch_assoc($res_items)):
                        ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-700"><?= sanitize($item['name']) ?></span>
                                <div class="flex items-center">
                                    <span class="text-gray-600 mr-4">× <?= $item['quantity'] ?></span>
                                    <span class="font-medium"><?= number_format($item['price'] * $item['quantity'], 2) ?> ₺</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>