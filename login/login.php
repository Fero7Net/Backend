<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/database.php';

// json formatında gelen veriyi alıyoruz
$input = json_decode(file_get_contents('php://input'), true);

// gerekli alanlar geldi mi diye kontrol
if (!isset($input['email'], $input['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Eksik bilgi gönderildi.'
    ]);
    exit;
}

// verileri temizleme
$email = trim($input['email']);
$password = $input['password'];

try {
    // veritabanından kullanıcıyı e-posta ile çekme
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // kullanıcı yoksa hata döndür
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'E-posta bulunamadı.'
        ]);
        exit;
    }

    // şifre doğrulama
    if (password_verify($password, $user['sifre'])) {
        session_start();
        $_SESSION['user_id'] = $user['kullaniciid'];

        // kullanıcı admin ise farklı sayfaya yönlendirir
        if ($user['yetki'] === 'admin') {
            echo json_encode([
                'success' => true,
                'redirect' => '/admin/adminindex.php'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'redirect' => '/index/index.php'
            ]);
        }

    } else {
        // hatalı şifre
        echo json_encode([
            'success' => false,
            'message' => 'Şifre hatalı.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorgu hatası: ' . $e->getMessage()
    ]);
}
?>
