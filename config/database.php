<?php
$config = include 'config.php';

$conn_string = "host={$config['host']} port={$config['port']} dbname={$config['dbname']} user={$config['username']} password={$config['password']}";
$dbconn = pg_connect($conn_string);

if (!$dbconn) {
  die("Veritabanı bağlantı hatası: " . pg_last_error());
}
