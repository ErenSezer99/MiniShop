<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash('Geçersiz sipariş ID.');
    redirect('orders.php');
    exit;
}

$order_id = (int) $_GET['id'];

// Siparişi sil
pg_prepare($dbconn, "delete_order", "DELETE FROM orders WHERE id = $1");
pg_execute($dbconn, "delete_order", [$order_id]);

set_flash('Sipariş başarıyla silindi.');
redirect('orders.php');
exit;
