<?php
/*
 * api/trending.php — En Çok Görüntülenen Kitaplar
 *
 * JavaScript'teki loadTrend() fonksiyonu bu endpoint'i çağırır.
 * Veritabanındaki arama_gecmisi tablosundan
 * en çok görüntülenen 6 kitabı getirir.
 *
 * ── ÇALIŞMA MANTIĞI ────────────────────────────────────────────
 * Bir kullanıcı book.php'ye girdiğinde:
 *   1) PHP, kitap ID'sini Google Books'tan çeker
 *   2) Database::kitapAramasiniKaydet() çağrılır
 *   3) Kitap varsa arama_sayisi + 1, yoksa yeni satır INSERT edilir
 * Bu endpoint o tablodan ORDER BY arama_sayisi DESC ile çeker.
 *
 * ── GÜVENLİK ───────────────────────────────────────────────────
 * Veritabanı sorgusu Database sınıfında Prepared Statement ile yapılır.
 * Bu endpoint'ten SQL Injection mümkün değil çünkü
 * kullanıcıdan gelen herhangi bir veri bu sorguda kullanılmıyor.
 * ────────────────────────────────────────────────────────────── */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Database sınıfı → config.php'yi kendi içinde require eder
require_once __DIR__ . '/../src/Database/Database.php';

// Veritabanı bağlantısını kur
$db = new Database();

// Bağlantı başarısızdı → boş dizi döndür (projeyi kırma)
if (!$db->isConnected()) {
    echo json_encode([]);
    exit;
}

// En çok görüntülenen 6 kitabı getir
$trend = $db->getTrendKitaplar(6);

echo json_encode($trend, JSON_UNESCAPED_UNICODE);