<?php
// DOSYA ADI: Test/admin/kullanici_sil.php
// AÇIKLAMA: Backend API - Admin kullanıcı silme işlemi

// === JSON YANIT AYARI ===
// Tarayıcıya JSON formatında yanıt göndereceğimizi belirt
header('Content-Type: application/json; charset=utf-8');

// === OTURUM KONTROLÜ ===
// Merkezi oturum dosyasını dahil et (kullanıcı giriş kontrolü için)
require_once __DIR__ . '/../session.php';

// === HTTP METODU KONTROLÜ ===
// Sadece POST isteklerini kabul et (güvenlik için)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ]);
    exit;
}

// === YETKİ KONTROLÜ ===
// Kullanıcının giriş yapmış ve admin yetkisine sahip olup olmadığını kontrol et
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlem için admin yetkisi gereklidir'
    ]);
    exit;
}

// === VERİ ALMA ===
// Tarayıcıdan gönderilen JSON verisini al ve PHP dizisine çevir
$input = json_decode(file_get_contents('php://input'), true);

// Gönderilen verinin geçerli olup olmadığını kontrol et
if (!$input || !isset($input['kullaniciId'])) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri'
    ]);
    exit;
}

// Silinecek kullanıcının ID'sini al ve integer'a çevir
$kullaniciId = (int)$input['kullaniciId'];

// === GÜVENLİK KONTROLÜ ===
// Admin'in kendi hesabını silmesini engelle
if ($kullaniciId == $currentUser['kullaniciid']) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Kendi hesabınızı silemezsiniz'
    ]);
    exit;
}

// === VERİTABANI İŞLEMİ ===
try {
    // Kullanıcıyı veritabanından sil
    // NOT: CASCADE kuralı sayesinde bu kullanıcının sepet kayıtları da otomatik silinir
    $stmt = $pdo->prepare("DELETE FROM Kullanici WHERE kullaniciid = ?");
    $stmt->execute([$kullaniciId]);
    
    // İşlem başarılı ise (en az 1 satır etkilendi)
    if ($stmt->rowCount() > 0) {
        // Başarı mesajı gönder
        echo json_encode([
            'success' => true,
            'message' => 'Kullanıcı başarıyla silindi'
        ]);
    } else {
        // Kullanıcı bulunamadı (ID yanlış)
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı bulunamadı'
        ]);
    }
    
} catch (PDOException $e) {
    // Veritabanı hatası olursa hata mesajı gönder
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
