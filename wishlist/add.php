<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Favorilere eklemek için lütfen giriş yapın.']);
    exit();
}

$user_id = current_user_id();
$product_id = (int) ($_POST['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün']);
    exit();
}

// Wishlist’te zaten var mı kontrol et
pg_prepare($dbconn, "check_wishlist", "SELECT id FROM wishlist WHERE user_id=$1 AND product_id=$2");
$res_check = pg_execute($dbconn, "check_wishlist", [$user_id, $product_id]);

if (pg_num_rows($res_check) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ürün zaten favorilerde ekli']);
    exit();
}

// Ekle
pg_prepare($dbconn, "insert_wishlist", "INSERT INTO wishlist (user_id, product_id) VALUES ($1,$2)");
$res = pg_execute($dbconn, "insert_wishlist", [$user_id, $product_id]);

if ($res) {
    echo json_encode(['status' => 'success', 'message' => 'Ürün favorilere eklendi']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bir hata oluştu']);
}
