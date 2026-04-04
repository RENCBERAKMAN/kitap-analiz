-- ══════════════════════════════════════
-- Midnight Library — Veritabanı Kurulum
-- Kullanım: phpMyAdmin > SQL > Çalıştır
-- ══════════════════════════════════════

CREATE DATABASE IF NOT EXISTS kitap_analiz
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_turkish_ci;

USE kitap_analiz;

CREATE TABLE IF NOT EXISTS arama_gecmisi (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    kitap_id     VARCHAR(50)  NOT NULL,
    kitap_adi    VARCHAR(255) NOT NULL,
    yazar        VARCHAR(255) DEFAULT '',
    kapak_url    VARCHAR(500) DEFAULT '',
    arama_sayisi INT          DEFAULT 1,
    son_arama    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP 
                 ON UPDATE CURRENT_TIMESTAMP
);