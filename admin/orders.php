<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';
require_admin();

// Status çeviri dizisi
$status_map = [
    'pending'    => 'Beklemede',
    'processing' => 'İşlemde',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal'
];

// --- Pagination ayarları ---
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Toplam sipariş sayısı
$total_stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $total_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// Siparişleri çekme sorgusu (limitli)
$sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.username
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Siparişler</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Kullanıcı</th>
        <th>Toplam Tutar</th>
        <th>Durum</th>
        <th>Oluşturulma Tarihi</th>
        <th>İşlemler</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td><?= number_format($order['total_amount'], 2) ?>₺</td>
            <td><?= $status_map[$order['status']] ?? htmlspecialchars($order['status']) ?></td>
            <td><?= $order['created_at'] ?></td>
            <td>
                <a href="edit_order.php?id=<?= $order['id'] ?>">Düzenle</a>
                <a href="delete_order.php?id=<?= $order['id'] ?>" onclick="return confirm('Silmek istediğinizden emin misiniz?')">Sil</a>
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
include_once __DIR__ . '/footer.php';
?>