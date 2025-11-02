<?php
// BACKEND: Admin paneli

require_once __DIR__ . '/../session.php';

if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Anasayfa</title>
    <link rel="stylesheet" href="/admin/adminindex.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Admin Paneli</h2>
            <nav>
                <ul>
                    <li><a href="/admin/adminindex.php" class="active"><i class="fas fa-home"></i> Anasayfa</a></li>
                    <li><a href="/admin/kullanicilar/kullanicilar.php"><i class="fas fa-users"></i> Kullanıcılar</a></li>
                    <li><a href="/admin/urunler/urunler.php"><i class="fas fa-box"></i> Ürünler</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header>
                <h1>Anasayfa</h1>
                <div class="header-buttons">
                <a href="/index/index.php" class="btn-home">
                    <i class="fas fa-home"></i>
                    <span>Anasayfa</span> 
                </a>
                <a href="/login/logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Çıkış Yap</span>
                </a>
                </div>
            </header>
            
            <p>Kontrol paneline hoş geldiniz, <?php echo htmlspecialchars($currentUser['adi']); ?>.</p>

            <section class="dashboard-buttons">
                <a href="/admin/kullanicilar/kullaniciekle.php" class="btn btn-add-user">
                    <i class="fas fa-user-plus"></i>
                    Kullanıcı Ekle
                </a>
                <a href="/admin/urunler/urunekle.php" class="btn btn-add-product">
                    <i class="fas fa-plus-circle"></i>
                    Ürün Ekle
                </a>
            </section>
        </main>
    </div>
</body>
</html>