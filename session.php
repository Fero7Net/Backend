<?php
// BACKEND: Oturum yönetimi

session_start();
require_once __DIR__ . '/db/database.php';

$isLoggedIn = false;
$currentUser = null;
$sepetUrunSayisi = 0;

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT kullaniciid, adi, soyadi, email, yetki FROM Kullanici WHERE kullaniciid = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($currentUser) {
            $isLoggedIn = true;
            
            try {
                $stmt = $pdo->prepare("SELECT SUM(adet) as toplam_adet FROM Sepet WHERE kullaniciid = ?");
                $stmt->execute([$currentUser['kullaniciid']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $sepetUrunSayisi = $result['toplam_adet'] ? (int)$result['toplam_adet'] : 0;
            } catch (PDOException $e) {
                $sepetUrunSayisi = 0;
            }
        } else {
            session_destroy();
        }
    } catch (PDOException $e) {
        session_destroy();
    }
}
?>