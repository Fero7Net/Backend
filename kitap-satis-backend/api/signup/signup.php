<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ad'], $input['soyad'], $input['email'], $input['sifre'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

$ad = trim($input['ad']);
$soyad = trim($input['soyad']);
$email = trim($input['email']);
$sifre = trim($input['sifre']);
$yetki = 'user';

// Ad ve soyad kontrolü (yalnızca harfler, Türkçe karakterler dahil)
if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $ad)) {
    echo json_encode([
        'success' => false,
        'message' => 'Ad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $soyad)) {
    echo json_encode([
        'success' => false,
        'message' => 'Soyad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

// Şifre kontrolü (yalnızca harf, rakam, _ ve .; 4-16 karakter arası)
if (!preg_match('/^[A-Za-z0-9_.]{4,16}$/', $sifre)) {
    echo json_encode([
        'success' => false,
        'message' => 'Şifre yalnızca harf, rakam, "_" ve "." içerebilir ve 4-16 karakter uzunluğunda olmalıdır.'
    ]);
    exit;
}

$sifre_hash = password_hash($sifre, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $soyad, $email, $sifre_hash, $yetki]);

    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla tamamlandı!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
