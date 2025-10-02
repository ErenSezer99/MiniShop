<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

// Varsayılan response
header('Content-Type: application/json');
$response = ['success' => false, 'html' => ''];

$keyword = trim($_POST['keyword'] ?? '');

// Kullanıcının favori ürünleri
$user_favs = [];
if (is_logged_in()) {
    $user_id = current_user_id();
    pg_prepare($dbconn, "select_wishlist", "SELECT product_id FROM wishlist WHERE user_id = $1");
    $res_wishlist = pg_execute($dbconn, "select_wishlist", [$user_id]);
    while ($row = pg_fetch_assoc($res_wishlist)) {
        $user_favs[] = $row['product_id'];
    }
}

if ($keyword !== '') {
    $params = ["%$keyword%", "%$keyword%"];

    // Sorgu
    $sql = "
        SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.name ILIKE $1 OR p.description ILIKE $2
        ORDER BY p.id DESC
        LIMIT 50
    ";

    $res = pg_query_params($dbconn, $sql, $params);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            $html .= '<div class="product-card">';

            if ($row['image']) {
                $html .= '<img src="/MiniShop/uploads/' . sanitize($row['image']) . '" alt="' . sanitize($row['name']) . '" class="product-img">';
            } else {
                $html .= '<div class="bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center text-gray-500">';
                $html .= '<i class="fas fa-image text-4xl"></i>';
                $html .= '</div>';
            }

            $html .= '<div class="product-info">';
            $html .= '<h3 class="product-name">' . sanitize($row['name']) . '</h3>';
            $html .= '<p class="product-price">' . number_format($row['price'], 2) . ' ₺</p>';
            $html .= '<p class="product-category">Kategori: ' . sanitize($row['category_name']) . '</p>';
            $html .= '<p class="product-desc">' . sanitize($row['description']) . '</p>';

            $html .= '<form class="add-to-cart-form" data-product-id="' . $row['id'] . '">';
            $html .= '<div class="cart-item-quantity">';
            $html .= '<label for="quantity-' . $row['id'] . '" class="text-gray-700">Adet:</label>';
            $html .= '<input type="number" id="quantity-' . $row['id'] . '" name="quantity" value="1" min="1" class="qty-input">';
            $html .= '</div>';
            $html .= '<button type="submit" class="btn-add-cart flex items-center justify-center">';
            $html .= '<i class="fas fa-cart-plus mr-2"></i> Sepete Ekle';
            $html .= '</button>';
            $html .= '</form>';

            // Favoriler Butonu
            $isFav = in_array($row['id'], $user_favs);
            $favAction = $isFav ? 'remove' : 'add';
            $favIcon   = $isFav ? '♥' : '♡';
            $favTitle  = $isFav ? 'Favorilerden çıkar' : 'Favorilere ekle';

            $html .= '<button class="btn-fav ' . ($isFav ? 'active' : '') . '" data-product-id="' . $row['id'] . '" data-action="' . $favAction . '" title="' . $favTitle . '">' . $favIcon . '</button>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $response['success'] = true;
        $response['html'] = $html;
    }
} else {
    // Keyword yoksa tüm ürünleri döndür
    $sql = "
        SELECT p.id, p.name, p.description, p.price, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC
        LIMIT 50
    ";

    $res = pg_query($dbconn, $sql);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            $html .= '<div class="product-card">';

            if ($row['image']) {
                $html .= '<img src="/MiniShop/uploads/' . sanitize($row['image']) . '" alt="' . sanitize($row['name']) . '" class="product-img">';
            } else {
                $html .= '<div class="bg-gray-200 border-2 border-dashed rounded-xl w-full h-48 flex items-center justify-center text-gray-500">';
                $html .= '<i class="fas fa-image text-4xl"></i>';
                $html .= '</div>';
            }

            $html .= '<div class="product-info">';
            $html .= '<h3 class="product-name">' . sanitize($row['name']) . '</h3>';
            $html .= '<p class="product-price">' . number_format($row['price'], 2) . ' ₺</p>';
            $html .= '<p class="product-category">Kategori: ' . sanitize($row['category_name']) . '</p>';
            $html .= '<p class="product-desc">' . sanitize($row['description']) . '</p>';

            $html .= '<form class="add-to-cart-form" data-product-id="' . $row['id'] . '">';
            $html .= '<div class="cart-item-quantity">';
            $html .= '<label for="quantity-' . $row['id'] . '" class="text-gray-700">Adet:</label>';
            $html .= '<input type="number" id="quantity-' . $row['id'] . '" name="quantity" value="1" min="1" class="qty-input">';
            $html .= '</div>';
            $html .= '<button type="submit" class="btn-add-cart flex items-center justify-center">';
            $html .= '<i class="fas fa-cart-plus mr-2"></i> Sepete Ekle';
            $html .= '</button>';
            $html .= '</form>';

            // Favoriler Butonu
            $isFav = in_array($row['id'], $user_favs);
            $favAction = $isFav ? 'remove' : 'add';
            $favIcon   = $isFav ? '♥' : '♡';
            $favTitle  = $isFav ? 'Favorilerden çıkar' : 'Favorilere ekle';

            $html .= '<button class="btn-fav ' . ($isFav ? 'active' : '') . '" data-product-id="' . $row['id'] . '" data-action="' . $favAction . '" title="' . $favTitle . '">' . $favIcon . '</button>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $response['success'] = true;
        $response['html'] = $html;
    }
}

echo json_encode($response);
exit();
