// Sayfada backend flash mesajı varsa (PHP ile basılmış), 3 sn sonra kaybolsun
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 3000);
    }
    
    // Update cart badge on page load for logged in users
    if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
        updateCartBadgeFromServer();
    }
    
    // Hide spinner on page load
    setSpinner(false);
    
    // If there are validation errors on registration page, ensure spinner is hidden
    if (window.location.pathname.includes('register.php')) {
        const errorMessages = document.querySelectorAll('.bg-red-100');
        if (errorMessages.length > 0) {
            // There are error messages, ensure spinner is hidden
            setSpinner(false);
        }
    }
});

// Dinamik JS ile flash mesaj göstermek için yardımcı fonksiyon
function showFlashMessage(message, type = 'success') {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flash ' + type;
    
    // Tailwind classes based on message type
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

    // 4. Form submit butonları - but exclude registration and checkout forms to prevent infinite spinner on validation errors
    if ((target.tagName === 'BUTTON' && target.type === 'submit') ||
        (target.tagName === 'INPUT' && target.type === 'submit')) {
        
        // Check if this is the registration form submit button
        const form = target.closest('form');
        if (form && (form.id === 'register-form' || form.id === 'checkout-form')) {
            // For registration and checkout forms, we'll handle spinner in the form's own script
            // to avoid infinite spinner on validation errors
            return;
        }
        
        // For all other forms, show spinner
        setSpinner(true);
    }
    
    // Wishlist button functionality - ONLY handle clicks that don't have data-action attribute
    // This prevents duplicate handling with wishlist.js which handles buttons with data-action
    if ((target.matches('.btn-fav') || target.closest('.btn-fav')) && 
        !target.hasAttribute('data-action') && 
        !target.closest('.btn-fav').hasAttribute('data-action')) {
        
        const button = target.matches('.btn-fav') ? target : target.closest('.btn-fav');
        const productId = button.getAttribute('data-product-id');
        const action = button.classList.contains('active') ? 'remove' : 'add';
        
        // Toggle active class immediately for better UX
        button.classList.toggle('active');
        button.textContent = button.classList.contains('active') ? '♥' : '♡';
        
        // Send request to server
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
                // Revert changes if request failed
                button.classList.toggle('active');
                button.textContent = button.classList.contains('active') ? '♥' : '♡';
                showFlashMessage(data.message || 'Bir hata oluştu', 'error');
            }
        })
        .catch(error => {
            // Revert changes if request failed
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

// Function to update cart badge from server
function updateCartBadgeFromServer() {
    // Only update for logged in users
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

// Update cart badge every 30 seconds for logged in users
if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
    setInterval(updateCartBadgeFromServer, 30000);
}

// Function to update cart badge UI with specific count
function updateCartBadgeUI(count) {
    const cartLink = document.querySelector('a[href="/MiniShop/cart/cart.php"]');
    if (cartLink) {
        let badge = cartLink.querySelector('.absolute');
        if (!badge && count > 0) {
            // Create badge if it doesn't exist
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