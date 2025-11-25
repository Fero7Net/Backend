<?php
require_once __DIR__ . '/../session.php';


//kategorileri çekmek için
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


//çıkış mesajı kontolü
$logoutMessage = '';
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logoutMessage = 'Başarıyla çıkış yaptınız!';
}


try {
    //ana sayfada rastgele 4 ürün listeleme
    $stmt = $pdo->prepare("
        SELECT u.*, k.kategoriadi 
        FROM Urun u 
        LEFT JOIN Kategoriler k ON u.kategoriid = k.kategoriid 
        ORDER BY RANDOM() 
        LIMIT 4
    ");
    $stmt->execute();
    $populerUrunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // en son eklenenler
    $stmt = $pdo->prepare("
        SELECT u.*, k.kategoriadi 
        FROM Urun u 
        LEFT JOIN Kategoriler k ON u.kategoriid = k.kategoriid 
        ORDER BY u.urunid DESC 
        LIMIT 4
    ");
    $stmt->execute();
    $sonEklenenler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // hata olursa sayfayı boş göstermek yerine boş diziler ata
    $populerUrunler = [];
    $sonEklenenler = [];
} 
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>E-Kitap - Anasayfa</title>

  <link rel="stylesheet" href="/index/index.css" />
  <link rel="stylesheet" href="/index/nav.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>

  <nav class="navbar">
    <div class="navbar-container">
      <a href="/index/index.php" class="navbar-brand">E-Kitap</a>

      <input type="checkbox" id="menu-toggle" class="menu-toggle" />
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
          
          <?php if ($isLoggedIn): ?>
            
            <div class="cart-minimal">
              <a href="/sepet/sepet.php" title="Sepetim"> 
                <img src="https://cdn-icons-png.flaticon.com/512/891/891462.png" alt="Sepet" />
                <span class="cart-count"><?php echo $sepetUrunSayisi; ?></span>
              </a>
            </div>

            <div class="user-menu">
              <button class="user-btn">
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Kullanıcı" />
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

          <?php else: ?>

            <a href="/login/login.html" class="nav-button secondary">Giriş Yap</a>
            <a href="/signup/signup.html" class="nav-button primary">Üye Ol</a>
            
          <?php endif; ?>

        </div>
      </div>
    </div>
  </nav>

  <?php if (!empty($logoutMessage)): ?>
    <div class="logout-message" style="display: block;">
      <div class="logout-alert">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars($logoutMessage); ?></span>
        <button onclick="this.parentElement.parentElement.style.display='none'" class="close-btn">&times;</button>
      </div>
    </div>
  <?php endif; ?>

  <section class="slider-wrapper">
    <div class="slider-container">
      <button class="slider-btn prev">&#10094;</button>
      <div class="slider" id="slider">
        <div class="slide"><img src="/index/img/slider1.jpg" alt="Kitap 1"></div>
        <div class="slide"><img src="/index/img/slider2.jpg" alt="Kitap 2"></div>
        <div class="slide"><img src="/index/img/slider3.jpg" alt="Kitap 3"></div>
      </div>
      <button class="slider-btn next">&#10095;</button>
    </div>
  </section>

  <main class="page-content">
    <div class="content">
      <h1>E-Kitap'a Hoş Geldiniz!</h1>
      <p>Kalite ve Ucuzluğun Adresi.</p>
      <p>Yeni çıkan kitaplar ve fırsatlar burada sizi bekliyor!</p>
    </div>

    <section class="anasayfa-populer-bolum">
      <div class="anasayfa-populer-container">
        <h2>Popüler Kitaplar</h2>
        <div class="anasayfa-kitap-izgara">
          <?php if (empty($populerUrunler)): ?>
            <p style="text-align: center; width: 100%; padding: 20px;">
              Henüz ürün eklenmemiş.
            </p>
          <?php else: ?>
            <?php foreach ($populerUrunler as $urun): ?>
              <div class="anasayfa-kitap-kart" data-urun-id="<?php echo $urun['urunid']; ?>">
                <div class="anasayfa-kitap-resim">
                  <?php 
                  $resimYolu = !empty($urun['resim']) ? '/kitaplar/' . $urun['resim'] : 'https://via.placeholder.com/200x300/ccc/000?text=' . urlencode($urun['urunadi']);
                  ?>
                  <img src="<?php echo $resimYolu; ?>" 
                       alt="<?php echo htmlspecialchars($urun['urunadi']); ?>">
                </div>
                <div class="anasayfa-kitap-detay">
                  <h3><?php echo htmlspecialchars($urun['urunadi']); ?></h3>
                  <p class="anasayfa-kitap-yazar"><?php echo htmlspecialchars($urun['yazar']); ?></p>
                  <p class="anasayfa-kitap-fiyat"><?php echo number_format($urun['fiyat'], 2); ?> ₺</p>
                  <?php if ($isLoggedIn): ?>
                    <div class="anasayfa-kitap-aksiyon">
                      <div class="anasayfa-adet-kontrol">
                        <button type="button" class="anasayfa-adet-azalt" aria-label="Adet azalt">-</button>
                        <input type="number" class="anasayfa-kitap-adet" value="1" min="1" max="10" readonly aria-label="Adet">
                        <button type="button" class="anasayfa-adet-arttir" aria-label="Adet arttır">+</button>
                      </div>
                      <button class="anasayfa-sepete-ekle-btn">Sepete Ekle</button>
                    </div>
                  <?php else: ?>
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">
                      Sepete eklemek için <a href="/login/login.html">giriş yapın</a>
                    </p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
    
    <section class="anasayfa-populer-bolum">
      <div class="anasayfa-populer-container">
        <h2>Son Eklenenler</h2>
        <div class="anasayfa-kitap-izgara">
          <?php if (empty($sonEklenenler)): ?>
            <p style="text-align: center; width: 100%; padding: 20px;">
              Henüz ürün eklenmemiş.
            </p>
          <?php else: ?>
            <?php foreach ($sonEklenenler as $urun): ?>
              <div class="anasayfa-kitap-kart" data-urun-id="<?php echo $urun['urunid']; ?>">
                <div class="anasayfa-kitap-resim">
                  <?php 
                  $resimYolu = !empty($urun['resim']) ? '/kitaplar/' . $urun['resim'] : 'https://via.placeholder.com/200x300/ccc/000?text=' . urlencode($urun['urunadi']);
                  ?>
                  <img src="<?php echo $resimYolu; ?>" 
                       alt="<?php echo htmlspecialchars($urun['urunadi']); ?>">
                </div>
                <div class="anasayfa-kitap-detay">
                  <h3><?php echo htmlspecialchars($urun['urunadi']); ?></h3>
                  <p class="anasayfa-kitap-yazar"><?php echo htmlspecialchars($urun['yazar']); ?></p>
                  <p class="anasayfa-kitap-fiyat"><?php echo number_format($urun['fiyat'], 2); ?> ₺</p>
                  <?php if ($isLoggedIn): ?>
                    <div class="anasayfa-kitap-aksiyon">
                      <div class="anasayfa-adet-kontrol">
                        <button type="button" class="anasayfa-adet-azalt" aria-label="Adet azalt">-</button>
                        <input type="number" class="anasayfa-kitap-adet" value="1" min="1" max="10" readonly aria-label="Adet">
                        <button type="button" class="anasayfa-adet-arttir" aria-label="Adet arttır">+</button>
                      </div>
                      <button class="anasayfa-sepete-ekle-btn">Sepete Ekle</button>
                    </div>
                  <?php else: ?>
                    <p style="color: #666; font-size: 14px; margin-top: 10px;">
                      Sepete eklemek için <a href="/login/login.html">giriş yapın</a>
                    </p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
    
    </main>

  <footer class="site-footer">
    <div class="footer-container">
      <p>&copy; 2025 E-Kitap. Tüm Hakları Saklıdır.</p>
    </div>
  </footer>

  <script src="/main.js"></script> 
  <script src="/index/slider.js"></script>

  <script>
    // Çıkış mesajını otomatik kapat
    document.addEventListener('DOMContentLoaded', function() {
      const logoutMessage = document.querySelector('.logout-message');
      if (logoutMessage && logoutMessage.style.display !== 'none') {
        setTimeout(function() {
          logoutMessage.style.animation = 'slideDown 0.5s ease-out reverse';
          setTimeout(function() {
            logoutMessage.style.display = 'none';
          }, 500);
        }, 5000);
      }
    });
  </script> 

</body>
</html>