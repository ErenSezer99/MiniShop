<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
  echo json_encode(['count' => 0]);
  exit;
}

$cart_count = get_cart_count();
echo json_encode(['count' => $cart_count]);
