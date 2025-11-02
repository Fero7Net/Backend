<?php
// BACKEND: Dinamik kategori sayfası

require_once __DIR__ . '/../session.php';

// 2. KATEGORİLERİ ÇEK
// Navbar'da listelemek için
try { 
    $query = $pdo->query("
        SELECT 
            k.kategoriid, 
            k.kategoriadi, 
            COUNT(u.urunid) AS urun_sayisi
        FROM 
            Kategoriler k
        LEFT JOIN 
            Urun u ON k.kategoriid = u.kategoriid
        GROUP BY 
            k.kategoriid, k.kategoriadi
        ORDER BY 
            k.kategoriadi ASC
    ");
    $kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategoriler = [];
}

// 1. URL'den kategori ID'sini al
$kategori_id = $_GET['id'] ?? null;

$urunler = [];
$current_kategori = null; // Kategori bilgilerini (ad, id) tutmak için (isim çakışmasını önlemek için $kategori -> $current_kategori yaptım)

// 2. Kategori ID'si geçerli mi kontrol et
if ($kategori_id && is_numeric($kategori_id)) {
    try {
        // 3. Kategori bilgilerini çek (Sayfa başlığı ve adı için)
        $stmt_kat = $pdo->prepare("SELECT kategoriid, kategoriadi FROM Kategoriler WHERE kategoriid = ?");
        $stmt_kat->execute([$kategori_id]);
        $current_kategori = $stmt_kat->fetch(PDO::FETCH_ASSOC);

        // 4. Kategori bulunduysa, o kategoriye ait ürünleri çek
        if ($current_kategori) {
            $stmt = $pdo->prepare("
                SELECT u.*, k.kategoriadi 
                FROM Urun u 
                LEFT JOIN Kategoriler k ON u.kategoriid = k.kategoriid 
                WHERE u.kategoriid = ?
                ORDER BY u.urunid DESC
            ");
            $stmt->execute([$current_kategori['kategoriid']]);
            $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
    } catch (PDOException $e) {
        $urunler = [];
        $current_kategori = null;
    }
}

?>