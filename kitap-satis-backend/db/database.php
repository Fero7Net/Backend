<?php
    try {
        $pdo = new PDO('sqlite:db/data.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Veritabanına başarıyla bağlanıldı.<br>";
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $sql_kodlari = "
            --Kategoriler Tablosu
CREATE TABLE IF NOT EXISTS Kategoriler (
    kategoriid INTEGER PRIMARY KEY,
    kategoriadi TEXT NOT NULL
);
--Kullanıcı Tablosu
CREATE TABLE IF NOT EXISTS Kullanici (
    kullaniciid INTEGER PRIMARY KEY,
    kullaniciadi TEXT NOT NULL,
    kullanicisoyadi TEXT NOT NULL,
    sifre TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    yetki TEXT NOT NULL CHECK(yetki IN ('admin', 'kullanici')) DEFAULT 'kullanici'
);
--Ürün Tablosu
CREATE TABLE IF NOT EXISTS Urun (
    urunid INTEGER PRIMARY KEY,
    urunadi TEXT NOT NULL,
    yazar TEXT NOT NULL,
    kategoriid INTEGER,
    fiyat INTEGER NOT NULL,
    aciklama TEXT,
    FOREIGN KEY (kategoriid) REFERENCES Kategoriler(kategoriid)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);
--Sepet Tablosu
CREATE TABLE IF NOT EXISTS Sepet (
    sepetid INTEGER PRIMARY KEY,
    kullaniciid INTEGER NOT NULL,
    urunid INTEGER NOT NULL,
    miktar INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (kullaniciid) REFERENCES Kullanici(kullaniciid)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (urunid) REFERENCES Urun(urunid)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
        ";
        $pdo->exec($sql_kodlari);
        echo "Tüm tablolar başarıyla oluşturuldu veya zaten mevcut.<br>";
    } catch (PDOException $e) {
        echo "Veritabanı bağlantı hatası: " . $e->getMessage();
        die();
    }
    $pdo = null;
?>