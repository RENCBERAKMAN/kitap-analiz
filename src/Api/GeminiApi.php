<?php
require_once __DIR__ . '/../../config/config.php';

class GeminiApi {

    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function analyzeBook($title, $author, $description) {

        $prompt = "Sen bir kitap eleştirmenisin. Aşağıdaki kitabı analiz et:

Kitap: {$title}
Yazar: {$author}
Açıklama: {$description}

Lütfen şu formatta yanıtla - başka hiçbir şey yazma:

KARAR: [OKUMALSIN veya OKUMAMALISIN]
OZET: [Kitabın ana fikrini 2 cümleyle anlat, çarpıcı bir dille]
KAZANIM_1: [En önemli kazanım]
KAZANIM_2: [İkinci kazanım]
KAZANIM_3: [Üçüncü kazanım]
ZORLUK_1: [Okuyucuyu zorlayabilecek şey]
ZORLUK_2: [İkinci zorluk]
KIME_UYGUN: [Hangi okuyucu profili için ideal]
KIME_UYGUN_DEGIL: [Kimler için uygun değil]
ALINTI: [Kitabın ruhunu yansıtan kısa özgün söz, tırnak içinde]";

        $data = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.8,
                'maxOutputTokens' => 2000,
            ]
        ]);

        $url = $this->apiUrl . '?key=' . GEMINI_API_KEY;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            return ['hata' => 'API hatası: ' . $httpCode, 'detay' => json_decode($response, true)];
        }

        $result = json_decode($response, true);
        $text   = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            return ['hata' => 'Boş cevap geldi'];
        }

        return $this->parseText($text);
    }

    private function parseText($text) {
    $result = [
        'karar'            => '',
        'ozet'             => '',
        'kazanimlar'       => [],
        'zorluklar'        => [],
        'kime_uygun'       => '',
        'kime_uygun_degil' => '',
        'alinti'           => '',
        'ham_metin'        => $text  // debug için
    ];

    $lines = explode("\n", $text);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (strpos($line, ':') === false) continue;

        [$key, $value] = explode(':', $line, 2);
        
        // Büyük harf, küçük harf, Türkçe karakter farkını ortadan kaldır
        $key = trim($key);
        $key = mb_strtoupper($key, 'UTF-8');
        
        // ** işaretlerini temizle (Gemini bazen bold yapıyor)
        $key   = str_replace(['*', '#', ' '], ['', '', '_'], $key);
        $value = trim($value);
        $value = str_replace('*', '', $value);

        switch ($key) {
            case 'KARAR':             $result['karar'] = $value; break;
            case 'OZET':
            case 'ÖZET':              $result['ozet'] = $value; break;
            case 'KAZANIM_1':
            case 'KAZANIM_2':
            case 'KAZANIM_3':         $result['kazanimlar'][] = $value; break;
            case 'ZORLUK_1':
            case 'ZORLUK_2':          $result['zorluklar'][] = $value; break;
            case 'KIME_UYGUN':
            case 'KİME_UYGUN':        $result['kime_uygun'] = $value; break;
            case 'KIME_UYGUN_DEGIL':
            case 'KİME_UYGUN_DEĞİL':  $result['kime_uygun_degil'] = $value; break;
            case 'ALINTI':
            case 'ALINTI':            $result['alinti'] = $value; break;
        }
    }

    return $result;
}
}