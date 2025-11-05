<?php
// DOSYA ADI: Test/yetkiduzenleme.php
// AÇIKLAMA: Backend komut satırı scripti - Kullanıcı yetkisini admin yapar
// Kullanım: php yetkiduzenleme.php email@ornek.com

// === KOMUT SATIRI KONTROLÜ ===
// Komut satırında email parametresi girilmemişse hata ver ve çık
if ($argc < 2) {
    echo "Kullanım: php promote_user.php email@ornek.com\n";
    exit(1);
}

// Komut satırından gelen email parametresini al.
$email = $argv[1];

// Veritabanı bağlantı dosyasını dahil et
require_once __DIR__ . '/db/database.php';

// === VERİTABANI İŞLEMİ ===
try {
    // Kullanıcının yetkisini 'admin' olarak güncelle
    $stmt = $pdo->prepare("UPDATE Kullanici SET yetki = 'admin' WHERE email = ?");
    $stmt->execute([$email]);

    // İşlem başarılı ise (en az 1 satır etkilendi)
    if ($stmt->rowCount() > 0) {
        echo "Başarılı: $email artık admin.\n";
    } else {
        echo "Uyarı: Bu e-posta bulunamadı veya zaten admin.\n";
    }

    // Kullanıcı bilgilerini tekrar çek ve ekrana yazdır (doğrulama için)
    $v = $pdo->prepare("SELECT kullaniciid, adi, soyadi, email, yetki FROM Kullanici WHERE email = ?");
    $v->execute([$email]);
    $user = $v->fetch(PDO::FETCH_ASSOC);
    
    // Kullanıcı bulundu ise bilgilerini yazdır
    if ($user) print_r($user);

} catch (PDOException $e) {
    // Veritabanı hatası olursa ekrana yazdır
    echo "Hata: " . $e->getMessage() . "\n";
}
