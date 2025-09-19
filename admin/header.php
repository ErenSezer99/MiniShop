<?php
include_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/../config/database.php';

// Yetki kontrolü: login değilse veya role admin değilse login sayfasına yönlendir
if (!is_logged_in() || (($_SESSION['role'] ?? '') !== 'admin')) {
    set_flash("Bu sayfaya erişim yetkiniz yok.", "error");
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Admin Panel - MiniShop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header class="admin-nav">
        <a href="../index.php">Siteye Dön</a>
        <a href="index.php">Dashboard</a>
        <a href="categories.php">Kategoriler</a>
        <a href="products.php">Ürünler</a>
        <span style="float:right">Hoşgeldin, <?php echo sanitize($_SESSION['username']); ?> | <a href="../logout.php">Çıkış</a></span>
    </header>

    <main class="admin-main">
        <?php
        // Flash mesaj göster
        $flash = get_flash();
        if ($flash) {
            $type = sanitize($flash['type']);
            echo '<div class="flash ' . $type . '">' . sanitize($flash['message']) . '</div>';
        }
        ?>