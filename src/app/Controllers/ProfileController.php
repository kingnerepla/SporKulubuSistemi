<?php
class ProfileController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function index() {
        // Oturum tipine göre veri çekme
        if (isset($_SESSION['parent_logged_in'])) {
            $id = $_SESSION['student_id'];
            $stmt = $this->db->prepare("SELECT FullName as Name, ParentPhone as Identity, [Password] FROM Students WHERE StudentID = ?");
            $type = 'parent';
        } else {
            $id = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT FullName as Name, Username as Identity, [Password] FROM Users WHERE UserID = ?");
            $type = 'admin';
        }

        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        include __DIR__ . '/../Views/profile/index.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newName = $_POST['full_name'];
            $newPass = $_POST['password'];
            
            if (isset($_SESSION['parent_logged_in'])) {
                $stmt = $this->db->prepare("UPDATE Students SET FullName = ?, [Password] = ? WHERE StudentID = ?");
                $id = $_SESSION['student_id'];
            } else {
                $stmt = $this->db->prepare("UPDATE Users SET FullName = ?, [Password] = ? WHERE UserID = ?");
                $id = $_SESSION['user_id'];
            }

            if ($stmt->execute([$newName, $newPass, $id])) {
                $_SESSION['user_name'] = $newName; // Session ismini güncelle
                header("Location: index.php?page=profile&status=success");
            } else {
                header("Location: index.php?page=profile&status=error");
            }
            exit;
        }
    }
}