<?php
// Admin dashboard
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../includes/header.php';
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

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Admin Dashboard</h2>
    <p class="text-gray-600">Yönetim paneline hoş geldiniz. Aşağıdaki menüden işlemleri yönetebilirsiniz.</p>
</div>

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

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
    <a href="/MiniShop/admin/products/products.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
        <div class="flex items-center">
            <div class="bg-blue-100 p-3 rounded-full mr-4">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Ürünler</h3>
                <p class="text-gray-600">Ürün yönetimi</p>
            </div>
        </div>
    </a>

    <a href="/MiniShop/admin/categories/categories.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
        <div class="flex items-center">
            <div class="bg-green-100 p-3 rounded-full mr-4">
                <i class="fas fa-tags text-green-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Kategoriler</h3>
                <p class="text-gray-600">Kategori yönetimi</p>
            </div>
        </div>
    </a>

    <a href="/MiniShop/admin/orders/orders.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
        <div class="flex items-center">
            <div class="bg-yellow-100 p-3 rounded-full mr-4">
                <i class="fas fa-shopping-cart text-yellow-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Siparişler</h3>
                <p class="text-gray-600">Sipariş yönetimi</p>
            </div>
        </div>
    </a>

    <a href="/MiniShop/admin/users/users.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
        <div class="flex items-center">
            <div class="bg-purple-100 p-3 rounded-full mr-4">
                <i class="fas fa-users text-purple-600 text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Kullanıcılar</h3>
                <p class="text-gray-600">Kullanıcı yönetimi</p>
            </div>
        </div>
    </a>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>