<?php
// Bu if kontrolü hatayı engeller
if (!class_exists('Database')) {
    class Database {
        private $host = 'host.docker.internal'; 
        private $db_name = 'ClubSystemDB';
        private $username = 'sa';
        private $password = 'Ab_kulup_248'; 
        public $conn;

        public function getConnection() {
            $this->conn = null;
            try {
                $dsn = "sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name . ";TrustServerCertificate=yes;Encrypt=no;LoginTimeout=30";
                $this->conn = new PDO($dsn, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                // Hata varsa ekrana basıp durduruyoruz
                die("Veritabanı Hatası: " . $e->getMessage());
            }
            return $this->conn;
        }
    }
}
?>