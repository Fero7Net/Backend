<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/session.php';

// sadece post isteklerini kabul etme
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

try {
    // kullanıcının sepetindeki kayıtlar silinir
    $stmt = $pdo->prepare("DELETE FROM Sepet WHERE kullaniciid = ?");
    $stmt->execute([$currentUser['kullaniciid']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Siparişiniz onaylandı'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
