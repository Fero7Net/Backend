<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db/database.php';

// Yetki kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş yapmalısınız.']);
    exit;
}

// Kullanıcının yetkisini al
$stmt = $pdo->prepare("SELECT yetki FROM Kullanici WHERE kullaniciid = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $currentUser['yetki'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ad'], $input['soyad'], $input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

$ad = trim($input['ad']);
$soyad = trim($input['soyad']);
$email = trim($input['email']);
$sifre = isset($input['sifre']) && $input['sifre'] !== "" ? password_hash($input['sifre'], PASSWORD_BCRYPT) : null;

try {
    // Kullanıcının var olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı.']);
        exit;
    }

    // Güncelleme sorgusu
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
