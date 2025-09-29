<?php
include_once __DIR__ . '/functions.php';
include_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title>MiniShop</title>
  <link rel="stylesheet" href="/MiniShop/assets/css/style.css">
</head>

<body>
  <!-- Loading Spinner -->
  <div id="loading-spinner">
    <div class="spinner"></div>
  </div>

  <header class="site-nav">
    <a href="/MiniShop/products/index.php" class="nav-link">MiniShop</a>
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
      | <a href="/MiniShop/admin/index.php" class="nav-link">Dashboard</a>
      | <a href="/MiniShop/admin/products/products.php" class="nav-link">Ürünler</a>
      | <a href="/MiniShop/admin/categories/categories.php" class="nav-link">Kategoriler</a>
      | <a href="/MiniShop/admin/users/users.php" class="nav-link">Kullanıcılar</a>
      | <a href="/MiniShop/admin/orders/orders.php" class="nav-link">Siparişleri Yönet</a>
    <?php endif; ?>
    <span style="float:right">
      <?php if (isset($_SESSION['user'])): ?>
        Hoşgeldin, <?= sanitize($_SESSION['user']['username']); ?> |
        <a href="/MiniShop/account/orders_history.php" class="nav-link">Siparişlerim</a> |
        <a href="/MiniShop/wishlist/index.php" class="nav-link">Favorilerim</a> |
        <a href="/MiniShop/auth/logout.php" class="nav-link">Çıkış Yap</a>
      <?php else: ?>
        <a href="/MiniShop/auth/login.php" class="nav-link">Giriş Yap</a> |
        <a href="/MiniShop/auth/register.php" class="nav-link">Kayıt Ol</a>
      <?php endif; ?>
    </span>
  </header>


  <main class="site-main">
    <?php
    // Flash mesaj göster
    $flash = get_flash();
    if ($flash) {
      $type = sanitize($flash['type']);
      echo '<div class="flash ' . $type . '">' . sanitize($flash['message']) . '</div>';
    }
    ?>
    <script src="/MiniShop/assets/js/main.js"></script>