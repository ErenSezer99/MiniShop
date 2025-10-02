document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('category-search');
    const tbody = document.getElementById('categories-tbody');

    let timeout = null;

    function doSearch() {
        const keyword = searchInput.value.trim();

        if (!tbody) return;

        setSpinner(true);

        const formData = new FormData();
        formData.append('keyword', keyword);

        fetch('/MiniShop/admin/categories/search_categories.php', {
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