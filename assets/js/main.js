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

    // fade-in
    requestAnimationFrame(() => {
        msgDiv.style.opacity = '1';
        msgDiv.style.marginBottom = '15px';
    });

    // fade-out ve kaldırma
    setTimeout(() => {
        msgDiv.style.opacity = '0';
        msgDiv.style.marginBottom = '0';
        setTimeout(() => msgDiv.remove(), 500);
    }, 3000);
}
