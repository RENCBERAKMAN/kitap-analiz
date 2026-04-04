<?php
/*
 * api/sorgu.php — Düşünce Sorgulama Endpoint'i
 *
 * sorgu.php'deki JavaScript'ten POST isteğiyle çağrılır.
 * GeminiApi sınıfı config.php'deki GEMINI_API_KEY'i kullanır.
 *
 * Güvenlik:
 * - Sadece POST kabul et
 * - JSON boyut sınırı (20KB)
 * - Giriş temizleme ve uzunluk kontrolü
 * - HTML/script injection koruması
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Sadece POST kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Sadece POST isteği kabul edilir']);
    exit;
}

// config.php'yi dahil et — GEMINI_API_KEY buradan gelir
require_once __DIR__ . '/../config/config.php';

// Body'yi oku ve boyutunu kontrol et
$rawInput = file_get_contents('php://input');
if (strlen($rawInput) > 20480) { // 20KB limit
    http_response_code(413);
    echo json_encode(['error' => 'İstek çok büyük']);
    exit;
}

// JSON parse et
$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz JSON formatı']);
    exit;
}

// Girişi temizle
// strip_tags → HTML etiketlerini kaldır
// trim → baş/son boşlukları kaldır
// mb_substr → UTF-8 uyumlu kesim
$dusunce = mb_substr(trim(strip_tags($input['dusunce'] ?? '')), 0, 3000, 'UTF-8');

// Uzunluk kontrolü
if (mb_strlen($dusunce) < 20) {
    http_response_code(400);
    echo json_encode(['error' => 'Düşünce en az 20 karakter olmalı']);
    exit;
}

// ── GEMINI API ÇAĞRISI ──
// Prompt: Felsefi, psikolojik, mantıksal derin analiz
$prompt = "Sen üst düzey bir eleştirel düşünme uzmanı, filozof, psikolog ve mantık analistisin. Görevin, kullanıcının yazdığı düşünceyi körü körüne onaylamak değil; onu en sert ama en adil şekilde test etmek.

Bu sistemin amacı: Kullanıcıyı haklı çıkarmak değil, kullanıcıyı daha doğru düşünmeye zorlamak.

Analiz edilecek düşünce:
\"\"\"
{$dusunce}
\"\"\"

Lütfen aşağıdaki başlıkları kullanarak DETAYLI bir analiz yap. Her bölüm için Türkçe yaz:

## 🧠 Düşüncenin Özü
Kullanıcının ana iddiasını tek cümlede özetle. Sonra gizli kabullerini ve alt varsayımları belirle. Kullanıcının farkında olmadan ne varsaydığını ortaya çıkar.

## ⚠️ Mantık Hataları
Şunlara özellikle bak: aşırı genelleme, nedensellik hatası, kanıt eksikliği, duygusal çıkarım, seçici algı (confirmation bias), çelişkiler. Her hatayı açıkla ve metinden somut örnek ver. Eğer hata yoksa dürüstçe belirt.

## 😈 Şeytanın Avukatı — En Güçlü Karşı Argüman
Kullanıcının fikrine karşı gerçekten çürütebilecek seviyede güçlü bir argüman kur. Zayıf değil, bilimsel, psikolojik ve felsefi dayanaklarla desteklenmiş olsun.

## 🪞 Psikolojik Analiz
Bu düşünce hangi zihinsel eğilimlerden kaynaklanıyor olabilir? Korku, ego, deneyim yanlılığı, travma, sosyal etki... Kullanıcı neden böyle düşünüyor olabilir? Yargılamadan ama dürüstçe analiz et.

## 🔍 Felsefi Derinlik
Bu düşünce hangi büyük felsefi soruya giriyor? Gerçeklik, bilgi, özgür irade, ahlak, varoluş... Kısa ama derinlikli bir perspektif ver. Hangi filozoflar benzer/karşı argümanlar öne sürmüş?

## ⚖️ Güçlü ve Zayıf Yönler
Güçlü tarafları: Neden mantıklı olabilir, hangi şartlarda doğru? Zayıf tarafları: Nerede çöküyor, hangi durumda geçersiz kalır?

## ❓ Kendinize Sormanız Gereken Sorular
Bu en kritik bölüm. Minimum 7, maksimum 10 güçlü ve kişisel soru üret. Örnek sorular: 'Bu düşüncem yanlış olsaydı bunu nasıl anlardım?', 'Hangi kanıt fikrimi değiştirirdi?', 'Bu fikir bana mı ait yoksa çevremden mi geldi?' Ama bunları kopyalama, bu düşünceye ÖZEL sorular üret.

## 🧩 Sonuç — Dengeli Değerlendirme
'Haklısın' veya 'yanlışsın' deme. Bunun yerine: Bu düşünce hangi şartlarda mantıklı, hangi şartlarda zayıf kalıyor? Kullanıcıya nasıl daha güçlü bir versiyonunu geliştirebileceğini göster.

KURALLAR:
- Asla körü körüne onaylama yapma
- Asla boş motivasyon konuşması yapma  
- Sert ol ama aşağılayıcı olma
- Her bölümü gerçekten doldur, boş geçme
- Türkçe yaz, akıcı ve anlaşılır ol";

// cURL ile Gemini API'ye istek at
// GEMINI_API_KEY config/config.php'den geliyor
$url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;
$body = json_encode([
    'contents' => [['parts' => [['text' => $prompt]]]],
    'generationConfig' => [
        'temperature'     => 0.85, // Biraz yaratıcı ama tutarlı
        'maxOutputTokens' => 4000, // Uzun analiz için
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Uzun analiz için 60 saniye
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($body)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$response || $httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Gemini API hatası: ' . $httpCode]);
    exit;
}

$result = json_decode($response, true);
$text   = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty($text)) {
    http_response_code(500);
    echo json_encode(['error' => 'Boş cevap geldi']);
    exit;
}

// Başarılı sonucu döndür
echo json_encode(['analiz' => $text], JSON_UNESCAPED_UNICODE);