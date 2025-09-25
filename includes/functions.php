<?php
// Genel kullanılacak fonksiyonlar buraya yazılacak

// tüm proje için tek session kontrol noktası
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Veriyi temizleme - XSS koruması için
function sanitize($data)
{
  // Fazla boşlukları temizle
  $data = trim($data);
  // Slash işaretlerini temizle
  $data = stripslashes($data);
  // HTML özel karakterlerini güvenli hale getir
  $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  return $data;
}

/*
 * CSRF Token Fonksiyonları
 * cross-site request forgery saldırılarını engellemek için
*/
function generate_csrf_token()
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  if (empty($_SESSION['csrf_token'])) {
    // güvenli rastgele token üret
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  if (empty($token) || empty($_SESSION['csrf_token'])) return false;
  // sabit-zamanlı karşılaştırma
  return hash_equals($_SESSION['csrf_token'], $token);
}

/*
 * Tek gösterimlik basit flash message sistemi
*/
function set_flash($message, $type = 'success')
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash()
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
  }
  return null;
}

/*
 * Auth helper
 */
function is_logged_in()
{
  return isset($_SESSION['user']) && is_array($_SESSION['user']) && !empty($_SESSION['user']['id']);
}


function current_user_id()
{
  return $_SESSION['user']['id'] ?? null;
}

function require_admin()
{
  if (!is_logged_in() || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    set_flash("Bu sayfaya erişim yetkiniz yok.", "error");
    redirect('../login.php');
  }
}

/*
 * Basit yönlendirme yardımcı fonksiyonu
 */
function redirect($url)
{
  header("Location: $url");
  exit();
}

/*
 * Resim yükleme
 */
/**
 
 * Dosya yükleme fonksiyonu
 * @param string $input_name  HTML input name değeri (örn. 'image')
 * @param string $upload_dir  Yükleme dizini, default: /uploads/
 * @return string|null        Yüklenen dosya adı veya null
 */
function upload_image($input_name, $upload_dir = __DIR__ . '/../uploads/')
{
  if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] !== 0) {
    return null;
  }

  $file = $_FILES[$input_name];
  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $filename = uniqid() . '.' . $ext;

  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
  }

  move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
  return $filename;
}

/**
 * Sipariş oluşturma fonksiyonu
 * @param int|null $user_id  Üye ID, misafir için null
 * @param array $cart_items  Sepet öğeleri, her öğe ['id','quantity','price']
 * @param float $total_amount
 * @return int|false  Oluşan sipariş ID veya false
 */
function create_order($user_id, $cart_items, $total_amount, $guest_name = null, $guest_email = null, $guest_address = null)
{
  global $dbconn;

  // Eğer üye kullanıcı ve guest_name/email boş ise users tablosundan al
  if ($user_id !== null) {
    pg_prepare($dbconn, "get_user_info", "SELECT username, email FROM users WHERE id=$1");
    $res_user = pg_execute($dbconn, "get_user_info", [$user_id]);
    if ($res_user && $row = pg_fetch_assoc($res_user)) {
      if (!$guest_name) $guest_name = $row['username'];
      if (!$guest_email) $guest_email = $row['email'];
    }
  }

  // Begin transaction
  pg_query($dbconn, "BEGIN");

  // orders tablosuna ekle
  pg_prepare($dbconn, "insert_order", "
        INSERT INTO orders (user_id, total_amount, guest_name, guest_email, guest_address)
        VALUES ($1, $2, $3, $4, $5) RETURNING id
    ");
  $res = pg_execute($dbconn, "insert_order", [
    $user_id,
    $total_amount,
    $guest_name,
    $guest_email,
    $guest_address
  ]);

  if (!$res) {
    pg_query($dbconn, "ROLLBACK");
    return false;
  }

  $order = pg_fetch_assoc($res);
  $order_id = $order['id'];

  // order_items ekle
  pg_prepare($dbconn, "insert_order_item", "
        INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($1, $2, $3, $4)
    ");
  foreach ($cart_items as $item) {
    $res_item = pg_execute($dbconn, "insert_order_item", [
      $order_id,
      $item['id'] ?? $item['product_id'],
      $item['quantity'],
      $item['price']
    ]);
    if (!$res_item) {
      pg_query($dbconn, "ROLLBACK");
      return false;
    }
  }

  pg_query($dbconn, "COMMIT");
  return $order_id;
}


/**
 * Sipariş email simülasyonu
 * Burada gerçek email yerine flash mesaj veya log 
 */
function send_order_email($order_id)
{
  // Örnek flash ile simülasyon
  set_flash("Siparişiniz (#$order_id) başarıyla alındı! Email gönderildi.", "success");
}
