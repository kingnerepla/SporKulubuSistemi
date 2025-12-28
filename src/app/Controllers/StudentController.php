<?php
require_once __DIR__ . '/../Config/Database.php';

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?page=login"); exit; }

        $role = $_SESSION['role'];
        $clubId = $_SESSION['club_id'];

        // --- KRİTİK FİLTRELEME BURADA ---
        if ($role === 'SystemAdmin') {
            // Süper Admin her şeyi görür
            $sql = "SELECT Students.*, Clubs.ClubName, Groups.GroupName 
                    FROM Students 
                    LEFT JOIN Clubs ON Students.ClubID = Clubs.ClubID 
                    LEFT JOIN Groups ON Students.GroupID = Groups.GroupID 
                    WHERE Students.IsActive = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } else {
            // Kulüp Yöneticisi sadece kendi kulübünü görür
            $sql = "SELECT Students.*, Clubs.ClubName, Groups.GroupName 
                    FROM Students 
                    LEFT JOIN Clubs ON Students.ClubID = Clubs.ClubID 
                    LEFT JOIN Groups ON Students.GroupID = Groups.GroupID 
                    WHERE Students.ClubID = ? AND Students.IsActive = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$clubId]);
        }

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../Views/admin/students/index.php';
    }
}