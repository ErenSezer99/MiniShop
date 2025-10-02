// Global variable to track if cart functionality is initialized
if (typeof window.cartInitialized === 'undefined') {
    window.cartInitialized = false;
}

// Initialize cart functionality
function initializeCart() {
    // Prevent multiple initializations
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
                // Update cart badge
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
                    // Update cart badge when item is removed
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

    // Increase quantity button
    document.querySelectorAll('.increase-qty').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Prevent multiple rapid clicks with a simple flag
            if (this.dataset.processing === 'true') return;
            this.dataset.processing = 'true';
            
            const input = this.parentElement.querySelector('.cart-qty');
            let value = parseInt(input.value) || 1;
            input.value = value + 1;
            // Update cart quantity directly
            updateCartQuantity(input, this);
            
            // Reset processing flag after a short delay
            setTimeout(() => {
                this.dataset.processing = 'false';
            }, 100);
        });
    });

    // Decrease quantity button
    document.querySelectorAll('.decrease-qty').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Prevent multiple rapid clicks with a simple flag
            if (this.dataset.processing === 'true') return;
            this.dataset.processing = 'true';
            
            const input = this.parentElement.querySelector('.cart-qty');
            let value = parseInt(input.value) || 1;
            if (value > 1) {
                input.value = value - 1;
                // Update cart quantity directly
                updateCartQuantity(input, this);
            } else if (value === 1) {
                // When quantity is 1 and user clicks decrease, remove the item
                input.value = 0;
                removeCartItem(input);
            }
            
            // Reset processing flag after a short delay
            setTimeout(() => {
                this.dataset.processing = 'false';
            }, 100);
        });
    });
}

// Function to update cart quantity
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
            // Update cart badge
            if (data.cart_count !== undefined) {
                updateCartBadgeUI(data.cart_count);
            }
            // Reload immediately for instant feedback
            location.reload();
        }
        else showFlashMessage(data.message, 'error');
    })
    .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
    .finally(() => setSpinner(false));
}

// Function to remove cart item
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
            // Update cart badge
            if (data.cart_count !== undefined) {
                updateCartBadgeUI(data.cart_count);
            }
            // Remove the row from the table
            tr.remove();
            showFlashMessage(data.message, 'success');
        }
        else showFlashMessage(data.message, 'error');
    })
    .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
    .finally(() => setSpinner(false));
}

// Function to update cart badge in real-time
function updateCartBadge(quantityChange) {
    const cartBadge = document.querySelector('a[href="/MiniShop/cart/cart.php"] .absolute');
    if (cartBadge) {
        // Get current count from badge
        let currentCount = parseInt(cartBadge.textContent) || 0;
        let newCount = currentCount + parseInt(quantityChange);
        
        // Ensure count doesn't go below 0
        newCount = Math.max(0, newCount);
        
        updateCartBadgeUI(newCount);
    } else if (quantityChange > 0) {
        // If badge doesn't exist but we're adding items, create it
        const cartLink = document.querySelector('a[href="/MiniShop/cart/cart.php"]');
        if (cartLink) {
            const badge = document.createElement('span');
            badge.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
            badge.textContent = quantityChange;
            cartLink.appendChild(badge);
        }
    }
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

// Initialize cart functionality when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCart);
} else {
    initializeCart();
}