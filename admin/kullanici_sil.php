<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../session.php';

//istekler post ise kabul edilir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ]);
    exit;
}
// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    http_response_code(403); // Forbidden
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlem için admin yetkisi gereklidir'
    ]);
    exit;
}

// json verisini alır ve işler
$input = json_decode(file_get_contents('php://input'), true);

// gönderilen verinin geçerli olup olmadığını kontrol eder
if (!$input || !isset($input['kullaniciId'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri'
    ]);
    exit;
}

// silinecek kullanıcının id sini alır ve int e çevirir
$kullaniciId = (int)$input['kullaniciId'];

//adminin kendi hesabını silmesini engeller
if ($kullaniciId == $currentUser['kullaniciid']) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Kendi hesabınızı silemezsiniz'
    ]);
    exit;
}

try {
    //kullanıcıyı veritabanından siler
    $stmt = $pdo->prepare("DELETE FROM Kullanici WHERE kullaniciid = ?");
    $stmt->execute([$kullaniciId]);
    
    if ($stmt->rowCount() > 0) {
        // başarı mesajı gönderir
        echo json_encode([
            'success' => true,
            'message' => 'Kullanıcı başarıyla silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı bulunamadı'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
