--Kategoriler Tablosu
CREATE TABLE IF NOT EXISTS Kategoriler (
    kategoriid INTEGER PRIMARY KEY,
    kategoriadi TEXT NOT NULL
);
--Kullanıcı Tablosu
CREATE TABLE IF NOT EXISTS Kullanici (
    kullaniciid INTEGER PRIMARY KEY,
    kullaniciadi TEXT NOT NULL,
    kullanicisoyadı TEXT NOT NULL,
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