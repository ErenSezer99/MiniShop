<?php
// Veritabanı bağlantısı ayarları
$host = "localhost";
$dbname = "minishop";
$username = "root";
$password = "";

try {
  // PDO ile veritabanına bağlanma
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  // PDO hata modunu exception olarak ayarlama
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
