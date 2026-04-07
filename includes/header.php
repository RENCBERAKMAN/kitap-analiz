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

  <!-- ══ FONT PRECONNECT ══ -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!--
    Crystal Azure tema fontları — style.css'deki değişkenlerle birebir eşleşmeli:
      --font-display:  'DM Serif Display'
      --font-body:     'DM Sans'
      --font-mono:     'JetBrains Mono'
      --font-cinzel:   'Cinzel'         (sohbet sayfası)
      --font-garamond: 'EB Garamond'    (sohbet sayfası)
      --font-courier:  'Courier Prime'  (sohbet sayfası)

    Tek <link> ile tüm aileler → tek HTTP isteği → daha hızlı
  -->
  <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300;1,9..40,400&family=JetBrains+Mono:wght@300;400;500&family=Cinzel:wght@400;500;600&family=EB+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Courier+Prime:ital@0;1&display=swap"
  >

  <!-- ══ ANA STYLESHEET ══ -->
  <link rel="stylesheet" href="/kitap-analiz/assets/css/style.css">
</head>
<body>

<!-- Sayfa yükleyici — style.css'deki .page-loader kuralıyla çalışır -->
<div class="page-loader" id="pageLoader" role="status" aria-label="Sayfa yükleniyor">
  <div class="loader-glyph" aria-hidden="true">✦</div>
  <div class="loader-text">Kütüphane açılıyor</div>
</div>

<header class="site-header" role="banner">
  <a href="/kitap-analiz/" class="logo" aria-label="Kitap Analizi — Ana Sayfa">
    <div class="logo-mark" aria-hidden="true">📚</div>
    <div class="logo-text">
      <span class="logo-name">Kitap Analizi</span>
      <span class="logo-sub">Seçtiğini kitapı derinlemesine analiz ediyoruz</span>
    </div>
  </a>

  <nav aria-label="Ana navigasyon">
    <ul class="nav-links">
      <li><a href="/kitap-analiz/">Keşfet</a></li>
      <li><a href="/kitap-analiz/sohbet.php" class="nav-sohbet">👤 Sohbet</a></li>
      <li><a href="/kitap-analiz/#kategori-kesif">Kategoriler</a></li>
      <li><a href="/kitap-analiz/sorgu.php" class="nav-sorgu">⚡ Düşünceni Sorgula</a></li>
      <li><a href="/kitap-analiz/#nasil-calisir">Nasıl Çalışır</a></li>
    </ul>
  </nav>
</header>