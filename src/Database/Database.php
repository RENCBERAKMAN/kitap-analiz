<?php
require_once __DIR__ . '/../../config/config.php';

class Database {

    // PDO = PHP'nin modern veritabanı bağlantı sistemi
    // private = sadece bu sınıf kullanabilir
    private $pdo;

    public function __construct() {

        // Bağlantı bilgileri
        // localhost = MySQL bizim bilgisayarımızda çalışıyor
        // root = XAMPP'ın varsayılan kullanıcısı
        // '' = XAMPP'ta şifre yok varsayılan olarak
        $host   = 'localhost';
        $dbname = 'kitap_analiz';
        $user   = 'root';
        $pass   = '';

        try {
            // PDO bağlantısı kur
            // DSN = Data Source Name, nereye bağlanacağını söylüyoruz
            $this->pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            // Bağlantı başarısız olursa sessizce geç
            // Proje veritabanı olmadan da çalışmaya devam etsin
            $this->pdo = null;
        }
    }

    // Bağlantı var mı kontrol et
    public function isConnected() {
        return $this->pdo !== null;
    }

    // ──────────────────────────────────────
    // Kitap aramasını kaydet veya sayacı artır
    // ──────────────────────────────────────
    public function kitapAramasiniKaydet($kitapId, $kitapAdi, $yazar, $kapakUrl) {
        if (!$this->isConnected()) return false;

        try {
            // Bu kitap daha önce arandı mı?
            $stmt = $this->pdo->prepare(
                "SELECT id, arama_sayisi FROM arama_gecmisi WHERE kitap_id = ?"
            );
            // ? = prepared statement — SQL injection'a karşı güvenli yöntem
            $stmt->execute([$kitapId]);
            $mevcut = $stmt->fetch();

            if ($mevcut) {
                // Varsa sayacı 1 artır
                $stmt = $this->pdo->prepare(
                    "UPDATE arama_gecmisi 
                     SET arama_sayisi = arama_sayisi + 1,
                         son_arama    = CURRENT_TIMESTAMP
                     WHERE kitap_id = ?"
                );
                $stmt->execute([$kitapId]);
            } else {
                // Yoksa yeni satır ekle
                $stmt = $this->pdo->prepare(
                    "INSERT INTO arama_gecmisi 
                     (kitap_id, kitap_adi, yazar, kapak_url) 
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([$kitapId, $kitapAdi, $yazar, $kapakUrl]);
            }

            return true;

        } catch (PDOException $e) {
            return false;
        }
    }

    // ──────────────────────────────────────
    // En çok aranan kitapları getir (Trend)
    // ──────────────────────────────────────
    public function getTrendKitaplar($limit = 6) {
    if (!$this->isConnected()) return [];

    try {
        // LIMIT'i doğrudan sorguya yazıyoruz — PDO'nun LIMIT ? sorunu var
        $limit = (int)$limit; // Güvenlik için integer'a çevir
        $stmt  = $this->pdo->query(
            "SELECT kitap_id, kitap_adi, yazar, kapak_url, arama_sayisi
             FROM arama_gecmisi
             ORDER BY arama_sayisi DESC
             LIMIT {$limit}"
        );
        return $stmt->fetchAll();

    } catch (PDOException $e) {
        return [];
    }
}
}