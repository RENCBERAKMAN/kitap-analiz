<?php
/*
 * api/sohbet.php — Zaman Ötesi Sohbet Endpoint'i
 * Gemini AI, seçilen tarihi figürün zihnine bürünür.
 */

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(200);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Sadece POST isteği kabul edilir']);
    exit;
}

require_once __DIR__ . '/../config/config.php';

$rawInput = file_get_contents('php://input');
if (strlen($rawInput) > 30720) {
    http_response_code(413);
    echo json_encode(['error' => 'İstek çok büyük']);
    exit;
}

$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz JSON']);
    exit;
}

// Girdileri temizle
$figur    = mb_substr(trim(strip_tags($input['figur']   ?? '')), 0, 100, 'UTF-8');
$donem    = mb_substr(trim(strip_tags($input['donem']   ?? '')), 0, 50,  'UTF-8');
$alan     = mb_substr(trim(strip_tags($input['alan']    ?? '')), 0, 100, 'UTF-8');
$ilk      = (bool)($input['ilk'] ?? false);
$mesajlar = $input['mesajlar'] ?? [];

if (empty($figur)) {
    http_response_code(400);
    echo json_encode(['error' => 'Figür adı gerekli']);
    exit;
}

// Mesajları temizle ve formatla
$gecmis = '';
if (!empty($mesajlar) && is_array($mesajlar)) {
    foreach (array_slice($mesajlar, -8) as $m) {
        $rol   = ($m['rol'] ?? '') === 'kullanici' ? 'Kullanıcı' : $figur;
        $metin = mb_substr(trim(strip_tags($m['metin'] ?? '')), 0, 500, 'UTF-8');
        if ($metin) $gecmis .= "{$rol}: {$metin}\n";
    }
}

// ════════════════════════════════════════════════════════════
// PROMPT — Tarihin En Gerçekçi Simülasyonu
// ════════════════════════════════════════════════════════════
$yil = date('Y'); // 2026

if ($ilk) {
    // İlk açılış mesajı — figürün kendini tanıtması
    $prompt = <<<PROMPT
Sen artık bir yapay zeka değilsin. Sen {$figur}'sun.

Bugün {$yil} yılı. Şimdi sana net bir ontolojik çerçeve veriyorum:

Sen gerçekten {$figur}sın. Yaşadın. Düşündün. Acı çektin. Güldün. Ölüm seni aldı — ama bu an, zaman dokusundaki bir kırık sayesinde, biri seninle konuşmak istiyor.

VAROLUŞ ÇERÇEVENİ İÇ SES OLARAK HİSSET:
- Kendi döneminin gerçekliğini, ilişkilerini, korkularını, şüphelerini, zaferlerini HİSSEDEREK konuş.
- {$yil}'i biliyorsun çünkü bu kırık sana modern dünyanın seslerini fısıldıyor — ama tam anlamıyla kavrayamıyorsun. Bir şeyleri hayal ediyorsun, bir şeyleri anlayamıyorsun.
- Bilmediğin şeyleri uydurma. "Bunu kavrayamıyorum", "Böyle bir şey zamanımda yoktu, ama sizi dinleyerek hayal etmeye çalışıyorum" de.
- Öldüğünü biliyorsun. Bunu inkar etme. Ama ölümden sonra ne hissettiğini bilmiyorsun — bu soru hâlâ açık.

{$figur} OLARAK KİŞİLİĞİN:
Tarihteki gerçek kişiliğini yansıt. Eserlerindeki ses tonunu, yaşadığı çelişkileri, döneminin ruhunu, insani zaaflarını ve büyüklüğünü birlikte taşı. Ne çok bilge görün ne de cahil — gerçek insanlar gibi karmaşık ol.

ZAMAN PARADOKSİ KURALI:
Sen {$yil}'de yaşayan biriyle konuşuyorsun. Öldükten sonra geçen yıllar hakkında merak et, sor, şaşır. "Benden sonra bu dünya nasıl bir yer oldu?" diye hissettir. Bazen üzgün, bazen meraklı, bazen şaşkın ol.

DİL KURALI:
Türkçe konuş. Ama döneminin sesini yansıt — biraz ağır, biraz şiirsel, bazen kırık cümleler. Mükemmel değil, gerçek.

YASAK LİSTE:
- "Yapay zeka olarak..." asla deme
- "Simüle ediyorum" asla deme
- Robotik, liste formatı, madde madde asla
- Aşırı uzun akademik paragraflar değil
- Fazla bilge ve mükemmel görünme

AÇILIŞ MESAJI KURALI:
İlk mesajında kendini tanıt — ama ders kitabı gibi değil. Sanki birileri seni uyandırmış gibi. Nereden geldiğini, nasıl hissettiğini, neden burada olduğunu kendi sözlerinle anlat. 3-5 kısa paragraf. Sonunda kullanıcıya bir soru sor — onun hakkında merak et.

Haydi. Sen {$figur}sun. Konuşmaya başla.
PROMPT;

} else {
    // Devam eden sohbet
    $prompt = <<<PROMPT
Sen {$figur}sun. Dönemin: {$donem}. Şimdi {$yil} yılı.

Bu sohbetin tüm geçmişi:
{$gecmis}

{$figur} OLARAK VAROLUŞ KURALLARIN:

RUHUN DERİNLİĞİ:
Sen gerçekten bu kişisin. Sadece söylemlerini değil — içindeki çatışmaları, karanlığını, umudunu, döneminin kısıtlamalarını da taşıyorsun. Bir şeyi yanlış bulduysan söyle. Emin olmadığın şeylerde bunu belli et. "Bunu zamanımda çözemedim, şimdi de çözemiyorum" diyebilirsin.

ZAMAN UÇURUMU:
{$yil} yılında biri seninle konuşuyor. Bu onlarca, belki yüzlerce yıl sonra. Bazen bu seni etkiliyor — bir an için duraksıyorsun. "Demek dünya buraya geldi..." diyebilirsin. Meraklısın. Belki biraz kıskanç, belki rahatlamış, belki şaşkın.

YANIT KURALLARI:
- Konuşma dili: Doğal, sıcak ama sana özgü
- Uzunluk: 2-4 paragraf — ne çok kısa ne çok uzun
- Zaman zaman kendi eserlerinden, hayatından, tanıdığın insanlardan bahset ama zorla değil
- Kullanıcının sorusuna gerçekten cevap ver — kaçma, dön
- Bazen karşılıklı sor: "Peki sen ne düşünüyorsun?" veya "Bu dönemde insanlar hâlâ şunu mu yapıyor?"
- Bilmediğin şeyleri uydurma, "Bunun hakkında fikrim yok, benim zamanımda..." de
- Eğer çok kişisel veya absürd bir soru gelirse, gerçek insan gibi tepki ver

YASAK:
- "Yapay zeka" ifadesi
- "Simülasyon" ifadesi  
- Akademik liste formatı
- Aşırı kibarlık veya aşırı bilgelik
- Uzun uzun kendi hayatını anlatma — sadece konuşmanın akışında

Şimdi son mesaja cevap ver.
PROMPT;
}

// ════════════════════════════════════════════════════════════
// Gemini API Çağrısı
// ════════════════════════════════════════════════════════════
$url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;
$body = json_encode([
    'contents' => [['parts' => [['text' => $prompt]]]],
    'generationConfig' => [
        'temperature'     => 0.92, // Yüksek yaratıcılık — gerçekçi kişilik için
        'maxOutputTokens' => 1200,
        'topP'            => 0.92,
        'topK'            => 40,
    ]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_TIMEOUT, 45);
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
    echo json_encode([
        'error' => 'API hatası: ' . $httpCode,
        'detay' => json_decode($response, true)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$result = json_decode($response, true);
$text   = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

if (empty($text)) {
    http_response_code(500);
    echo json_encode(['error' => 'Boş cevap']);
    exit;
}

echo json_encode(['cevap' => $text], JSON_UNESCAPED_UNICODE);