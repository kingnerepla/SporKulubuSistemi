<?php
// Hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'app/Config/Database.php';

try {
    $db = (new Database())->getConnection();
    echo "<style>
            body{font-family:sans-serif; background:#f4f4f4; padding:20px;} 
            .card{background:#fff; padding:20px; margin-bottom:20px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
            table{width:100%; border-collapse:collapse; margin-top:10px;}
            th, td{border-bottom:1px solid #ddd; padding:8px; text-align:left;}
            th{background:#333; color:#fff;}
            .exists{color:green; font-weight:bold;}
            .missing{color:red; font-weight:bold;}
          </style>";

    echo "<h1>📊 Veritabanı Mevcut Yapısı</h1>";

    // KONTROL EDİLECEK TABLOLAR
    $tables = ['Students', 'Users', 'Groups'];

    foreach ($tables as $table) {
        echo "<div class='card'>";
        echo "<h3>Tablo: <span style='color:#0d6efd'>$table</span></h3>";
        
        $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = '$table'";
        $stmt = $db->query($sql);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($columns) {
            echo "<table>";
            echo "<tr><th>Sütun Adı</th><th>Veri Tipi</th><th>Boş Olabilir mi?</th></tr>";
            foreach ($columns as $col) {
                // Kritik sütunları işaretleyelim
                $highlight = "";
                if(in_array($col['COLUMN_NAME'], ['ArchivedAt', 'ArchiveReason', 'Notes', 'Description', 'ParentID', 'Email'])) {
                    $highlight = "style='background-color:#fff3cd'";
                }

                echo "<tr $highlight>";
                echo "<td>" . $col['COLUMN_NAME'] . "</td>";
                echo "<td>" . $col['DATA_TYPE'] . "</td>";
                echo "<td>" . $col['IS_NULLABLE'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='missing'>Tablo bulunamadı!</p>";
        }
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<h3>Hata:</h3>" . $e->getMessage();
}
?>