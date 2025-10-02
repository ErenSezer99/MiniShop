<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../includes/header.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash('Geçersiz sipariş ID.');
    redirect('orders.php');
    exit;
}

$order_id = (int) $_GET['id'];

// Status çeviri dizisi
$status_map = [
    'pending'    => 'Beklemede',
    'processing' => 'İşlemde',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal'
];

// Siparişi çek
pg_prepare($dbconn, "select_order", "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.username
                                   FROM orders o
                                   LEFT JOIN users u ON o.user_id = u.id
                                   WHERE o.id = $1");
$res = pg_execute($dbconn, "select_order", [$order_id]);
$order = pg_fetch_assoc($res);

if (!$order) {
    set_flash('Sipariş bulunamadı.');
    redirect('orders.php');
    exit;
}

// Form gönderilmişse güncelle
if (isset($_POST['update_order'])) {
    $status = $_POST['status'];

    pg_prepare($dbconn, "update_order", "UPDATE orders SET status = $1 WHERE id = $2");
    pg_execute($dbconn, "update_order", [$status, $order_id]);

    set_flash('Sipariş durumu güncellendi.');
    redirect('orders.php');
    exit;
}
?>

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Sipariş Düzenle</h2>
    <p class="text-gray-600">Sipariş ID: <?= $order['id'] ?></p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-700 mb-2">Kullanıcı</h3>
            <p class="text-lg"><?= sanitize($order['username']) ?></p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-700 mb-2">Toplam Tutar</h3>
            <p class="text-lg"><?= number_format($order['total_amount'], 2) ?>₺</p>
        </div>
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-700 mb-2">Oluşturulma Tarihi</h3>
            <p class="text-lg"><?= $order['created_at'] ?></p>
        </div>
    </div>

    <form action="" method="post" class="mt-6">
        <div>
            <label for="status" class="form-label">Durum:</label>
            <select id="status" name="status" required class="form-input">
                <?php
                foreach ($status_map as $en => $tr) {
                    $selected = $order['status'] === $en ? 'selected' : '';
                    echo "<option value=\"$en\" $selected>$tr</option>";
                }
                ?>
            </select>
        </div>

        <div class="mt-6">
            <button type="submit" name="update_order" class="form-button">
                <i class="fas fa-save mr-2"></i> Durumu Güncelle
            </button>
            <a href="orders.php" class="ml-4 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Geri
            </a>
        </div>
    </form>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>