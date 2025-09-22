<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

if (isset($_GET['id'])) {
    $product_id = (int) $_GET['id'];

    // Ürünü getir
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Resmi sil
        if (!empty($product['image'])) {
            $image_path = __DIR__ . '/../../uploads/' . $product['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $stmt_delete = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt_delete->execute([':id' => $product_id]);
    }
}

set_flash('Ürün başarıyla silindi!');
redirect('products.php');
exit;
