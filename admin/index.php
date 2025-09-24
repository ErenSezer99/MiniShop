<?php
// Admin dashboard
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
require_admin();

// Basit istatistik sorguları
$products_count = $categories_count = $orders_count = $users_count = "-";

// Products sayısı
$res = pg_query($dbconn, "SELECT COUNT(*) AS count FROM products");
if ($res) {
    $row = pg_fetch_assoc($res);
    $products_count = (int)$row['count'];
}

// Categories sayısı
$res = pg_query($dbconn, "SELECT COUNT(*) AS count FROM categories");
if ($res) {
    $row = pg_fetch_assoc($res);
    $categories_count = (int)$row['count'];
}

// Orders sayısı
$res = pg_query($dbconn, "SELECT COUNT(*) AS count FROM orders");
if ($res) {
    $row = pg_fetch_assoc($res);
    $orders_count = (int)$row['count'];
}

// Users sayısı
$res = pg_query($dbconn, "SELECT COUNT(*) AS count FROM users");
if ($res) {
    $row = pg_fetch_assoc($res);
    $users_count = (int)$row['count'];
}
?>

<h2>Admin Dashboard</h2>

<div class="admin-stats">
    <div class="admin-stat">
        <strong><?php echo sanitize($products_count); ?></strong>
        <div>Ürün</div>
    </div>
    <div class="admin-stat">
        <strong><?php echo sanitize($categories_count); ?></strong>
        <div>Kategori</div>
    </div>
    <div class="admin-stat">
        <strong><?php echo sanitize($orders_count); ?></strong>
        <div>Sipariş</div>
    </div>
    <div class="admin-stat">
        <strong><?php echo sanitize($users_count); ?></strong>
        <div>Kullanıcı</div>
    </div>
</div>

<p>Soldaki menüden kategori veya ürün yönetimini seçerek devam edebilirsiniz.</p>

<?php include_once __DIR__ . '/layout/footer.php'; ?>