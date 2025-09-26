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

<h2>Sipariş Düzenle (ID: <?= $order['id'] ?>)</h2>
<p>Kullanıcı: <?= sanitize($order['username']) ?></p>
<p>Toplam Tutar: <?= number_format($order['total_amount'], 2) ?>₺</p>
<p>Oluşturulma Tarihi: <?= $order['created_at'] ?></p>

<form action="" method="post">
    <label>Durum:</label>
    <select name="status" required>
        <?php
        foreach ($status_map as $en => $tr) {
            $selected = $order['status'] === $en ? 'selected' : '';
            echo "<option value=\"$en\" $selected>$tr</option>";
        }
        ?>
    </select><br><br>

    <button type="submit" name="update_order">Güncelle</button>
</form>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>