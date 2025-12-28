<?php
require_once __DIR__ . '/app/Config/Database.php';

try {
    $db = (new Database())->getConnection();
    echo "<h1>Veritabanı Kontrol Paneli</h1>";
    echo "<hr>";

    // 1. Kullanıcılar Tablosunu Tara
    echo "<h3>1. Kullanıcı Kontrolü (super@admin.com)</h3>";
    $stmt = $db->prepare("SELECT UserID, FullName, Email, Password, RoleID, ClubID FROM Users WHERE Email = 'super@admin.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<span style='color:green;'>✅ Kullanıcı Bulundu!</span><br>";
        echo "<b>Ad Soyad:</b> " . $user['FullName'] . "<br>";
        echo "<b>DB'deki Şifre:</b> " . $user['Password'] . "<br>";
        echo "<b>RoleID:</b> " . $user['RoleID'] . "<br>";
        
        // 2. Rol Kontrolü
        echo "<h3>2. Rol Yetkisi Kontrolü</h3>";
        $roleStmt = $db->prepare("SELECT RoleName FROM Roles WHERE RoleID = ?");
        $roleStmt->execute([$user['RoleID']]);
        $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            echo "<span style='color:green;'>✅ Rol Tanımlı: " . $role['RoleName'] . "</span><br>";
        } else {
            echo "<span style='color:red;'>❌ HATA: Kullanıcının RoleID'si (" . $user['RoleID'] . ") Roles tablosunda bulunamadı!</span>";
        }
    } else {
        echo "<span style='color:red;'>❌ HATA: super@admin.com e-postasıyla hiçbir kullanıcı bulunamadı!</span>";
    }

} catch (Exception $e) {
    echo "<span style='color:red;'>🔥 Bağlantı Hatası: " . $e->getMessage() . "</span>";
}