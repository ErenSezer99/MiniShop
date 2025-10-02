// Sayfada backend flash mesajı varsa (PHP ile basılmış), 3 sn sonra kaybolsun
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 3000);
    }
    
    // Oturum açmış kullanıcılar için sayfa yüklendiğinde etiketi güncelle
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
        updateCartBadgeFromServer();
    }
    
    // Sayfa yüklendiğinde spinner'ı gizle
    setSpinner(false);
    
    // Kayıt sırasındaki doğrulama hatalarında spinner'ı gizle
    if (window.location.pathname.includes('register.php')) {
        const errorMessages = document.querySelectorAll('.bg-red-100');
        if (errorMessages.length > 0) {
            setSpinner(false);
        }
    }
});

// Dinamik JS ile flash mesaj göstermek için yardımcı fonksiyon
function showFlashMessage(message, type = 'success') {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flash ' + type;
    
    // Mesaj tipine göre tailwind sınıfları
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    msgDiv.className += ` ${bgColor} border px-4 py-3 rounded relative mb-4`;
    
    msgDiv.innerHTML = `
        <span class="block sm:inline">${message}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    msgDiv.style.opacity = '0';
    
    const container = document.querySelector('main');
    container.insertBefore(msgDiv, container.firstChild);
    
    requestAnimationFrame(() => {
        msgDiv.style.opacity = '1';
    });
    
    setTimeout(() => {
        msgDiv.style.opacity = '0';
        setTimeout(() => msgDiv.remove(), 500);
    }, 3000);
}

// Spinner gösterme yardımcı fonksiyonu
function setSpinner(show = true) {
    const spinner = document.getElementById('loading-spinner');
    if (!spinner) return;
    spinner.classList.toggle('hidden', !show);
}

// Event delegation ile tüm sayfalarda spinner
document.addEventListener('click', (e) => {
    const target = e.target;

    // 1. Navbar linkleri
    if (target.matches('.nav-link') || target.closest('a')) {
        setSpinner(true);
    }

    // 2. Sepete git / checkout butonları
    if (target.matches('.btn-cart, .btn-checkout, .btn-add-cart')) {
        setSpinner(true);
    }

    // 3. Diğer tüm normal linkler 
    if (target.tagName === 'A' && target.getAttribute('href') && !target.getAttribute('href').startsWith('#')) {
        setSpinner(true);
    }

    // 4. Form submit butonları - Kayıt ve ödeme formları hariç (infinite spinnerı engellemek için)
    if ((target.tagName === 'BUTTON' && target.type === 'submit') ||
        (target.tagName === 'INPUT' && target.type === 'submit')) {
        
        // Kayıt formu submit butonu mu kontrol
        const form = target.closest('form');
        if (form && (form.id === 'register-form' || form.id === 'checkout-form')) {
            return;
        }
        
        // Diğer tüm formlar için spinner'ı göster
        setSpinner(true);
    }
    
    // Favoriler butonu - Sadece data-action özelliği yoksa işle
    // Wishlist.js ile aynı işlemin tekrarını önleme amaçlı
    if ((target.matches('.btn-fav') || target.closest('.btn-fav')) && 
        !target.hasAttribute('data-action') && 
        !target.closest('.btn-fav').hasAttribute('data-action')) {
        
        const button = target.matches('.btn-fav') ? target : target.closest('.btn-fav');
        const productId = button.getAttribute('data-product-id');
        const action = button.classList.contains('active') ? 'remove' : 'add';
        
        // Favori simgesini hemen değiş (daha iyi UX için)
        button.classList.toggle('active');
        button.textContent = button.classList.contains('active') ? '♥' : '♡';
        
        // Server'a istek gönder
        fetch(`/MiniShop/wishlist/${action}.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFlashMessage(data.message, 'success');
            } else {
                // İstek başarısızsa değişikliği geri al
                button.classList.toggle('active');
                button.textContent = button.classList.contains('active') ? '♥' : '♡';
                showFlashMessage(data.message || 'Bir hata oluştu', 'error');
            }
        })
        .catch(error => {
            // İstek başarısızsa değişikliği geri al
            button.classList.toggle('active');
            button.textContent = button.classList.contains('active') ? '♥' : '♡';
            showFlashMessage('Bir hata oluştu', 'error');
        });
    }
});

// Sayfa yenileme / manuel reload sırasında spinner kontrolü
window.addEventListener('beforeunload', () => {
    setSpinner(true);
});

window.addEventListener('load', () => {
    setSpinner(false);
});

// Sunucudan sepet etiketini güncelleme işlevi
function updateCartBadgeFromServer() {
    // Sadece oturum açmış kullanıcılar için güncelle
    if (document.querySelector('a[href="/MiniShop/cart/cart.php"]')) {
        fetch('/MiniShop/cart/get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                updateCartBadgeUI(data.count);
            })
            .catch(error => {
                console.log('Cart badge update failed:', error);
            });
    }
}

// 30 saniyede bir sepet etiketini güncelle
if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
    setInterval(updateCartBadgeFromServer, 30000);
}

// Sepet etiketini belirli sayıda güncelleme
function updateCartBadgeUI(count) {
    const cartLink = document.querySelector('a[href="/MiniShop/cart/cart.php"]');
    if (cartLink) {
        let badge = cartLink.querySelector('.absolute');
        if (!badge && count > 0) {
            // Sepet etiketi yoksa oluştur
            badge = document.createElement('span');
            badge.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
            cartLink.appendChild(badge);
        }
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }
}

// Menü aç/kapat işlemleri
document.addEventListener('DOMContentLoaded', function() {
    // Kullanıcı menüsü aç/kapat
    const userMenuButton = document.getElementById('user-menu-button');
    if (userMenuButton) {
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = document.getElementById('user-menu');
            const arrow = document.getElementById('user-menu-arrow');
            if (menu && arrow) {
                menu.classList.toggle('hidden');
                arrow.classList.toggle('rotate-180');
            }
        });
    }

    // Kullanıcı menüsünü dışarı tıklandığında kapat
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('user-menu');
        const userMenuButton = document.getElementById('user-menu-button');
        const arrow = document.getElementById('user-menu-arrow');
        if (userMenu && userMenuButton && arrow &&
            !userMenu.contains(event.target) &&
            !userMenuButton.contains(event.target)) {
            userMenu.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    });

    // Mobil menü aç/kapat
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            if (menu) menu.classList.toggle('hidden');
        });
    }
});

// Sayfa yüklendiğinde kayıt formu spinner'ını gizle
document.addEventListener('DOMContentLoaded', function() {
    const loadingSpinner = document.getElementById('loading-spinner');

    if (loadingSpinner) {
        loadingSpinner.classList.add('hidden');
    }
});
