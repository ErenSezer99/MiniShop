<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün ID']);
    exit;
}

if (is_logged_in()) {
    $user_id = current_user_id();

    // Sepeti güncelle
    pg_prepare($dbconn, "update_cart_qty", "UPDATE cart SET quantity=$1 WHERE user_id=$2 AND product_id=$3");
    pg_execute($dbconn, "update_cart_qty", [$quantity, $user_id, $product_id]);
} else {
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ürün sepetinizde yok']);
        exit;
    }
}

echo json_encode(['status' => 'success', 'message' => 'Sepet güncellendi']);
