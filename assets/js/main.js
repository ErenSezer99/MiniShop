// Flash mesaj varsa 3 saniye sonra kaybolsun
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.flash');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500); 
        }, 3000); 
    }
});
