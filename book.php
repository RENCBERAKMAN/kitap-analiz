<?php
/*
 * book.php — Kitap Detay & Analiz Sayfası
 *
 * ── API KEY AKIŞI (Öğrenme Notu) ──────────────────────────────
 * config/config.php dosyasında:
 *   define('GOOGLE_BOOKS_API_KEY', 'xxx');
 *   define('GEMINI_API_KEY', 'yyy');
 *
 * Bu dosya → config.php'yi require eder
 * GoogleBooksApi sınıfı da kendi başında config.php'yi require eder
 * GeminiApi sınıfı da kendi başında config.php'yi require eder
 * Database sınıfı da kendi başında config.php'yi require eder
 *
 * Yani key'ler tek bir dosyada tanımlanır (config.php),
 * diğer tüm sınıflar oradan okur. Değiştirmek istersen
 * sadece config.php'yi düzenliyorsun — başka hiçbir dosyaya dokunmak gerekmez.
 * ────────────────────────────────────────────────────────────────
 *
 * ── GÜVENLİK BAŞLIKLARI NEDEN ÖNEMLİ? ──────────────────────────
 * X-Content-Type-Options: nosniff
 *   → Tarayıcı, dosya uzantısı ne olursa olsun içeriği
 *     Content-Type header'ına göre yorumlar.
 *     "nosniff" olmadan saldırgan bir .jpg dosyasını
 *     JavaScript olarak çalıştırabilir (MIME sniffing attack).
 *
 * X-Frame-Options: DENY
 *   → Sayfanın başka bir sitede iframe içine alınmasını engeller.
 *     "Clickjacking" saldırısına karşı koruma.
 *     Saldırgan kendi sitesine gizli iframe koyar, kullanıcı
 *     bizim sitemize tıkladığını sanarak ona tıklar.
 *
 * X-XSS-Protection: 1; mode=block
 *   → Eski tarayıcılar için XSS filtresi. Modern tarayıcılarda
 *     Content Security Policy daha güçlü ama yine de ekleriz.
 * ────────────────────────────────────────────────────────────────
 */

// Tarayıcıya güvenlik direktifleri gönder
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Gerekli dosyaları dahil et
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Api/GoogleBooksApi.php';
require_once __DIR__ . '/src/Database/Database.php';

/* ── KİTAP ID'Sİ DOĞRULAMASI ───────────────────────────────────
 * URL'den gelen her veri güvensiz kabul edilmelidir.
 *
 * Saldırı senaryosu (Path Traversal):
 *   /book.php?id=../../etc/passwd
 *   Eğer ID doğrulanmazsa ve dosya okuma yapılıyorsa
 *   sunucudaki kritik dosyalar okunabilir.
 *
 * Çözüm: Regex ile sadece harf, rakam, tire ve alt çizgiye izin ver.
 *   preg_match('/^[a-zA-Z0-9_\-]+$/', $id)
 *   Google Books ID'leri zaten bu formatta gelir.
 * ────────────────────────────────────────────────────────────── */
$bookId = isset($_GET['id']) ? trim(strip_tags($_GET['id'])) : '';

// Geçerli karakter kontrolü — regex ile doğrula
if (empty($bookId) || !preg_match('/^[a-zA-Z0-9_\-]+$/', $bookId)) {
    header('Location: /kitap-analiz/');
    exit;
}

/* ── GOOGLE BOOKS API'DEN KİTAP VERİSİNİ ÇEK ──────────────────
 * GoogleBooksApi sınıfı → src/Api/GoogleBooksApi.php
 * Bu sınıf config.php'deki GOOGLE_BOOKS_API_KEY'i kullanır.
 * cURL ile Google'ın sunucusuna HTTP isteği atar,
 * gelen JSON'ı PHP dizisine çevirir.
 * ────────────────────────────────────────────────────────────── */
$api  = new GoogleBooksApi();
$book = $api->getBook($bookId); // ID ile tek kitap getir

// Kitap bulunamazsa ana sayfaya yönlendir
if (!$book) {
    header('Location: /kitap-analiz/');
    exit;
}

/* ── VERİ HAZIRLIĞI ─────────────────────────────────────────── */

// Sayfa başlığı (sekme adı için)
$pageTitle = $book['title'];

// Yazar dizisini stringe çevir
// implode() = diziyi birleştirir: ["Yazar1","Yazar2"] → "Yazar1, Yazar2"
$authors = is_array($book['authors'])
    ? implode(', ', $book['authors'])
    : ($book['authors'] ?? 'Bilinmiyor');

// Kategori
$category = !empty($book['categories']) ? $book['categories'][0] : 'Roman';

// Okuma süresini formatla
$readingMins = $book['readingTime'] ?? 0;
$readingText = '';
if ($readingMins > 0) {
    // 60 dakika ve üzeriyse saat cinsine çevir
    $readingText = $readingMins >= 60
        ? round($readingMins / 60, 1) . ' saat'
        : $readingMins . ' dakika';
}

/* ── AÇIKLAMA TEMİZLEME (HTML TAG SORUNU) ──────────────────────
 * Google Books API, açıklamaları HTML formatında gönderir:
 *   "<p>Harry Potter <br/> büyük bir macera...</p>"
 *
 * Eğer bunu doğrudan htmlspecialchars() ile yazar ve ekrana basarsan:
 *   htmlspecialchars("<p>metin</p>") → "&lt;p&gt;metin&lt;/p&gt;"
 *   Tarayıcı bunu TEXT olarak gösterir: <p>metin</p> (literal)
 *   İşte bu yüzden ekranda <p> etiketi görünüyordu!
 *
 * Doğru sıra:
 *   1) strip_tags()         → HTML etiketlerini kaldır: "<p>metin</p>" → "metin"
 *   2) html_entity_decode() → &amp; gibi entity'leri çöz
 *   3) preg_replace()       → Fazla boşlukları temizle
 *   4) mb_substr()          → Uzunluğu sınırla (UTF-8 uyumlu)
 *   5) htmlspecialchars()   → Son çıktıda güvenli yaz
 * ────────────────────────────────────────────────────────────── */
$description = $book['description'] ?? '';
$cleanDesc   = '';

if (!empty($description) && $description !== 'Açıklama yok') {
    // Adım 1: HTML etiketlerini kaldır
    $cleanDesc = strip_tags($description);
    // Adım 2: &amp; &quot; gibi HTML varlıklarını çöz
    $cleanDesc = html_entity_decode($cleanDesc, ENT_QUOTES, 'UTF-8');
    // Adım 3: Ardışık boşlukları tek boşluğa indir (satır atlamaları vs.)
    $cleanDesc = preg_replace('/\s+/', ' ', trim($cleanDesc));
}

// 420 karakterden uzunsa kes ve üç nokta ekle
$shortDesc = '';
if (!empty($cleanDesc)) {
    $shortDesc = mb_strlen($cleanDesc) > 420
        ? mb_substr($cleanDesc, 0, 420, 'UTF-8') . '...'
        : $cleanDesc;
}

/* ── VERİTABANINA KAYDET ────────────────────────────────────────
 * Database sınıfı → src/Database/Database.php
 * PDO + Prepared Statement kullanıyor (SQL Injection'a karşı güvenli)
 *
 * SQL Injection Nedir?
 *   Saldırgan ID olarak şunu gönderir: '; DROP TABLE arama_gecmisi; --
 *   Normal sorgu: SELECT * FROM tablo WHERE id = 'xxx'
 *   Saldırı sonrası: SELECT * FROM tablo WHERE id = ''; DROP TABLE arama_gecmisi; --'
 *   Tablo silinir!
 *
 * Prepared Statement Nasıl Önler?
 *   $stmt = $pdo->prepare("SELECT * FROM tablo WHERE id = ?");
 *   $stmt->execute([$bookId]);
 *   Burada ? bir placeholder'dır. PHP, ID'yi SQL kodu olarak değil
 *   saf VERİ olarak işler. DROP TABLE gibi komutlar çalışmaz.
 * ────────────────────────────────────────────────────────────── */
$db = new Database();
if ($db->isConnected()) {
    // Bu fonksiyon içinde Prepared Statement kullanılıyor
    $db->kitapAramasiniKaydet(
        $bookId,
        $book['title'],
        $authors,
        $book['cover'] ?? ''
    );
}

// HTML çıktısını başlat — header.php'yi dahil et
require_once __DIR__ . '/includes/header.php';
?>

<div class="detail-page">

  <!-- Breadcrumb: Ana Sayfa / Kategori -->
  <div class="breadcrumb">
    <a href="/kitap-analiz/">← Ana Sayfa</a>
    &nbsp;/&nbsp;
    <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
  </div>

  <!-- ══ KİTAP HERO ══ -->
  <div class="detail-hero">

    <!-- Sol kolon: Kapak görseli (sticky — kaydırırken sabit kalır) -->
    <div class="detail-cover-wrap">
      <?php if (!empty($book['cover'])): ?>
        <!--
          htmlspecialchars → src içine javascript: veya " gibi
          zararlı içerik enjekte edilmesini engeller (XSS koruması)
          ENT_QUOTES → hem ' hem " kaçırılır
          'UTF-8' → Türkçe karakterler bozulmaz
        -->
        <img
          class="detail-cover"
          src="<?= htmlspecialchars($book['cover'], ENT_QUOTES, 'UTF-8') ?>"
          alt="<?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?> kapağı"
          loading="eager"
        >
      <?php else: ?>
        <div class="detail-cover-placeholder">📖</div>
      <?php endif; ?>
    </div>

    <!-- Sağ kolon: Kitap bilgileri -->
    <div class="detail-info">

      <!-- Kategori etiketi -->
      <div class="detail-category">
        <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
      </div>

      <!-- Kitap adı -->
      <h1 class="detail-title">
        <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
      </h1>

      <!-- Yazar -->
      <p class="detail-author">
        <?= htmlspecialchars($authors, ENT_QUOTES, 'UTF-8') ?>
      </p>

      <!-- Meta bilgiler: sayfa, yıl, puan, yayınevi -->
      <div class="detail-meta">

        <?php if (!empty($book['pageCount']) && $book['pageCount'] > 0): ?>
        <div class="meta-pill">
          <span class="meta-label">Sayfa</span>
          <!-- (int) → güvenli integer cast, XSS imkansız -->
          <span class="meta-value"><?= (int)$book['pageCount'] ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($book['publishedDate'])): ?>
        <div class="meta-pill">
          <span class="meta-label">Yıl</span>
          <!--
            substr → "2023-01-15" gibi uzun tarihin sadece ilk 4 karakterini al
            intval → string'i integer'a çevirir (sayısal olmayan karakter etkisiz)
          -->
          <span class="meta-value">
            <?= intval(substr($book['publishedDate'], 0, 4)) ?>
          </span>
        </div>
        <?php endif; ?>

        <?php if (!empty($book['rating']) && $book['rating'] > 0): ?>
        <div class="meta-pill">
          <span class="meta-label">Puan</span>
          <!-- number_format → ondalıklı sayıyı formatlar: 4.3 → "4.3" -->
          <span class="meta-value">⭐ <?= number_format((float)$book['rating'], 1) ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($book['publisher'])): ?>
        <div class="meta-pill">
          <span class="meta-label">Yayınevi</span>
          <span class="meta-value" style="font-size:.82rem;">
            <?= htmlspecialchars($book['publisher'], ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>
        <?php endif; ?>

      </div>

      <!-- Okuma süresi rozeti — sadece hesaplanabilirse göster -->
      <?php if ($readingText): ?>
      <div class="reading-badge">
        ⏱ Tahmini okuma süresi: <strong><?= htmlspecialchars($readingText) ?></strong>
      </div>
      <?php endif; ?>

      <!--
        Temizlenmiş açıklama — strip_tags ile <p> tagları kaldırıldı,
        htmlspecialchars ile kalan özel karakterler güvenli hale getirildi
      -->
      <?php if ($shortDesc): ?>
      <p class="detail-description">
        <?= htmlspecialchars($shortDesc, ENT_QUOTES, 'UTF-8') ?>
      </p>
      <?php endif; ?>

      <!-- Analiz butonu — onclick: app.js'deki startAnalysis() çağrılır -->
      <button class="analyze-btn" id="analyzeBtn" onclick="startAnalysis()">
        ✦ Yapay Zeka ile Analiz Et
      </button>

    </div>
  </div>

  <!-- ══ ANALİZ BÖLÜMÜ ══ -->
  <div class="analysis-section">

    <!--
      Yüklenme göstergesi — başlangıçta gizli
      app.js'deki analyzeBook() → loading.classList.add('active') ile gösterir
      İşlem bitince → loading.classList.remove('active') ile gizler
    -->
    <div class="loading-state" id="analysisLoading">
      <div class="spinner"></div>
      <span>Rençber analiz ediyor...</span>
    </div>

    <!--
      Analiz kartı — başlangıçta boş ve gizli
      app.js'deki renderAnalysis() fonksiyonu bu div'i doldurur
      ve .active class'ı ekleyerek görünür yapar
    -->
    <div class="analysis-card" id="analysisCard"></div>

  </div>

  <!-- ══ KİTAP UYUM ANKETİ ══
       Analiz tamamlandıktan sonra app.js'deki showQuiz() bu section'ı gösterir.
       style="display:none" → başlangıçta gizli
  -->
  <div class="quiz-section" id="quizSection" style="display:none;">
    <div class="quiz-card">

      <!-- Anket başlık alanı -->
      <div class="quiz-header">
        <span class="quiz-eyebrow">Kişiselleştirilmiş Değerlendirme</span>
        <h2 class="quiz-title">Bu kitap <em>sana</em> göre mi?</h2>
        <p class="quiz-subtitle">
          5 soruyu yanıtla — okuma alışkanlıklarına göre
          bu kitapla uyum yüzdeni hesaplayalım.
        </p>
      </div>

      <!--
        Sorular burada app.js'deki showQuiz() tarafından render edilir.
        JavaScript çalıştıktan sonra bu div dolar.
      -->
      <div class="quiz-body" id="quizBody"></div>

      <!-- Hesapla butonu — JavaScript tarafından eklenir -->
      <div class="quiz-footer" id="quizFooter" style="padding:0 2rem 2rem;text-align:center;">
        <button
          class="quiz-submit-btn"
          id="quizSubmit"
          onclick="calculateResult()"
          disabled
          type="button"
        >
          Uyumumu Hesapla →
        </button>
      </div>

      <!-- Sonuç ekranı — başlangıçta gizli, calculateResult() doldurur -->
      <div class="quiz-result" id="quizResult" style="display:none;"></div>

    </div>
  </div>

  <!-- ══ BENZER KİTAPLAR ══
       loadSimilarBooks() bu section'ı gösterir ve #similarBooks'u doldurur
  -->
  <div class="similar-section" id="similarSection" style="display:none;">
    <h2 class="section-title">Benzer Kitaplar</h2>
    <div class="books-grid" id="similarBooks"></div>
  </div>

</div>

<!--
  PHP'den JavaScript'e güvenli veri aktarımı.

  Neden json_encode + JSON_HEX_TAG?
  json_encode() metin içindeki " ve \ gibi karakterleri zaten kaçırır.
  JSON_HEX_TAG eklediğimizde < ve > de \u003C \u003E formatına döner.
  Bu şekilde başlık içinde "<script>" gibi bir şey olsa bile
  JavaScript kodu olarak çalışmaz — XSS imkansız.
-->
<script>
const BOOK_DATA = {
  id:          <?= json_encode($bookId,          JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  title:       <?= json_encode($book['title'],   JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  author:      <?= json_encode($authors,         JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  description: <?= json_encode($cleanDesc,       JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  category:    <?= json_encode($category,        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
  pageCount:   <?= json_encode((int)($book['pageCount'] ?? 0)) ?>
};

/* Analiz butonu tıklandıkça çağrılır */
function startAnalysis() {
  analyzeBook(BOOK_DATA.title, BOOK_DATA.author, BOOK_DATA.description);
  loadSimilarBooks();
}

/* Benzer kitapları yükle — aynı kategoriden */
async function loadSimilarBooks() {
  const section = document.getElementById('similarSection');
  const grid    = document.getElementById('similarBooks');
  const query   = BOOK_DATA.category || BOOK_DATA.title;

  try {
    const res  = await fetch(`/kitap-analiz/api/search.php?q=${encodeURIComponent(query)}`);
    const data = await res.json();

    if (!data || !data.length) return;

    // Mevcut kitabı filtrele, ilk 6'yı al
    const filtered = data.filter(b => b.id !== BOOK_DATA.id).slice(0, 6);
    if (!filtered.length) return;

    grid.innerHTML = filtered.map((b, i) => renderBookCard(b, i)).join('');
    section.style.display = 'block';

  } catch (e) {
    console.warn('Benzer kitaplar yüklenemedi:', e);
  }
}
</script>

<?php
// Sayfanın footer'ını dahil et — </body> ve </html> burada kapanır
require_once __DIR__ . '/includes/footer.php';
?>