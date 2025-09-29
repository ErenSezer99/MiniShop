<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız']);
    exit();
}

$user_id = current_user_id();
$product_id = (int) ($_POST['product_id'] ?? 0);

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün']);
    exit();
}

// Sil
pg_prepare($dbconn, "delete_wishlist", "DELETE FROM wishlist WHERE user_id=$1 AND product_id=$2");
$res = pg_execute($dbconn, "delete_wishlist", [$user_id, $product_id]);

if ($res) {
    echo json_encode(['status' => 'success', 'message' => 'Ürün favorilerden kaldırıldı']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bir hata oluştu']);
}
