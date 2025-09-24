<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

if (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];

    // Ürünü getir
    pg_prepare($dbconn, "select_product_image", "SELECT image FROM products WHERE id = $1");
    $res = pg_execute($dbconn, "select_product_image", [$product_id]);
    $product = pg_fetch_assoc($res);

    if ($product) {
        // Resmi sil
        if (!empty($product['image'])) {
            $image_path = __DIR__ . '/../../uploads/' . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Ürünü sil
        pg_prepare($dbconn, "delete_product", "DELETE FROM products WHERE id = $1");
        pg_execute($dbconn, "delete_product", [$product_id]);
    }
}

set_flash('Ürün başarıyla silindi!');
redirect('products.php');
exit;
