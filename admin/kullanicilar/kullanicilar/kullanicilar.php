<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../db/database.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['yetki'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'GET') {
        // Tüm kullanıcıları çek
        $stmt = $pdo->query("SELECT adi, soyadi, email FROM Kullanici");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $users]);

    } elseif ($method === 'DELETE') {
        if (!isset($input['email'])) {
            echo json_encode(['success' => false, 'message' => 'E-posta gönderilmedi.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM Kullanici WHERE email = ?");
        $stmt->execute([$input['email']]);
        echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla silindi!']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Desteklenmeyen istek yöntemi.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>
