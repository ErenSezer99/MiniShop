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

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Sipariş Yönetimi</h2>
    <p class="text-gray-600">Tüm siparişleri görüntüleyebilir ve yönetebilirsiniz.</p>
</div>

<!-- Search UI (AJAX) -->
<div class="mb-4">
    <form id="order-search-form" onsubmit="return false;" class="flex">
        <input
            type="search"
            id="order-search"
            name="keyword"
            placeholder="Sipariş ara (ID, kullanıcı, email, adres)..."
            class="form-input"
            autocomplete="off">
    </form>
</div>

<!-- Spinner -->
<div id="loading-spinner" class="hidden mb-6">
    <div class="flex justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="table-container">
        <table class="table">
            <thead>
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
            </thead>
            <tbody id="orders-tbody">
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
                        <td>
                            <span class="px-2 py-1 rounded 
                                <?= $order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                <?= $order['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '' ?>
                                <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' ?>
                                <?= $order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>">
                                <?= $status_map[$order['status']] ?? sanitize($order['status']) ?>
                            </span>
                        </td>
                        <td><?= $order['formatted_date'] ?></td>
                        <td>
                            <a href="edit_order.php?id=<?= $order['id'] ?>" class="btn-edit mr-2">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <a href="delete_order.php?id=<?= $order['id'] ?>" class="btn-delete">
                                <i class="fas fa-trash"></i> Sil
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sayfalama Linkleri -->
    <?php if ($total_pages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="inline-flex rounded-md shadow">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="orders.php?page=<?= $i ?>"
                        class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?> border border-gray-300 first:rounded-l-md last:rounded-r-md">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>
<script src="/MiniShop/assets/js/admin-orders-search.js"></script>