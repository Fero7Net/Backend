<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '../../db/database.php';//veritabanı bağlantısını sağlar.

$input = json_decode(file_get_contents('php://input'), true);//frontendden gelen json verisini okur.

if (!isset($input['email'], $input['password'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi gönderildi.']);
    exit;
}//kullanıcı bilgilerin hepsini veya doğru girmişmi diye kontrol eder.

$email = trim($input['email']);//email yazılırkan başında ve sonunda boşluk varsa silinir.
$password = $input['password'];

try {//veritabanında kullanıcı araması yapılır.
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {//kullanıcı bulunamadıysa bu mesajı verir
        echo json_encode(['success' => false, 'message' => 'E-posta bulunamadı.']);
        exit;
    }

    if (password_verify($password, $user['sifre'])) {//burda kullanıcının girdiği şifreyle veritabanındaki hash ile doğrulanır. eğer doğru ise session ile oturum başlar.
        echo json_encode(['success' => true, 'message' => 'Giriş başarılı!']);
        session_start();
        $_SESSION['user_id'] = $user['kullaniciid'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Şifre hatalı.']);
    }
} catch (PDOException $e) {//veritabanına bağlanılamadığında bu kod devreye girer.
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>