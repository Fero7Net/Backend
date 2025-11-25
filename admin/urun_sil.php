<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../session.php';

// istekler post ise kabul edilir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ]);
    exit;
}

// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Bu işlem için admin yetkisi gereklidir'
    ]);
    exit;
}

// json verisini alır ve işler
$input = json_decode(file_get_contents('php://input'), true);

// gönderilen verinin geçerli olup olmadığını kontrol eder
if (!$input || !isset($input['urunId'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz veri'
    ]);
    exit;
}

//silinecek urun id sini int e çevirir
$urunId = (int)$input['urunId'];

try {
    //ürünü veritabanından silme işlemi
    $stmt = $pdo->prepare("DELETE FROM Urun WHERE urunid = ?");
    $stmt->execute([$urunId]);
    
    if ($stmt->rowCount() > 0) {
        // başarı mesajı gönderir
        echo json_encode([
            'success' => true,
            'message' => 'Ürün başarıyla silindi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı'
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
