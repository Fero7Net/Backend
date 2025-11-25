<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db/database.php';

// burda kullanıcı giriş yapmamışsa giriş yapmasını ister
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
    exit;
}

// burda kullanıcının yetkisine bakar
$stmt = $pdo->prepare("SELECT yetki FROM Kullanici WHERE kullaniciid = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
// eğer yetki admin değil ise yetkisiz erişim mesajı vererek sayfada işlem yapmasını engeller
if (!$currentUser || $currentUser['yetki'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}
// istemciden gelen json verisini okur
$input = json_decode(file_get_contents('php://input'), true);

//kullanıcı düzenlenirken eksik bilgi veririlirse
if (!isset($input['ad'], $input['soyad'], $input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}
//boşluk girilmiş ise veritabanına boşluksuz geçer
$ad = trim($input['ad']);
$soyad = trim($input['soyad']);
$email = trim($input['email']);
$sifre = isset($input['sifre']) && $input['sifre'] !== "" ? password_hash($input['sifre'], PASSWORD_BCRYPT) : null;

try {
    //kullanıcının olup olmadığını kontrol etmek için
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı.']);
        exit;
    }

    // eğer kullanıcı şifreyi boş girdi ise şifre güncellenmez
    if ($sifre) {
        $stmt = $pdo->prepare("UPDATE Kullanici SET adi = ?, soyadi = ?, sifre = ? WHERE email = ?");
        $stmt->execute([$ad, $soyad, $sifre, $email]);
    } else {
        $stmt = $pdo->prepare("UPDATE Kullanici SET adi = ?, soyadi = ? WHERE email = ?");
        $stmt->execute([$ad, $soyad, $email]);
    }

    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
