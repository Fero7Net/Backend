<?php
// DOSYA ADI: admin/kategori_sil.php
// AÇIKLAMA: Kategori silme API endpoint'i - AJAX ile dinamik kategori silme işlemleri için

// 1. Merkezi oturum kontrol dosyamızı çağır
require_once __DIR__ . '/../session.php';

// 2. ADMIN GÜVENLİK KONTROLÜ - Sadece admin yetkisi olanlar kategori silebilir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Yetkiniz yok!'
    ]);
    exit;
}

// 3. AJAX isteği kontrolü - Sadece POST istekleri kabul edilir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek tipi!'
    ]);
    exit;
}

// 4. JSON verisini al ve işle
$input = json_decode(file_get_contents('php://input'), true);
$kategoriId = isset($input['kategoriId']) ? (int)$input['kategoriId'] : 0;

// 5. Kategori ID kontrolü
if ($kategoriId <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz kategori ID!'
    ]);
    exit;
}

try {
    // 6. Kategoriyi silmeden önce, bu kategoride ürün olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT COUNT(*) as sayi FROM Urun WHERE kategoriid = ?");
    $stmt->execute([$kategoriId]);
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 7. Kategori silme işlemi
    if ($sonuc && $sonuc['sayi'] > 0) {
        // Bu kategoride ürünler var - Foreign key constraint sayesinde kategoriid NULL olacak
        $stmt = $pdo->prepare("DELETE FROM Kategoriler WHERE kategoriid = ?");
        $stmt->execute([$kategoriId]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Kategori silindi. Bu kategorideki ürünlerin kategorisi kaldırıldı.',
            'warning' => true
        ]);
    } else {
        // Kategoride ürün yok - Normal silme
        $stmt = $pdo->prepare("DELETE FROM Kategoriler WHERE kategoriid = ?");
        $stmt->execute([$kategoriId]);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Kategori başarıyla silindi!'
        ]);
    }
    
} catch (PDOException $e) {
    // 8. Hata durumunda mesaj döndür
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Hata: ' . $e->getMessage()
    ]);
}

