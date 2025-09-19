<?php
include_once "functions.php";
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title>MiniShop</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <header>
    <h1>MiniShop</h1>
    <nav>
      <?php if (is_logged_in()): ?>
        <!-- Kullanıcı giriş yapmışsa -->
        <a href="index.php">Ana Sayfa</a>
        <a href="products.php">Ürünler</a>
        <a href="cart.php">Sepet</a>
        <span>Hoşgeldin, <?php echo sanitize($_SESSION['username']); ?>!</span>
        <a href="logout.php">Çıkış Yap</a>
      <?php else: ?>
        <!-- Kullanıcı giriş yapmamışsa -->
        <a href="login.php">Giriş Yap</a>
        <a href="register.php">Kayıt Ol</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>