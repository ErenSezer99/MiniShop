document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-fav').forEach(btn => {
        btn.addEventListener('click', function() {
            const product_id = this.dataset.productId;
            const action = this.dataset.action; // 'add' veya 'remove'

            if (!product_id || !action) return;

            setSpinner(true);

            fetch('/MiniShop/wishlist/' + action + '.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + product_id
            })
            .then(res => res.json())
            .then(data => {
                showFlashMessage(data.message, data.status);

                if (data.status === 'success') {
                    if (action === 'add') {
                        btn.dataset.action = 'remove';
                        btn.textContent = '♥';
                        btn.title = 'Favorilerden çıkar';
                    } else {
                        btn.dataset.action = 'add';
                        btn.textContent = '♡';
                        btn.title = 'Favorilere ekle';

                        // Sadece wishlist/index.php sayfasındaysak DOM'dan sil
                        if (window.location.pathname.includes('/wishlist/index.php')) {
                            const card = btn.closest('.product-card');
                            if (card) card.remove();

                            const row = btn.closest('tr');
                            if (row) row.remove();
                        }
                    }
                }
            })
            .finally(() => setSpinner(false));
        });
    });
});
