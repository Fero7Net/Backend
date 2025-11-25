<?php
// BACKEND: Dinamik kategori sayfası

require_once __DIR__ . '/../session.php';

// 2. KATEGORİLERİ ÇEK
// Navbar'da listelemek için
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

// 1. URL'den kategori ID'sini al
$kategori_id = $_GET['id'] ?? null;

$urunler = [];
$current_kategori = null; // Kategori bilgilerini (ad, id) tutmak için (isim çakışmasını önlemek için $kategori -> $current_kategori yaptım)

// 2. Kategori ID'si geçerli mi kontrol et
if ($kategori_id && is_numeric($kategori_id)) {
    try {
        // 3. Kategori bilgilerini çek (Sayfa başlığı ve adı için)
        $stmt_kat = $pdo->prepare("SELECT kategoriid, kategoriadi FROM Kategoriler WHERE kategoriid = ?");
        $stmt_kat->execute([$kategori_id]);
        $current_kategori = $stmt_kat->fetch(PDO::FETCH_ASSOC);

        // 4. Kategori bulunduysa, o kategoriye ait ürünleri çek
        if ($current_kategori) {
            $stmt = $pdo->prepare("
                SELECT u.*, k.kategoriadi 
                FROM Urun u 
                LEFT JOIN Kategoriler k ON u.kategoriid = k.kategoriid 
                WHERE u.kategoriid = ?
                ORDER BY u.urunid DESC
            ");
            $stmt->execute([$current_kategori['kategoriid']]);
            $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
    } catch (PDOException $e) {
        $urunler = [];
        $current_kategori = null;
    }
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $current_kategori ? htmlspecialchars($current_kategori['kategoriadi']) : 'Kategori'; ?> Kitapları - E-Kitap</title>
    
    <link rel="stylesheet" href="/index/nav.css">
    <link rel="stylesheet" href="/index/index.css">

    <style>
        .navbar-collapse {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            width: 100%; 
        }
        .navbar-search {
            flex-grow: 1; 
            display: flex;
            justify-content: center; 
            padding: 0 20px; 
        }
        .search-form {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 25px; 
            overflow: hidden; 
            background-color: #f7f7f7;
            width: 100%;
            max-width: 450px; 
        }
        .search-input {
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            flex-grow: 1; 
            outline: none; 
            background-color: transparent; 
        }
        .search-button {
            border: none;
            background-color: transparent;
            padding: 0 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .search-button img {
            height: 16px; 
            width: 16px;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .search-button:hover img {
            opacity: 1;
        }

        /* RESPONSIVE (MOBİL GÖRÜNÜM) */
        @media (max-width: 768px) {
            /* 1. Mobilde menü içeriğini gizle */
            .navbar-collapse {
                display: none; 
                flex-direction: column; 
                align-items: flex-start; 
            }
            /* 2. Tıklandığında menüyü göster */
            .menu-toggle:checked ~ .navbar-collapse {
                display: flex; 
            }
            .navbar-search {
                width: 100%; 
                padding: 15px 5%; 
                box-sizing: border-box; 
                margin-top: 0; 
                max-width: none; 
            }
            .search-form {
                max-width: none;
            }
        }
    </style>
    </head>
<body>

    <nav class="navbar">
      <div class="navbar-container">
        <a href="../index/index.php" class="navbar-brand">E-Kitap</a>
        <input type="checkbox" id="menu-toggle" class="menu-toggle" />
        <label for="menu-toggle" class="menu-toggle-label"><span></span><span></span><span></span></label>
        
        <div class="navbar-collapse">
          <ul class="nav-links">
            <li><a href="../index/index.php">Anasayfa</a></li>
            <li class="dropdown">
              <a href="#" <?php echo $current_kategori ? 'style="color:#3498db;"' : ''; ?>>Kategoriler</a> 
              <ul class="dropdown-menu">
                <?php
                  if (count($kategoriler) > 0) {
                      // $kategoriler dizisindeki 'kategori' değişkeni ile 
                      // dışarıdaki $kategori değişkeninin çakışmaması için 
                      // PHP bloğu içindeki değişkene $kat diyelim.
                      foreach ($kategoriler as $kat) {
                          echo '<li>'
                             . '<a href="/turler/kategori.php?id=' . $kat['kategoriid'] . '">' 
                             . htmlspecialchars($kat['kategoriadi'])
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

          <div class="navbar-search">
            <form class="search-form" id="page-search-form">
                <input type="text" placeholder="Bu kategoride ara..." class="search-input" id="page-search-input">
                <button type="submit" class="search-button" title="Ara">
                    <img src="https://cdn-icons-png.flaticon.com/512/149/149852.png" alt="Ara">
                </button>
            </form>
          </div>
          <div class="navbar-right">
            <?php if ($isLoggedIn): ?>
              <div class="cart-minimal">
                <a href="/sepet/sepet.php" title="Sepetim"> 
                  <img src="https://cdn-icons-png.flaticon.com/512/891/891462.png" alt="Sepet" />
                  <span class="cart-count"><?php echo $sepetUrunSayisi; ?></span>
                </a>
              </div>
              <div class="user-menu">
                <button class="user-btn"><img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Kullanıcı" /></button>
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
    
    <main class="page-content" style="padding-top: 100px;">
        <div class="anasayfa-populer-container">
            
            <?php if ($current_kategori): ?>
                
                <h2>Kategori: <?php echo htmlspecialchars($current_kategori['kategoriadi']); ?></h2>
                <div class="anasayfa-kitap-izgara">
                    
                    <?php if (empty($urunler)): ?>
                        <p style="text-align: center; width: 100%; padding: 20px;">
                            Bu kategoride henüz ürün bulunmuyor.
                        </p>
                    <?php else: ?>
                        <?php foreach ($urunler as $urun): ?>
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

                    <p id="arama-bulunamadi" style="display: none; text-align: center; width: 100%; padding: 20px; font-size: 1.1em; color: #555;">
                        Bu kategoride aradığınız kriterlere uygun ürün bulunamadı.
                    </p>
                    
                </div>
            
            <?php else: ?>
                <h2 style="text-align: center;">Kategori Bulunamadı</h2>
                <p style="text-align: center; width: 100%; padding: 20px;">
                    Aradığınız kategori mevcut değil veya geçerli bir kategori seçmediniz.
                </p>
            <?php endif; ?>
            
        </div>
    </main>
    
    <footer class="site-footer" style="margin-top: 50px;">
      <div class="footer-container">
        <p>&copy; 2025 E-Kitap. Tüm Hakları Saklıdır.</p>
      </div>
    </footer>

    <script src="/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const searchInput = document.getElementById('page-search-input');
            const searchForm = document.getElementById('page-search-form');
            const allProductCards = document.querySelectorAll('.anasayfa-kitap-kart');
            const notFoundMessage = document.getElementById('arama-bulunamadi');

            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = searchInput.value.toLowerCase().trim();
                    let visibleCount = 0;

                    allProductCards.forEach(function(card) {
                        // Kart içindeki metinleri al (Kitap adı ve yazar)
                        const title = card.querySelector('h3').textContent.toLowerCase();
                        const author = card.querySelector('.anasayfa-kitap-yazar').textContent.toLowerCase();

                        // Kategori bu sayfada tüm kartlarda aynı olduğu için 
                        // aramaya dahil etmeye gerek yok.
                        const cardText = title + ' ' + author;

                        if (cardText.includes(searchTerm)) {
                            card.style.display = ''; 
                            visibleCount++;
                        } else {
                            card.style.display = 'none'; 
                        }
                    });

                    // Gösterilen ürün yoksa "bulunamadı" mesajını göster
                    // (Sadece başlangıçta ürün varsa bu mesajı göster)
                    if (visibleCount === 0 && allProductCards.length > 0) {
                        notFoundMessage.style.display = 'block';
                    } else {
                        notFoundMessage.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>