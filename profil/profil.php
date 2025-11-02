<?php
// BACKEND: Profil sayfası

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
?>
