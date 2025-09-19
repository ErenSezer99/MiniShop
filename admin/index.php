<?php
// Admin dashboard
include_once __DIR__ . '/header.php';

// Basit istatistik sorguları (try/catch ile hata güvenliği)
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $products_count = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $categories_count = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $orders_count = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $users_count = (int)$stmt->fetchColumn();
} catch (PDOException $e) {
    $products_count = $categories_count = $orders_count = $users_count = "-";
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

<?php include_once __DIR__ . '/footer.php'; ?>