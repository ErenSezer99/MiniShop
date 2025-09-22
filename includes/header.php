<?php
include_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <title>MiniShop</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <header>
    <h1>MiniShop</h1>
    <nav>
      <a href="/MiniShop/index.php">Ana Sayfa</a>
      <a href="#">Ürünler</a>
      <a href="#">Sepet</a>

      <?php if (isset($_SESSION['user'])): ?>
        <!-- Kullanıcı giriş yapmışsa -->
        <span>Hoşgeldin, <?php echo sanitize($_SESSION['user']['username']); ?>!</span>
        <a href="auth/logout.php">Çıkış Yap</a>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          <a href="admin/index.php">Dashboard</a>
        <?php endif; ?>
      <?php else: ?>
        <!-- Kullanıcı giriş yapmamışsa -->
        <a href="/MiniShop/auth/login.php">Giriş Yap</a>
        <a href="/MiniShop/auth/register.php">Kayıt Ol</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>