// Sepet başladı mı kontrol
if (typeof window.cartInitialized === 'undefined') {
    window.cartInitialized = false;
}

// Sepeti başlat
function initializeCart() {
    // Birden fazla başlatmayı önle
    if (window.cartInitialized) {
        return;
    }
    window.cartInitialized = true;

    // Sepete ekleme
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            setSpinner(true); 

            const product_id = this.dataset.productId;
            const quantity = this.querySelector('[name="quantity"]').value;

            fetch('/MiniShop/cart/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + product_id + '&quantity=' + quantity
            })
            .then(res => res.json())
            .then(data => {
                showFlashMessage(data.message, data.status);
                // Sepet etiketini güncelle
                if (data.status === 'success' && data.cart_count !== undefined) {
                    updateCartBadgeUI(data.cart_count);
                }
            })
            .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
            .finally(() => setSpinner(false)); 
        });
    });

    // Sepet miktarı güncelleme
    document.querySelectorAll('.cart-qty').forEach(input => {
        input.addEventListener('change', function() {
            updateCartQuantity(this);
        });
    });

    // Sepetten silme
    document.querySelectorAll('.remove-cart').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            setSpinner(true); 
            const tr = this.closest('tr');
            const product_id = tr.dataset.productId;

            fetch('/MiniShop/cart/remove_from_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + product_id
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Ürün silinince sepet etiketini güncelle
                    if (data.cart_count !== undefined) {
                        updateCartBadgeUI(data.cart_count);
                    }
                    location.reload();
                }
                else showFlashMessage(data.message, 'error');
            })
            .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
            .finally(() => setSpinner(false)); 
        });
    });

    // Sepetteki ürün miktarını artırma butonu
    document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Birden fazla hızlı tıklamayı önleme
            if (this.dataset.processing === 'true') return;
            this.dataset.processing = 'true';
            
            const input = this.parentElement.querySelector('.cart-qty');
            let value = parseInt(input.value) || 1;
            input.value = value + 1;
            // Sepet miktarını güncelle
            updateCartQuantity(input, this);
            
            // İşlemeyi sıfırla (100ms gecikmeli)
            setTimeout(() => {
                this.dataset.processing = 'false';
            }, 100);
        });
    });

    // Sepetteki ürün miktarını azaltma butonu
    document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Birden fazla hızlı tıklamayı önleme
            if (this.dataset.processing === 'true') return;
            this.dataset.processing = 'true';
            
            const input = this.parentElement.querySelector('.cart-qty');
            let value = parseInt(input.value) || 1;
            if (value > 1) {
                input.value = value - 1;
                // Sepet miktarını güncelle
                updateCartQuantity(input, this);
            } else if (value === 1) {
                // Miktar sıfıra düştüğünde ürünü sil
                input.value = 0;
                removeCartItem(input);
            }
            
            // İşlemeyi sıfırla (100ms gecikmeli)
            setTimeout(() => {
                this.dataset.processing = 'false';
            }, 100);
        });
    });
}

// Sepetteki ürün miktarını güncelleme fonksiyonu
function updateCartQuantity(input, button = null) {
    setSpinner(true);
    const tr = input.closest('tr');
    const product_id = tr.dataset.productId;
    const quantity = parseInt(input.value);

    fetch('/MiniShop/cart/update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + product_id + '&quantity=' + quantity
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Sepet etiketini güncelle
            if (data.cart_count !== undefined) {
                updateCartBadgeUI(data.cart_count);
            }
            // Anında geri bildirim için sayfayı yeniden yükle
            location.reload();
        }
        else showFlashMessage(data.message, 'error');
    })
    .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
    .finally(() => setSpinner(false));
}

// Sepetteki ürünü silme fonksiyonu
function removeCartItem(input) {
    setSpinner(true);
    const tr = input.closest('tr');
    const product_id = tr.dataset.productId;

    fetch('/MiniShop/cart/remove_from_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + product_id
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            // Sepet etiketini güncelle
            if (data.cart_count !== undefined) {
                updateCartBadgeUI(data.cart_count);
            }
            // Tablodan satırı kaldır
            tr.remove();
            showFlashMessage(data.message, 'success');
        }
        else showFlashMessage(data.message, 'error');
    })
    .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
    .finally(() => setSpinner(false));
}

// Sepet etiketini anında güncelleme fonksiyonu
function updateCartBadge(quantityChange) {
    const cartBadge = document.querySelector('a[href="/MiniShop/cart/cart.php"] .absolute');
    if (cartBadge) {
        // Etiketten şu anki sayıyı al
        let currentCount = parseInt(cartBadge.textContent) || 0;
        let newCount = currentCount + parseInt(quantityChange);
        
        // Miktar sıfırın altına inmesin
        newCount = Math.max(0, newCount);
        
        updateCartBadgeUI(newCount);
    } else if (quantityChange > 0) {
        // Etiket yoksa ve ürün ekleniyorsa, etiket oluştur
        const cartLink = document.querySelector('a[href="/MiniShop/cart/cart.php"]');
        if (cartLink) {
            const badge = document.createElement('span');
            badge.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
            badge.textContent = quantityChange;
            cartLink.appendChild(badge);
        }
    }
}

// Sepette belirli sayıda ürün varken etiketi güncelleme
function updateCartBadgeUI(count) {
    const cartLink = document.querySelector('a[href="/MiniShop/cart/cart.php"]');
    if (cartLink) {
        let badge = cartLink.querySelector('.absolute');
        if (!badge && count > 0) {
            // Etiket yoksa oluştur
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

// Ödeme formu spinner yönetimi
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    const loadingSpinner = document.getElementById('loading-spinner');

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            setTimeout(function() {
                if (loadingSpinner) {
                    loadingSpinner.classList.add('hidden');
                }
            }, 100);
        });
    }
});

// DOM yüklendiğinde sepeti başlat
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCart);
} else {
    initializeCart();
}
