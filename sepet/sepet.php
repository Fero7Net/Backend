<?php
// BACKEND: Sepet sayfası

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

if (!$isLoggedIn) {
    header("Location: /login/login.html");
    exit;
}

$orderMessage = '';
if (isset($_GET['order']) && $_GET['order'] === 'success') {
    $orderMessage = 'Siparişiniz onaylandı!';
}

// Sepetteki ürünleri getir
try {
    $stmt = $pdo->prepare("
        SELECT s.sepetid, s.adet, u.urunid, u.urunadi, u.yazar, u.fiyat, u.aciklama, u.resim
        FROM Sepet s 
        JOIN Urun u ON s.urunid = u.urunid 
        WHERE s.kullaniciid = ?
        ORDER BY s.sepetid DESC
    ");
    $stmt->execute([$currentUser['kullaniciid']]);
    $sepetUrunleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $toplamTutar = 0;
    $toplamUrunSayisi = 0;
    foreach ($sepetUrunleri as $urun) {
        $toplamTutar += $urun['fiyat'] * $urun['adet'];
        $toplamUrunSayisi += $urun['adet'];
    }
    
} catch (PDOException $e) {
    $sepetUrunleri = [];
    $toplamTutar = 0;
    $toplamUrunSayisi = 0;
}
?>
