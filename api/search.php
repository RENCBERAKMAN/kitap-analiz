<?php
/*
 * api/search.php — Kitap Arama Endpoint'i
 *
 * JavaScript'ten fetch() ile çağrılır:
 *   fetch('/kitap-analiz/api/search.php?q=Harry Potter')
 *
 * ── GÜVENLİK KATMANLARI ────────────────────────────────────────
 *
 * 1) QUERY STRING TEMİZLEME (Input Sanitization)
 *    Saldırı: ?q=<script>alert('xss')</script>
 *    strip_tags → HTML etiketlerini tamamen çıkarır
 *    htmlspecialchars → < > " ' & karakterlerini kaçırır
 *    Sonuç: Script çalışamaz, yalnızca metin kalır.
 *
 * 2) REGEX DOĞRULAMA (Whitelist approach)
 *    "Güvenli ne?" yerine "Bu izin verildi mi?" diye soruyoruz.
 *    Sadece harf, rakam, boşluk, Türkçe karakter ve temel noktalamaya izin ver.
 *    Bunun dışında herhangi bir karakter gelirse → 400 hatası döndür.
 *    Bu yöntem "whitelist" güvenliğidir — blacklist'ten çok daha güvenli.
 *    (Blacklist = bilinen tehlikelileri engelle — ama bilinmeyeni ıskalayabilirsin)
 *    (Whitelist = sadece bilinen güvenlilere izin ver — çok daha sağlam)
 *
 * 3) UZUNLUK SINIRI (Denial of Service önlemi)
 *    Saldırı: 10.000 karakterlik sorgu göndererek sunucuyu yavaşlatma.
 *    100 karakter sınırı bu saldırıyı etkisiz kılar.
 *
 * 4) SADECE GET KABUL ET
 *    Bu endpoint'e POST, PUT, DELETE gibi yöntemlerle erişmeyi engelle.
 *    Yöntem kısıtlaması saldırı yüzeyini daraltır.
 *
 * 5) X-Content-Type-Options: nosniff
 *    MIME sniffing saldırısına karşı koruma.
 *    Tarayıcı, dosyayı Content-Type'a göre yorumlar (tahmine değil).
 * ────────────────────────────────────────────────────────────── */

// JSON yanıt başlığı
header('Content-Type: application/json; charset=utf-8');

// Güvenlik başlıkları
header('X-Content-Type-Options: nosniff');

// Bu endpoint GET dışında bir yöntemle çağrılabilir olmamalı
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Sadece GET isteği kabul edilir']);
    exit;
}

// GoogleBooksApi sınıfını dahil et
// Bu sınıf kendi içinde config/config.php'yi require eder → GOOGLE_BOOKS_API_KEY oradan gelir
require_once __DIR__ . '/../src/Api/GoogleBooksApi.php';

/* ── GİRİŞ DOĞRULAMA ─────────────────────────────────────────── */

// isset kontrolü — $_GET['q'] yoksa boş string ata
$query = isset($_GET['q']) ? $_GET['q'] : '';

// Adım 1: Baştaki ve sondaki boşlukları kır
$query = trim($query);

// Adım 2: HTML etiketlerini kaldır
// "<script>alert()</script>" → "alert()"
$query = strip_tags($query);

// Adım 3: HTML özel karakterlerini güvenli hale getir
// ENT_QUOTES → hem tek tırnak hem çift tırnak
// 'UTF-8' → Türkçe karakterler bozulmaz
$query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

// Adım 4: Uzunluk kontrolü (DoS saldırısı önlemi)
if (mb_strlen($query) > 100) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Arama terimi çok uzun (maksimum 100 karakter)']);
    exit;
}

// Adım 5: Boş sorgu kontrolü
if (empty($query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Arama terimi boş olamaz']);
    exit;
}

/*
 * Adım 6: Whitelist karakter doğrulaması (En kritik adım)
 *
 * \p{L} → Unicode harf (Türkçe ğüşıöç dahil TÜM dillerin harfleri)
 * \p{N} → Unicode rakam (0-9)
 * \s    → Boşluk, tab, satır sonu
 * \-_\.\,\'\" → Tire, alt çizgi, nokta, virgül, tırnak
 * /u flag → Unicode desteği (Türkçe için zorunlu)
 *
 * Bu regex dışındaki HER karakter reddedilir.
 * Örnekler:
 *   "Harry Potter" → geçer ✓
 *   "1984"         → geçer ✓
 *   "Suç ve Ceza"  → geçer ✓ (Türkçe ç, ü)
 *   "<script>"     → strip_tags zaten temizler ama extra güvenlik
 *   "SELECT *"     → 'SELECT' harfler → geçer ama bu API sorgu değil,
 *                    Google Books'a gönderilir, zarar veremez
 */
if (!preg_match('/^[\p{L}\p{N}\s\-_\.\,\'\"]+$/u', $query)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz karakter içeriyor. Lütfen yalnızca harf ve rakam kullanın.']);
    exit;
}

/* ── API ÇAĞRISI ─────────────────────────────────────────────── */

// GoogleBooksApi sınıfı → GOOGLE_BOOKS_API_KEY'i config.php'den okur
// cURL ile Google'a HTTP isteği atar, JSON döndürür
$api   = new GoogleBooksApi();
$books = $api->search($query);

// PHP dizisini JSON'a çevir ve döndür
// JSON_UNESCAPED_UNICODE → "S\u00fc\u00e7" yerine "Suç" yazar (okunabilir)
echo json_encode($books, JSON_UNESCAPED_UNICODE);