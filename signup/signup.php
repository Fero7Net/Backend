<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/database.php';

// json formatında gelen veriyi alıyoruz
$input = json_decode(file_get_contents('php://input'), true);

// gerekli alanlar geldi mi diye kontrol
if (!isset($input['ad'], $input['soyad'], $input['email'], $input['sifre'])) {
    // Eksik bilgi varsa hata mesajı gönder ve çık
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

// verileri temizleme
$ad = trim($input['ad']); // Adı temizle
$soyad = trim($input['soyad']); // Soyadı temizle
$email = trim($input['email']); // Email'i temizle
$sifre = trim($input['sifre']); // Şifreyi temizle
$yetki = 'user'; // Yeni kullanıcılar varsayılan olarak 'user' yetkisine sahip

//ad kontrolü
if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $ad)) {
    // ad hatalı ise hata mesajı gönder ve çık
    echo json_encode([
        'success' => false,
        'message' => 'Ad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

//soyad kontrolü
if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $soyad)) {
    echo json_encode([
        'success' => false,
        'message' => 'Soyad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

//şifre kontrolü
if (!preg_match('/^[A-Za-z0-9_.]{4,16}$/', $sifre)) {
    echo json_encode([
        'success' => false,
        'message' => 'Şifre yalnızca harf, rakam, "_" ve "." içerebilir ve 4-16 karakter uzunluğunda olmalıdır.'
    ]);
    exit;
}

// şifre hashleme
$sifre_hash = password_hash($sifre, PASSWORD_BCRYPT);

//veritabanı işlemleri
try {
    // veritabanında aynı eposta varmı diye bakar
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    
    // eğer var ise
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı.']);
        exit;
    }

    // eposta veritabanında yok ise
    $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $soyad, $email, $sifre_hash, $yetki]);

    // Kayıt başarılı, başarı mesajı gönder
    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla tamamlandı!']);
} catch (PDOException $e) {
    // Veritabanı hatası olursa hata mesajı gönder
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
