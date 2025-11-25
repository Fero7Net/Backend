<?php
require_once __DIR__ . '/../../session.php';

// kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

//kategori ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_adi'])) {
    $kategoriAdi = trim($_POST['kategori_adi']);
    
    if (!empty($kategoriAdi)) {
        try {
            // kategori zaten var mı kontrol eder
            $stmt = $pdo->prepare("SELECT kategoriid FROM Kategoriler WHERE kategoriadi = ?");
            $stmt->execute([$kategoriAdi]);
            
            if ($stmt->fetch()) {
                $kategoriMesaji = "Bu kategori zaten mevcut!";
                $kategoriMesajTipi = "error";
            } else {
                // yeni kategori ekleme
                $stmt = $pdo->prepare("INSERT INTO Kategoriler (kategoriadi) VALUES (?)");
                $stmt->execute([$kategoriAdi]);
                $kategoriMesaji = "Kategori başarıyla eklendi!";
                $kategoriMesajTipi = "success";
            }
        } catch (PDOException $e) {
            $kategoriMesaji = "Hata: " . $e->getMessage();
            $kategoriMesajTipi = "error";
        }
    } else {
        $kategoriMesaji = "Kategori adı boş olamaz!";
        $kategoriMesajTipi = "error";
    }
}

// veritabanından kategorileri çeker
try {
    $stmt = $pdo->prepare("SELECT * FROM Kategoriler ORDER BY kategoriadi");
    $stmt->execute();
    $kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategoriler = [];
}

// veritabanından ürünleri çeker
try {
    $stmt = $pdo->prepare("
        SELECT u.*, k.kategoriadi 
        FROM Urun u 
        LEFT JOIN Kategoriler k ON u.kategoriid = k.kategoriid 
        ORDER BY u.urunid DESC
    ");
    $stmt->execute();
    $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $urunler = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Listesi</title>
    <link rel="stylesheet" href="urunler/urunler.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <div class="container">
        <header class="main-header">
            <h1>Ürün Listesi</h1>
        </header>

        <section class="controls">
            <!-- KATEGORİ EKLEME FORMU (R4.3) -->
            <form class="category-form" method="POST">
                <input type="text" name="kategori_adi" placeholder="Eklenecek Kategori Adı" required>
                <button type="submit" class="btn btn-light">Kategori Ekle</button>
            </form>
            
            <?php if (isset($kategoriMesaji)): ?>
                <div class="alert alert-<?php echo $kategoriMesajTipi; ?>">
                    <?php echo htmlspecialchars($kategoriMesaji); ?>
                </div>
            <?php endif; ?>
            
            <div class="page-actions">
                <button onclick="location.href='/admin/adminindex.php'" class="btn btn-secondary">Anasayfaya Dön</button>
                <button onclick="location.href='/admin/urunler/urunekle.php'" class="btn btn-primary">Yeni Ürün Ekle</button>
            </div>
        </section>

        <!-- KATEGORİ LİSTESİ VE SİLME (R4.4) -->
        <section class="category-management">
            <h2 class="section-title">
                <i class="fas fa-tags"></i> Kategoriler
            </h2>
            
            <?php if (empty($kategoriler)): ?>
                <div class="alert alert-info">
                    Henüz kategori eklenmemiş.
                </div>
            <?php else: ?>
                <div class="category-list">
                    <?php foreach ($kategoriler as $kategori): ?>
                        <div class="category-item" data-kategori-id="<?php echo $kategori['kategoriid']; ?>">
                            <span class="category-name">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($kategori['kategoriadi']); ?>
                            </span>
                            <button onclick="deleteCategory(<?php echo $kategori['kategoriid']; ?>)" 
                                    class="btn-icon btn-delete-small" 
                                    aria-label="Kategoriyi Sil"
                                    title="Kategoriyi Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="product-list">
            <div class="product-table">
                <div class="table-header">
                    <div class="header-cell">Ürün Resmi</div>
                    <div class="header-cell">Ürün Adı</div>
                    <div class="header-cell">Yazar</div>
                    <div class="header-cell">Kategori</div>
                    <div class="header-cell">Fiyat</div>
                    <div class="header-cell">İşlemler</div>
                </div>

                <?php if (empty($urunler)): ?>
                    <div class="table-row">
                        <div class="table-cell" colspan="6" style="text-align: center; padding: 20px;">
                            Henüz ürün eklenmemiş.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($urunler as $urun): ?>
                        <div class="table-row">
                            <div class="table-cell" data-label="Ürün Resmi">
                                <?php 
                                $resimPath = !empty($urun['resim']) ? '../../kitaplar/' . $urun['resim'] : 'https://via.placeholder.com/80x120/ccc/000?text=' . urlencode($urun['urunadi']);
                                ?>
                                <img src="<?php echo $resimPath; ?>" 
                                     alt="<?php echo htmlspecialchars($urun['urunadi']); ?>" class="product-image">
                            </div>
                            <div class="table-cell" data-label="Ürün Adı">
                                <?php echo htmlspecialchars($urun['urunadi']); ?>
                            </div>
                            <div class="table-cell" data-label="Yazar">
                                <?php echo htmlspecialchars($urun['yazar']); ?>
                            </div>
                            <div class="table-cell" data-label="Kategori">
                                <?php echo htmlspecialchars($urun['kategoriadi'] ?? 'Kategori Yok'); ?>
                            </div>
                            <div class="table-cell" data-label="Fiyat">
                                <?php echo number_format($urun['fiyat'], 2); ?> ₺
                            </div>
                            <div class="table-cell" data-label="İşlemler">
                                <button onclick="location.href='/admin/urunler/urunduzenle.php?id=<?php echo $urun['urunid']; ?>'" 
                                        class="btn-icon btn-edit" aria-label="Düzenle">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button onclick="deleteProduct(<?php echo $urun['urunid']; ?>)" 
                                        class="btn-icon btn-delete" aria-label="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        // ÜRÜN SİLME FONKSİYONU
        function deleteProduct(urunId) {
            if (confirm('Bu ürünü silmek istediğinizden emin misiniz?')) {
                // AJAX ile ürün silme işlemi
                fetch('/admin/urun_sil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        urunId: urunId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Ürün başarıyla silindi!');
                        location.reload();
                    } else {
                        alert('Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Bir hata oluştu');
                });
            }
        }

        // KATEGORİ SİLME FONKSİYONU (R4.4)
        function deleteCategory(kategoriId) {
            // Kullanıcıya onay penceresi göster
            if (confirm('Bu kategoriyi silmek istediğinizden emin misiniz?\n\nBu kategoride ürünler varsa, bu ürünlerin kategorisi kaldırılacaktır.')) {
                // AJAX ile kategori silme işlemi - Dinamik olarak çalışır, sayfa yenilenmez
                fetch('/admin/kategori_sil.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        kategoriId: kategoriId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Başarılı mesaj göster
                        if (data.warning) {
                            alert('⚠ ' + data.message);
                        } else {
                            alert('✓ ' + data.message);
                        }
                        
                        // Kategori listesinden dinamik olarak kaldır
                        const kategoriItem = document.querySelector(`.category-item[data-kategori-id="${kategoriId}"]`);
                        if (kategoriItem) {
                            kategoriItem.style.transition = 'opacity 0.3s';
                            kategoriItem.style.opacity = '0';
                            setTimeout(() => {
                                kategoriItem.remove();
                                
                                // Eğer son kategori silindiyse, bilgilendirme mesajı göster
                                const categoryList = document.querySelector('.category-list');
                                if (categoryList && categoryList.children.length === 0) {
                                    categoryList.parentElement.innerHTML = '<div class="alert alert-info">Henüz kategori eklenmemiş.</div>';
                                }
                            }, 300);
                        } else {
                            // HTML bulunamadıysa sayfayı yenile
                            location.reload();
                        }
                    } else {
                        alert('❌ Hata: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Bir hata oluştu');
                });
            }
        }
    </script>

</body>
</html>
