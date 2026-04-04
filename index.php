<?php
/*
 * index.php — Ana Sayfa
 *
 * Bu dosyada PHP sadece header/footer'ı dahil eder.
 * Tüm dinamik içerik (arama, kategoriler, trend)
 * JavaScript (assets/js/app.js) tarafından yönetilir.
 *
 * DOM'daki ID'ler JavaScript ile eşleşmeli:
 *   #searchResults  → doSearch() buraya yazar
 *   #trendBooks     → loadTrend() buraya yazar
 *   #categoryGrid   → initCategories() buraya yazar
 *   #categoryResults→ loadCategoryBooks() buraya yazar
 */

$pageTitle = 'Keşfet';
require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- ══ HERO — ARAMA BÖLÜMÜ ══ -->
  <section class="hero">

    <!-- Büyük dekoratif tırnak — yalnızca görsel -->
    <div class="hero-deco" aria-hidden="true">"</div>

    <!-- Üst küçük etiket -->
    <div class="hero-eyebrow">AI Destekli Kitap Analizi</div>

    <!-- Ana başlık -->
    <h1 class="hero-title">
      Bir kitap <em>değer mi</em>,<br>
      okumadan nasıl anlarsın?
    </h1>

    <!-- Açıklama metni -->
    <p class="hero-subtitle">
      Kitap adını yaz. Google Books'tan binlerce eser arasından buluyoruz,
      Gemini AI ile senin için derinlemesine analiz ediyoruz.
    </p>

    <!-- Arama kutusu -->
    <div class="search-container">
      <div class="search-wrapper">
        <span class="search-icon" aria-hidden="true">🔍</span>
        <!--
          maxlength → istemci tarafında da sınırlama (sunucu da kontrol eder)
          autocomplete="off" → tarayıcının önerileri karışmasın
          spellcheck="false" → kırmızı çizgi göstermesin (kitap isimleri yanlış gözükür)
        -->
        <input
          type="text"
          id="searchInput"
          placeholder="Kitap adı veya yazar..."
          autocomplete="off"
          spellcheck="false"
          maxlength="100"
          aria-label="Kitap ara"
        >
        <button class="search-btn" type="button" aria-label="Ara">Ara</button>
      </div>
    </div>

    <!--
      Hızlı arama chip'leri
      data-query → JavaScript'in okuyacağı değer (initHints() kullanır)
    -->
    <div class="search-hints" role="list">
      <span class="hint-chip" data-query="Suç ve Ceza" role="listitem">Suç ve Ceza</span>
      <span class="hint-chip" data-query="Sapiens" role="listitem">Sapiens</span>
      <span class="hint-chip" data-query="Dune" role="listitem">Dune</span>
      <span class="hint-chip" data-query="Atomik Alışkanlıklar" role="listitem">Atomik Alışkanlıklar</span>
      <span class="hint-chip" data-query="1984" role="listitem">1984</span>
      <span class="hint-chip" data-query="Küçük Prens" role="listitem">Küçük Prens</span>
    </div>

    <!--
      Arama sonuçları konteyneri
      JavaScript'teki doSearch() fonksiyonu bu div'e HTML yazar
    -->
    <div id="searchResults" role="region" aria-live="polite" aria-label="Arama sonuçları"></div>

  </section>

  <!-- ══ TREND KİTAPLAR ══
       loadTrend() → api/trending.php → veritabanından en çok görüntülenenler
       Hiç kitap yoksa (boş veritabanı) bu bölüm gizli kalır.
       Kitaplar görüntülendikçe veritabanı dolar ve trend çıkmaya başlar.
  -->
  <section class="trend-section">
    <!--
      id="trend-baslik" → JavaScript bu başlığı başlangıçta gizli tutar.
      loadTrend() veri bulunca style.display='block' ile gösterir.
    -->
    <h2
      class="section-title"
      id="trend-baslik"
      style="display:none;"
    >
      Bu Hafta Çok Bakılanlar
    </h2>
    <!--
      id="trendBooks" → loadTrend() bu div'e kitap kartlarını yazar
    -->
    <div class="books-grid" id="trendBooks"></div>
  </section>

  <!-- ══ KATEGORİ KEŞFİ ══ -->
  <section class="category-section" id="kategori-kesif">

    <span class="section-eyebrow">Kategori ile Keşfet</span>
    <h2 class="section-heading">Hangi dünyayı<br>keşfetmek istersin?</h2>
    <p class="section-sub">
      Bir alan seç. O alanda başlangıç için en önemli 10 eseri sıralıyoruz.
      İlgini çeken esere tıkla, Gemini AI açıklasın.
    </p>

    <!--
      id="categoryGrid" → initCategories() bu div'e 10 kategori kartı yazar
    -->
    <div class="category-grid" id="categoryGrid" role="list"></div>

    <!--
      id="categoryResults" → loadCategoryBooks() seçili kategorinin kitaplarını buraya yazar
    -->
    <div id="categoryResults" role="region" aria-live="polite"></div>

  </section>

  <!-- ══ NASIL ÇALIŞIR ══ -->
  <section class="how-section" id="nasil-calisir">

    <span class="section-eyebrow">Süreç</span>
    <h2 class="section-heading" style="text-align:center;">Nasıl Çalışır?</h2>

    <div class="steps-row">

      <div class="step-item" style="text-align:center;">
        <div class="step-num">01</div>
        <div class="step-title">Ara</div>
        <p class="step-desc">
          Kitap adını yaz. Google Books API üzerinden milyonlarca eser arasında
          anında tarama yapar, doğru kitabı buluruz.
        </p>
      </div>

      <div class="step-connector" aria-hidden="true">···</div>

      <div class="step-item" style="text-align:center;">
        <div class="step-num">02</div>
        <div class="step-title">Analiz Et</div>
        <p class="step-desc">
          Kitabı seç. Gemini AI kitabın özetini, sana ne kazandıracağını
          ve seni ne zorlayabileceğini derinlemesine analiz eder.
        </p>
      </div>

      <div class="step-connector" aria-hidden="true">···</div>

      <div class="step-item" style="text-align:center;">
        <div class="step-num">03</div>
        <div class="step-title">Karar Ver</div>
        <p class="step-desc">
          Oku mu, okuma mı? Net bir karar ve kişiselleştirilmiş gerekçe.
          Doğru kitabı, doğru zamanda seç.
        </p>
      </div>

    </div>

  </section>

</main>

<?php
// Footer'ı dahil et — JavaScript dosyası ve kapanış tag'leri buradan gelir
require_once __DIR__ . '/includes/footer.php';
?>