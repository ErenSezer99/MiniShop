<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../includes/header.php';;
require_admin();

// Status çeviri dizisi
$status_map = [
    'pending'    => 'Beklemede',
    'processing' => 'İşlemde',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal'
];

// --- Sayfalama ayarları ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam sipariş sayısı
$res_total = pg_query($dbconn, "SELECT COUNT(*) FROM orders");
$total_orders = pg_fetch_result($res_total, 0, 0);
$total_pages = ceil($total_orders / $limit);

// Siparişleri çekme sorgusu (limitli)
pg_prepare($dbconn, "select_orders", "
    SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, 
           u.username, o.guest_name, o.guest_email, o.guest_address
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
    LIMIT $1 OFFSET $2
");
$res_orders = pg_execute($dbconn, "select_orders", [$limit, $offset]);

$orders = [];
while ($row = pg_fetch_assoc($res_orders)) {
    // Tarihi okunabilir formata çevir
    $row['formatted_date'] = date('d-m-Y H:i', strtotime($row['created_at']));
    $orders[] = $row;
}
?>

<h2>Siparişler</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Kullanıcı / Misafir</th>
        <th>Email</th>
        <th>Adres</th>
        <th>Toplam Tutar</th>
        <th>Durum</th>
        <th>Oluşturulma Tarihi</th>
        <th>İşlemler</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td>
                <?php
                if ($order['user_id']) {
                    echo sanitize($order['username']);
                } else {
                    echo 'Misafir#' . $order['id'] . ' (' . sanitize($order['guest_name']) . ')';
                }
                ?>
            </td>
            <td><?= sanitize($order['guest_email']) ?></td>
            <td><?= sanitize($order['guest_address']) ?></td>
            <td><?= number_format($order['total_amount'], 2) ?>₺</td>
            <td><?= $status_map[$order['status']] ?? sanitize($order['status']) ?></td>
            <td><?= $order['formatted_date'] ?></td>
            <td>
                <a href="edit_order.php?id=<?= $order['id'] ?>">Düzenle</a>
                <a href="delete_order.php?id=<?= $order['id'] ?>">Sil</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Sayfalama Linkleri -->
<div style="margin-top:15px;">
    <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="orders.php?page=<?= $i ?>"
                style="margin:0 5px; <?= $i == $page ? 'font-weight:bold; text-decoration:underline;' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>