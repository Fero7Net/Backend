<?php
require_once __DIR__ . '/../session.php';

// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Yetkiniz yok!'
    ]);
    exit;
}

// istekler post ise kabul edilir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek tipi!'
    ]);
    exit;
}

// json verisini alır ve işler
$input = json_decode(file_get_contents('php://input'), true);
$kategoriId = isset($input['kategoriId']) ? (int)$input['kategoriId'] : 0;

// kategori id kontrolü
if ($kategoriId <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz kategori ID!'
    ]);
    exit;
}

try {
    //kategoriyi silmeden önce ürün olup olmadığını kontrol eder
    $stmt = $pdo->prepare("SELECT COUNT(*) as sayi FROM Urun WHERE kategoriid = ?");
    $stmt->execute([$kategoriId]);
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    //kategori silme
    if ($sonuc && $sonuc['sayi'] > 0) {
        // bu kategoride ürün olduğunu belirtir
        $stmt = $pdo->prepare("DELETE FROM Kategoriler WHERE kategoriid = ?");
        $stmt->execute([$kategoriId]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Kategori silindi. Bu kategorideki ürünlerin kategorisi kaldırıldı.',
            'warning' => true
        ]);
    } else {
        // kategoride ürün yoksa
        $stmt = $pdo->prepare("DELETE FROM Kategoriler WHERE kategoriid = ?");
        $stmt->execute([$kategoriId]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Kategori başarıyla silindi!'
        ]);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}

