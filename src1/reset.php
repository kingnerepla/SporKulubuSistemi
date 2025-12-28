<?php
// Veritabanı bağlantısını çağır
require_once 'app/Config/Database.php';
$db = (new Database())->getConnection();

// Şifre: 123456 (Bunun şifrelenmiş halini oluşturuyoruz)
$password = password_hash("123456", PASSWORD_DEFAULT);

echo "<h3>Kurtarma Operasyonu Başladı...</h3>";

try {
    // 1. Önce Rolleri Kontrol Et (Eğer yoksa ekle)
    // SystemAdmin rolünün ID'sini bulmaya çalışalım, yoksa oluşturalım.
    $roleName = 'SystemAdmin';
    $stmt = $db->prepare("SELECT RoleID FROM Roles WHERE RoleName = ?");
    $stmt->execute([$roleName]);
    $role = $stmt->fetch();

    if ($role) {
        $roleId = $role['RoleID'];
        echo "✅ '$roleName' rolü bulundu. (ID: $roleId)<br>";
    } else {
        // Rol yoksa ekle
        $db->exec("INSERT INTO Roles (RoleName) VALUES ('$roleName')");
        $roleId = $db->lastInsertId(); // Eklenen ID'yi al (MySQL/SQLServer farkedebilir)
        
        // Eğer lastInsertId çalışmazsa tekrar sorgula
        if(!$roleId) {
             $stmt = $db->prepare("SELECT RoleID FROM Roles WHERE RoleName = ?");
             $stmt->execute([$roleName]);
             $roleId = $stmt->fetchColumn();
        }
        echo "✅ '$roleName' rolü oluşturuldu. (ID: $roleId)<br>";
    }

    // 2. Eski admin@kulup.com varsa şifresini güncelle, yoksa yeni oluştur
    $email = 'admin@kulup.com';
    $checkUser = $db->prepare("SELECT UserID FROM Users WHERE Email = ?");
    $checkUser->execute([$email]);
    $user = $checkUser->fetch();

    if ($user) {
        // Varsa Şifresini 123456 yap
        $update = $db->prepare("UPDATE Users SET PasswordHash = ?, IsActive = 1 WHERE Email = ?");
        $update->execute([$password, $email]);
        echo "✅ Mevcut 'admin@kulup.com' kullanıcısının şifresi '123456' olarak güncellendi.<br>";
    } else {
        // Yoksa yeni oluştur
        $insert = $db->prepare("INSERT INTO Users (RoleID, FullName, Email, PasswordHash, IsActive) VALUES (?, ?, ?, ?, 1)");
        $insert->execute([$roleId, 'Süper Yönetici', $email, $password]);
        echo "✅ Yeni Admin oluşturuldu: admin@kulup.com / 123456<br>";
    }

    echo "<hr><h3>🎉 İşlem Tamam!</h3>";
    echo "<p>Artık aşağıdaki bilgilerle giriş yapabilirsin:</p>";
    echo "<ul><li><strong>Email:</strong> admin@kulup.com</li><li><strong>Şifre:</strong> 123456</li></ul>";
    echo "<a href='index.php?page=login'>Giriş Ekranına Git</a>";

} catch (PDOException $e) {
    die("HATA: " . $e->getMessage());
}
?>