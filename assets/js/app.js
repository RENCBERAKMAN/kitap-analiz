/* ============================================================
   KİTAP ANALİZİ — Ana JavaScript Dosyası
   Güvenlik · Arama · Kategoriler · Analiz · Quiz · Trend
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
  initLoader();     // Sayfa yükleme ekranını gizle
  initSearch();     // Arama kutusu olaylarını bağla
  initHints();      // Hızlı arama chiplerini bağla
  initCategories(); // Kategori kartlarını oluştur
  loadTrend();      // En çok bakılan kitapları yükle
});

/* ════════════════════════════════════════
   SAYFA YÜKLEYİCİ
════════════════════════════════════════ */
function initLoader() {
  setTimeout(() => {
    const el = document.querySelector('.page-loader');
    if (el) el.classList.add('hidden');
  }, 500);
}

/* ════════════════════════════════════════
   GÜVENLİK — GİRİŞ TEMİZLEME (Sanitization)

   XSS (Cross-Site Scripting) Nedir?
   Saldırgan arama kutusuna <script>alert('hack')</script> yazar.
   Eğer bu doğrudan innerHTML'e yazılırsa tarayıcı çalıştırır.
   esc() fonksiyonu < ve > karakterlerini &lt; &gt;'ya çevirerek
   tarayıcının bunları KOD olarak değil, TEXT olarak görmesini sağlar.
════════════════════════════════════════ */
function esc(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g,  '&amp;')   // & → &amp;
    .replace(/</g,  '&lt;')    // < → &lt;   (XSS'i öldürür)
    .replace(/>/g,  '&gt;')    // > → &gt;
    .replace(/"/g,  '&quot;')  // " → &quot;
    .replace(/'/g,  '&#39;');  // ' → &#39;
}

/* Arama sorgusunu temizle:
   1) HTML etiketlerini çıkar
   2) 100 karakterle sınırla
   3) Baştaki/sondaki boşlukları kır */
function sanitizeQuery(q) {
  return q
    .replace(/<[^>]*>/g, '') // <script> gibi tag'leri kaldır
    .substring(0, 100)       // max 100 karakter
    .trim();
}

/* Kitap ID'sini doğrula — URL'e enjekte edilmesin
   Sadece harf, rakam, tire ve alt çizgiye izin ver */
function isValidBookId(id) {
  return /^[a-zA-Z0-9_\-]+$/.test(id);
}

/* ════════════════════════════════════════
   ARAMA SİSTEMİ
════════════════════════════════════════ */
let searchTimer  = null; // Debounce için zamanlayıcı
let activeQuery  = '';   // Son yapılan arama (tekrar önlemek için)

function initSearch() {
  const input = document.getElementById('searchInput');
  const btn   = document.querySelector('.search-btn');
  if (!input) return;

  // Maksimum 100 karakter — HTML tarafında da sınırla
  input.setAttribute('maxlength', '100');

  // Enter tuşu
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') triggerSearch(input.value.trim());
  });

  // Buton tıklaması
  if (btn) btn.addEventListener('click', () => triggerSearch(input.value.trim()));

  // Canlı arama — kullanıcı durdurunca 650ms sonra ara (debounce)
  // Her tuşa basışta API çağrısı yapmak sunucu ve Google Books kotası için zararlı
  input.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = input.value.trim();
    if (q.length < 2) return;
    searchTimer = setTimeout(() => triggerSearch(q), 650);
  });
}

// Öneri chip'lerini tıklanabilir yap
function initHints() {
  document.querySelectorAll('.hint-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      const input = document.getElementById('searchInput');
      if (!input) return;
      input.value = chip.dataset.query;
      triggerSearch(chip.dataset.query);
      input.focus();
    });
  });
}

// Arama tetikleyicisi — temizleme ve tekrar engellemesi yapar
function triggerSearch(query) {
  query = sanitizeQuery(query); // Güvenli temizle

  if (!query || query === activeQuery) return; // Boş veya aynı sorgu → dur
  activeQuery = query;

  // Kategori seçimini sıfırla
  document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
  const cr = document.getElementById('categoryResults');
  if (cr) cr.innerHTML = '';

  doSearch(query);
}

// Asıl arama — api/search.php'ye GET isteği atar
async function doSearch(query) {
  const container = document.getElementById('searchResults');
  if (!container) return;

  container.innerHTML = renderLoading('Kitaplar aranıyor...');

  try {
    // encodeURIComponent → "Suç ve Ceza" → "Su%C3%A7%20ve%20Ceza"
    // URL'deki özel karakterleri kodlar, URL injection'ı engeller
    const res = await fetch(`/kitap-analiz/api/search.php?q=${encodeURIComponent(query)}`);

    // HTTP durum kodunu kontrol et (200 = başarılı)
    if (!res.ok) {
      container.innerHTML = renderError(`Sunucu hatası (${res.status})`);
      return;
    }

    const data = await res.json();

    if (data.error)   { container.innerHTML = renderError(data.error); return; }
    if (!data.length) { container.innerHTML = renderEmpty(query);      return; }

    container.innerHTML = renderResults(data, query);

  } catch (e) {
    container.innerHTML = renderError('Bağlantı hatası. XAMPP çalışıyor mu?');
    console.error('Arama hatası:', e);
  }
}

/* ════════════════════════════════════════
   TREND KİTAPLAR
   api/trending.php → veritabanındaki en çok
   görüntülenen kitapları getirir
════════════════════════════════════════ */
async function loadTrend() {
  try {
    const res = await fetch('/kitap-analiz/api/trending.php');
    if (!res.ok) return;

    const data = await res.json();
    if (!data || !data.length) return; // Henüz veri yoksa gösterme

    const grid    = document.getElementById('trendBooks');
    const baslik  = document.getElementById('trend-baslik');
    if (!grid) return;

    // Veritabanı formatını → renderBookCard formatına çevir
    const books = data.map(row => ({
      id:      row.kitap_id,
      title:   row.kitap_adi,
      authors: [row.yazar],
      cover:   row.kapak_url,
      rating:  0,
    }));

    grid.innerHTML = books.map((book, i) => renderBookCard(book, i)).join('');
    if (baslik) baslik.style.display = 'block';

  } catch (e) {
    // Trend yüklenemezse sessizce geç — kritik değil
    console.warn('Trend yüklenemedi:', e);
  }
}

/* ════════════════════════════════════════
   KATEGORİ SİSTEMİ
   Her kategorinin Google Books query'si,
   rengi ve ikonu tanımlıdır
════════════════════════════════════════ */
const CATEGORIES = [
  { id: 'edebiyat',  label: 'Edebiyat',       icon: '📜', color: '#C49335', query: 'classic literature masterpiece' },
  { id: 'felsefe',   label: 'Felsefe',         icon: '🦉', color: '#7ABFA0', query: 'philosophy wisdom thinking essential' },
  { id: 'bilim',     label: 'Bilim',           icon: '🔬', color: '#5B9BD5', query: 'popular science best discovery' },
  { id: 'tarih',     label: 'Tarih',           icon: '🏛️', color: '#B8956A', query: 'world history essential civilization' },
  { id: 'psikoloji', label: 'Psikoloji',       icon: '🧠', color: '#A87AC0', query: 'psychology human behavior essential' },
  { id: 'gelisim',   label: 'Kişisel Gelişim', icon: '✨', color: '#E8B95A', query: 'self improvement habits success best' },
  { id: 'kurgu',     label: 'Kurgu & Bilim-K', icon: '🌌', color: '#4DA6B4', query: 'science fiction fantasy essential novel' },
  { id: 'biyografi', label: 'Biyografi',       icon: '👤', color: '#D4856A', query: 'biography memoir great people essential' },
  { id: 'sanat',     label: 'Sanat & Tasarım', icon: '🎨', color: '#E07090', query: 'art design creativity essential' },
  { id: 'teknoloji', label: 'Teknoloji',       icon: '💻', color: '#6ABFA0', query: 'technology future digital essential' },
];

let activeCategory = null;

// Kategori kartlarını DOM'a yaz
function initCategories() {
  const grid = document.getElementById('categoryGrid');
  if (!grid) return;

  // esc() ile renk değerini de temizliyoruz (inline style injection karşı)
  grid.innerHTML = CATEGORIES.map(cat => `
    <div
      class="category-card"
      data-id="${esc(cat.id)}"
      style="--cat-color:${cat.color}"
      onclick="selectCategory('${esc(cat.id)}')"
      role="button"
      aria-label="${esc(cat.label)} kategorisini seç"
    >
      <span class="cat-icon" aria-hidden="true">${cat.icon}</span>
      <span class="cat-label">${esc(cat.label)}</span>
      <span class="cat-desc">En iyi 10 eser</span>
    </div>
  `).join('');
}

// Kategori seçildi
async function selectCategory(catId) {
  // Güvenlik: catId'nin beklenen değerlerden biri olduğunu kontrol et
  const cat = CATEGORIES.find(c => c.id === catId);
  if (!cat) return; // Bilinmeyen kategori → dur

  // Aynı kategoriye tekrar tıklandıysa kapat (toggle)
  if (activeCategory === catId) {
    activeCategory = null;
    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
    const cr = document.getElementById('categoryResults');
    if (cr) cr.innerHTML = '';
    return;
  }

  activeCategory = catId;

  // Aktif kartı vurgula
  document.querySelectorAll('.category-card').forEach(c => {
    c.classList.toggle('active', c.dataset.id === catId);
  });

  // Arama kutusunu temizle
  const input = document.getElementById('searchInput');
  if (input) { input.value = ''; activeQuery = ''; }
  const sr = document.getElementById('searchResults');
  if (sr) sr.innerHTML = '';

  await loadCategoryBooks(cat);
}

// Kategori kitaplarını Google Books'tan çek
async function loadCategoryBooks(cat) {
  const container = document.getElementById('categoryResults');
  if (!container) return;

  container.innerHTML = renderLoading(`${cat.icon} ${cat.label} kitapları yükleniyor...`);

  try {
    const res  = await fetch(`/kitap-analiz/api/search.php?q=${encodeURIComponent(cat.query)}`);
    const data = await res.json();

    if (data.error || !data.length) {
      container.innerHTML = renderEmpty(cat.label);
      return;
    }

    const books = data.slice(0, 10);
    container.innerHTML = `
      <div class="cat-results-header">
        <span class="cat-results-title">${cat.icon} ${esc(cat.label)} — Başlangıç İçin Önerilen Eserler</span>
        <span class="cat-results-badge">En İyi 10</span>
      </div>
      <div class="books-grid">
        ${books.map((book, i) => renderBookCard(book, i)).join('')}
      </div>`;

    setTimeout(() => container.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);

  } catch {
    container.innerHTML = renderError('Kitaplar yüklenemedi.');
  }
}

/* ════════════════════════════════════════
   RENDER FONKSİYONLARI
   DOM'a yazılacak HTML parçalarını üretir
════════════════════════════════════════ */

function renderLoading(msg) {
  return `
    <div class="loading-state active">
      <div class="spinner"></div>
      <span>${esc(msg)}</span>
    </div>`;
}

function renderError(msg) {
  return `
    <div class="error-state">
      <span class="error-icon">⚠</span>
      <p class="error-title">Bir sorun oluştu</p>
      <p class="error-sub">${esc(msg)}</p>
    </div>`;
}

function renderEmpty(query) {
  return `
    <div class="empty-state">
      <span class="empty-icon">📚</span>
      <p class="empty-title">"${esc(query)}" için sonuç bulunamadı</p>
      <p class="empty-sub">Farklı bir yazım deneyin</p>
    </div>`;
}

function renderResults(books, query) {
  return `
    <div class="results-header">
      <span class="results-title">"<em>${esc(query)}</em>" için sonuçlar</span>
      <span class="results-count">${books.length} kitap</span>
    </div>
    <div class="books-grid">
      ${books.map((book, i) => renderBookCard(book, i)).join('')}
    </div>`;
}

// Tek kitap kartı
function renderBookCard(book, index) {
  const authors = Array.isArray(book.authors)
    ? book.authors.join(', ')
    : (book.authors || 'Yazar bilinmiyor');

  const safeTitle = esc(book.title);
  const safeId    = esc(book.id);

  // Kapak görseli veya yer tutucu
  const coverHtml = book.cover
    ? `<img
         class="book-cover"
         src="${esc(book.cover)}"
         alt="${safeTitle}"
         loading="lazy"
         onerror="this.parentElement.innerHTML=coverPlaceholder('${safeTitle.replace(/'/g, '')}')"
       >`
    : `<div class="book-cover-placeholder">
         <div class="placeholder-icon">📖</div>
         <div class="placeholder-title">${safeTitle}</div>
       </div>`;

  return `
    <div
      class="book-card"
      style="animation-delay:${index * 0.06}s"
      onclick="goToBook('${safeId}')"
      role="button"
      tabindex="0"
      aria-label="${safeTitle} kitabına git"
    >
      <div class="book-cover-wrapper">
        ${coverHtml}
        <div class="book-overlay" aria-hidden="true">
          <button class="overlay-btn" onclick="event.stopPropagation();goToBook('${safeId}')">
            Analiz Et →
          </button>
        </div>
      </div>
      <div class="book-info">
        <div class="book-title">${safeTitle}</div>
        <div class="book-author">${esc(authors)}</div>
      </div>
    </div>`;
}

function coverPlaceholder(title) {
  return `<div class="book-cover-placeholder">
    <div class="placeholder-icon">📖</div>
    <div class="placeholder-title">${title}</div>
  </div>`;
}

// Kitap detay sayfasına git
// Güvenlik: ID'yi URL'ye koymadan önce doğrula
function goToBook(bookId) {
  if (!isValidBookId(bookId)) return; // Geçersiz karakter varsa dur
  window.location.href = `/kitap-analiz/book.php?id=${encodeURIComponent(bookId)}`;
}

/* ════════════════════════════════════════
   GEMİNİ ANALİZ — book.php için
   PHP'deki api/analyze.php'ye POST atar
════════════════════════════════════════ */
async function analyzeBook(title, author, description) {
  const btn     = document.getElementById('analyzeBtn');
  const loading = document.getElementById('analysisLoading');
  const card    = document.getElementById('analysisCard');

  // Butonu devre dışı bırak, yükleme göster
  if (btn)     { btn.disabled = true; btn.innerHTML = '⏳ Rençber analiz ediyor...'; }
  if (loading) loading.classList.add('active');
  if (card)    card.classList.remove('active');

  try {
    const res = await fetch('/kitap-analiz/api/analyze.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        // Uzunluk sınırlaması istemci tarafında da yapılıyor
        // Sunucu da kendi sınırını uygular
        title:       String(title).substring(0, 200),
        author:      String(author).substring(0, 200),
        description: String(description).substring(0, 1000),
      })
    });

    if (!res.ok) {
      alert(`Sunucu hatası: ${res.status}. Lütfen tekrar deneyin.`);
      return;
    }

    const data = await res.json();

    if (!data || data.error || data.hata) {
      alert('Analiz yapılamadı. Lütfen tekrar deneyin.');
      return;
    }

    renderAnalysis(data); // Sonuçları ekrana yaz
    showQuiz();           // Analiz bittikten sonra anketi göster

  } catch (e) {
    alert('Bağlantı hatası oluştu.');
    console.error('Analiz hatası:', e);
  } finally {
    // Her durumda (başarı veya hata) butonu serbest bırak
    if (btn)     { btn.disabled = false; btn.innerHTML = '✦ Yeniden Analiz Et'; }
    if (loading) loading.classList.remove('active');
  }
}

// Analiz verisini DOM'a yaz
function renderAnalysis(data) {
  const card = document.getElementById('analysisCard');
  if (!card) return;

  // Karar: "OKUMALSIN" içeriyorsa oku, yoksa okuma
  const shouldRead   = data.karar && data.karar.toUpperCase().includes('OKUMALSIN');
  const verdictClass = shouldRead ? 'verdict-oku'   : 'verdict-okuma';
  const verdictBadge = shouldRead ? '✦ OKUMALSIN'   : '✕ OKUMAMALISIN';
  const verdictText  = shouldRead
    ? 'Bu kitap sana göre — başlamak için doğru seçim.'
    : 'Bu kitap şu an için uygun olmayabilir.';

  // Kazanım ve zorluk listelerini oluştur
  const gains      = (data.kazanimlar || []).map(k => `<li>${esc(k)}</li>`).join('');
  const challenges = (data.zorluklar  || []).map(z => `<li>${esc(z)}</li>`).join('');

  // Tüm metinler esc() ile korunuyor — Gemini'den gelen veri de güvensiz sayılmalı
  card.innerHTML = `
    <div class="analysis-verdict ${verdictClass}">
      <span class="verdict-badge">${verdictBadge}</span>
      <span class="verdict-text">${verdictText}</span>
    </div>
    <div class="analysis-body">
      <div class="analysis-block full" style="animation-delay:0.05s">
        <div class="block-label">📖 Kitap Özeti</div>
        <div class="block-text">${esc(data.ozet || '—')}</div>
      </div>
      <div class="analysis-block" style="animation-delay:0.1s">
        <div class="block-label">✦ Ne Kazanırsın</div>
        <ul class="item-list gains">${gains || '<li>Veri alınamadı</li>'}</ul>
      </div>
      <div class="analysis-block" style="animation-delay:0.15s">
        <div class="block-label">◈ Seni Zorlayabilir</div>
        <ul class="item-list challenges">${challenges || '<li>Veri alınamadı</li>'}</ul>
      </div>
      <div class="analysis-block" style="animation-delay:0.2s">
        <div class="block-label">👤 Kime Uygun</div>
        <div class="block-text">${esc(data.kime_uygun || '—')}</div>
      </div>
      <div class="analysis-block" style="animation-delay:0.25s">
        <div class="block-label">✕ Kime Uygun Değil</div>
        <div class="block-text">${esc(data.kime_uygun_degil || '—')}</div>
      </div>
      ${data.alinti ? `
      <div class="quote-block" style="animation-delay:0.3s">
        "${esc(data.alinti)}"
      </div>` : ''}
    </div>`;

  card.classList.add('active');
  setTimeout(() => card.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
}

/* ════════════════════════════════════════
   KİTAP UYUM ANKETİ
   Kullanıcının okuma profiline göre
   kitapla uyumunu yüzde olarak hesaplar
════════════════════════════════════════ */

// 5 soru, 4 seçenek, her seçeneğin bir puan değeri var
const QUIZ_QUESTIONS = [
  {
    soru: 'Günde ortalama ne kadar okuyorsun?',
    secenekler: [
      { metin: '1 saatten fazla',   puan: 4 },
      { metin: '30–60 dakika',      puan: 3 },
      { metin: '15–30 dakika',      puan: 2 },
      { metin: 'Fırsat buldukça',   puan: 1 },
    ]
  },
  {
    soru: 'Hangi tür kitapları daha çok seviyorsun?',
    secenekler: [
      { metin: 'Ağır edebiyat & felsefe',  puan: 4 },
      { metin: 'Kurgu & roman',            puan: 3 },
      { metin: 'Kişisel gelişim & bilim',  puan: 2 },
      { metin: 'Farketmez, her türü',      puan: 3 },
    ]
  },
  {
    soru: 'Karmaşık karakterlere ve derin anlatıya ne dersin?',
    secenekler: [
      { metin: 'Tam benim için',         puan: 4 },
      { metin: 'Severim ama zor gelir',  puan: 3 },
      { metin: 'Bazen bunaltıcı',        puan: 2 },
      { metin: 'Tercih etmem',           puan: 1 },
    ]
  },
  {
    soru: 'Kitaptan öncelikli beklentin ne?',
    secenekler: [
      { metin: 'Derin düşünce & iç yolculuk', puan: 4 },
      { metin: 'Bilgi & öğrenme',             puan: 3 },
      { metin: 'Eğlence & aksiyon',           puan: 2 },
      { metin: 'Motivasyon & ilham',          puan: 2 },
    ]
  },
  {
    soru: 'Kalın kitaplara (400+ sayfa) karşı tutumun?',
    secenekler: [
      { metin: 'Ne kadar uzun, o kadar iyi', puan: 4 },
      { metin: 'Sorun değil, sabırlıyım',    puan: 3 },
      { metin: 'Biraz ürkütücü ama denerim', puan: 2 },
      { metin: 'Kısa tutsun tercihim',       puan: 1 },
    ]
  }
];

let quizAnswers = {}; // { soruIndex: puan } formatında saklanır

// Anketi göster — analiz tamamlandıktan sonra çağrılır
function showQuiz() {
  const section = document.getElementById('quizSection');
  const body    = document.getElementById('quizBody');
  const result  = document.getElementById('quizResult');
  if (!section || !body) return; // HTML elementleri yoksa dur

  // Sıfırla
  quizAnswers = {};
  if (result) { result.style.display = 'none'; result.innerHTML = ''; }
  body.style.display = 'block';

  // Soruları HTML olarak oluştur
  body.innerHTML = QUIZ_QUESTIONS.map((q, qi) => `
    <div class="quiz-question">
      <span class="quiz-q-num">Soru ${qi + 1} / ${QUIZ_QUESTIONS.length}</span>
      <div class="quiz-q-text">${esc(q.soru)}</div>
      <div class="quiz-options">
        ${q.secenekler.map((s, si) => `
          <button
            class="quiz-option"
            data-qi="${qi}"
            onclick="selectAnswer(${qi}, ${si}, ${s.puan}, this)"
            type="button"
          >
            <span class="option-letter">${['A','B','C','D'][si]}</span>
            ${esc(s.metin)}
          </button>
        `).join('')}
      </div>
    </div>
  `).join('');

  // Section'ı göster ve aşağı kaydır
  section.style.display = 'block';
  setTimeout(() => section.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
}

// Seçenek seçildi
function selectAnswer(qi, si, puan, btn) {
  // Aynı sorunun diğer seçeneklerinin seçimini kaldır
  document.querySelectorAll(`[data-qi="${qi}"]`).forEach(b => b.classList.remove('selected'));

  // Bu seçeneği aktif yap
  btn.classList.add('selected');
  quizAnswers[qi] = puan; // Puanı kaydet

  // Tüm sorular cevaplandıysa "Hesapla" butonunu aktifleştir
  const submit = document.getElementById('quizSubmit');
  if (submit && Object.keys(quizAnswers).length === QUIZ_QUESTIONS.length) {
    submit.disabled = false;
    submit.classList.add('ready');
  }
}

// Sonucu hesapla ve göster
function calculateResult() {
  // Toplam puan: tüm cevapların puanlarının toplamı
  const total  = Object.values(quizAnswers).reduce((a, b) => a + b, 0);
  // Maksimum puan: her sorudan 4 puan → 5 soru × 4 = 20
  const maxPuan = QUIZ_QUESTIONS.length * 4;
  // Yüzde
  const yuzde  = Math.round((total / maxPuan) * 100);

  // Skora göre mesaj belirle
  let emoji, renk, mesaj;
  if (yuzde >= 80) {
    emoji = '🔥'; renk = '#7ABFA0';
    mesaj = 'Mükemmel uyum! Bu kitap tam sana göre yazılmış gibi. Başlamak için doğru an.';
  } else if (yuzde >= 60) {
    emoji = '✦';  renk = '#E8B95A';
    mesaj = 'İyi uyum. Bazı bölümler seni zorlayabilir, ama kesinlikle değer.';
  } else if (yuzde >= 40) {
    emoji = '◈';  renk = '#C49335';
    mesaj = 'Orta uyum. Doğru ruh halinde başlarsan keyif alabilirsin.';
  } else {
    emoji = '✕';  renk = '#D07060';
    mesaj = 'Düşük uyum. Şu an bu kitap için doğru zaman olmayabilir.';
  }

  const body   = document.getElementById('quizBody');
  const footer = document.getElementById('quizFooter');
  const result = document.getElementById('quizResult');
  if (!result) return;

  if (body)   body.style.display   = 'none';
  if (footer) footer.style.display = 'none';

  result.innerHTML = `
    <div class="quiz-result-inner">
      <div class="result-emoji">${emoji}</div>
      <div class="result-yuzde" style="color:${renk};">${yuzde}<span>%</span></div>
      <div class="result-label">Uyum Skoru</div>
      <p class="result-mesaj">${esc(mesaj)}</p>
      <div class="result-bar-wrap">
        <div class="result-bar" style="--hedef:${yuzde}%;--renk:${renk};"></div>
      </div>
      <button class="quiz-retry-btn" onclick="retryQuiz()">Anketi Yeniden Doldur</button>
    </div>`;

  result.style.display = 'block';
  setTimeout(() => result.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
}

// Anketi sıfırla
function retryQuiz() {
  const body   = document.getElementById('quizBody');
  const footer = document.getElementById('quizFooter');
  const result = document.getElementById('quizResult');

  if (result) result.style.display = 'none';
  if (body)   body.style.display   = 'block';
  if (footer) footer.style.display = 'block';

  document.querySelectorAll('.quiz-option').forEach(b => b.classList.remove('selected'));

  const submit = document.getElementById('quizSubmit');
  if (submit) { submit.disabled = true; submit.classList.remove('ready'); }

  quizAnswers = {};
}