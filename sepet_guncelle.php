<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/session.php';

// sadece POST isteklerini kabul eder
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST istekleri kabul edilir'
    ]);
    exit;
}

// kullanıcı giriş yapmamışsa hata verir
if (!$isLoggedIn) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Giriş yapmanız gerekiyor'
    ]);
    exit;
}

// json formatında gelen veriyi alıyoruz
$input = json_decode(file_get_contents('php://input'), true);

// json verisi geçersiz ise hata verir
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz JSON verisi'
    ]);
    exit;
}

// gelen verileri alma
$action = $input['action'] ?? '';
$urunId = $input['urunId'] ?? null;
$quantity = $input['quantity'] ?? 1;
$sepetId = $input['sepetId'] ?? null;

try {
    switch ($action) {
        case 'add':
            // sepete ürün ekleme işlemi
            if (!$urunId || $quantity < 1) {
                throw new Exception('Geçersiz ürün ID veya adet');
            }
            
            // ürün sepette varmı kontrol eder
            $stmt = $pdo->prepare("SELECT sepetid, adet FROM Sepet WHERE kullaniciid = ? AND urunid = ?");
            $stmt->execute([$currentUser['kullaniciid'], $urunId]);
            $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingItem) {
                // ürün sepette var ise adet güncellenir
                $newQuantity = $existingItem['adet'] + $quantity;
                $stmt = $pdo->prepare("UPDATE Sepet SET adet = ? WHERE sepetid = ?");
                $stmt->execute([$newQuantity, $existingItem['sepetid']]);
            } else {
                // ürün sepette yok ise eklenir
                $stmt = $pdo->prepare("INSERT INTO Sepet (kullaniciid, urunid, adet) VALUES (?, ?, ?)");
                $stmt->execute([$currentUser['kullaniciid'], $urunId, $quantity]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Ürün sepete eklendi'
            ]);
            break;
            
        case 'update':
            // sepetteki ürünün adedini günceller
            if (!$sepetId || $quantity < 1) {
                throw new Exception('Geçersiz sepet ID veya adet');
            }
            
            // bu sepetteki öge kullanıcıya ait mi kontrol eder
            $stmt = $pdo->prepare("SELECT sepetid FROM Sepet WHERE sepetid = ? AND kullaniciid = ?");
            $stmt->execute([$sepetId, $currentUser['kullaniciid']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Bu sepet öğesi size ait değil');
            }
            
            $stmt = $pdo->prepare("UPDATE Sepet SET adet = ? WHERE sepetid = ?");
            $stmt->execute([$quantity, $sepetId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Sepet güncellendi'
            ]);
            break;
            
        case 'remove':
            // sepetten ürün kaldırır
            if (!$sepetId) {
                throw new Exception('Geçersiz sepet ID');
            }
            
            // bu sepetteki öge kullanıcıya ait mi kontrol eder
            $stmt = $pdo->prepare("SELECT sepetid FROM Sepet WHERE sepetid = ? AND kullaniciid = ?");
            $stmt->execute([$sepetId, $currentUser['kullaniciid']]);
            
            if (!$stmt->fetch()) {
                throw new Exception('Bu sepet öğesi size ait değil');
            }
            
            // ürünü sepetten kaldırır
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
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
