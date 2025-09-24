<?php
include_once __DIR__ . '/../../includes/functions.php';
include_once __DIR__ . '/../../config/database.php';
require_admin();

if (!isset($_GET['id'])) {
    echo "Geçersiz kullanıcı ID'si.";
    exit;
}

$user_id = $_GET['id'];

// Kullanıcıyı çek
pg_prepare($dbconn, "select_user", "SELECT * FROM users WHERE id = $1");
$res_user = pg_execute($dbconn, "select_user", [$user_id]);
$user = pg_fetch_assoc($res_user);

if (!$user) {
    echo "Kullanıcı bulunamadı.";
    exit;
}

// Sadece 'user' rolündekiler silinebilir
if ($user['role'] === 'admin') {
    echo "Admin kullanıcı silinemez.";
    exit;
}

// Kullanıcı kendi hesabını siliyorsa logout yap
$is_current_user = ($user['id'] === ($_SESSION['user']['id'] ?? ''));

// Kullanıcıyı sil
pg_prepare($dbconn, "delete_user", "DELETE FROM users WHERE id = $1");
pg_execute($dbconn, "delete_user", [$user_id]);

if ($is_current_user) {
    // Oturumu temizle ve login sayfasına yönlendir
    session_unset();
    session_destroy();
    echo "Hesabınız başarıyla silindi. Yönlendiriliyorsunuz...";
    header("Refresh:1; url=../../auth/login.php");
    exit;
} else {
    echo "Kullanıcı başarıyla silindi. Yönlendiriliyorsunuz...";
    header("Refresh:1; url=users.php");
    exit;
}
