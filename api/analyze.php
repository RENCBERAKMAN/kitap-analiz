<?php
/*
 * api/analyze.php — Gemini AI Analiz Endpoint'i
 *
 * book.php'deki JavaScript'ten POST isteğiyle çağrılır:
 *   fetch('/kitap-analiz/api/analyze.php', {
 *     method: 'POST',
 *     body: JSON.stringify({ title, author, description })
 *   })
 *
 * ── GÜVENLİK KATMANLARI ────────────────────────────────────────
 *
 * 1) SADECE POST KABUL ET
 *    Bu endpoint GET ile açılırsa hata döndür.
 *    Tarayıcıdan direkt ziyaret edilememeli.
 *
 * 2) JSON BODY BOYUT SINIRI (50KB)
 *    Saldırı: Devasa JSON body göndererek PHP belleğini doldurma.
 *    file_get_contents('php://input') önce okunur,
 *    strlen() ile boyut kontrol edilir.
 *    50KB = 51200 byte → yeterli limit (gerçek veriler çok daha küçük)
 *
 * 3) JSON PARSE DOĞRULAMA
 *    json_decode() başarısız olursa json_last_error() != JSON_ERROR_NONE.
 *    Geçersiz JSON → 400 Bad Request döndür.
 *    Bu olmadan parse hatası ilerleyen satırlarda undefined index hatası doğurur.
 *
 * 4) GİRİŞ TEMİZLEME
 *    strip_tags() → <script> gibi etiketleri kaldır
 *    trim() → boşlukları temizle
 *    mb_substr() → UTF-8 uyumlu karakter kesimi (Türkçe bozulmaz)
 *    Uzunluk limitleri:
 *      title       → 200 karakter
 *      author      → 200 karakter
 *      description → 1000 karakter (Gemini'ye çok uzun veri göndermeyelim)
 *
 * 5) ZORUNLU ALAN KONTROLÜ
 *    Kitap adı olmadan analiz anlamsız → 400 hatası döndür.
 *
 * 6) RATE LIMITING (Oran Sınırlama) — Bu projede basit tutuldu
 *    Gerçek projede: IP başına saatte X istek limiti koyulur.
 *    Session veya Redis cache ile sayaç tutulur.
 *    Aşılınca → 429 Too Many Requests döndürülür.
 *    Bu olmadan saldırgan saniyede yüzlerce Gemini isteği yaparak
 *    API kotanı tüketebilir (credential stuffing / API abuse).
 * ────────────────────────────────────────────────────────────── */

// JSON yanıt formatı
header('Content-Type: application/json; charset=utf-8');

// Güvenlik başlıkları
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

/* ── YÖNTEMİ KONTROL ET ─────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Sadece POST isteği kabul edilir']);
    exit;
}

// GeminiApi sınıfını dahil et
// Bu sınıf config/config.php'yi require eder → GEMINI_API_KEY oradan okunur
require_once __DIR__ . '/../src/Api/GeminiApi.php';

/* ── BODY'Yİ OKU VE BOYUTUNU KONTROL ET ───────────────────────
 * php://input = ham HTTP body'si
 * Normal $_POST aksine, JSON gibi içerikleri de okuyabilir
 * ────────────────────────────────────────────────────────────── */
$rawInput = file_get_contents('php://input');

// 50KB boyut limiti (51200 byte)
if (strlen($rawInput) > 51200) {
    http_response_code(413); // Request Entity Too Large
    echo json_encode(['error' => 'İstek çok büyük (maksimum 50KB)']);
    exit;
}

// JSON parse et — array olarak al
$input = json_decode($rawInput, true);

// Parse başarısız mı?
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Geçersiz JSON formatı: ' . json_last_error_msg()]);
    exit;
}

// Null geldiyse de kontrol et
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz istek formatı']);
    exit;
}

/* ── GİRİŞ VERİLERİNİ TEMİZLE ──────────────────────────────────
 * Null coalescing operatörü (??) → alan yoksa boş string kullan
 * strip_tags → HTML etiketlerini kaldır
 * trim → boşlukları kır
 * mb_substr → UTF-8 uyumlu kesim (Türkçe karakter güvenli)
 * ────────────────────────────────────────────────────────────── */
$title       = mb_substr(trim(strip_tags($input['title']       ?? '')), 0, 200,  'UTF-8');
$author      = mb_substr(trim(strip_tags($input['author']      ?? '')), 0, 200,  'UTF-8');
$description = mb_substr(trim(strip_tags($input['description'] ?? '')), 0, 1000, 'UTF-8');

// Kitap adı zorunlu alan
if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Kitap adı zorunludur']);
    exit;
}

/* ── GEMİNİ API ÇAĞRISI ─────────────────────────────────────────
 * GeminiApi → GEMINI_API_KEY'i config.php'den alır
 * analyzeBook() cURL ile Google'ın Gemini API'sine istek atar
 * Prompt'u gönderir, gelen metni parse eder, dizi olarak döndürür
 * ────────────────────────────────────────────────────────────── */
$gemini = new GeminiApi();
$analiz = $gemini->analyzeBook($title, $author, $description);

// Sonucu JSON olarak döndür
// JSON_UNESCAPED_UNICODE → Türkçe karakterler bozulmadan yazılır
echo json_encode($analiz, JSON_UNESCAPED_UNICODE);