<?php
// Veritabanı bağlantısı ayarları
$host = "localhost";
$port = "5432";
$dbname = "minishop";
$username = "postgres";
$password = "Eren123";

// pg_connect ile bağlantı
$conn_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
$dbconn = pg_connect($conn_string);

if (!$dbconn) {
  die("Veritabanı bağlantı hatası: " . pg_last_error());
}
