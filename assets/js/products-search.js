document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('product-search');
    const productsGrid = document.getElementById('products-grid');

    let timeout = null;

    function doSearch() {
        const keyword = searchInput.value.trim();

        if (!productsGrid) return;

        setSpinner(true);

        const formData = new FormData();
        formData.append('keyword', keyword);

        fetch('/MiniShop/products/search_products.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                productsGrid.innerHTML = data.html;
                
                // Re-attach event listeners for add to cart and wishlist buttons
                attachEventListeners();
            }
        })
        .catch(() => {
            console.error('Arama sırasında bir hata oluştu.');
        })
        .finally(() => setSpinner(false));
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(doSearch, 300);
    });
});