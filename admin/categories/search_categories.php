<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

// Varsayılan response
header('Content-Type: application/json');
$response = ['success' => false, 'html' => ''];

$keyword = trim($_POST['keyword'] ?? '');

if ($keyword !== '') {
    $params = ["%$keyword%", "%$keyword%"];

    // Sorgu
    $sql = "
        SELECT * FROM categories 
        WHERE name ILIKE $1 OR description ILIKE $2
        ORDER BY id DESC
        LIMIT 50
    ";

    $res = pg_query_params($dbconn, $sql, $params);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            $html .= '<tr>';
            $html .= '<td>' . $row['id'] . '</td>';
            $html .= '<td>' . sanitize($row['name']) . '</td>';
            $html .= '<td>' . sanitize($row['description']) . '</td>';
            $html .= '<td>';
            if ($row['image']) {
                $html .= '<img src="../../uploads/' . $row['image'] . '" width="50">';
            }
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<a href="categories.php?edit=' . $row['id'] . '" class="btn-edit mr-2">';
            $html .= '<i class="fas fa-edit"></i> Düzenle';
            $html .= '</a>';
            $html .= '<a href="categories.php?delete=' . $row['id'] . '" class="btn-delete">';
            $html .= '<i class="fas fa-trash"></i> Sil';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $response['success'] = true;
        $response['html'] = $html;
    }
} else {
    // Return all categories if no keyword
    $sql = "SELECT * FROM categories ORDER BY id DESC LIMIT 50";
    $res = pg_query($dbconn, $sql);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            $html .= '<tr>';
            $html .= '<td>' . $row['id'] . '</td>';
            $html .= '<td>' . sanitize($row['name']) . '</td>';
            $html .= '<td>' . sanitize($row['description']) . '</td>';
            $html .= '<td>';
            if ($row['image']) {
                $html .= '<img src="../../uploads/' . $row['image'] . '" width="50">';
            }
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<a href="categories.php?edit=' . $row['id'] . '" class="btn-edit mr-2">';
            $html .= '<i class="fas fa-edit"></i> Düzenle';
            $html .= '</a>';
            $html .= '<a href="categories.php?delete=' . $row['id'] . '" class="btn-delete">';
            $html .= '<i class="fas fa-trash"></i> Sil';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $response['success'] = true;
        $response['html'] = $html;
    }
}

echo json_encode($response);
exit();
