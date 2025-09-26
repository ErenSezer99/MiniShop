// Sayfada backend flash mesajı varsa (PHP ile basılmış), 3 sn sonra kaybolsun
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 3000);
    }
});

// Dinamik JS ile flash mesaj göstermek için yardımcı fonksiyon
function showFlashMessage(message, type = 'success') {
    const msgDiv = document.createElement('div');
    msgDiv.className = 'flash ' + type;
    msgDiv.textContent = message;
    msgDiv.style.opacity = '0';
    msgDiv.style.marginBottom = '0';

    const h2 = document.querySelector('h2');
    h2.insertAdjacentElement('afterend', msgDiv);

    requestAnimationFrame(() => {
        msgDiv.style.opacity = '1';
        msgDiv.style.marginBottom = '15px';
    });

    setTimeout(() => {
        msgDiv.style.opacity = '0';
        msgDiv.style.marginBottom = '0';
        setTimeout(() => msgDiv.remove(), 500);
    }, 3000);
}

// Spinner gösterme yardımcı fonksiyonu
function setSpinner(show = true) {
    const spinner = document.getElementById('loading-spinner');
    if (!spinner) return;
    spinner.style.display = show ? 'block' : 'none';
}

// Event delegation ile tüm sayfalarda spinner
document.addEventListener('click', (e) => {
    const target = e.target;

    // 1. Navbar linkleri
    if (target.matches('.nav-link')) {
        setSpinner(true);
    }

    // 2. Sepete git / checkout butonları
    if (target.matches('.btn-cart, .btn-checkout')) {
        setSpinner(true);
    }

    // 3. Diğer tüm normal linkler 
    if (target.tagName === 'A' && target.getAttribute('href') && !target.getAttribute('href').startsWith('#')) {
        setSpinner(true);
    }

    // 4. Form submit butonları
    if ((target.tagName === 'BUTTON' && target.type === 'submit') ||
        (target.tagName === 'INPUT' && target.type === 'submit')) {
        setSpinner(true);
    }
});

// Sayfa yenileme / manuel reload sırasında spinner kontrolü
window.addEventListener('beforeunload', () => {
    setSpinner(true);
});

window.addEventListener('load', () => {
    setSpinner(false);
});
