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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Bilgilerim - E-Kitap</title>
    
    <link rel="stylesheet" href="/index/nav.css">
    
    <link rel="stylesheet" href="/profil/profil.css">
</head>
<body>

    <nav class="navbar">
      <div class="navbar-container">
        <a href="/index/index.php" class="navbar-brand">E-Kitap</a>
        <input type="checkbox" id="menu-toggle" class="menu-toggle" />
        <label for="menu-toggle" class="menu-toggle-label"><span></span><span></span><span></span></label>
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
            <?php // Navbar'ın sağ tarafı $isLoggedIn = true olduğu için GİRİŞ YAPMIŞ menüsünü gösterecek ?>
            <div class="cart-minimal">
              <a href="/sepet/sepet.php" title="Sepetim"> 
                <img src="https://cdn-icons-png.flaticon.com/512/891/891462.png" alt="Sepet" />
                <span class="cart-count">0</span>
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
          </div>
        </div>
      </div>
    </nav>
    <div class="profil-container">
        <div class="profil-card">
            <h2>Profil Bilgilerim</h2>
            
            <ul class="bilgi-listesi">
                <li>
                    <strong>Ad:</strong>
                    <span><?php echo htmlspecialchars($currentUser['adi']); ?></span>
                </li>
                <li>
                    <strong>Soyad:</strong>
                    <span><?php echo htmlspecialchars($currentUser['soyadi']); ?></span>
                </li>
                <li>
                    <strong>E-posta:</strong>
                    <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
                </li>
            </ul>
            
            </div>
    </div>
    <footer class="site-footer">
      <div class="footer-container">
      <p>&copy; 2025 E-Kitap. Tüm Hakları Saklıdır.</p>
      </div>
    </footer>
    <script src="/main.js"></script>
</body>
</html>