<?php
// Footer dosyası - her sayfanın sonunda çağrılacak
?>

</main>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8 flex-shrink-0">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- About Section -->
      <div>
        <h3 class="text-xl font-bold mb-4">MiniShop</h3>
        <p class="text-gray-300">
          Profesyonel e-ticaret deneyimi sunan, kullanıcı dostu arayüzü ile alışverişin keyfini çıkarabileceğiniz bir platform.
        </p>
      </div>

      <!-- Links Section -->
      <div>
        <h3 class="text-xl font-bold mb-4">Hızlı Linkler</h3>
        <ul class="space-y-2">
          <li><a href="/MiniShop/products/index.php" class="text-gray-300 hover:text-white transition duration-300">Ürünler</a></li>
          <?php if (isset($_SESSION['user'])): ?>
            <li><a href="/MiniShop/account/orders_history.php" class="text-gray-300 hover:text-white transition duration-300">Siparişlerim</a></li>
            <li><a href="/MiniShop/wishlist/index.php" class="text-gray-300 hover:text-white transition duration-300">Favorilerim</a></li>
          <?php else: ?>
            <li><a href="/MiniShop/auth/login.php" class="text-gray-300 hover:text-white transition duration-300">Giriş Yap</a></li>
            <li><a href="/MiniShop/auth/register.php" class="text-gray-300 hover:text-white transition duration-300">Kayıt Ol</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Contact Section -->
      <div>
        <h3 class="text-xl font-bold mb-4">İletişim</h3>
        <ul class="space-y-2 text-gray-300">
          <li class="flex items-start">
            <i class="fas fa-envelope mt-1 mr-2"></i>
            <span>info@minishop.com</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-phone mt-1 mr-2"></i>
            <span>+90 123 456 7890</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-map-marker-alt mt-1 mr-2"></i>
            <span>İstanbul, Türkiye</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="border-t border-gray-700 mt-8 pt-6 text-center text-gray-400">
      <p>&copy; <?php echo date("Y"); ?> MiniShop. Tüm hakları saklıdır.</p>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="/MiniShop/assets/js/main.js"></script>
<script src="/MiniShop/assets/js/cart.js"></script>
<script src="/MiniShop/assets/js/wishlist.js"></script>

<!-- Menu Toggle Scripts -->
<script>
  // User menu toggle functionality
  document.getElementById('user-menu-button').addEventListener('click', function(e) {
    e.stopPropagation();
    const menu = document.getElementById('user-menu');
    const arrow = document.getElementById('user-menu-arrow');

    menu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
  });

  // Close user menu when clicking outside
  document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('user-menu');
    const userMenuButton = document.getElementById('user-menu-button');

    if (userMenu && userMenuButton &&
      !userMenu.contains(event.target) &&
      !userMenuButton.contains(event.target)) {
      userMenu.classList.add('hidden');
      document.getElementById('user-menu-arrow').classList.remove('rotate-180');
    }
  });

  // Mobile menu toggle
  document.getElementById('mobile-menu-button').addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
  });
</script>
</body>

</html>