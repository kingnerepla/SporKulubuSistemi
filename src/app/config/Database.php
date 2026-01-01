<?php
/**
 * Database Sınıfı
 * 'Cannot declare class Database' hatasını önlemek için if kontrolü eklenmiştir.
 */
if (!class_exists('Database')) {

    class Database {
        // Docker kullanıyorsan 'host.docker.internal', yerel ise 'localhost'
        private $host = 'host.docker.internal'; 
        private $db_name = 'ClubSystemDB';
        private $username = 'sa';
        
        // Şifre ayarın
        private $password = 'Ab_kulup_248'; 

        public $conn;

        public function getConnection() {
            $this->conn = null;
            try {
                // Bağlantı dizesi (SSL bypass ve Timeout dahil)
                $dsn = "sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name . ";TrustServerCertificate=yes;Encrypt=no;LoginTimeout=30";
                
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                // Hata durumunda mesajı bas ve durdur
                die("<h3 style='color:red text-align:center; padding:20px; border:1px solid red; border-radius:10px;'>
                    <i class='fa-solid fa-triangle-exclamation'></i> Veritabanı Bağlantı Hatası:<br>
                    <small style='color:gray'>" . $e->getMessage() . "</small>
                </h3>");
            }
            return $this->conn;
        }
    }

} // class_exists sonu