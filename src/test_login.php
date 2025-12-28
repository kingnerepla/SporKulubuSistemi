<?php
// Hataları zorla açalım
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'app/Config/Database.php';

echo "<h1>🔍 Login Test Aracı</h1>";

// 1. Veritabanı Bağlantısı
try {
    $db = (new Database())->getConnection();
    echo "<p style='color:green'>✅ Veritabanı bağlantısı başarılı.</p>";
} catch (Exception $e) {
    die("<p style='color:red'>❌ Veritabanı Hatası: " . $e->getMessage() . "</p>");
}

// 2. Test Edilecek Veriler (BURAYI KENDİ MAİLİNLE DEĞİŞTİR)
$testEmail = "admin@mail.com"; // <--- BURAYA KENDİ MAİLİNİ YAZ
$testPassword = "123456";

echo "Testing Email: <strong>$testEmail</strong><br>";
echo "Testing Password: <strong>$testPassword</strong><hr>";

// 3. Kullanıcıyı Çekme
$sql = "SELECT * FROM Users WHERE Email = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$testEmail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p style='color:red'>❌ HATA: Bu mail adresi veritabanında bulunamadı!</p>";
    exit;
}

echo "<p style='color:green'>✅ Kullanıcı Veritabanında Bulundu: " . htmlspecialchars($user['FullName']) . "</p>";

// 4. Şifre Hash Kontrolü
$dbHash = $user['PasswordHash'];
echo "Veritabanındaki Hash: <span style='background:#eee; padding:5px; font-family:monospace'>" . $dbHash . "</span><br><br>";

// 5. Karşılaştırma
if (password_verify($testPassword, $dbHash)) {
    echo "<h2 style='color:green'>✅ BAŞARILI! Şifre Doğru.</h2>";
    echo "<p>Sisteminizde bu şifre ile giriş yapabilmeniz lazım.</p>";
} else {
    echo "<h2 style='color:red'>❌ BAŞARISIZ! Şifre Yanlış.</h2>";
    echo "<p><strong>Sebep:</strong> Veritabanındaki şifre ile '123456' eşleşmiyor.</p>";
    
    // ÇÖZÜM ÖNERİSİ: ŞİFREYİ ZORLA GÜNCELLE
    echo "<hr><h3>🛠️ Otomatik Onarım:</h3>";
    
    $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
    $update = $db->prepare("UPDATE Users SET PasswordHash = ? WHERE Email = ?");
    $update->execute([$newHash, $testEmail]);
    
    echo "<p style='color:blue'>Şifre şimdi '123456' olarak veritabanında güncellendi.<br>
    Lütfen sayfayı yenileyin (F5), yukarıda 'BAŞARILI' yazısını görmelisiniz.</p>";
}
?>