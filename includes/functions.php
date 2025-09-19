<?php
// Genel kullanılacak fonksiyonlar buraya yazılacak

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
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  return !empty($_SESSION['user_id']);
}

function current_user_id()
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
  return $_SESSION['user_id'] ?? null;
}

/*
 * Basit yönlendirme yardımcı fonksiyonu
 */
function redirect($url)
{
  header("Location: $url");
  exit();
}
