<?php
/*
 * sorgu.php — Düşünce Sorgulama Sayfası
 * Kullanıcının yazdığı fikri Gemini AI ile felsefi/mantıksal açıdan sorgular.
 */

// Güvenlik başlıkları
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$pageTitle = 'Düşünceni Sorgula';
require_once __DIR__ . '/includes/header.php';
?>

<div class="sorgu-page">

  <!-- ══ HERO ══ -->
  <section class="sorgu-hero">
    <div class="sorgu-hero-bg"></div>

    <div class="sorgu-hero-inner">
      <div class="sorgu-eyebrow">Eleştirel Düşünme Motoru</div>
      <h1 class="sorgu-title">
        Fikirler <em>test edilmeden</em><br>
        güçlenmez.
      </h1>
      <p class="sorgu-subtitle">
        Hayat felsefeni, bir kararını ya da herhangi bir düşünceni yaz.
        Sistem bu fikri en sert şekilde sorgular, çelişkilerini bulur
        ve sana kendin hakkında düşünmen gereken soruları gösterir.
      </p>

      <!-- Nasıl Çalışır -->
      <div class="sorgu-info-box">
        <div class="sorgu-info-title">💡 Nasıl Çalışır?</div>
        <p class="sorgu-info-text">
          Hayat felsefenizi, bir kararınızı ya da bir düşüncenizi yazın.
          Sistem bu fikri en sert şekilde sorgular, çelişkilerini bulur
          ve size kendinize sormanız gereken soruları gösterir.
          <strong>Amaç sizi haklı çıkarmak değil, daha doğru düşünmenizi sağlamaktır.</strong>
        </p>
        <div class="sorgu-info-steps">
          <span class="info-step">✦ Mantık hatalarını tespit eder</span>
          <span class="info-step">✦ En güçlü karşı argümanı kurar</span>
          <span class="info-step">✦ Psikolojik kökleri analiz eder</span>
          <span class="info-step">✦ Felsefi derinlik ekler</span>
          <span class="info-step">✦ Kendinize sormanız gereken soruları üretir</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ══ GİRİŞ ALANI ══ -->
  <section class="sorgu-input-section">
    <div class="sorgu-input-card">

      <div class="sorgu-input-header">
        <div class="sorgu-input-eyebrow">Düşünceni Yaz</div>
        <div class="sorgu-char-counter">
          <span id="charCount">0</span> / 3000
        </div>
      </div>

      <!--
        textarea — maxlength ile istemci tarafında sınırlandırıldı
        Sunucu tarafında da kontrol ediliyor (api/sorgu.php)
      -->
      <textarea
        id="sorguInput"
        class="sorgu-textarea"
        placeholder="Örneğin: 'İnsanlar temelde iyidir, kötülük koşulların sonucudur.' ya da 'Para mutluluğu satın alamaz ama özgürlük satın alır.' Herhangi bir fikir, karar veya hayat görüşü yazabilirsin..."
        maxlength="3000"
        spellcheck="true"
        aria-label="Sorgulanacak düşünceni yaz"
      ></textarea>

      <div class="sorgu-input-footer">
        <div class="sorgu-warning">
          ⚠ Bu sistem seni onaylamak için değil, düşündürmek için tasarlandı.
        </div>
        <button class="sorgu-btn" id="sorguBtn" onclick="startSorgu()" type="button">
          <span class="sorgu-btn-icon">⚡</span>
          <span class="sorgu-btn-text">Düşüncemi Sorgula</span>
          <span class="sorgu-btn-shine"></span>
        </button>
      </div>

    </div>
  </section>

  <!-- ══ YÜKLEME ══ -->
  <div class="sorgu-loading" id="sorguLoading" style="display:none;">
    <div class="sorgu-loading-inner">
      <div class="sorgu-spinner"></div>
      <div class="sorgu-loading-text">Rençber düşünceni sorguluyor...</div>
      <div class="sorgu-loading-sub">Mantık hataları · Karşı argümanlar · Felsefi derinlik</div>
    </div>
  </div>

  <!-- ══ SONUÇ ══ -->
  <section class="sorgu-result" id="sorguResult" style="display:none;">
    <div class="sorgu-result-inner" id="sorguResultInner"></div>
  </section>

  <!-- Tekrar Sorgula -->
  <div class="sorgu-retry" id="sorguRetry" style="display:none;">
    <button class="sorgu-retry-btn" onclick="resetSorgu()" type="button">
      ← Yeni Bir Düşünce Sorgula
    </button>
  </div>

</div>

<script>
/* ══ Karakter sayacı ══ */
document.getElementById('sorguInput').addEventListener('input', function() {
  document.getElementById('charCount').textContent = this.value.length;
});

/* ══ Ana fonksiyon ══ */
async function startSorgu() {
  const input  = document.getElementById('sorguInput');
  const btn    = document.getElementById('sorguBtn');
  const loading = document.getElementById('sorguLoading');
  const result  = document.getElementById('sorguResult');
  const retry   = document.getElementById('sorguRetry');

  // İçeriği temizle ve kontrol et
  let text = input.value.replace(/<[^>]*>/g, '').trim(); // HTML tagları kaldır
  if (text.length < 20) {
    alert('Lütfen en az 20 karakter yazın. Fikrin ne kadar kısa olursa analiz o kadar yüzeysel olur.');
    return;
  }
  if (text.length > 3000) {
    alert('Lütfen 3000 karakterden kısa yazın.');
    return;
  }

  // UI güncelle
  btn.disabled = true;
  btn.querySelector('.sorgu-btn-text').textContent = 'Sorgulanıyor...';
  loading.style.display = 'flex';
  result.style.display  = 'none';
  retry.style.display   = 'none';

  try {
    const res = await fetch('/kitap-analiz/api/sorgu.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ dusunce: text.substring(0, 3000) })
    });

    if (!res.ok) {
      alert('Sunucu hatası: ' + res.status);
      return;
    }

    const data = await res.json();

    if (data.error) {
      alert('Hata: ' + data.error);
      return;
    }

    renderSorguResult(data.analiz);
    result.style.display = 'block';
    retry.style.display  = 'block';
    setTimeout(() => result.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);

  } catch(e) {
    alert('Bağlantı hatası. XAMPP çalışıyor mu?');
    console.error(e);
  } finally {
    btn.disabled = false;
    btn.querySelector('.sorgu-btn-text').textContent = 'Düşüncemi Sorgula';
    loading.style.display = 'none';
  }
}

/* ══ Sonucu render et ══ */
function renderSorguResult(text) {
  const container = document.getElementById('sorguResultInner');

  // Metni bölümlere ayır — Gemini ## başlıkları kullanıyor
  const sections = parseSections(text);

  let html = '<div class="sorgu-sections">';

  sections.forEach((section, i) => {
    const icon  = getSectionIcon(section.title);
    const color = getSectionColor(i);
    html += `
      <div class="sorgu-section" style="animation-delay:${i * 0.08}s">
        <div class="sorgu-section-header" style="--sec-color:${color}">
          <span class="sorgu-section-icon">${icon}</span>
          <span class="sorgu-section-title">${escHtml(section.title)}</span>
        </div>
        <div class="sorgu-section-body">${formatText(section.content)}</div>
      </div>`;
  });

  html += '</div>';
  container.innerHTML = html;
}

/* ══ Gemini cevabını bölümlere ayır ══ */
function parseSections(text) {
  const lines    = text.split('\n');
  const sections = [];
  let current    = null;

  lines.forEach(line => {
    const trimmed = line.trim();

    // ## veya **Başlık** formatını yakala
    const headerMatch = trimmed.match(/^#+\s+(.+)$/) || trimmed.match(/^\*\*(.+)\*\*\s*$/);

    if (headerMatch) {
      if (current) sections.push(current);
      current = { title: headerMatch[1].replace(/\*/g, '').trim(), content: '' };
    } else if (current) {
      current.content += line + '\n';
    } else if (trimmed) {
      // Başlık olmadan gelen içerik
      if (!sections.length) {
        current = { title: 'Analiz', content: line + '\n' };
      }
    }
  });

  if (current && current.content.trim()) sections.push(current);

  // Bölüm bulunamadıysa tümünü tek bölüm yap
  if (!sections.length) {
    sections.push({ title: 'Analiz Sonucu', content: text });
  }

  return sections;
}

/* Metni formatla — bullet, bold vs. */
function formatText(text) {
  return text
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/^[\*\-\•]\s+(.+)$/gm, '<li>$1</li>')
    .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
    .replace(/\n{2,}/g, '</p><p>')
    .replace(/^/, '<p>').replace(/$/, '</p>')
    .replace(/<p><\/p>/g, '');
}

function escHtml(str) {
  return String(str || '')
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function getSectionIcon(title) {
  const t = title.toLowerCase();
  if (t.includes('öz') || t.includes('özet')) return '🎯';
  if (t.includes('mantık') || t.includes('hata')) return '⚠️';
  if (t.includes('şeytan') || t.includes('karşı')) return '😈';
  if (t.includes('psikoloji') || t.includes('psikolojik')) return '🪞';
  if (t.includes('felsef')) return '🔍';
  if (t.includes('güçlü') || t.includes('zayıf') || t.includes('denge')) return '⚖️';
  if (t.includes('soru')) return '❓';
  if (t.includes('sonuç')) return '🧩';
  return '✦';
}

function getSectionColor(index) {
  const colors = ['#C49335','#7ABFA0','#A87AC0','#5B9BD5','#D4856A','#E8B95A','#4DA6B4','#E07090'];
  return colors[index % colors.length];
}

function resetSorgu() {
  document.getElementById('sorguInput').value = '';
  document.getElementById('charCount').textContent = '0';
  document.getElementById('sorguResult').style.display = 'none';
  document.getElementById('sorguRetry').style.display  = 'none';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>