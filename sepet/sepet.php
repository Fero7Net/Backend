<?php
require_once __DIR__ . '/../session.php';

//kategorileri çeker
try { 
    $query = $pdo->query("
        SELECT 
            k.kategoriid, 
            k.kategoriadi, 
            COUNT(u.urunid) AS urun_sayisi
        FROM 
            Kategoriler k
        LEFT JOIN 
            Urun u ON k.kategoriid = u.kategoriid
        GROUP BY 
            k.kategoriid, k.kategoriadi
        ORDER BY 
            k.kategoriadi ASC
    ");
    $kategoriler = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kategoriler = [];
}

//giriş yapılmadıysa giriş yap sayfasına yönlendirir
if (!$isLoggedIn) {
    header("Location: /login/login.html");
    exit;
}

$orderMessage = '';
if (isset($_GET['order']) && $_GET['order'] === 'success') {
    $orderMessage = 'Siparişiniz onaylandı!';
}

// sepetteki ürünleri getirir
try {
    $stmt = $pdo->prepare("
        SELECT s.sepetid, s.adet, u.urunid, u.urunadi, u.yazar, u.fiyat, u.aciklama, u.resim
        FROM Sepet s 
        JOIN Urun u ON s.urunid = u.urunid 
        WHERE s.kullaniciid = ?
        ORDER BY s.sepetid DESC
    ");
    $stmt->execute([$currentUser['kullaniciid']]);
    $sepetUrunleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $toplamTutar = 0;
    $toplamUrunSayisi = 0;
    foreach ($sepetUrunleri as $urun) {
        $toplamTutar += $urun['fiyat'] * $urun['adet'];
        $toplamUrunSayisi += $urun['adet'];
    }
    
} catch (PDOException $e) {
    $sepetUrunleri = [];
    $toplamTutar = 0;
    $toplamUrunSayisi = 0;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Kitap - Sepetim</title>
    <link rel="stylesheet" href="/index/nav.css">
    <link rel="stylesheet" href="/index/index.css">
    <link rel="stylesheet" href="sepet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>

    <nav class="navbar">
        <div class="navbar-container">
            <a href="/index/index.php" class="navbar-brand">E-Kitap</a>

            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="menu-toggle-label">
                <span></span><span></span><span></span>
            </label>

            <div class="navbar-collapse">
                <ul class="nav-links">
                    <li><a href="/index/index.php">Anasayfa</a></li>
                    <li class="dropdown">
                        <a href="#">Kategoriler</a>
                        <ul class="dropdown-menu">
                            <?php
                            if (count($kategoriler) > 0) {
                                foreach ($kategoriler as $kategori) {
                                    echo '<li>'
                                        . '<a href="/turler/kategori.php?id=' . $kategori['kategoriid'] . '">' 
                                        . htmlspecialchars($kategori['kategoriadi'])
                                        . '</a>'
                                        . '</li>';
                                }
                            } else {
                                echo '<li><a>Henüz kategori eklenmemiş.</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <li><a href="/urunler/urunler.php">Ürünler</a></li>
                </ul>

                <div class="navbar-right">
                    <div class="cart-minimal">
                        <a href="/sepet/sepet.php" title="Sepetim">
                            <img src="https://cdn-icons-png.flaticon.com/512/891/891462.png" alt="Sepet">
                            <span class="cart-count"><?php echo $sepetUrunSayisi; ?></span>
                        </a>
                    </div>

                    <div class="user-menu">
                        <button class="user-btn">
                            <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Kullanıcı">
                        </button>
                        <ul class="user-dropdown">
                            <li><a href="#">Merhaba, <?php echo htmlspecialchars($currentUser['adi']); ?></a></li>
                            <?php if ($currentUser['yetki'] === 'admin'): ?>
                              <li><a href="/admin/adminindex.php">Admin Paneli</a></li>
                            <?php endif; ?>
                            <li><a href="/profil/profil.php">Bilgilerimi Görüntüle</a></li>
                            <li><a href="/login/logout.php">Çıkış Yap</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- SİPARİŞ ONAY MESAJI -->
    <?php if (!empty($orderMessage)): ?>
        <div class="logout-message">
            <div class="logout-alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($orderMessage); ?></span>
                <button onclick="this.parentElement.parentElement.style.display='none'" class="close-btn">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="page-content">
        <div class="cart-container">
            <h1>Sepetim (<?php echo $toplamUrunSayisi; ?> Ürün)</h1>

            <?php if (empty($sepetUrunleri)): ?>
                <!-- SEPET BOŞ MESAJI -->
                <div class="empty-cart">
                    <h2>Sepetiniz boş.</h2>
                    <p>Alışverişe devam etmek için <a href="/urunler/urunler.php">ürünler sayfasını</a> ziyaret edebilirsiniz.</p>
                </div>
            <?php else: ?>
                <!-- SEPET DOLU - ÜRÜNLERİ LİSTELE -->
                <div class="cart-layout">
                    <div class="cart-items-wrapper">
                        <div class="cart-header">
                            <span class="header-product">Ürün</span>
                            <span class="header-quantity">Adet</span>
                            <span class="header-total">Toplam</span>
                        </div>

                        <div class="cart-items-list">
                            <?php foreach ($sepetUrunleri as $urun): ?>
                                <div class="cart-item" data-sepet-id="<?php echo $urun['sepetid']; ?>">
                                    <?php 
                                    $resimYolu = !empty($urun['resim']) ? '/kitaplar/' . $urun['resim'] : 'https://via.placeholder.com/80x120/ccc/000?text=' . urlencode($urun['urunadi']);
                                    ?>
                                    <img src="<?php echo $resimYolu; ?>" 
                                         alt="<?php echo htmlspecialchars($urun['urunadi']); ?>" class="item-image">
                                    
                                    <div class="item-details">
                                        <h3 class="item-title"><?php echo htmlspecialchars($urun['urunadi']); ?></h3>
                                        <p class="item-author"><?php echo htmlspecialchars($urun['yazar']); ?></p>
                                        <p class="item-price">Birim Fiyat: <?php echo number_format($urun['fiyat'], 2); ?> ₺</p>
                                    </div>

                                    <div class="item-quantity">
                                        <button class="quantity-btn minus" onclick="updateQuantity(<?php echo $urun['sepetid']; ?>, -1)">-</button>
                                        <input type="number" value="<?php echo $urun['adet']; ?>" min="1" class="quantity-input" 
                                               onchange="updateQuantity(<?php echo $urun['sepetid']; ?>, 0, this.value)">
                                        <button class="quantity-btn plus" onclick="updateQuantity(<?php echo $urun['sepetid']; ?>, 1)">+</button>
                                    </div>

                                    <div class="item-total-price"><?php echo number_format($urun['fiyat'] * $urun['adet'], 2); ?> ₺</div>
                                    <button class="item-remove-btn" onclick="removeFromCart(<?php echo $urun['sepetid']; ?>)" title="Kaldır">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="cart-summary">
                        <h2>Sipariş Özeti</h2>
                        <div class="summary-row">
                            <span>Ara Toplam (<?php echo $toplamUrunSayisi; ?> ürün)</span>
                            <span><?php echo number_format($toplamTutar, 2); ?> ₺</span>
                        </div>
                        <div class="summary-row">
                            <span>Kargo</span>
                            <span class="free-shipping">Ücretsiz</span>
                        </div>
                        <div class="summary-row total">
                            <strong>Genel Toplam</strong>
                            <strong><?php echo number_format($toplamTutar, 2); ?> ₺</strong>
                        </div>
                        
                        <!-- Siparişi tamamla -->
                        <button class="btn-checkout" onclick="completeOrder()">Siparişi Bitir</button>
                        <a href="/urunler/urunler.php" class="btn-continue">Alışverişe Devam Et</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <p>&copy; 2025 E-Kitap. Tüm Hakları Saklıdır.</p>
        </div>
    </footer>

    <script src="/main.js"></script>
    <script>
        // Sepet güncelleme fonksiyonları
        function updateQuantity(sepetId, change, newValue = null) {
            let quantity = 1;
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                const input = document.querySelector(`[data-sepet-id="${sepetId}"] .quantity-input`);
                quantity = parseInt(input.value) + change;
            }
            
            if (quantity < 1) quantity = 1;
            
            fetch('/sepet_guncelle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    sepetId: sepetId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Sayfayı yenile
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu');
            });
        }

        function removeFromCart(sepetId) {
            if (confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
                fetch('/sepet_guncelle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        sepetId: sepetId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Sayfayı yenile
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

        function completeOrder() {
            if (confirm('Siparişinizi tamamlamak istediğinizden emin misiniz?')) {
                fetch('/siparis_tamamla.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Sepet sayfasına yönlendir (mesaj göstermek için)
                        window.location.href = '/sepet/sepet.php?order=success';
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

        // SİPARİŞ ONAY MESAJI OTOMATİK KAPATMA
        document.addEventListener('DOMContentLoaded', function() {
            const orderMessage = document.querySelector('.logout-message');
            if (orderMessage) {
                setTimeout(function() {
                    orderMessage.style.animation = 'slideDown 0.5s ease-out reverse';
                    setTimeout(function() {
                        orderMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
