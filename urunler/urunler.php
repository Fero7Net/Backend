<?php
// BACKEND: Tüm ürünler

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
    <title>Tüm Ürünler - E-Kitap</title>
    
    <link rel="stylesheet" href="/index/nav.css">
    <link rel="stylesheet" href="/index/index.css"> 
    
    <style>
        /* Navbar'ın ana yapısını flex (esnek) hale getiriyoruz (DESKTOP) */
        .navbar-collapse {
            display: flex;
            justify-content: space-between; /* Linkler, Arama ve Sağ Menü arasını açar */
            align-items: center;
            width: 100%; /* Mobil menü kapalıyken tam genişlik */
        }
        .navbar-search {
            flex-grow: 1; /* Arama çubuğunun mevcut alanı doldurmasını sağlar */
            display: flex;
            justify-content: center; /* Arama çubuğunu yatayda ortalar */
            padding: 0 20px; /* Linkler ve ikonlarla arasına boşluk koyar */
        }
        .search-form {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 25px; /* Oval görünüm */
            overflow: hidden; /* Butonun kenarlarını gizler */
            background-color: #f7f7f7;
            width: 100%;
            max-width: 450px; /* Arama çubuğu maksimum genişliği */
        }
        .search-input {
            border: none;
            padding: 10px 15px;
            font-size: 14px;
            flex-grow: 1; /* Input alanının esneyip genişlemesini sağlar */
            outline: none; /* Tıklama çerçevesini kaldırır */
            background-color: transparent; /* Formun arka plan rengini alır */
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
            height: 16px; /* İkon boyutu */
            width: 16px;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .search-button:hover img {
            opacity: 1; /* Üzerine gelince ikonu netleştir */
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
            <li><a href="/urunler/urunler.php" style="color:#3498db;">Ürünler</a></li>
          </ul>

          <div class="navbar-search">
            <form class="search-form" id="page-search-form">
                <input type="text" placeholder="Kitap, yazar veya kategori ara..." class="search-input" id="page-search-input">
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
            <h2>Tüm Ürünler</h2>
            <div class="anasayfa-kitap-izgara">
                <?php if (empty($urunler)): ?>
                    <p style="text-align: center; width: 100%; padding: 20px;">
                        Henüz ürün eklenmemiş.
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
                                <p class="anasayfa-kitap-kategori"><?php echo htmlspecialchars($urun['kategoriadi'] ?? 'Kategori Yok'); ?></p>
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
                    Aradığınız kriterlere uygun ürün bulunamadı.
                </p>
            </div>
        </div>
    </main>
    
    <footer class="site-footer" style="margin-top: 50px;">
      <div class="footer-container">
        <p>&copy; 2025 E-Kitap. Tüm Hakları Saklıdır.</p>
      </div>
    </footer>

    <script src="/main.js"></script>

    <script>
        // Sayfanın tamamen yüklendiğinden emin ol
        document.addEventListener('DOMContentLoaded', function() {
            
            // Gerekli HTML elementlerini seç
            const searchInput = document.getElementById('page-search-input');
            const searchForm = document.getElementById('page-search-form');
            const allProductCards = document.querySelectorAll('.anasayfa-kitap-kart');
            const notFoundMessage = document.getElementById('arama-bulunamadi');

            // Enter tuşuna basıldığında sayfanın yenilenmesini engelle
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                });
            }

            // Arama kutusuna her harf girildiğinde (veya silindiğinde) filtrele
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = searchInput.value.toLowerCase().trim();
                    let visibleCount = 0;

                    // Tüm ürün kartlarını dolaş
                    allProductCards.forEach(function(card) {
                        // Kart içindeki metinleri al (Kitap adı, yazar, kategori)
                        const title = card.querySelector('h3').textContent.toLowerCase();
                        const author = card.querySelector('.anasayfa-kitap-yazar').textContent.toLowerCase();
                        const category = card.querySelector('.anasayfa-kitap-kategori').textContent.toLowerCase();

                        // Tüm metinleri birleştir
                        const cardText = title + ' ' + author + ' ' + category;

                        // Arama terimi, kart metninde geçiyorsa
                        if (cardText.includes(searchTerm)) {
                            card.style.display = ''; // Kartı göster (varsayılan stile dön)
                            visibleCount++;
                        } else {
                            card.style.display = 'none'; // Kartı gizle
                        }
                    });

                    // Gösterilen ürün yoksa "bulunamadı" mesajını göster
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