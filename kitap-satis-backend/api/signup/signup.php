<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $pdo = null;
        $hata = "";
        try{
            $pdo = new PDO('sqlite:../db/data.db');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            $ad = $_POST['ad'];
            $soyad = $_POST['soyad'];
            $email = $_POST['email'];
            $sifre = $_POST['sifre'];
            $sifre_tekrar = $_POST['sifre-tekrar'];

            if (empty($hata)){
                $stmt = $pdo->prepare("SELECT email FROM Kullanici WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $hata = "Bu email zaten kayıtlı.";
                }
            }

            if (!empty($hata)){
                echo "<h2>Hata!</h2>";
                echo "<p>" . $hata . "</p>";
            } else {
                $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
                $sql = "INSERT INTO Kullanici (kullaniciadi, kullanicisoyadi, email, sifre) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$ad, $soyad, $email, $hashed_sifre]);
                echo "<h2>Başarılı!</h2>";
                echo "<p>Başarıyla üye oldunuz.</p>";
        } 
    } catch (PDOException $e) {
        echo "<h2>Veritabanı hatası!</h2>";
        echo "<p>Bir sorun oluştu:" . $e->getMessage() . "</p>";
    } finally {
            $pdo = null;
    }
} else {
        header("Location: ../../index.html");
        exit();
    }
?>