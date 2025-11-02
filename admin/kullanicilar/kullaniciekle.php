<?php
// BACKEND: Kullanıcı ekleme

require_once __DIR__ . '/../../session.php';

if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

// 3. KULLANICI EKLEME İŞLEMİ
$mesaj = '';
$mesajTipi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adi = trim($_POST['adi'] ?? '');
    $soyadi = trim($_POST['soyadi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sifre = trim($_POST['sifre'] ?? '');
    $yetki = $_POST['yetki'] ?? 'user';
    
    // Validasyon
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
    } elseif (empty($sifre)) {
        $mesaj = "Şifre boş olamaz!";
        $mesajTipi = "error";
    } elseif (strlen($sifre) < 4) {
        $mesaj = "Şifre en az 4 karakter olmalıdır!";
        $mesajTipi = "error";
    } else {
        try {
            // E-posta zaten var mı kontrol et
            $stmt = $pdo->prepare("SELECT kullaniciid FROM Kullanici WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $mesaj = "Bu e-posta adresi zaten kullanılıyor!";
                $mesajTipi = "error";
            } else {
                // Şifreyi hashle
                $hashedPassword = password_hash($sifre, PASSWORD_DEFAULT);
                
                // Yeni kullanıcı ekle
                $stmt = $pdo->prepare("INSERT INTO Kullanici (adi, soyadi, email, sifre, yetki) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$adi, $soyadi, $email, $hashedPassword, $yetki]);
                
                $mesaj = "Kullanıcı başarıyla eklendi!";
                $mesajTipi = "success";
                
                // Formu temizle
                $adi = $soyadi = $email = $sifre = '';
                $yetki = 'user';
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
    <title>Yeni Kullanıcı Ekle</title>
    <link rel="stylesheet" href="kullaniciekle/kullaniciekle.css">
</head>
<body>

    <div class="form-container">
        <header class="form-header">
            <h1>Yeni Kullanıcı Ekle</h1>
            <div class="header-buttons">
                <a href="/admin/adminindex.php" class="btn btn-secondary">
                    &#8962; Anasayfaya Dön
                </a>
                <a href="/admin/kullanicilar/kullanicilar.php" class="btn btn-primary">
                    &larr; Listeye Geri Dön
                </a>
            </div>
        </header>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-<?php echo $mesajTipi; ?>">
                <?php echo htmlspecialchars($mesaj); ?>
            </div>
        <?php endif; ?>
        
        <form action="#" method="POST">
            <div class="form-group">
                <label for="adi">Ad</label>
                <input type="text" id="adi" name="adi" 
                       value="<?php echo htmlspecialchars($adi ?? ''); ?>" 
                       placeholder="Kullanıcının adını girin" required>
            </div>
            
            <div class="form-group">
                <label for="soyadi">Soyad</label>
                <input type="text" id="soyadi" name="soyadi" 
                       value="<?php echo htmlspecialchars($soyadi ?? ''); ?>" 
                       placeholder="Kullanıcının soyadını girin" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                       placeholder="kullanici@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="sifre">Şifre</label>
                <input type="password" id="sifre" name="sifre" 
                       placeholder="Güçlü bir şifre belirleyin" required>
            </div>
            
            <div class="form-group">
                <label for="yetki">Yetki</label>
                <select id="yetki" name="yetki">
                    <option value="user" <?php echo (!isset($yetki) || $yetki === 'user') ? 'selected' : ''; ?>>Kullanıcı</option>
                    <option value="admin" <?php echo (isset($yetki) && $yetki === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Ekle</button>
                <button type="button" class="btn btn-warning" onclick="window.location.href='/admin/kullanicilar/kullanicilar.php'">İptal</button>
            </div>
        </form>
    </div>

<script src="kullaniciekle.js"></script>
</body>
</html>
