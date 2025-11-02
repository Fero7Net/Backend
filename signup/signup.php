<?php
// DOSYA ADI: Test/signup/signup.php
// AÇIKLAMA: Yeni kullanıcı kaydını işleyen PHP dosyası

// Tarayıcıya JSON formatında yanıt göndereceğimizi belirt
header('Content-Type: application/json; charset=utf-8');

// Veritabanı bağlantı dosyasını dahil et
require_once __DIR__ . '/../db/database.php';

// Tarayıcıdan gönderilen JSON verisini oku ve PHP dizisine çevir
$input = json_decode(file_get_contents('php://input'), true);

// Gerekli alanların (ad, soyad, email, sifre) gelip gelmediğini kontrol et
if (!isset($input['ad'], $input['soyad'], $input['email'], $input['sifre'])) {
    // Eksik bilgi varsa hata mesajı gönder ve çık
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

// Gelen verileri temizle (başındaki/sonundaki boşlukları sil)
$ad = trim($input['ad']); // Adı temizle
$soyad = trim($input['soyad']); // Soyadı temizle
$email = trim($input['email']); // Email'i temizle
$sifre = trim($input['sifre']); // Şifreyi temizle
$yetki = 'user'; // Yeni kullanıcılar varsayılan olarak 'user' yetkisine sahip

// === VERİ DOĞRULAMA (VALIDATION) ===

// Ad kontrolü: Yalnızca harfler (Türkçe karakterler dahil) olabilir
if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $ad)) {
    // Ad hatalı ise hata mesajı gönder ve çık
    echo json_encode([
        'success' => false,
        'message' => 'Ad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

// Soyad kontrolü: Yalnızca harfler (Türkçe karakterler dahil) olabilir
if (!preg_match('/^[a-zA-ZçÇğĞıİöÖşŞüÜ]+$/u', $soyad)) {
    // Soyad hatalı ise hata mesajı gönder ve çık
    echo json_encode([
        'success' => false,
        'message' => 'Soyad yalnızca harflerden oluşmalıdır.'
    ]);
    exit;
}

// Şifre kontrolü: Harf, rakam, "_" ve "." karakterleri olabilir, uzunluk 4-16 karakter arası
if (!preg_match('/^[A-Za-z0-9_.]{4,16}$/', $sifre)) {
    // Şifre hatalı ise hata mesajı gönder ve çık
    echo json_encode([
        'success' => false,
        'message' => 'Şifre yalnızca harf, rakam, "_" ve "." içerebilir ve 4-16 karakter uzunluğunda olmalıdır.'
    ]);
    exit;
}

// Şifreyi güvenlik için hash'le (şifrele)
$sifre_hash = password_hash($sifre, PASSWORD_BCRYPT);

// === VERİTABANI İŞLEMLERİ ===
try {
    // Önce bu email'in veritabanında kayıtlı olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    
    // Eğer bu email zaten kayıtlıysa
    if ($stmt->fetch()) {
        // Hata mesajı gönder ve çık
        echo json_encode(['success' => false, 'message' => 'Bu e-posta zaten kayıtlı.']);
        exit;
    }

    // Email kayıtlı değilse, yeni kullanıcıyı veritabanına ekle
    $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $soyad, $email, $sifre_hash, $yetki]);

    // Kayıt başarılı, başarı mesajı gönder
    echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla tamamlandı!']);
} catch (PDOException $e) {
    // Veritabanı hatası olursa hata mesajı gönder
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
