<?php
header('Content-Type: application/json; charset=utf-8'); //tarayıcıya json olarak veri gönderir.
require_once __DIR__ . '../../db/database.php';//veritabanı bağlantısını sağlar.

$input = json_decode(file_get_contents('php://input'), true);//frontendden gelen json verisini okur.

if (!isset($input['ad'], $input['soyad'], $input['email'], $input['sifre'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}//kullanıcı bilgilerin hepsini veya doğru girmişmi diye kontrol eder.

$ad = trim($input['ad']);
$soyad = trim($input['soyad']);
$email = trim($input['email']);//bilgilerin başındaki ve sonundaki boşlukları siler.

$sifre = password_hash($input['sifre'], PASSWORD_BCRYPT);//şifreleri hashler.
$yetki = 'user';//üye olan kullanıcıların yetkisini verir.

try {
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı.']);
        exit;
    }//veritabanında aynı email adresi varmı diye bakar.

    $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $soyad, $email, $sifre, $yetki]);//yeni kullanıcıyı veritabanına ekler.

    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla tamamlandı!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}//veritabanına bağlanılamadığında bu kod devreye girer.
?>