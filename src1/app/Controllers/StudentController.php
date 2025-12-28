<?php
require_once __DIR__ . '/../Config/Database.php';

class StudentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        $role = $_SESSION['role'] ?? '';
        $clubId = $_SESSION['club_id'] ?? 0;

        try {
            if ($role === 'SystemAdmin') {
                $sql = "SELECT s.*, g.GroupName FROM Students s LEFT JOIN Groups g ON s.group_id = g.GroupID";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT s.*, g.GroupName FROM Students s LEFT JOIN Groups g ON s.group_id = g.GroupID WHERE s.club_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$clubId]);
            }
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { $students = []; }

        require_once __DIR__ . '/../Views/admin/students.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'] ?? '';
            $clubId = ($_SESSION['role'] === 'SystemAdmin') ? ($_POST['club_id'] ?? null) : $_SESSION['club_id'];

            try {
                $sql = "INSERT INTO Students (full_name, club_id, created_at) VALUES (?, ?, GETDATE())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$fullName, $clubId]);
                echo "<script>alert('Başarıyla Eklendi'); window.location.href='index.php?page=students';</script>";
            } catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
        }
    }
}