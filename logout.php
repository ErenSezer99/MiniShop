<?php
// Çıkış sayfası

session_start();

// Tüm session verilerini temizle
$_SESSION = [];

/*
Eğer kullanılıyorsa session çerezi sil, 
Browser eski session id'ye sahip olmasın, güvenlik riski olmasın
*/

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();
header("Location: index.php");
exit();
