<?php
// veritabanı Bağlantısı

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    //veritabanını oluşturan sql kodları
    $sql = "
    CREATE TABLE IF NOT EXISTS Kategoriler (
        kategoriid INTEGER PRIMARY KEY AUTOINCREMENT,
        kategoriadi TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS Kullanici (
        kullaniciid INTEGER PRIMARY KEY AUTOINCREMENT,
        adi TEXT NOT NULL,
        soyadi TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        sifre TEXT NOT NULL,
        yetki TEXT NOT NULL CHECK(yetki IN ('admin','user')) DEFAULT 'user'
    );

    CREATE TABLE IF NOT EXISTS Urun (
        urunid INTEGER PRIMARY KEY AUTOINCREMENT,
        urunadi TEXT NOT NULL,
        yazar TEXT NOT NULL,
        kategoriid INTEGER,
        fiyat DECIMAL(10,2) NOT NULL,
        aciklama TEXT,
        resim TEXT,
        FOREIGN KEY (kategoriid) REFERENCES Kategoriler(kategoriid)
            ON DELETE SET NULL
            ON UPDATE CASCADE
    );

    CREATE TABLE IF NOT EXISTS Sepet (
        sepetid INTEGER PRIMARY KEY AUTOINCREMENT,
        kullaniciid INTEGER NOT NULL,
        urunid INTEGER NOT NULL,
        adet INTEGER NOT NULL DEFAULT 1,
        FOREIGN KEY (kullaniciid) REFERENCES Kullanici(kullaniciid)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        FOREIGN KEY (urunid) REFERENCES Urun(urunid)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    );
    ";

    // bu kod ile yukarıdaki SQL komutlarını veritabanında çalıştır
    $pdo->exec($sql);

} catch (PDOException $e) {
    // eğer try bloğunda hata olursa:
    header('Content-Type: application/json; charset=utf-8');
    
    // sunucu hatası ise hata kodu 500 olarak gösterilir
    http_response_code(500); 
    
    // json formatında basarak hatanın nedenini yazıyoruz
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()
    ]);
    
    exit;
}
?>