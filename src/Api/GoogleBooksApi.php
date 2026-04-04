<?php
// config.php'yi dahil ediyoruz - API key'lerimiz orada tanımlı
require_once __DIR__ . '/../../config/config.php';

// CLASS = bir nesnenin şablonu
// GoogleBooksApi sınıfı = "Google Books ile konuşmayı bilen bir nesne"
class GoogleBooksApi {

    // Bu sınıfa ait sabit değer - Google Books'un temel adresi
    // private = sadece bu sınıfın içinden erişilebilir
    private $baseUrl = 'https://www.googleapis.com/books/v1/volumes';

    // ------------------------------------------------
    // PUBLIC FONKSİYONLAR - dışarıdan çağrılabilir
    // ------------------------------------------------

    // Kitap arama fonksiyonu
    // $query = kullanıcının arama kutusuna yazdığı metin
    public function search($query) {

        // trim() = başındaki ve sonundaki boşlukları siler
        // "  harry potter  " → "harry potter"
        $query = trim($query);

        // URL'yi parça parça oluşturuyoruz
        // urlencode() = boşlukları ve özel karakterleri URL'e uygun hale getirir
        // "harry potter" → "harry+potter"
        $url = $this->baseUrl
             . '?q='          . urlencode($query)
             . '&maxResults=10'          // en fazla 10 sonuç
             . '&langRestrict=tr'        // Türkçe kitapları önceliklendir
             . '&printType=books';       // sadece kitap, dergi değil

        // Eğer API key tanımlıysa URL'e ekle
        // key olmadan da çalışır ama günlük limit düşer
        if (GOOGLE_BOOKS_API_KEY) {
            $url .= '&key=' . GOOGLE_BOOKS_API_KEY;
        }

        // makeRequest() ile API'ye istek at, JSON cevabı al
        $response = $this->makeRequest($url);

        // Gelen ham veriyi temizle ve düzenli hale getir
        return $this->parseBooks($response);
    }

    // Tek bir kitabın detay bilgisini çeker
    // $bookId = Google'ın her kitaba verdiği benzersiz ID
    // Örnek: "zyTCAlFPjgYC"
    public function getBook($bookId) {
        // Detay için URL farklı: /volumes/ID şeklinde
        $url = $this->baseUrl . '/' . $bookId;

        if (GOOGLE_BOOKS_API_KEY) {
            $url .= '?key=' . GOOGLE_BOOKS_API_KEY;
        }

        $response = $this->makeRequest($url);
        return $this->parseBookDetail($response);
    }

    // ------------------------------------------------
    // PRIVATE FONKSİYONLAR - sadece sınıf içi kullanım
    // ------------------------------------------------

    // cURL ile HTTP isteği yapan ana motor
    // Tüm API çağrıları buradan geçiyor
    // $url = gidilecek adres
    private function makeRequest($url) {

        // curl_init() = cURL bağlantısını başlat, $ch değişkenine ata
        // $ch = "cURL handle" yani bağlantı tutacağı
        $ch = curl_init();

        // curl_setopt() = cURL'e ayar veriyoruz
        // Her satır farklı bir ayar

        curl_setopt($ch, CURLOPT_URL, $url);
        // → hangi adrese gidecek

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // → cevabı ekrana yazdırma, değişkene döndür
        // false olsaydı direkt ekrana yazardı, biz işleyeceğiz

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // → 10 saniye içinde cevap gelmezse vazgeç
        // internet yavaşsa sonsuz beklemez

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // → SSL sertifikasını doğrulama (localhost'ta sorun çıkarabiliyor)

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
            // → Google'a "bana JSON formatında cevap ver" diyoruz
        ]);

        // curl_exec() = isteği gönder, cevabı al
        // Bu satırda gerçekten internete gidiyoruz
        $response = curl_exec($ch);

        // HTTP durum kodunu al
        // 200 = başarılı, 404 = bulunamadı, 403 = yetkisiz
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Bağlantıyı kapat - belleği serbest bırak
        // Her zaman kapatılmalı
        curl_close($ch);

        // Hata kontrolü
        if ($response === false || $httpCode !== 200) {
            return null; // Bir şeyler ters gitti, null döndür
        }

        // json_decode() = JSON metni → PHP dizisine çevirir
        // true parametresi = object değil array olarak döndür
        // '{"title":"Harry"}' → ['title' => 'Harry']
        return json_decode($response, true);
    }

    // API'den gelen kitaplar dizisini işler
    // Ham veri çok karmaşık, sadece işimize yarayanları alıyoruz
    private function parseBooks($data) {

        // Veri yoksa veya 'items' anahtarı yoksa boş dizi döndür
        if (!$data || !isset($data['items'])) {
            return [];
        }

        $books = []; // Temiz kitap listesi

        foreach ($data['items'] as $item) {

            // volumeInfo = kitabın tüm bilgileri burada
            // ?? [] = eğer yoksa boş dizi kullan (PHP 7+ null coalescing)
            $info = $item['volumeInfo'] ?? [];

            $books[] = [
                'id'            => $item['id'],
                'title'         => $info['title']                    ?? 'Başlık yok',
                'authors'       => $info['authors']                  ?? ['Yazar bilinmiyor'],
                'description'   => $info['description']              ?? 'Açıklama yok',
                'pageCount'     => $info['pageCount']                ?? 0,
                'categories'    => $info['categories']               ?? [],
                'rating'        => $info['averageRating']            ?? 0,
                'ratingCount'   => $info['ratingsCount']             ?? 0,
                'cover'         => $this->getCoverUrl($info),        // ← kapak fotoğrafı
                'language'      => $info['language']                 ?? '',
                'publishedDate' => $info['publishedDate']            ?? '',
                'publisher'     => $info['publisher']                ?? '',
                // Okuma süresi hesabı: sayfa * 250 kelime / 300 kelime per dakika
                'readingTime'   => isset($info['pageCount'])
                                   ? round($info['pageCount'] * 250 / 300)
                                   : 0,
            ];
        }

        return $books;
    }

    // Tek kitap detayı için aynı işlem
    private function parseBookDetail($data) {
        if (!$data) return null;

        $info = $data['volumeInfo'] ?? [];

        return [
            'id'            => $data['id'],
            'title'         => $info['title']           ?? 'Başlık yok',
            'authors'       => $info['authors']         ?? ['Yazar bilinmiyor'],
            'description'   => $info['description']     ?? 'Açıklama yok',
            'pageCount'     => $info['pageCount']        ?? 0,
            'categories'    => $info['categories']       ?? [],
            'rating'        => $info['averageRating']    ?? 0,
            'ratingCount'   => $info['ratingsCount']     ?? 0,
            'cover'         => $this->getCoverUrl($info),
            'language'      => $info['language']         ?? '',
            'publishedDate' => $info['publishedDate']    ?? '',
            'publisher'     => $info['publisher']        ?? '',
            'readingTime'   => isset($info['pageCount'])
                               ? round($info['pageCount'] * 250 / 300)
                               : 0,
        ];
    }

    // Kapak fotoğrafı URL'sini düzenler
    // Google bazen http bazen https gönderiyor
    // Biz her zaman https yapıyoruz - güvenli bağlantı
    private function getCoverUrl($info) {

        // Önce büyük kapak, yoksa küçük kapak, yoksa boş
        $cover = $info['imageLinks']['large']
              ?? $info['imageLinks']['medium']
              ?? $info['imageLinks']['thumbnail']
              ?? '';

        if (empty($cover)) return '';

        // http → https dönüşümü
        // str_replace("aranan", "yeni", "kaynak")
        return str_replace('http://', 'https://', $cover);
    }
}