<?php
// Genel kullanılacak fonksiyonlar buraya yazılacak

// Örnek: güvenli veri temizleme fonksiyonu
function sanitize($data)
{
  // Fazla boşlukları temizle
  $data = trim($data);
  // Slash işaretlerini temizle
  $data = stripslashes($data);
  // HTML özel karakterlerini güvenli hale getir
  $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  return $data;
}
