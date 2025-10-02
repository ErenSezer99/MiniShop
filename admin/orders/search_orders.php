<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

// Varsayılan response
header('Content-Type: application/json');
$response = ['success' => false, 'html' => ''];

// Status çeviri dizisi
$status_map = [
    'pending'    => 'Beklemede',
    'processing' => 'İşlemde',
    'completed'  => 'Tamamlandı',
    'cancelled'  => 'İptal'
];

$keyword = trim($_POST['keyword'] ?? '');

if ($keyword !== '') {
    $params = ["%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"];

    // Sorgu
    $sql = "
        SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, 
               u.username, o.guest_name, o.guest_email, o.guest_address
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id::text ILIKE $1 
           OR u.username ILIKE $2 
           OR o.guest_name ILIKE $3 
           OR o.guest_email ILIKE $4 
           OR o.guest_address ILIKE $5
        ORDER BY o.id DESC
        LIMIT 50
    ";

    $res = pg_query_params($dbconn, $sql, $params);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            // Tarihi okunabilir formata çevir
            $formatted_date = date('d-m-Y H:i', strtotime($row['created_at']));

            $html .= '<tr>';
            $html .= '<td>' . $row['id'] . '</td>';
            $html .= '<td>';

            if ($row['user_id']) {
                $html .= sanitize($row['username']);
            } else {
                $html .= 'Misafir#' . $row['id'] . ' (' . sanitize($row['guest_name']) . ')';
            }

            $html .= '</td>';
            $html .= '<td>' . sanitize($row['guest_email']) . '</td>';
            $html .= '<td>' . sanitize($row['guest_address']) . '</td>';
            $html .= '<td>' . number_format($row['total_amount'], 2) . '₺</td>';
            $html .= '<td>';
            $html .= '<span class="px-2 py-1 rounded ';
            $html .= $row['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '';
            $html .= $row['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '';
            $html .= $row['status'] === 'completed' ? 'bg-green-100 text-green-800' : '';
            $html .= $row['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '';
            $html .= '">';
            $html .= $status_map[$row['status']] ?? sanitize($row['status']);
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . $formatted_date . '</td>';
            $html .= '<td>';
            $html .= '<a href="edit_order.php?id=' . $row['id'] . '" class="btn-edit mr-2">';
            $html .= '<i class="fas fa-edit"></i> Düzenle';
            $html .= '</a>';
            $html .= '<a href="delete_order.php?id=' . $row['id'] . '" class="btn-delete">';
            $html .= '<i class="fas fa-trash"></i> Sil';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';
        }

        $response['success'] = true;
        $response['html'] = $html;
    }
} else {
    // Keyword yoksa tüm siparişleri döndür
    $sql = "
        SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, 
               u.username, o.guest_name, o.guest_email, o.guest_address
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC
        LIMIT 50
    ";

    $res = pg_query($dbconn, $sql);
    if ($res) {
        $html = '';
        while ($row = pg_fetch_assoc($res)) {
            // Tarihi okunabilir formata çevir
            $formatted_date = date('d-m-Y H:i', strtotime($row['created_at']));

            $html .= '<tr>';
            $html .= '<td>' . $row['id'] . '</td>';
            $html .= '<td>';

            if ($row['user_id']) {
                $html .= sanitize($row['username']);
            } else {
                $html .= 'Misafir#' . $row['id'] . ' (' . sanitize($row['guest_name']) . ')';
            }

            $html .= '</td>';
            $html .= '<td>' . sanitize($row['guest_email']) . '</td>';
            $html .= '<td>' . sanitize($row['guest_address']) . '</td>';
            $html .= '<td>' . number_format($row['total_amount'], 2) . '₺</td>';
            $html .= '<td>';
            $html .= '<span class="px-2 py-1 rounded ';
            $html .= $row['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : '';
            $html .= $row['status'] === 'processing' ? 'bg-blue-100 text-blue-800' : '';
            $html .= $row['status'] === 'completed' ? 'bg-green-100 text-green-800' : '';
            $html .= $row['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '';
            $html .= '">';
            $html .= $status_map[$row['status']] ?? sanitize($row['status']);
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . $formatted_date . '</td>';
            $html .= '<td>';
            $html .= '<a href="edit_order.php?id=' . $row['id'] . '" class="btn-edit mr-2">';
            $html .= '<i class="fas fa-edit"></i> Düzenle';
            $html .= '</a>';
            $html .= '<a href="delete_order.php?id=' . $row['id'] . '" class="btn-delete">';
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
