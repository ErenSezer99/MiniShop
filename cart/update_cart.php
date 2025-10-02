<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün ID']);
    exit;
}

// If quantity is 0 or less, remove the item from cart
if ($quantity <= 0) {
    if (is_logged_in()) {
        $user_id = current_user_id();

        // Sepetten sil
        pg_prepare($dbconn, "delete_cart_item", "DELETE FROM cart WHERE user_id=$1 AND product_id=$2");
        pg_execute($dbconn, "delete_cart_item", [$user_id, $product_id]);

        // Get updated cart count
        $cart_count = get_cart_count();
    } else {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }

        // Calculate cart count for guest user
        $cart_count = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $qty) {
                $cart_count += $qty;
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Ürün sepetten silindi', 'cart_count' => $cart_count]);
    exit;
}

if (is_logged_in()) {
    $user_id = current_user_id();

    // Sepeti güncelle
    pg_prepare($dbconn, "update_cart_qty", "UPDATE cart SET quantity=$1 WHERE user_id=$2 AND product_id=$3");
    pg_execute($dbconn, "update_cart_qty", [$quantity, $user_id, $product_id]);

    // Get updated cart count
    $cart_count = get_cart_count();
} else {
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ürün sepetinizde yok']);
        exit;
    }

    // Calculate cart count for guest user
    $cart_count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $qty) {
            $cart_count += $qty;
        }
    }
}

echo json_encode(['status' => 'success', 'message' => 'Sepet güncellendi', 'cart_count' => $cart_count]);
