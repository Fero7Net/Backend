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
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['yetki'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ad'], $input['soyad'], $input['email'], $input['sifre'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

$ad = trim($input['ad']);
$soyad = trim($input['soyad']);
$email = trim($input['email']);
$sifre = password_hash($input['sifre'], PASSWORD_BCRYPT);
$yetki = 'user';

try {
    // Aynı e-posta var mı kontrol et
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı.']);
        exit;
    }

    // Yeni kullanıcı ekle
    $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $soyad, $email, $sifre, $yetki]);

    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla eklendi!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
