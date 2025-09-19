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
