document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('product-search');
    const categorySelect = document.getElementById('product-category-filter');
    const tbody = document.getElementById('products-tbody');

    let timeout = null;

    function doSearch() {
        const keyword = searchInput.value.trim();
        const category = categorySelect.value;

        if (!tbody) return;

        setSpinner(true);

        const formData = new FormData();
        formData.append('keyword', keyword);
        formData.append('category', category);

        fetch('/MiniShop/admin/products/search_products.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                tbody.innerHTML = data.html;
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

    categorySelect.addEventListener('change', doSearch);
});
