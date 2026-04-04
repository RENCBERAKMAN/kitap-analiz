<?php
$title = isset($pageTitle)
    ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' — Kitap Analizi'
    : 'Kitap Analizi';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Google Books ve Gemini AI ile kitap analizi — okumaya değer mi, öğren.">
  <title><?= $title ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,600&family=Lora:ital,wght@0,400;0,500;1,400&family=JetBrains+Mono:wght@400;500&display=swap">
  <link rel="stylesheet" href="/kitap-analiz/assets/css/style.css">
</head>
<body>

<div class="page-loader" id="pageLoader" role="status" aria-label="Sayfa yükleniyor">
  <div class="loader-glyph" aria-hidden="true">❧</div>
  <div class="loader-text">Kütüphane açılıyor</div>
</div>

<header class="site-header" role="banner">
  <a href="/kitap-analiz/" class="logo" aria-label="Kitap Analizi — Ana Sayfa">
    <div class="logo-mark" aria-hidden="true">📚</div>
    <div class="logo-text">
      <span class="logo-name">Kitap Analizi</span>
      <span class="logo-sub">Google Books · Gemini AI</span>
    </div>
  </a>
  <nav aria-label="Ana navigasyon">
    <ul class="nav-links">
      <li><a href="/kitap-analiz/">Keşfet</a></li>
      <li><a href="/kitap-analiz/#kategori-kesif">Kategoriler</a></li>
      <li><a href="/kitap-analiz/sorgu.php" class="nav-sorgu">⚡ Düşünceni Sorgula</a></li>
      <li><a href="/kitap-analiz/#nasil-calisir">Nasıl Çalışır</a></li>
    </ul>
  </nav>
</header>