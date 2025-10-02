<?php
include_once __DIR__ . '/functions.php';
include_once __DIR__ . '/../config/database.php';

// Get cart count for logged in users
$cart_count = 0;
$is_logged_in = isset($_SESSION['user']);
if ($is_logged_in) {
  $cart_count = get_cart_count();
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MiniShop - Professional E-Commerce</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Custom styles -->
  <link rel="stylesheet" href="/MiniShop/assets/css/style.css">
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
  <!-- Loading Spinner -->
  <div id="loading-spinner" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>
  </div>

  <!-- Header -->
  <header class="bg-white shadow-md flex-shrink-0">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="/MiniShop/products/index.php" class="text-2xl font-bold text-blue-600 hover:text-blue-800 transition duration-300">
            <i class="fas fa-shopping-bag mr-2"></i>MiniShop
          </a>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex space-x-6">
          <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="/MiniShop/admin/index.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Dashboard</a>
            <a href="/MiniShop/admin/products/products.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Ürünler</a>
            <a href="/MiniShop/admin/categories/categories.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Kategoriler</a>
            <a href="/MiniShop/admin/users/users.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Kullanıcılar</a>
            <a href="/MiniShop/admin/orders/orders.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Siparişler</a>
          <?php endif; ?>
        </nav>

        <!-- User Actions -->
        <div class="flex items-center space-x-4">
          <?php if (isset($_SESSION['user'])): ?>
            <div class="relative">
              <a href="/MiniShop/cart/cart.php" class="text-gray-700 hover:text-blue-600 transition duration-300 relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <?php if ($cart_count > 0): ?>
                  <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                    <?= $cart_count ?>
                  </span>
                <?php endif; ?>
              </a>
            </div>
            <a href="/MiniShop/wishlist/index.php" class="text-gray-700 hover:text-blue-600 transition duration-300">
              <i class="fas fa-heart text-xl"></i>
            </a>
            <div class="relative">
              <button id="user-menu-button" class="flex items-center text-gray-700 hover:text-blue-600 transition duration-300 focus:outline-none">
                <i class="fas fa-user mr-1"></i>
                <span class="hidden md:inline"><?= sanitize($_SESSION['user']['username']); ?></span>
                <i id="user-menu-arrow" class="fas fa-chevron-down ml-1 text-xs transition-transform duration-300"></i>
              </button>
              <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-10">
                <a href="/MiniShop/account/orders_history.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Siparişlerim</a>
                <a href="/MiniShop/wishlist/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Favorilerim</a>
                <a href="/MiniShop/auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Çıkış Yap</a>
              </div>
            </div>
          <?php else: ?>
            <a href="/MiniShop/auth/login.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">
              <i class="fas fa-sign-in-alt mr-1"></i>Giriş Yap
            </a>
            <a href="/MiniShop/auth/register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition duration-300">
              Kayıt Ol
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mobile Navigation Toggle -->
      <div class="md:hidden flex justify-between items-center pb-4">
        <button id="mobile-menu-button" class="text-gray-700">
          <i class="fas fa-bars text-2xl"></i>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div id="mobile-menu" class="md:hidden hidden pb-4">
        <div class="flex flex-col space-y-3">
          <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
            <a href="/MiniShop/admin/index.php" class="text-gray-700 hover:text-blue-600 font-medium">Dashboard</a>
            <a href="/MiniShop/admin/products/products.php" class="text-gray-700 hover:text-blue-600 font-medium">Ürünler</a>
            <a href="/MiniShop/admin/categories/categories.php" class="text-gray-700 hover:text-blue-600 font-medium">Kategoriler</a>
            <a href="/MiniShop/admin/users/users.php" class="text-gray-700 hover:text-blue-600 font-medium">Kullanıcılar</a>
            <a href="/MiniShop/admin/orders/orders.php" class="text-gray-700 hover:text-blue-600 font-medium">Siparişler</a>
          <?php endif; ?>
          <a href="/MiniShop/cart/cart.php" class="text-gray-700 hover:text-blue-600 font-medium">Sepetim</a>
          <a href="/MiniShop/wishlist/index.php" class="text-gray-700 hover:text-blue-600 font-medium">Favorilerim</a>
          <?php if (isset($_SESSION['user'])): ?>
            <a href="/MiniShop/account/orders_history.php" class="text-gray-700 hover:text-blue-600 font-medium">Siparişlerim</a>
            <a href="/MiniShop/auth/logout.php" class="text-gray-700 hover:text-blue-600 font-medium">Çıkış Yap</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8 flex-grow">
    <?php
    // Flash mesaj göster
    $flash = get_flash();
    if ($flash) {
      $type = sanitize($flash['type']);
      $bgColor = ($type === 'success') ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
      echo '<div class="flash ' . $type . ' ' . $bgColor . ' border px-4 py-3 rounded relative mb-4" role="alert">
              <span class="block sm:inline">' . sanitize($flash['message']) . '</span>
            </div>';
    }
    ?>

    <!-- Pass login status to JavaScript -->
    <script>
      const isLoggedIn = <?= $is_logged_in ? 'true' : 'false' ?>;
    </script>