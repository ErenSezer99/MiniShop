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
        .then(data => showFlashMessage(data.message, data.status))
        .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
        .finally(() => setSpinner(false)); 
    });
});

// Sepet miktarı güncelleme
document.querySelectorAll('.cart-qty').forEach(input => {
    input.addEventListener('change', function() {
        setSpinner(true);
        const tr = this.closest('tr');
        const product_id = tr.dataset.productId;
        const quantity = parseInt(this.value);

        fetch('/MiniShop/cart/update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + product_id + '&quantity=' + quantity
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') location.reload();
            else showFlashMessage(data.message, 'error');
        })
        .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
        .finally(() => setSpinner(false)); 
    });
});

// Sepetten silme
document.querySelectorAll('.remove-cart').forEach(btn => {
    btn.addEventListener('click', function() {
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
            if (data.status === 'success') location.reload();
            else showFlashMessage(data.message, 'error');
        })
        .catch(() => showFlashMessage('Bir hata oluştu.', 'error'))
        .finally(() => setSpinner(false)); 
    });
});
