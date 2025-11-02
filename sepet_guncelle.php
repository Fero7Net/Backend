<?php
// BACKEND: Sepet API

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/session.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // HTTP 405 Method Not Allowed hatası döndür
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ]);
    exit;
}

// Kullanıcı giriş yapmamışsa hata döndür
if (!$isLoggedIn) {
    // HTTP 401 Unauthorized hatası döndür
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Giriş yapmanız gerekiyor'
    ]);
    exit;
}

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

// JSON verisi geçersizse hata döndür
if (!$input) {
    // HTTP 400 Bad Request hatası döndür
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz JSON verisi'
    ]);
    exit;
}

// Gelen verileri al
$action = $input['action'] ?? ''; // İşlem türü (add, update, remove)
$urunId = $input['urunId'] ?? null; // Ürün ID'si
$quantity = $input['quantity'] ?? 1; // Ürün adedi
$sepetId = $input['sepetId'] ?? null; // Sepet ID'si

try {
    switch ($action) {
        case 'add':
            // Sepete ürün ekleme işlemi
            if (!$urunId || $quantity < 1) {
                throw new Exception('Geçersiz ürün ID veya adet');
            }
            
            // Önce bu ürünün sepetinde var mı kontrol et
            $stmt = $pdo->prepare("SELECT sepetid, adet FROM Sepet WHERE kullaniciid = ? AND urunid = ?");
            $stmt->execute([$currentUser['kullaniciid'], $urunId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingItem) {
                // Varsa adedini güncelle
                $newQuantity = $existingItem['adet'] + $quantity;
                $stmt = $pdo->prepare("UPDATE Sepet SET adet = ? WHERE sepetid = ?");
                $stmt->execute([$newQuantity, $existingItem['sepetid']]);
            } else {
                // Yoksa yeni ekle
                $stmt = $pdo->prepare("INSERT INTO Sepet (kullaniciid, urunid, adet) VALUES (?, ?, ?)");
                $stmt->execute([$currentUser['kullaniciid'], $urunId, $quantity]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Ürün sepete eklendi'
            ]);
            break;
            
        case 'update':
            // Sepetteki ürünün adedini güncelle
            if (!$sepetId || $quantity < 1) {
                throw new Exception('Geçersiz sepet ID veya adet');
            }
            
            // Bu sepet öğesinin kullanıcıya ait olduğunu kontrol et
            $stmt = $pdo->prepare("SELECT sepetid FROM Sepet WHERE sepetid = ? AND kullaniciid = ?");
            $stmt->execute([$sepetId, $currentUser['kullaniciid']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Bu sepet öğesi size ait değil');
            }
            
            // Adedi güncelle
            $stmt = $pdo->prepare("UPDATE Sepet SET adet = ? WHERE sepetid = ?");
            $stmt->execute([$quantity, $sepetId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sepet güncellendi'
            ]);
            break;
            
        case 'remove':
            // Sepetten ürün kaldır
            if (!$sepetId) {
                throw new Exception('Geçersiz sepet ID');
            }
            
            // Bu sepet öğesinin kullanıcıya ait olduğunu kontrol et
            $stmt = $pdo->prepare("SELECT sepetid FROM Sepet WHERE sepetid = ? AND kullaniciid = ?");
            $stmt->execute([$sepetId, $currentUser['kullaniciid']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Bu sepet öğesi size ait değil');
            }
            
            // Ürünü sepetten sil
            $stmt = $pdo->prepare("DELETE FROM Sepet WHERE sepetid = ?");
            $stmt->execute([$sepetId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ürün sepetten kaldırıldı'
            ]);
            break;
            
        default:
            throw new Exception('Geçersiz işlem');
    }
    
} catch (Exception $e) {
    // Genel hata durumu
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Veritabanı hatası durumu
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
