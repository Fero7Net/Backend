<?php
require_once __DIR__ . '/../../session.php';

// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

// kategorileri veritabanından çeker
try {
    $stmt = $pdo->prepare("SELECT * FROM Kategoriler ORDER BY kategoriadi");
    $stmt->execute();
    $kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategoriler = [];
}

$mesaj = '';
$mesajTipi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // formdan gelen verileri alır boşluk var ise temizler
    $urunAdi    = trim($_POST['urun_adi'] ?? '');
    $yazar      = trim($_POST['yazar'] ?? '');
    $kategoriId = (int)($_POST['kategori_id'] ?? 0);
    $fiyat      = (float)($_POST['fiyat'] ?? 0);
    $aciklama   = trim($_POST['aciklama'] ?? '');

    // boş veya hatalı kontrolü
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
            // resim yükleme
            $resimAdi = '';
            $resimDosyasi = $_FILES['urun_resmi'] ?? null;

            // resim yüklenmişmi diye kontrol eder
            if ($resimDosyasi && $resimDosyasi['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../kitaplar/';
                $dosyaUzantisi = strtolower(pathinfo($resimDosyasi['name'], PATHINFO_EXTENSION));
                $gecerliUzantilar = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                //dosya türü geçersiz olduğunda hata gösterir
                if (!in_array($dosyaUzantisi, $gecerliUzantilar)) {
                    $mesaj = "Geçersiz dosya uzantısı! Sadece JPG, PNG, GIF, WEBP kabul edilir.";
                    $mesajTipi = "error";
                } else {
                    $urunId = time();
                    $yeniDosyaAdi = 'urun_' . $urunId . '_' . rand(1000, 9999) . '.' . $dosyaUzantisi;
                    $hedefYol = $uploadDir . $yeniDosyaAdi;

                    if (move_uploaded_file($resimDosyasi['tmp_name'], $hedefYol)) {
                        $resimAdi = $yeniDosyaAdi;
                    } else {
                        $mesaj = "Resim yükleme hatası!";
                        $mesajTipi = "error";
                    }
                }
            }

            //veritabanını günceller
            if ($mesajTipi !== "error") {
                $stmt = $pdo->prepare("INSERT INTO Urun (urunadi, yazar, kategoriid, fiyat, aciklama, resim) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$urunAdi, $yazar, $kategoriId, $fiyat, $aciklama, $resimAdi]);

                $mesaj = "Ürün başarıyla eklendi!";
                $mesajTipi = "success";

                //güncellenmiş ürünü tekrar çeker
                $urunAdi = $yazar = $aciklama = '';
                $kategoriId = $fiyat = 0;
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
    <title>Yeni Ürün Ekle</title>
    <link rel="stylesheet" href="urunekle/urunekle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="container">
        <div class="form-container">
            <header class="form-header">
                <h1>Yeni Ürün Ekle</h1>
                <div class="header-buttons">
                    <a href="/admin/adminindex.php" class="btn btn-nav">
                        <i class="fas fa-home"></i> Anasayfa Dön
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
                           value="<?php echo htmlspecialchars($urunAdi ?? ''); ?>" 
                           placeholder="Örn: Hayvan Çiftliği" required>
                </div>

                <div class="form-group">
                    <label for="yazar">Yazar</label>
                    <input type="text" id="yazar" name="yazar" 
                           value="<?php echo htmlspecialchars($yazar ?? ''); ?>" 
                           placeholder="Örn: George Orwell" required>
                </div>

                <div class="form-group">
                    <label for="kategori_id">Kategori</label>
                    <select id="kategori_id" name="kategori_id" required>
                        <option value="" disabled <?php echo !isset($kategoriId) || $kategoriId == 0 ? 'selected' : ''; ?>>Bir kategori seçin...</option>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <option value="<?php echo $kategori['kategoriid']; ?>" 
                                    <?php echo (isset($kategoriId) && $kategoriId == $kategori['kategoriid']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kategori['kategoriadi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fiyat">Fiyat (₺)</label>
                    <input type="number" id="fiyat" name="fiyat" step="0.01" min="0" 
                           value="<?php echo $fiyat ?? ''; ?>" 
                           placeholder="Örn: 120.00" required>
                </div>

                <div class="form-group">
                    <label for="aciklama">Açıklama</label>
                    <textarea id="aciklama" name="aciklama" rows="4" 
                              placeholder="Ürün hakkında açıklama..."><?php echo htmlspecialchars($aciklama ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ürün Resmi</label>
                    <div class="image-upload-wrapper">
                        <input type="file" id="product-image" accept="image/*" class="file-input" name="urun_resmi">
                        
                        <div id="image-preview" class="image-preview">
                            <span class="preview-text">Önizleme</span>
                        </div>

                        <label for="product-image" class="btn btn-secondary">
                            <i class="fas fa-upload"></i> Resim Seç
                        </label>
                    </div>
                    <small class="form-text">İsteğe bağlı - JPG, PNG, GIF, WEBP formatları kabul edilir.</small>
                </div>

                <div class="form-actions">
                    <button onclick="location.href='/admin/urunler/urunler.php'" type="button" class="btn btn-cancel">İptal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ürünü Kaydet
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
