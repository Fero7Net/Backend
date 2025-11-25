<?php
if ($argc < 2) {
    echo "Kullanım: php promote_user.php email@ornek.com\n";
    exit(1);
}

// komut satırından gelen email parametresini al
$email = $argv[1];

require_once __DIR__ . '/db/database.php';

try {
    // kullanıcının yetkisini admin olarak güncelleme
    $stmt = $pdo->prepare("UPDATE Kullanici SET yetki = 'admin' WHERE email = ?");
    $stmt->execute([$email]);

    //işlem başarılı ise
    if ($stmt->rowCount() > 0) {
        echo "Başarılı: $email artık admin.\n";
    } else {
        echo "Uyarı: Bu e-posta bulunamadı veya zaten admin.\n";
    }

    //kullanıcı bilgilerini tekrar ekrana yazdırma
    $v = $pdo->prepare("SELECT kullaniciid, adi, soyadi, email, yetki FROM Kullanici WHERE email = ?");
    $v->execute([$email]);
    $user = $v->fetch(PDO::FETCH_ASSOC);
    
    // kullanıcı bulundu ise ekrana yazdır
    if ($user) print_r($user);

} catch (PDOException $e) {
    // veritabanı hatası olursa ekrana yazdır
    echo "Hata: " . $e->getMessage() . "\n";
}
