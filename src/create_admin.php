<?php
require_once 'app/Config/Database.php';

$db = (new Database())->getConnection();

// 1. Şifreyi güvenli hale getir (Hash'le)
$password = "123456"; 
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 2. Kullanıcıyı ekle
try {
    // SystemAdmin rolünün ID'sini bul (Genelde 1'dir ama garanti olsun)
    $roleQuery = $db->query("SELECT RoleID FROM Roles WHERE RoleName = 'SystemAdmin'");
    $roleId = $roleQuery->fetchColumn();

    $sql = "INSERT INTO Users (RoleID, FullName, Email, PasswordHash, IsActive) VALUES (?, ?, ?, ?, 1)";
    $stmt = $db->prepare($sql);
    
    // SystemAdmin'in ClubID'si NULL olur.
    $stmt->execute([$roleId, 'Süper Yönetici', 'admin@kulup.com', $passwordHash]);

    echo "<h1>✅ Yönetici Oluşturuldu!</h1>";
    echo "Email: <strong>admin@kulup.com</strong><br>";
    echo "Şifre: <strong>123456</strong>";

} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>