<?php

require_once __DIR__ . '/../../session.php';

//kullanıcı admin değil ise anasayfaya yönlendirir
if (!$isLoggedIn || $currentUser['yetki'] !== 'admin') {
    header("Location: /index/index.php");
    exit;
}

//kullanıcıları veritabanından çeker
try {
    $stmt = $pdo->prepare("SELECT kullaniciid, adi, soyadi, email, yetki FROM Kullanici ORDER BY kullaniciid DESC");
    $stmt->execute();
    $kullanicilar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kullanicilar = [];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi</title>
    <link rel="stylesheet" href="kullanicilar/kullanicilar.css">
</head>
<body>

    <div class="container">
        <div class="user-management-card">
            
            <header class="card-header">
                <h1>Kullanıcı Yönetimi</h1>
                <a href="/admin/adminindex.php" class="btn btn-secondary">Anasayfaya Dön</a>
                <a href="/admin/kullanicilar/kullaniciekle.php" class="btn btn-green">Yeni Kullanıcı Ekle</a>
            </header>
            
            <div class="table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>AD</th>
                            <th>SOYAD</th>
                            <th>E-POSTA</th>
                            <th>YETKİ</th>
                            <th>İŞLEMLER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kullanicilar)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
                                    Henüz kullanıcı eklenmemiş.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($kullanicilar as $kullanici): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kullanici['adi']); ?></td>
                                    <td><?php echo htmlspecialchars($kullanici['soyadi']); ?></td>
                                    <td><?php echo htmlspecialchars($kullanici['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $kullanici['yetki'] === 'admin' ? 'admin' : 'user'; ?>">
                                            <?php echo $kullanici['yetki'] === 'admin' ? 'Admin' : 'Kullanıcı'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/admin/kullanicilar/kullaniciduzenle.php?id=<?php echo $kullanici['kullaniciid']; ?>" 
                                           class="btn btn-sm btn-edit">Düzenle</a>
                                        <?php if ($kullanici['kullaniciid'] != $currentUser['kullaniciid']): ?>
                                            <button onclick="deleteUser(<?php echo $kullanici['kullaniciid']; ?>)" 
                                                    class="btn btn-sm btn-delete">Sil</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

<script src="kullanicilar.js"></script>
<script>
    function deleteUser(kullaniciId) {
        if (confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) {
            fetch('/admin/kullanici_sil.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    kullaniciId: kullaniciId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Kullanıcı başarıyla silindi!');
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
</script>
</body>
</html>
