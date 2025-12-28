<?php
class Database {
    // Docker kullanıyorsan 'host.docker.internal'
    private $host = 'host.docker.internal'; 
    private $db_name = 'ClubSystemDB';
    private $username = 'sa';
    
    // !!! BURAYA KENDİ DOĞRU ŞİFRENİ YAZ !!!
    private $password = 'Ab_kulup_248'; 

    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Çalışan ayarlarımız (SSL bypass ve Timeout)
            $dsn = "sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name . ";TrustServerCertificate=yes;Encrypt=no;LoginTimeout=30";
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("<h3 style='color:red'>Veritabanı Dosyası Hatası: " . $e->getMessage() . "</h3>");
        }
        return $this->conn;
    }
}