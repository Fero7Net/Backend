================================================================================
E-KİTAP E-TİCARET PROJESİ - README
================================================================================

PROJE HAKKINDA
--------------------------------------------------------------------------------
Bu proje, SQLite veritabanı ve PHP backend teknolojisi kullanılarak geliştirilmiş
basit bir demo e-kitap satış sitesidir. Proje, modern web teknolojileri ile
e-ticaret temel işlevlerini gösteren, eğitim ve demo amaçlı bir uygulamadır.

PROJE ÖZELLİKLERİ
--------------------------------------------------------------------------------

1. KULLANICI YÖNETİMİ
   - Kullanıcı kaydı (Kayıt Ol) ve giriş sistemi (Giriş Yap)
     [AÇIKLAMA] signup/signup.html + signup.js → signup.php (BCRYPT hash, email kontrolü)
     [AÇIKLAMA] login/login.html + login.js → login.php (password_verify, session başlatma)
   - Oturum yönetimi (Session Management)
     [AÇIKLAMA] session.php (tüm sayfalarda require edilir, $isLoggedIn, $currentUser, $sepetUrunSayisi sağlar)
   - Şifre güvenliği (Password Hashing - BCRYPT)
     [AÇIKLAMA] signup.php içinde password_hash(), login.php içinde password_verify()
   - Admin ve normal kullanıcı yetki sistemi
     [AÇIKLAMA] Kullanici tablosunda 'yetki' alanı ('admin'/'user'), admin sayfalarında kontrol: if($currentUser['yetki'] !== 'admin')
   - Kullanıcı profil sayfası
     [AÇIKLAMA] profil/profil.php (session.php ile kullanıcı bilgilerini gösterir)

2. ÜRÜN YÖNETİMİ
   - Ürün listeleme (kategorilere göre)
     [AÇIKLAMA] urunler/urunler.php (tüm ürünler), turler/kategori.php?id=X (kategoriye göre)
   - Ürün arama/filtreleme
     [AÇIKLAMA] urunler/urunler.php içinde JavaScript ile client-side filtreleme (kitap adı, yazar, kategori)
   - Popüler ürünler ve son eklenenler bölümleri
     [AÇIKLAMA] index/index.php (ORDER BY RANDOM() LIMIT 4 ve ORDER BY urunid DESC LIMIT 4)
   - Ürün resim yükleme ve görüntüleme
     [AÇIKLAMA] Admin: admin/urunler/urunekle.php → kitaplar/ klasörüne yükler, görüntüleme: /kitaplar/{resim} yolu

3. KATEGORİ YÖNETİMİ
   - Kategori listeleme
     [AÇIKLAMA] Tüm sayfalarda navbar'da dinamik kategori menüsü (LEFT JOIN ile ürün sayısı hesaplanır)
   - Kategoriye göre ürün filtreleme
     [AÇIKLAMA] turler/kategori.php?id=X (URL parametresi ile kategori ID alınır, WHERE u.kategoriid = ? sorgusu)
   - Dinamik navigasyon menüsü
     [AÇIKLAMA] Her sayfada session.php → kategoriler çekilir → navbar dropdown menüsünde gösterilir

4. SEPET İŞLEMLERİ
   - Sepete ürün ekleme
     [AÇIKLAMA] main.js → fetch('/sepet_guncelle.php', action:'add') → Sepet tablosuna INSERT/UPDATE
   - Sepet içeriğini görüntüleme
     [AÇIKLAMA] sepet/sepet.php (JOIN ile Sepet + Urun tabloları birleştirilir, kullanıcıya özel)
   - Ürün miktarını artırma/azaltma
     [AÇIKLAMA] sepet/sepet.php içinde JavaScript → fetch('/sepet_guncelle.php', action:'update')
   - Sepetten ürün silme
     [AÇIKLAMA] sepet/sepet.php içinde JavaScript → fetch('/sepet_guncelle.php', action:'remove')
   - Sepet toplam tutar hesaplama
     [AÇIKLAMA] sepet/sepet.php içinde PHP döngüsü ile fiyat * adet toplamı hesaplanır
   - Sipariş tamamlama
     [AÇIKLAMA] sepet/sepet.php → fetch('/siparis_tamamla.php') → Sepet tablosundan kullanıcının tüm kayıtları DELETE

5. ADMIN PANELİ
   - Admin yetkisi kontrolü
     [AÇIKLAMA] admin/adminindex.php başında: if($currentUser['yetki'] !== 'admin') → yönlendirme
   - Kullanıcı yönetimi (Ekleme, Düzenleme, Silme)
     [AÇIKLAMA] admin/kullanicilar/kullanicilar.php (liste), kullaniciekle.php (form+API), kullaniciduzenle.php (form+API), admin/kullanici_sil.php (API)
   - Ürün yönetimi (Ekleme, Düzenleme, Silme)
     [AÇIKLAMA] admin/urunler/urunler.php (liste), urunekle.php (form+resim yükleme), urunduzenle.php (form+API), admin/urun_sil.php (API)
   - Kategori yönetimi (Ekleme, Silme)
     [AÇIKLAMA] Admin panelinde kategori ekleme formu, admin/kategori_sil.php (API)
   - Dashboard (Yönetim ana sayfası)
     [AÇIKLAMA] admin/adminindex.php (sidebar menü, hızlı erişim butonları)

TEKNİK ÖZELLİKLER
--------------------------------------------------------------------------------

VERİTABANI (SQLite)
   - SQLite veritabanı kullanılmıştır (db/data.db)
     [AÇIKLAMA] db/database.php içinde PDO bağlantısı, CREATE TABLE IF NOT EXISTS ile otomatik tablo oluşturma
   - 4 ana tablo:
     * Kategoriler: Kitap kategorileri
     * Kullanici: Kullanıcı bilgileri ve yetkileri
     * Urun: Ürün (kitap) bilgileri, fiyat, yazar, kategori
     * Sepet: Kullanıcı sepet içeriği
   - Foreign key ilişkileri ile veri bütünlüğü sağlanmıştır
     [AÇIKLAMA] Urun.kategoriid → Kategoriler.kategoriid, Sepet.kullaniciid → Kullanici.kullaniciid, Sepet.urunid → Urun.urunid
   - ON DELETE CASCADE ve ON UPDATE CASCADE kullanılmıştır
     [AÇIKLAMA] Kullanıcı silinince sepeti silinir, kategori silinince ürünün kategoriid NULL olur

BACKEND (PHP)
   - PHP 7.x+ uyumlu
   - PDO (PHP Data Objects) ile veritabanı işlemleri
     [AÇIKLAMA] db/database.php → $pdo değişkeni, tüm sayfalarda require_once ile kullanılır
   - Session yönetimi ile kullanıcı oturum kontrolü
     [AÇIKLAMA] session.php → session_start(), $_SESSION['user_id'] kontrolü, $isLoggedIn ve $currentUser değişkenleri
   - Password hashing (BCRYPT) ile güvenli şifre saklama
     [AÇIKLAMA] signup.php: password_hash(), login.php: password_verify()
   - RESTful API benzeri JSON endpoint'ler
     [AÇIKLAMA] login.php, signup.php, sepet_guncelle.php, siparis_tamamla.php → header('Content-Type: application/json'), JSON yanıt
   - Admin yetkisi kontrolü
     [AÇIKLAMA] Admin sayfalarında: if(!$isLoggedIn || $currentUser['yetki'] !== 'admin') → yönlendirme
   - Güvenlik: SQL Injection koruması (Prepared Statements)
     [AÇIKLAMA] Tüm SQL sorgularında $pdo->prepare() ve ->execute() kullanımı
   - Güvenlik: XSS koruması (htmlspecialchars)
     [AÇIKLAMA] Tüm kullanıcı verileri ekrana yazdırılırken htmlspecialchars() ile escape edilir

FRONTEND
   - Vanilla JavaScript (jQuery yok)
   - Fetch API ile AJAX istekleri
   - Responsive tasarım (Mobil uyumlu)
   - Modern CSS (Flexbox, Grid)
   - Font Awesome ikonları
   - Dinamik içerik yükleme

KURULUM VE ÇALIŞTIRMA
--------------------------------------------------------------------------------

1. GEREKSİNİMLER:
   - PHP 7.4 veya üzeri
   - SQLite3 desteği (genellikle PHP ile birlikte gelir)
   - Web sunucusu (Apache, Nginx) veya PHP built-in server

2. KURULUM:
   a) Projeyi web sunucu dizinine kopyalayın
   b) db/data.db dosyasının yazılabilir olduğundan emin olun
      (Linux/Mac: chmod 666 db/data.db)
   c) kitaplar/ klasörünün yazılabilir olduğundan emin olun
      (Ürün resimleri buraya yüklenir)

3. İLK KULLANICI OLUŞTURMA:
   - Signup sayfasından normal kullanıcı oluşturun
   - Admin yetkisi vermek için yetkiduzenleme.php script'ini kullanın:
     php yetkiduzenleme.php email@example.com
   - VEYA veritabanında direkt olarak yetkiyi 'admin' yapın

4. ÇALIŞTIRMA:
   Apache/Nginx kullanıyorsanız:
   - Proje otomatik çalışır (örn: http://localhost/e-ticaret/)
   
   PHP built-in server kullanıyorsanız:
   - Terminalde proje dizinine gidin
   - php -S localhost:8000 komutunu çalıştırın
   - Tarayıcıda http://localhost:8000 adresine gidin

GÜVENLİK NOTLARI
--------------------------------------------------------------------------------

⚠ BU PROJE DEMO/EĞİTİM AMAÇLIDIR ⚠

Gerçek bir e-ticaret sitesi için aşağıdaki iyileştirmeler yapılmalıdır:

1. Güvenlik:
   - HTTPS kullanımı zorunludur
   - CSRF token koruması eklenmelidir
   - Rate limiting uygulanmalıdır
   - Dosya yükleme güvenliği artırılmalıdır
   - SQL Injection koruması mevcut ama ek kontroller eklenebilir

2. Veritabanı:
   - Production için MySQL/PostgreSQL gibi güçlü veritabanları kullanılmalıdır
   - Veritabanı yedekleme stratejisi oluşturulmalıdır
   - Connection pooling kullanılmalıdır

3. Performans:
   - Cache mekanizması eklenmelidir
   - Resim optimizasyonu yapılmalıdır
   - CDN kullanımı değerlendirilmelidir

4. Özellikler:
   - Ödeme entegrasyonu (iyzico, PayTR, vb.)
   - E-posta bildirimleri
   - Sipariş takibi
   - Kullanıcı yorumları ve değerlendirmeleri
   - Favori ürünler listesi

LİSANS
--------------------------------------------------------------------------------

Bu proje eğitim ve demo amaçlıdır.

Emeği Geçenler Ahmet Sunar, Arda Eri, Ferhat Alkan


Son Güncelleme: 2025