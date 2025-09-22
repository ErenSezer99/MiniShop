<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';
require_admin();

if (!isset($_GET['id'])) {
    echo "Geçersiz kullanıcı ID'si.";
    exit;
}

$user_id = $_GET['id'];

// Kullanıcıyı çek
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
$stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmt_delete->execute([':id' => $user_id]);

if ($is_current_user) {
    // Oturumu temizle ve login sayfasına yönlendir
    session_unset();
    session_destroy();
    echo "Hesabınız başarıyla silindi. Yönlendiriliyorsunuz...";
    header("Refresh:1; url=../login.php");
    exit;
} else {
    echo "Kullanıcı başarıyla silindi. Yönlendiriliyorsunuz...";
    header("Refresh:1; url=users.php");
    exit;
}
