<?php
// DOSYA ADI: Test/admin/urunler/urunduzenle.php
// AÇIKLAMA: Admin ürün düzenleme sayfası

// 1. Merkezi oturum kontrol dosyamızı çağır
require_once __DIR__ . '/../../session.php';

// 2. ADMIN GÜVENLİK KONTROLÜ
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

// 3. Ürün ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: /admin/urunler/urunler.php");
    exit;
}

$urunId = (int)$_GET['id'];

// 4. Ürün bilgilerini çek
try {
    $stmt = $pdo->prepare("SELECT * FROM Urun WHERE urunid = ?");
    $stmt->execute([$urunId]);
    $urun = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$urun) {
        header("Location: /admin/urunler/urunler.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: /admin/urunler/urunler.php");
    exit;
}

// 5. KATEGORİLERİ ÇEK
try {
    $stmt = $pdo->prepare("SELECT * FROM Kategoriler ORDER BY kategoriadi");
    $stmt->execute();
    $kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategoriler = [];
}

// 6. ÜRÜN GÜNCELLEME İŞLEMİ
$mesaj = '';
$mesajTipi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urunAdi = trim($_POST['urun_adi'] ?? '');
    $yazar = trim($_POST['yazar'] ?? '');
    $kategoriId = (int)($_POST['kategori_id'] ?? 0);
    $fiyat = (float)($_POST['fiyat'] ?? 0);
    $aciklama = trim($_POST['aciklama'] ?? '');
    
    // Validasyon
    if (empty($urunAdi)) {
        $mesaj = "Ürün adı boş olamaz!";
        $mesajTipi = "error";
    } elseif (empty($yazar)) {
        $mesaj = "Yazar adı boş olamaz!";
        $mesajTipi = "error";
    } elseif ($kategoriId <= 0) {
        $mesaj = "Geçerli bir kategori seçiniz!";
        $mesajTipi = "error";
    } elseif ($fiyat <= 0) {
        $mesaj = "Geçerli bir fiyat giriniz!";
        $mesajTipi = "error";
    } else {
        try {
            // RESİM YÜKLEME İŞLEMİ
            $resimDosyasi = $_FILES['urun_resmi'] ?? null;
            $resimAdi = $urun['resim']; // Mevcut resmi koruyalım
            
            // Eğer yeni bir resim yüklenmişse
            if ($resimDosyasi && $resimDosyasi['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../kitaplar/';
                $dosyaUzantisi = strtolower(pathinfo($resimDosyasi['name'], PATHINFO_EXTENSION));
                $gecerliUzantilar = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                // Dosya uzantısını kontrol et
                if (!in_array($dosyaUzantisi, $gecerliUzantilar)) {
                    $mesaj = "Geçersiz dosya uzantısı! Sadece JPG, PNG, GIF, WEBP kabul edilir.";
                    $mesajTipi = "error";
                } else {
                    // Benzersiz dosya adı oluştur
                    $yeniDosyaAdi = 'urun_' . $urunId . '_' . time() . '.' . $dosyaUzantisi;
                    $hedefYol = $uploadDir . $yeniDosyaAdi;
                    
                    // Dosyayı yükle
                    if (move_uploaded_file($resimDosyasi['tmp_name'], $hedefYol)) {
                        // Eski resmi sil (varsa ve yeni resim farklıysa)
                        if (!empty($urun['resim']) && $urun['resim'] !== $yeniDosyaAdi) {
                            $eskiResimYolu = $uploadDir . $urun['resim'];
                            if (file_exists($eskiResimYolu)) {
                                @unlink($eskiResimYolu);
                            }
                        }
                        $resimAdi = $yeniDosyaAdi;
                    } else {
                        $mesaj = "Resim yükleme hatası!";
                        $mesajTipi = "error";
                    }
                }
            }
            
            // Eğer resim yükleme hatası yoksa veya resim yüklenmemişse (sadece diğer bilgiler güncellenecek)
            if ($mesajTipi !== "error") {
                // Ürünü veritabanında güncelle (resim dahil)
                $stmt = $pdo->prepare("UPDATE Urun SET urunadi = ?, yazar = ?, kategoriid = ?, fiyat = ?, aciklama = ?, resim = ? WHERE urunid = ?");
                $stmt->execute([$urunAdi, $yazar, $kategoriId, $fiyat, $aciklama, $resimAdi, $urunId]);
                
                $mesaj = "Ürün başarıyla güncellendi!";
                $mesajTipi = "success";
                
                // Güncellenmiş verileri tekrar çek
                $stmt = $pdo->prepare("SELECT * FROM Urun WHERE urunid = ?");
                $stmt->execute([$urunId]);
                $urun = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Ürün Düzenle</title>
    <link rel="stylesheet" href="urunduzenle/urunduzenle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="container">
        <div class="form-container">
            <header class="form-header">
                <h1>Ürün Düzenle</h1>
                <div class="header-buttons">
                    <a href="/admin/adminindex.php" class="btn btn-nav">
                        <i class="fas fa-home"></i> Anasayfaya Dön
                    </a>
                    <a href="/admin/urunler/urunler.php" class="btn btn-nav">
                        <i class="fas fa-list"></i> Ürün Listesine Dön
                    </a>
                </div>
            </header>

            <?php if ($mesaj): ?>
                <div class="alert alert-<?php echo $mesajTipi; ?>">
                    <?php echo htmlspecialchars($mesaj); ?>
                </div>
            <?php endif; ?>

            <form class="product-form" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="urun_adi">Ürün Adı</label>
                    <input type="text" id="urun_adi" name="urun_adi" 
                           value="<?php echo htmlspecialchars($urun['urunadi']); ?>" 
                           placeholder="Örn: Hayvan Çiftliği" required>
                </div>

                <div class="form-group">
                    <label for="yazar">Yazar</label>
                    <input type="text" id="yazar" name="yazar" 
                           value="<?php echo htmlspecialchars($urun['yazar']); ?>" 
                           placeholder="Örn: George Orwell" required>
                </div>

                <div class="form-group">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <option value="" disabled>Bir kategori seçin...</option>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <option value="<?php echo $kategori['kategoriid']; ?>" 
                                    <?php echo ($urun['kategoriid'] == $kategori['kategoriid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kategori['kategoriadi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fiyat">Fiyat (₺)</label>
                    <input type="number" id="fiyat" name="fiyat" step="0.01" min="0" 
                           value="<?php echo number_format($urun['fiyat'], 2); ?>" 
                           placeholder="Örn: 120.00" required>
                </div>

                <div class="form-group">
                    <label for="aciklama">Açıklama</label>
                    <textarea id="aciklama" name="aciklama" rows="4" 
                              placeholder="Ürün hakkında açıklama..."><?php echo htmlspecialchars($urun['aciklama'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ürün Resmi</label>
                    <div class="image-upload-wrapper">
                        <input type="file" id="product-image" accept="image/*" class="file-input" name="urun_resmi">
                        
                        <div id="image-preview" class="image-preview">
                            <?php if (!empty($urun['resim'])): ?>
                                <img src="/kitaplar/<?php echo htmlspecialchars($urun['resim']); ?>" alt="Mevcut Resim" style="max-width: 100%; max-height: 200px;">
                            <?php else: ?>
                                <span class="preview-text">Önizleme</span>
                            <?php endif; ?>
                        </div>

                        <label for="product-image" class="btn btn-secondary">
                            <i class="fas fa-upload"></i> Resim Değiştir
                        </label>
                    </div>
                    <small class="form-text">İsteğe bağlı - Yeni resim seçmezseniz mevcut resim korunur.</small>
                </div>

                <div class="form-actions">
                    <button onclick="location.href='/admin/urunler/urunler.php'" type="button" class="btn btn-cancel">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ürünü Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Resim önizleme fonksiyonu
        document.getElementById('product-image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Önizleme" style="max-width: 100%; max-height: 200px;">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<span class="preview-text">Önizleme</span>';
            }
        });
    </script>

</body>
</html>

