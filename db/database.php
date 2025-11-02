<?php
// BACKEND: Veritabanı bağlantısı

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // Veritabanı tablolarını (Kategoriler, Kullanici, Urun, Sepet) oluşturan SQL komutları.
    // "IF NOT EXISTS" sayesinde, tablolar zaten varsa tekrar oluşturulmaz, hata vermez.
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

    // Yukarıdaki SQL komutlarını veritabanında çalıştır.
    $pdo->exec($sql);

} catch (PDOException $e) {
    // --- GÜNCELLENEN HATA YÖNETİMİ ---
    // Eğer 'try' bloğunda (örn: veritabanı dosyasına yazma izni yoksa) bir hata olursa:
    
    // Tarayıcıya bunun bir JSON yanıtı olduğunu söylüyoruz.
    header('Content-Type: application/json; charset=utf-8');
    
    // Tarayıcıya bunun bir 'Sunucu Hatası' (500) olduğunu söylüyoruz.
    http_response_code(500); 
    
    // Hatayı, login.js'in anlayabileceği JSON formatında basıyoruz.
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()
    ]);
    
    // Betiği sonlandırıyoruz.
    exit;
}
?>