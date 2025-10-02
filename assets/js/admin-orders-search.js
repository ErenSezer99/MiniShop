document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('order-search');
    const tbody = document.getElementById('orders-tbody');

    let timeout = null;

    function doSearch() {
        const keyword = searchInput.value.trim();

        if (!tbody) return;

        setSpinner(true);

        const formData = new FormData();
        formData.append('keyword', keyword);

        fetch('/MiniShop/admin/orders/search_orders.php', {
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
});