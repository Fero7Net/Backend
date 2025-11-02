<?php
// BACKEND: Login API

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email'], $input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'E-posta bulunamadı.']);
        exit;
    }

    if (password_verify($password, $user['sifre'])) {
        session_start();
        $_SESSION['user_id'] = $user['kullaniciid'];
        
        if ($user['yetki'] === 'admin') {
            echo json_encode(['success' => true, 'redirect' => '/admin/adminindex.php']);
        } else {
            echo json_encode(['success' => true, 'redirect' => '/index/index.php']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Şifre hatalı.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Sorgu hatası: ' . $e->getMessage()]);
}
?>