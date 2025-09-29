<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

// Varsayılan response
header('Content-Type: application/json');
$response = ['success' => false, 'html' => ''];

$keyword = trim($_POST['keyword'] ?? '');
$category = (int) ($_POST['category'] ?? 0);

$params = [];
$where = [];

// Arama keyword varsa ILIKE
if ($keyword !== '') {
    $where[] = "(p.name ILIKE $1 OR p.description ILIKE $1)";
    $params[] = "%$keyword%";
}

// Kategori seçilmişse filtrele
if ($category > 0) {
    $where[] = "p.category_id = " . ($category);
}

// Where clause
$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

// Sorgu
$sql = "
    SELECT p.id, p.name, p.description, p.price, p.stock, p.image, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where_sql
    ORDER BY p.id DESC
    LIMIT 50
";

$res = pg_query_params($dbconn, $sql, $params);
if ($res) {
    $html = '';
    while ($row = pg_fetch_assoc($res)) {
        $html .= '<tr data-product-id="' . $row['id'] . '">';
        $html .= '<td>' . $row['id'] . '</td>';
        $html .= '<td>' . sanitize($row['name']) . '</td>';
        $html .= '<td>' . sanitize($row['description']) . '</td>';
        $html .= '<td>' . $row['price'] . '</td>';
        $html .= '<td>' . $row['stock'] . '</td>';
        $html .= '<td>' . sanitize($row['category_name']) . '</td>';
        $html .= '<td>';
        if ($row['image']) {
            $html .= '<img src="../../uploads/' . $row['image'] . '" width="50">';
        }
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<a href="edit_product.php?id=' . $row['id'] . '">Düzenle</a> ';
        $html .= '<a href="delete_product.php?id=' . $row['id'] . '">Sil</a>';
        $html .= '</td>';
        $html .= '</tr>';
    }

    $response['success'] = true;
    $response['html'] = $html;
}

echo json_encode($response);
exit();
