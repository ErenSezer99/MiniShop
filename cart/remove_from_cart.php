<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün ID']);
    exit;
}

if (is_logged_in()) {
    $user_id = current_user_id();

    // Sepetten sil
    pg_prepare($dbconn, "delete_cart_item", "DELETE FROM cart WHERE user_id=$1 AND product_id=$2");
    pg_execute($dbconn, "delete_cart_item", [$user_id, $product_id]);

    // Sepetteki güncel ürün sayısını al
    $cart_count = get_cart_count();
} else {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ürün sepetinizde yok']);
        exit;
    }

    // Misafir kullanıcı için sepetteki ürün sayısını hesapla
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $cart_count += $qty;
        }
    }
}

echo json_encode(['status' => 'success', 'message' => 'Ürün sepetten silindi', 'cart_count' => $cart_count]);
