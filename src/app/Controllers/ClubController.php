<?php

class ClubController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function index() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') {
            header("Location: index.php?page=dashboard");
            exit;
        }

        $selectedClubId = $_SESSION['selected_club_id'] ?? null;
        $tab = $_GET['tab'] ?? 'students';
        
        $data = [
            'clubs' => [],
            'tabData' => [],
            'stats' => ['students' => 0, 'revenue' => 0, 'system_debt' => 0, 'per_student' => 100, 'license' => 5000],
            'selectedClub' => null,
            'activeTab' => $tab
        ];

        try {
            // Tüm kulüplerin listesi
            $data['clubs'] = $this->db->query("SELECT * FROM Clubs ORDER BY ClubName ASC")->fetchAll(PDO::FETCH_ASSOC);

            if ($selectedClubId) {
                // Seçili kulüp detayı
                $stmtClub = $this->db->prepare("SELECT * FROM Clubs WHERE ClubID = ?");
                $stmtClub->execute([$selectedClubId]);
                $data['selectedClub'] = $stmtClub->fetch(PDO::FETCH_ASSOC);

                // Kulübe özel fiyatları alıyoruz (Veritabanındaki yeni sütunlardan)
                $data['stats']['per_student'] = $data['selectedClub']['MonthlyPerStudentFee'] ?? 100;
                $data['stats']['license'] = $data['selectedClub']['AnnualLicenseFee'] ?? 5000;

                // İstatistikler (KPI)
                $data['stats']['students'] = $this->getScalar("SELECT COUNT(*) FROM Students WHERE ClubID = ? AND IsActive = 1", [$selectedClubId]);
                $data['stats']['coaches']  = $this->getScalar("SELECT COUNT(*) FROM Users WHERE ClubID = ? AND RoleID = 2", [$selectedClubId]);
                $data['stats']['groups']   = $this->getScalar("SELECT COUNT(*) FROM Groups WHERE ClubID = ?", [$selectedClubId]);
                $data['stats']['revenue']  = $this->getScalar("SELECT SUM(Amount) FROM Payments WHERE ClubID = ?", [$selectedClubId]);

                // Sistem Hakediş Hesabı (Özel fiyatlar üzerinden)
                $data['stats']['system_debt'] = $data['stats']['license'] + ($data['stats']['students'] * $data['stats']['per_student']);

                // Tab verileri
                switch ($tab) {
                    case 'students':
                        $stmt = $this->db->prepare("SELECT s.*, g.GroupName FROM Students s LEFT JOIN Groups g ON s.GroupID = g.GroupID WHERE s.ClubID = ? AND s.IsActive = 1 ORDER BY s.FullName ASC");
                        $stmt->execute([$selectedClubId]);
                        $data['tabData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                    case 'finance':
                        $stmt = $this->db->prepare("SELECT p.*, s.FullName as StudentName FROM Payments p JOIN Students s ON p.StudentID = s.StudentID WHERE p.ClubID = ? ORDER BY p.PaymentDate DESC");
                        $stmt->execute([$selectedClubId]);
                        $data['tabData'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        break;
                }
            }
        } catch (Exception $e) { 
            die("SQL Hatası: " . $e->getMessage()); 
        }

        $this->render('clubs', $data);
    }

    public function selectClub() {
        $clubId = $_GET['id'] ?? null;
        if ($clubId) {
            $stmt = $this->db->prepare("SELECT ClubID, ClubName, LogoPath FROM Clubs WHERE ClubID = ?");
            $stmt->execute([$clubId]);
            $club = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($club) {
                $_SESSION['selected_club_id'] = $club['ClubID'];
                $_SESSION['selected_club_name'] = $club['ClubName'];
                $_SESSION['selected_club_logo'] = $club['LogoPath'];
            }
        }
        header("Location: index.php?page=clubs");
        exit;
    }

    public function clearSelection() {
        unset($_SESSION['selected_club_id'], $_SESSION['selected_club_name'], $_SESSION['selected_club_logo']);
        header("Location: index.php?page=clubs");
        exit;
    }

    public function updateAgreement() {
        if (trim(strtolower($_SESSION['role'] ?? '')) !== 'systemadmin') exit;
        
        $clubId = $_POST['club_id'];
        $license = $_POST['annual_license'];
        $perStudent = $_POST['per_student'];

        $stmt = $this->db->prepare("UPDATE Clubs SET AnnualLicenseFee = ?, MonthlyPerStudentFee = ? WHERE ClubID = ?");
        $stmt->execute([$license, $perStudent, $clubId]);
        
        header("Location: index.php?page=clubs&tab=saas_billing&msg=updated");
        exit;
    }

    private function getScalar($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetchColumn();
        return $res !== false ? $res : 0;
    }

    private function render($view, $data = []) {
        extract($data);
        ob_start();
        include __DIR__ . "/../Views/admin/{$view}.php";
        $content = ob_get_clean();
        include __DIR__ . '/../Views/layouts/admin_layout.php';
    }
}