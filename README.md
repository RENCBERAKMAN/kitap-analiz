<div align="center">

# 📚 Midnight Library

**AI-powered book analysis platform — should you read it or not?**

*Google Books API · Gemini 2.5 Flash · PHP · MySQL*

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)
![Gemini](https://img.shields.io/badge/Gemini-2.5_Flash-4285F4?style=flat-square&logo=google&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

</div>

---

## What is Midnight Library?

Midnight Library is a PHP web application that helps you decide whether a book is worth reading **before you open it**. It fetches book data from Google Books API and uses Gemini AI to generate a deep, original analysis — not a generic summary, but a real opinion.

---

## ✦ Features

| Feature | Description |
|---|---|
| 🔍 Smart Search | Instant search across millions of books via Google Books API |
| 🤖 AI Analysis | Gemini 2.5 Flash generates unique analysis for every book |
| ✦ Read / Skip Decision | Clear, reasoned recommendation |
| 📊 Gain & Challenge Map | What you'll gain and what might be difficult |
| ⏱ Reading Time | Estimated reading time calculated from page count |
| 🖼 Cover Images | Automatic book cover display |
| 📚 Similar Books | Auto-suggestions from the same category |
| 🔥 Trending Books | Most viewed books tracked via MySQL |
| 🧩 Compatibility Quiz | 5-question quiz to calculate your match percentage |
| ⚡ Thought Challenger | Write any idea — Gemini challenges it philosophically |
| 🔒 Security | XSS protection, SQL injection prevention, input sanitization |

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 7.4+ |
| HTTP Requests | cURL |
| Book Data | Google Books API (free) |
| AI Analysis | Google Gemini 2.5 Flash (free) |
| Database | MySQL via PDO |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Typography | Cormorant Garamond, Lora, JetBrains Mono |
| Server | Apache (XAMPP) |

---

## 📂 Project Structure

```
kitap-analiz/
│
├── index.php                    ← Homepage (search)
├── book.php                     ← Book detail & AI analysis
├── sorgu.php                    ← Thought challenger page
│
├── config/
│   ├── config.php               ← API keys (NOT in Git)
│   └── config.example.php       ← Example config file
│
├── src/
│   ├── Api/
│   │   ├── GoogleBooksApi.php   ← Google Books API class
│   │   └── GeminiApi.php        ← Gemini AI API class
│   ├── Cache/
│   │   └── FileCache.php        ← File-based cache
│   └── Database/
│       └── Database.php         ← MySQL PDO class
│
├── api/                         ← AJAX endpoints
│   ├── search.php               ← Book search
│   ├── analyze.php              ← AI book analysis
│   ├── trending.php             ← Trending books
│   └── sorgu.php                ← Thought analysis
│
├── includes/
│   ├── header.php
│   └── footer.php
│
├── assets/
│   ├── css/style.css
│   └── js/app.js
│
├── cache/                       ← Cache files (NOT in Git)
│   └── .gitkeep
│
├── database.sql                 ← Database setup script
└── .gitignore
```

---

## 🎨 Design Philosophy

**"Midnight Library"** — the theme is built on color psychology for focused reading:

<<<<<<< HEAD
```bash
git clone https://github.com/RENCBERAKMAN/kitap-analiz.git
=======
| Color | Hex | Psychological Effect |
|---|---|---|
| Warm Black | `#0C1710` | Focus, depth, reduces eye strain |
| Amber / Gold | `#C49335` | Curiosity, warmth, creativity |
| Parchment | `#EDE0C4` | Comfort, belonging, warmth |
| Ember Red | `#A84838` | Highlights critical info |

---

## 🔌 How the API Works

```
[User searches a book]
        │
        ▼
[api/search.php]          ← AJAX GET request
        │  cURL → GET
        ▼
[Google Books API]        ← Returns JSON
        │
        ▼
[Book cards rendered]
        │
[User clicks "Analyze"]
        │
        ▼
[api/analyze.php]         ← AJAX POST request
        │  cURL → POST + prompt
        ▼
[Gemini 2.5 Flash]        ← Returns AI analysis
        │
        ▼
[Analysis card rendered]
>>>>>>> 88f09ecd6a1264cd8acc8de34ffe0d1571123f7c
```

**GET vs POST:**
- Google Books → `GET` — parameters in URL (`?q=harry+potter`)
- Gemini → `POST` — data sent in JSON body (prompt is too long for URL)

---

# 🚀 Kurulum / Installation

## 🇬🇧 English

### 1. Clone the repository
```bash
git clone https://github.com/RENCBERAKMAN/kitap-analiz.git
```

### 2. Move to XAMPP
Copy the folder to `C:\xampp\htdocs\kitap-analiz`

### 3. Get API Keys

**Google Books API:**
1. Go to [console.cloud.google.com](https://console.cloud.google.com)
2. Create a new project
3. Library → search "Books API" → Enable
4. Credentials → Create API Key → Copy

**Gemini API:**
1. Go to [aistudio.google.com](https://aistudio.google.com)
2. Sign in with Google
3. Left menu → "Get API Key" → "Create API Key" → Copy

### 4. Create config file
```bash
cp config/config.example.php config/config.php
```
Open `config/config.php` and paste your keys:
```php
define('GOOGLE_BOOKS_API_KEY', 'YOUR_KEY_HERE');
define('GEMINI_API_KEY',       'YOUR_KEY_HERE');
```

### 5. Set up the database
1. Start XAMPP → Start Apache + MySQL
2. Go to `http://localhost/phpmyadmin`
3. Click "New" → name it `kitap_analiz` → Create
4. Click SQL tab → paste contents of `database.sql` → Go

### 6. Run
```
http://localhost/kitap-analiz
```

---

## 🇹🇷 Türkçe

### 1. Projeyi indir
```bash
git clone https://github.com/RENCBERAKMAN/kitap-analiz.git
```

### 2. XAMPP'a taşı
Klasörü `C:\xampp\htdocs\kitap-analiz` içine kopyala.

### 3. API Key'leri al

**Google Books API:**
1. [console.cloud.google.com](https://console.cloud.google.com) adresine git
2. Yeni proje oluştur
3. Kütüphane → "Books API" yaz → Etkinleştir
4. Kimlik Bilgileri → API Anahtarı Oluştur → Kopyala

**Gemini API:**
1. [aistudio.google.com](https://aistudio.google.com) adresine git
2. Google hesabınla giriş yap
3. Sol menü → "Get API Key" → "Create API Key" → Kopyala

### 4. Config dosyasını oluştur
```bash
cp config/config.example.php config/config.php
```
`config/config.php` dosyasını aç ve key'leri yapıştır:
```php
define('GOOGLE_BOOKS_API_KEY', 'KEY_BURAYA');
define('GEMINI_API_KEY',       'KEY_BURAYA');
```

### 5. Veritabanını kur
1. XAMPP aç → Apache ve MySQL'i başlat
2. `http://localhost/phpmyadmin` adresine git
3. "Yeni" → isim: `kitap_analiz` → Oluştur
4. SQL sekmesi → `database.sql` içeriğini yapıştır → Git

### 6. Çalıştır
```
http://localhost/kitap-analiz
```

---

## 👨‍💻 Developer

**Rençber Akman**
Akdeniz Üniversitesi — 



---

## 📄 License

MIT License — Free to use however you like.
