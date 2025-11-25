<?php
session_start();

require_once __DIR__ . '/db/database.php';

$isLoggedIn = false;      // kullanıcı giriş yapmış mı
$currentUser = null;      // kullanıcı bilgisi
$sepetUrunSayisi = 0;     // sepetteki toplam ürün sayısı

// eğer session da kullanıcı id varsa
if (isset($_SESSION['user_id'])) {
    try {
        // kullanıcıyı veritabanından çeker
        $stmt = $pdo->prepare("SELECT kullaniciid, adi, soyadi, email, yetki FROM Kullanici WHERE kullaniciid = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentUser) {
            // kullanıcı bulundu ise
            $isLoggedIn = true;

            // sepetteki ürün sayısını çekme
            try {
                $stmt = $pdo->prepare("SELECT SUM(adet) as toplam_adet FROM Sepet WHERE kullaniciid = ?");
                $stmt->execute([$currentUser['kullaniciid']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // eğer sepet boş ise 0 olarak ayarlama
                $sepetUrunSayisi = $result['toplam_adet'] ? (int)$result['toplam_adet'] : 0;

            } catch (PDOException $e) {
                // sepet sorgusu hata verirse 0 olarak ayarla
                $sepetUrunSayisi = 0;
            }

        } else {
            // kullanıcı session da yoksa session ı temizle
            session_destroy();
        }

    } catch (PDOException $e) {
        // kullanıcı sorgusu hata verirse session ı temizle
        session_destroy();
    }
}
?>
