# 📚 Midnight Library

> *Bir kitabı okumadan önce gerçekten tanımak için.*

Midnight Library, herhangi bir kitap için yapay zeka destekli analiz sunan bir PHP web uygulamasıdır. Google Books API'den kitap verilerini çeker, Gemini AI ile derinlemesine analiz eder ve kullanıcıya net bir "oku / okuma" kararı sunar.

---

## ✦ Özellikler

- **Akıllı Arama** — Google Books API ile milyonlarca kitap arasında anlık arama
- **AI Analizi** — Gemini 2.5 Flash ile her kitap için özgün, kalıplaşmamış analiz
- **Oku / Okuma Kararı** — Net ve gerekçeli öneri
- **Kazanım & Zorluk Haritası** — Kitabın sana ne katacağı ve seni nerede zorlayacağı
- **Okuma Süresi** — Sayfa sayısına göre hesaplanmış tahmini süre
- **Kapak Fotoğrafı** — Her kitap için otomatik kapak görseli
- **Benzer Kitaplar** — Aynı kategoriden otomatik öneri
- **Dosya Tabanlı Cache** — Aynı kitap tekrar aratılınca API'ye gitmez

---

## 🛠 Teknoloji Yığını

| Katman | Teknoloji |
|---|---|
| Backend | PHP 8+ |
| HTTP İstekleri | cURL |
| Kitap Verisi | Google Books API |
| AI Analiz | Google Gemini 2.5 Flash |
| Frontend | HTML5, CSS3, Vanilla JS |
| Tipografi | Playfair Display, Crimson Pro, JetBrains Mono |
| Sunucu | Apache (XAMPP) |

---

## 📂 Klasör Yapısı

```
kitap-analiz/
│
├── index.php              ← Ana sayfa (arama)
├── book.php               ← Kitap detay & analiz sayfası
│
├── config/
│   ├── config.php         ← API key'ler (Git'e gitmiyor!)
│   └── config.example.php ← Örnek config dosyası
│
├── src/
│   ├── Api/
│   │   ├── GoogleBooksApi.php   ← Google Books API sınıfı
│   │   └── GeminiApi.php        ← Gemini AI API sınıfı
│   └── Cache/
│       └── FileCache.php        ← Dosya tabanlı önbellek
│
├── api/                   ← AJAX endpoint'leri
│   ├── search.php         ← Kitap arama
│   ├── analyze.php        ← AI analiz
│   └── trending.php       ← Popüler kitaplar
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── assets/
│   ├── css/style.css
│   └── js/app.js
│
└── cache/                 ← Önbellek (Git'e gitmiyor)
    └── .gitkeep
```

---

## 🚀 Kurulum

### 1. Projeyi İndir

```bash
git clone https://github.com/KULLANICI_ADIN/kitap-analiz.git
```

### 2. XAMPP'a Taşı

Klasörü `C:\xampp\htdocs\` içine kopyala.

### 3. API Key'leri Al

**Google Books API:**
1. [console.cloud.google.com](https://console.cloud.google.com) → Yeni proje oluştur
2. Kütüphane → "Books API" → Etkinleştir
3. Kimlik Bilgileri → API Anahtarı oluştur → Kopyala

**Gemini API:**
1. [aistudio.google.com](https://aistudio.google.com) → Giriş yap
2. Sol menü → "Get API Key" → "Create API Key" → Kopyala

### 4. Config Dosyasını Oluştur

```bash
cp config/config.example.php config/config.php
```

`config/config.php` dosyasını aç ve key'leri yapıştır:

```php
define('GOOGLE_BOOKS_API_KEY', 'BURAYA_GOOGLE_BOOKS_KEY');
define('GEMINI_API_KEY',       'BURAYA_GEMINI_KEY');
```

### 5. XAMPP Başlat

Apache'yi başlat → Tarayıcıda aç:

```
http://localhost/kitap-analiz
```

---

## 🔌 API Nasıl Çalışır?

Bu proje iki farklı API ile iletişim kurar. Her ikisi de PHP'nin `cURL` kütüphanesi kullanılarak çağrılır.

```
[Kullanıcı Arama Yapar]
        │
        ▼
[api/search.php]
        │  cURL → GET isteği
        ▼
[Google Books API]
        │  JSON cevap döner
        ▼
[GoogleBooksApi.php parse eder]
        │  Temiz dizi döner
        ▼
[Tarayıcıda kitap kartları görünür]
        │
        │ [Kullanıcı "Analiz Et" basar]
        ▼
[api/analyze.php]
        │  cURL → POST isteği + prompt
        ▼
[Google Gemini API]
        │  AI analizi döner
        ▼
[GeminiApi.php parse eder]
        │  Yapılandırılmış analiz
        ▼
[Ekranda analiz kartı görünür]
```

**GET vs POST farkı:**
- Google Books → `GET` — parametreler URL'de gider (`?q=harry+potter`)
- Gemini → `POST` — veri body'de JSON olarak gider (prompt çok uzun olduğu için)

---

## 🎨 Tasarım Felsefesi

**"Midnight Library"** teması bilinçli renk psikolojisine dayanır:

| Renk | Hex | Psikolojik Etki |
|---|---|---|
| Sıcak Siyah | `#0f0e17` | Odak, derinlik, göz yorgunluğunu azaltır |
| Amber / Altın | `#f4a261` | Merak, sıcaklık, yaratıcılık uyarır |
| Krem Beyaz | `#fffffe` | Soğuk beyaza göre daha az yorucu |
| Derin Bordo | `#e63946` | Kritik bilgileri vurgular, dikkat çeker |

Tipografi: `Playfair Display` (başlık) + `Crimson Pro` (metin) + `JetBrains Mono` (teknik etiket)

---

## 📸 Ekran Görüntüleri

> *(Proje tamamlandıktan sonra eklenecek)*

---

## 🗺 Gelecek Planlar

- [ ] MySQL ile arama geçmişi ve istatistik
- [ ] Kullanıcı kayıt / giriş sistemi
- [ ] Kitap listesi oluşturma ("Okuyacaklarım")
- [ ] Çoklu dil desteği
- [ ] PWA desteği (mobil uygulama gibi çalışsın)

---

## 👨‍💻 Geliştirici

**Rencber Akman**
Akdeniz Üniversitesi 

---

## 📄 Lisans

MIT License — Dilediğin gibi kullanabilirsin.
