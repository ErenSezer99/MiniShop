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
  <header class="site-nav">
    <a href="/MiniShop/products/index.php">MiniShop</a>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
      | <a href="/MiniShop/admin/index.php">Dashboard</a>
      | <a href="/MiniShop/admin/products/products.php">Ürünler</a>
      | <a href="/MiniShop/admin/categories/categories.php">Kategoriler</a>
      | <a href="/MiniShop/admin/orders/orders.php">Siparişler</a>
      | <a href="/MiniShop/admin/users/users.php">Kullanıcılar</a>
    <?php endif; ?>

    <span style="float:right">
      <?php if (isset($_SESSION['user'])): ?>
        Hoşgeldin, <?= sanitize($_SESSION['user']['username']); ?> |
        <a href="/MiniShop/auth/logout.php">Çıkış Yap</a>
      <?php else: ?>
        <a href="/MiniShop/auth/login.php">Giriş Yap</a> |
        <a href="/MiniShop/auth/register.php">Kayıt Ol</a>
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