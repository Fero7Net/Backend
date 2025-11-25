<?php
require_once __DIR__ . '/../../session.php';
// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

$kullaniciId = (int)($_GET['id'] ?? 0); // get parametresi id alınır int çevrilir
if ($kullaniciId <= 0) {
    // ıd geçersiz ise kullanıcılar sayfasına döndürür
    header("Location: /admin/kullanicilar/kullanicilar.php");
    exit;
}

//kullanıcı bilgilerini veritabanından çeker
try {
    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE kullaniciid = ?");
    $stmt->execute([$kullaniciId]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kullanici) {
        //kullanıcı bulunamazsa listeye döndürür
        header("Location: /admin/kullanicilar/kullanicilar.php");
        exit;
    }
} catch (PDOException $e) {
    // PDO hatası olursa listeye döndürür
    header("Location: /admin/kullanicilar/kullanicilar.php");
    exit;
}

$mesaj = '';       // mesaj içeriği
$mesajTipi = '';   // mesaj tipi success veya error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // formdan gelen verileri alır boşluk var ise temizler
    $adi = trim($_POST['adi'] ?? '');
    $soyadi = trim($_POST['soyadi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    $yetki = $_POST['yetki'] ?? 'user';

    //boş veya hatalı kontrolü
    if (empty($adi)) {
        $mesaj = "Ad boş olamaz!";
        $mesajTipi = "error";
    } elseif (empty($soyadi)) {
        $mesaj = "Soyad boş olamaz!";
        $mesajTipi = "error";
    } elseif (empty($email)) {
        $mesaj = "E-posta boş olamaz!";
        $mesajTipi = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mesaj = "Geçerli bir e-posta adresi giriniz!";
        $mesajTipi = "error";
    } else {
        try {
            //kullanıcı ile epostayı karşılaştırır
            $stmt = $pdo->prepare("SELECT kullaniciid FROM Kullanici WHERE email = ? AND kullaniciid != ?");
            $stmt->execute([$email, $kullaniciId]);

            if ($stmt->fetch()) {
                $mesaj = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor!";
                $mesajTipi = "error";
            } else {
                //şifre güncelleme
                if (!empty($sifre)) {
                    if (strlen($sifre) < 4) {
                        $mesaj = "Şifre en az 4 karakter olmalıdır!";
                        $mesajTipi = "error";
                    } else {
                        //şifreyi hashler ve günceller
                        $hashedPassword = password_hash($sifre, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE Kullanici SET adi = ?, soyadi = ?, email = ?, sifre = ?, yetki = ? WHERE kullaniciid = ?");
                        $stmt->execute([$adi, $soyadi, $email, $hashedPassword, $yetki, $kullaniciId]);

                        $mesaj = "Kullanıcı başarıyla güncellendi!";
                        $mesajTipi = "success";

                        //güncellenmiş bilgileri tekrar çeker
                        $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE kullaniciid = ?");
                        $stmt->execute([$kullaniciId]);
                        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                } else {
                    //düzenlemede şifre boş bırakıldı ise şifre değişmez
                    $stmt = $pdo->prepare("UPDATE Kullanici SET adi = ?, soyadi = ?, email = ?, yetki = ? WHERE kullaniciid = ?");
                    $stmt->execute([$adi, $soyadi, $email, $yetki, $kullaniciId]);

                    $mesaj = "Kullanıcı başarıyla güncellendi!";
                    $mesajTipi = "success";

                    // güncellenmiş bilgileri tekrar çeker
                    $stmt = $pdo->prepare("SELECT * FROM Kullanici WHERE kullaniciid = ?");
                    $stmt->execute([$kullaniciId]);
                    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            $mesaj = "Hata: " . $e->getMessage();
            $mesajTipi = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcıyı Düzenle</title>
    
    <!-- Stil Dosyası -->
    <link rel="stylesheet" href="kullaniciduzenle/kullaniciduzenle.css">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome (Güncel sürüm) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <div class="form-container">
        <div class="form-header">
            <h2>Kullanıcıyı Düzenle</h2>
            <div class="header-buttons">
                <a href="/admin/adminindex.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Anasayfaya Dön
                </a>
                <a href="/admin/kullanicilar/kullanicilar.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Listeye Geri Dön
                </a>
            </div>
        </div>

        <?php if ($mesaj): ?>
            <div class="alert alert-<?php echo $mesajTipi; ?>">
                <?php echo htmlspecialchars($mesaj); ?>
            </div>
        <?php endif; ?>

        <form action="#" method="POST">
            <div class="form-body">
                <div class="form-group">
                    <label for="adi">Ad</label>
                    <input type="text" id="adi" name="adi" class="form-control" 
                           value="<?php echo htmlspecialchars($kullanici['adi']); ?>" 
                           placeholder="Kullanıcının adını girin" required>
                </div>

                <div class="form-group">
                    <label for="soyadi">Soyad</label>
                    <input type="text" id="soyadi" name="soyadi" class="form-control" 
                           value="<?php echo htmlspecialchars($kullanici['soyadi']); ?>" 
                           placeholder="Kullanıcının soyadını girin" required>
                </div>

                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($kullanici['email']); ?>" 
                           placeholder="kullanici@example.com" required>
                </div>

                <div class="form-group">
                    <label for="yetki">Yetki</label>
                    <select id="yetki" name="yetki" class="form-control">
                        <option value="user" <?php echo $kullanici['yetki'] === 'user' ? 'selected' : ''; ?>>Kullanıcı</option>
                        <option value="admin" <?php echo $kullanici['yetki'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="sifre">Yeni Şifre (İsteğe bağlı)</label>
                    <input type="password" id="sifre" name="sifre" class="form-control" 
                           placeholder="Değiştirmek istemiyorsanız boş bırakın">
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Güncelle
                </button>
                <button type="button" class="btn btn-warning" onclick="window.location.href='/admin/kullanicilar/kullanicilar.php'">
                    <i class="fas fa-times"></i> İptal
                </button>
            </div>
        </form>
    </div>

    <script src="kullaniciduzenle.js"></script>
</body>
</html>
