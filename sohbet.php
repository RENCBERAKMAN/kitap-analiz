<?php
/*
 * sohbet.php — Tarih Ötesi Sohbet
 * Kullanıcı bir tarihsel figürü seçer, Gemini o kişinin ruhuna bürünür.
 */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$pageTitle = 'Zaman Ötesi Sohbet';
require_once __DIR__ . '/includes/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=EB+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Courier+Prime:ital@0;1&display=swap">

<div class="sohbet-page" id="sohbetPage">

  <!-- ══ KANVAs — Parçacık animasyonu ══ -->
  <canvas id="particleCanvas" class="particle-canvas"></canvas>

  <!-- ══ KARAKTER SEÇİMİ ══ -->
  <section class="karakter-section" id="karakterSection">

    <div class="karakter-hero">
      <div class="karakter-eyebrow">Zamanın Ötesinden</div>
      <h1 class="karakter-title">
        Tarihin <em>sesi</em>ni<br>duymak ister misin?
      </h1>
      <p class="karakter-subtitle">
        Bir düşünür, yazar ya da tarih figürü seç. onun hayatını,
        eserlerini ve dünya görüşünü derinlemesine analiz ederek o kişinin
        zihnine bürünür. Zamanlar arası bir sohbet başlar.
      </p>
    </div>

    <!-- Figür kartları -->
    <div class="figur-grid" id="figurGrid">

      <div class="figur-kart" data-isim="Fyodor Dostoyevski" data-donem="1821–1881" data-alan="Rus Edebiyatı" data-emoji="📖" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">📖</div>
        <div class="figur-isim">Fyodor Dostoyevski</div>
        <div class="figur-donem">1821 – 1881</div>
        <div class="figur-alan">Rus Edebiyatı</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Friedrich Nietzsche" data-donem="1844–1900" data-alan="Felsefe" data-emoji="⚡" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">⚡</div>
        <div class="figur-isim">Friedrich Nietzsche</div>
        <div class="figur-donem">1844 – 1900</div>
        <div class="figur-alan">Felsefe</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Sokrates" data-donem="MÖ 470–399" data-alan="Antik Felsefe" data-emoji="🏛️" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🏛️</div>
        <div class="figur-isim">Sokrates</div>
        <div class="figur-donem">MÖ 470 – 399</div>
        <div class="figur-alan">Antik Felsefe</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Albert Einstein" data-donem="1879–1955" data-alan="Fizik & Felsefe" data-emoji="🌌" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🌌</div>
        <div class="figur-isim">Albert Einstein</div>
        <div class="figur-donem">1879 – 1955</div>
        <div class="figur-alan">Fizik & Felsefe</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Lev Tolstoy" data-donem="1828–1910" data-alan="Rus Edebiyatı" data-emoji="🕊️" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🕊️</div>
        <div class="figur-isim">Lev Tolstoy</div>
        <div class="figur-donem">1828 – 1910</div>
        <div class="figur-alan">Edebiyat & Ahlak</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Sigmund Freud" data-donem="1856–1939" data-alan="Psikanaliz" data-emoji="🧠" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🧠</div>
        <div class="figur-isim">Sigmund Freud</div>
        <div class="figur-donem">1856 – 1939</div>
        <div class="figur-alan">Psikanaliz</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Marcus Aurelius" data-donem="MS 121–180" data-alan="Stoa Felsefesi" data-emoji="⚖️" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">⚖️</div>
        <div class="figur-isim">Marcus Aurelius</div>
        <div class="figur-donem">MS 121 – 180</div>
        <div class="figur-alan">Stoa Felsefesi</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Nikola Tesla" data-donem="1856–1943" data-alan="Bilim & İcat" data-emoji="⚡" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🔭</div>
        <div class="figur-isim">Nikola Tesla</div>
        <div class="figur-donem">1856 – 1943</div>
        <div class="figur-alan">Bilim & İcat</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

      <div class="figur-kart" data-isim="Yunus Emre" data-donem="1240–1321" data-alan="Türk Tasavvuf Şiiri" data-emoji="🌹" onclick="figurSec(this)">
        <div class="figur-hale"></div>
        <div class="figur-emoji">🌹</div>
        <div class="figur-isim">Yunus Emre</div>
        <div class="figur-donem">1240 – 1321</div>
        <div class="figur-alan">Tasavvuf & Şiir</div>
        <div class="figur-secim-btn">Çağır →</div>
      </div>

    </div>

    <!-- Özel kişi girişi -->
    <div class="ozel-giris">
      <div class="ozel-giris-label">Ya da kendi seçimini yap:</div>
      <div class="ozel-giris-wrapper">
        <input type="text" id="ozelKisiInput" class="ozel-giris-input"
          placeholder="Örn: Immanuel Kant, Leonardo da Vinci, Mevlana..."
          maxlength="100">
        <button class="ozel-giris-btn" onclick="ozelKisiSec()" type="button">
          <span>Çağır</span>
          <span class="ozel-btn-arrow">→</span>
        </button>
      </div>
    </div>

  </section>

  <!-- ══ SOHBET ARAYÜZÜ ══ -->
  <section class="sohbet-section" id="sohbetSection" style="display:none;">

    <!-- Başlık -->
    <div class="sohbet-header">
      <div class="sohbet-figur-info">
        <div class="sohbet-figur-avatar" id="sohbetAvatar"></div>
        <div class="sohbet-figur-detay">
          <div class="sohbet-figur-ad" id="sohbetFigurAd"></div>
          <div class="sohbet-figur-donem" id="sohbetFigurDonem"></div>
        </div>
        <div class="sohbet-durum">
          <div class="sohbet-durum-nokta"></div>
          <div class="sohbet-durum-yazi">Zaman kırığı aktif</div>
        </div>
      </div>
      <button class="sohbet-geri-btn" onclick="geriDon()" type="button">← Başka Biri Seç</button>
    </div>

    <!-- Mesajlar -->
    <div class="mesajlar-container" id="mesajlarContainer">
      <div class="mesajlar-ic" id="mesajlarIc">
        <!-- Hoş geldin mesajı buraya gelecek -->
      </div>
    </div>

    <!-- Giriş alanı -->
    <div class="sohbet-input-area">
      <div class="sohbet-input-wrapper">
        <textarea
          id="sohbetInput"
          class="sohbet-textarea"
          placeholder="Ne sormak istersin?"
          maxlength="1000"
          rows="1"
          onkeydown="inputKeyDown(event)"
          oninput="autoResize(this)"
        ></textarea>
        <button class="sohbet-gonder-btn" id="gonderBtn" onclick="mesajGonder()" type="button">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
          </svg>
        </button>
      </div>
      <div class="sohbet-uyari">
        Bu bir AI simulasyonu. Yanıtlar tarihi araştırmalara dayalıdır ama hayal gücü içerir.
      </div>
    </div>

  </section>

</div>

<script>
/* ══════════════════════════════
   PARTİKÜL KANVAS ANİMASYONU
══════════════════════════════ */
(function() {
  const canvas = document.getElementById('particleCanvas');
  const ctx    = canvas.getContext('2d');
  let W, H, particles = [];

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  window.addEventListener('resize', resize);
  resize();

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x    = Math.random() * W;
      this.y    = Math.random() * H;
      this.size = Math.random() * 1.5 + 0.2;
      this.vx   = (Math.random() - 0.5) * 0.18;
      this.vy   = -Math.random() * 0.25 - 0.05;
      this.life = Math.random();
      this.maxLife = Math.random() * 0.5 + 0.5;
      // Cyan veya mor
      this.color = Math.random() > 0.6
        ? `rgba(0, 245, 212, ${this.life})`
        : `rgba(124, 58, 237, ${this.life * 0.6})`;
    }
    update() {
      this.x   += this.vx;
      this.y   += this.vy;
      this.life -= 0.0015;
      if (this.life <= 0 || this.y < -10) this.reset();
    }
    draw() {
      ctx.save();
      ctx.globalAlpha = this.life;
      ctx.fillStyle   = this.color;
      ctx.shadowBlur  = 6;
      ctx.shadowColor = this.color;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }
  }

  for (let i = 0; i < 120; i++) particles.push(new Particle());

  function animate() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
  }
  animate();
})();

/* ══════════════════════════════
   SOHBET DURUMU
══════════════════════════════ */
let aktifFigur    = null;
let sohbetGecmisi = []; // { rol: 'kullanici'|'figur', metin: '...' }

/* Figür kartından seç */
function figurSec(el) {
  const isim  = el.dataset.isim;
  const donem = el.dataset.donem;
  const emoji = el.dataset.emoji || el.querySelector('.figur-emoji').textContent;
  const alan  = el.dataset.alan  || '';
  baslatSohbet(isim, donem, emoji, alan);
}

/* Özel kişi girişinden seç */
function ozelKisiSec() {
  const input = document.getElementById('ozelKisiInput');
  const isim  = input.value.trim();
  if (!isim || isim.length < 2) {
    input.classList.add('shake');
    setTimeout(() => input.classList.remove('shake'), 500);
    return;
  }
  baslatSohbet(isim, '', '👤', '');
}

/* Sohbeti başlat */
async function baslatSohbet(isim, donem, emoji, alan) {
  aktifFigur    = { isim, donem, emoji, alan };
  sohbetGecmisi = [];

  // UI geçişi
  document.getElementById('karakterSection').style.display = 'none';
  document.getElementById('sohbetSection').style.display   = 'flex';

  document.getElementById('sohbetAvatar').textContent    = emoji;
  document.getElementById('sohbetFigurAd').textContent   = isim;
  document.getElementById('sohbetFigurDonem').textContent = donem || '';

  const mesajlarIc = document.getElementById('mesajlarIc');
  mesajlarIc.innerHTML = '';

  // Açılış mesajı yükle
  const yukleniyorId = yaziyorGoster(isim);

  try {
    const res = await fetch('/kitap-analiz/api/sohbet.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        figur:   isim,
        donem:   donem,
        alan:    alan,
        mesajlar: [],
        ilk:     true
      })
    });

    yaziyorGizle(yukleniyorId);

    if (!res.ok) { hataGoster('Bağlantı kurulamadı.'); return; }
    const data = await res.json();
    if (data.error) { hataGoster(data.error); return; }

    mesajEkle('figur', data.cevap);
    sohbetGecmisi.push({ rol: 'figur', metin: data.cevap });

  } catch(e) {
    yaziyorGizle(yukleniyorId);
    hataGoster('Bağlantı hatası.');
  }

  document.getElementById('sohbetInput').focus();
}

/* Mesaj gönder */
async function mesajGonder() {
  const input = document.getElementById('sohbetInput');
  const metin = input.value.trim();
  if (!metin || !aktifFigur) return;

  const btn = document.getElementById('gonderBtn');
  btn.disabled    = true;
  input.value     = '';
  input.style.height = '';

  mesajEkle('kullanici', metin);
  sohbetGecmisi.push({ rol: 'kullanici', metin });

  const yukleniyorId = yaziyorGoster(aktifFigur.isim);

  try {
    const res = await fetch('/kitap-analiz/api/sohbet.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        figur:    aktifFigur.isim,
        donem:    aktifFigur.donem,
        alan:     aktifFigur.alan,
        mesajlar: sohbetGecmisi.slice(-10), // Son 10 mesaj
        ilk:      false
      })
    });

    yaziyorGizle(yukleniyorId);

    if (!res.ok) { hataGoster('Sunucu hatası.'); return; }
    const data = await res.json();
    if (data.error) { hataGoster(data.error); return; }

    mesajEkle('figur', data.cevap);
    sohbetGecmisi.push({ rol: 'figur', metin: data.cevap });

  } catch(e) {
    yaziyorGizle(yukleniyorId);
    hataGoster('Bağlantı hatası.');
  }

  btn.disabled = false;
  input.focus();
}

/* Mesaj DOM'a ekle */
function mesajEkle(rol, metin) {
  const container = document.getElementById('mesajlarIc');
  const div       = document.createElement('div');
  div.className   = `mesaj mesaj-${rol}`;

  const icerik = metin
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
    .replace(/\*(.+?)\*/g,'<em>$1</em>')
    .replace(/\n/g,'<br>');

  div.innerHTML = `
    ${rol === 'figur' ? `<div class="mesaj-avatar">${aktifFigur.emoji}</div>` : ''}
    <div class="mesaj-balon">
      ${rol === 'figur' ? `<div class="mesaj-kimlik">${aktifFigur.isim}</div>` : ''}
      <div class="mesaj-metin">${icerik}</div>
    </div>`;

  container.appendChild(div);
  requestAnimationFrame(() => {
    div.classList.add('gorünür');
    scrollToBottom();
  });
}

/* Yazıyor göstergesi */
let yaziyorSayac = 0;
function yaziyorGoster(isim) {
  const id = 'yazıyor-' + (++yaziyorSayac);
  const container = document.getElementById('mesajlarIc');
  const div = document.createElement('div');
  div.className = 'mesaj mesaj-figur yazıyor-mesaj';
  div.id = id;
  div.innerHTML = `
    <div class="mesaj-avatar">${aktifFigur ? aktifFigur.emoji : '👤'}</div>
    <div class="mesaj-balon">
      <div class="yazıyor-indicator">
        <span></span><span></span><span></span>
      </div>
    </div>`;
  container.appendChild(div);
  scrollToBottom();
  return id;
}

function yaziyorGizle(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}

function hataGoster(msg) {
  const container = document.getElementById('mesajlarIc');
  const div = document.createElement('div');
  div.className = 'mesaj-hata';
  div.textContent = '⚠ ' + msg;
  container.appendChild(div);
  scrollToBottom();
}

function scrollToBottom() {
  const c = document.getElementById('mesajlarContainer');
  c.scrollTo({ top: c.scrollHeight, behavior: 'smooth' });
}

function geriDon() {
  document.getElementById('sohbetSection').style.display   = 'none';
  document.getElementById('karakterSection').style.display = 'block';
  aktifFigur    = null;
  sohbetGecmisi = [];
}

function inputKeyDown(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    mesajGonder();
  }
}

function autoResize(el) {
  el.style.height = '';
  el.style.height = Math.min(el.scrollHeight, 160) + 'px';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>