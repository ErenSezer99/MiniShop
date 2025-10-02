<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Ürün ID geçersiz']);
    exit;
}

if (is_logged_in()) {
    // Kullanıcı login ise veritabanına ekle
    $user_id = current_user_id();

    // Önce sepette var mı kontrol et
    pg_prepare($dbconn, "check_cart", "SELECT id, quantity FROM cart WHERE user_id=$1 AND product_id=$2");
    $res = pg_execute($dbconn, "check_cart", [$user_id, $product_id]);
    if ($row = pg_fetch_assoc($res)) {
        // Var, güncelle
        $new_qty = $row['quantity'] + $quantity;
        pg_prepare($dbconn, "update_cart", "UPDATE cart SET quantity=$1 WHERE id=$2");
        pg_execute($dbconn, "update_cart", [$new_qty, $row['id']]);
    } else {
        // Yok, ekle
        pg_prepare($dbconn, "insert_cart", "INSERT INTO cart (user_id, product_id, quantity) VALUES ($1,$2,$3)");
        pg_execute($dbconn, "insert_cart", [$user_id, $product_id, $quantity]);
    }
    
    // Get updated cart count
    $cart_count = get_cart_count();
} else {
    // Misafir, session tabanlı
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    // Calculate cart count for guest user
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $cart_count += $qty;
        }
    }
}

echo json_encode(['status' => 'success', 'message' => 'Ürün sepete eklendi', 'cart_count' => $cart_count]);