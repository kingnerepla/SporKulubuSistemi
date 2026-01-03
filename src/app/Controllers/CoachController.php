<?php
// Hataları göster
ini_set('display_errors', 1);
error_reporting(E_ALL);

class CoachController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- LİSTELEME ---
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        
        // Gösterim durumu (Aktif/Pasif)
        $showStatus = isset($_GET['show']) && $_GET['show'] == 'passive' ? 0 : 1;

        // Antrenörleri ve Gruplarını (Virgülle birleşik) Çek
        $sql = "SELECT u.*, 
                       (SELECT COUNT(*) FROM Groups WHERE CoachID = u.UserID) as GroupCount,
                       STUFF((SELECT ', ' + GroupName FROM Groups WHERE CoachID = u.UserID FOR XML PATH('')), 1, 2, '') as GroupNames,
                       STUFF((SELECT ',' + CAST(GroupID AS VARCHAR) FROM Groups WHERE CoachID = u.UserID FOR XML PATH('')), 1, 1, '') as GroupIDs
                FROM Users u
                WHERE u.ClubID = ? AND u.RoleID = 3 AND u.IsActive = ?
                ORDER BY u.FullName ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clubId, $showStatus]);
        $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Modal'da seçmek için tüm grupları çek
        $grpSql = "SELECT GroupID, GroupName FROM Groups WHERE ClubID = ? ORDER BY GroupName ASC";
        $stmtGrp = $this->db->prepare($grpSql);
        $stmtGrp->execute([$clubId]);
        $allGroups = $stmtGrp->fetchAll(PDO::FETCH_ASSOC);

        $this->render('coach_list', [
            'coaches' => $coaches, 
            'allGroups' => $allGroups,
            'currentStatus' => $showStatus
        ]);
    }

    // --- KAYDET / GÜNCELLE ---
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->db->beginTransaction();
                $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
                
                $id = !empty($_POST['coach_id']) ? $_POST['coach_id'] : null;
                $fullName = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $password = $_POST['password']; 
                $canViewReports = isset($_POST['can_view_reports']) ? 1 : 0;

                // 1. Kullanıcıyı Kaydet/Güncelle
                if ($id) {
                    // GÜNCELLEME
                    $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanViewReports = ? WHERE UserID = ? AND ClubID = ?";
                    $params = [$fullName, $email, $phone, $canViewReports, $id, $clubId];
                    
                    // Şifre alanı doluysa onu da güncelle
                    if (!empty($password)) {
                        $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanViewReports = ?, PasswordHash = ? WHERE UserID = ? AND ClubID = ?";
                        $params = [$fullName, $email, $phone, $canViewReports, password_hash($password, PASSWORD_DEFAULT), $id, $clubId];
                    }
                    $this->db->prepare($sql)->execute($params);
                    $coachId = $id; 
                } else {
                    // YENİ KAYIT
                    $finalPass = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);
                    $sql = "INSERT INTO Users (ClubID, FullName, Email, PasswordHash, Phone, RoleID, CanViewReports, IsActive, CreatedAt) 
                            VALUES (?, ?, ?, ?, ?, 3, ?, 1, GETDATE())";
                    $this->db->prepare($sql)->execute([$clubId, $fullName, $email, $finalPass, $phone, $canViewReports]);
                    $coachId = $this->db->lastInsertId();
                }

                // 2. GRUP ATAMA MANTIĞI (SORUNUN OLABİLECEĞİ YER)
                $selectedGroupIds = $_POST['group_ids'] ?? []; // Formdan gelen dizi (Array)

                // A) Önce bu antrenörün üzerindeki TÜM grupları boşa çıkar (Sıfırla)
                // Bu adım önemlidir: Eğer formda seçim kaldırılmışsa veritabanından da silinmesini sağlar.
                $this->db->prepare("UPDATE Groups SET CoachID = NULL WHERE CoachID = ?")->execute([$coachId]);

                // B) Şimdi formdan gelen SEÇİLİ grupları bu antrenöre ata
                if (!empty($selectedGroupIds)) {
                    $ids = array_map('intval', $selectedGroupIds); // Güvenlik için integer yap
                    $inQuery = implode(',', $ids);
                    
                    // Toplu Güncelleme
                    $this->db->query("UPDATE Groups SET CoachID = $coachId WHERE GroupID IN ($inQuery) AND ClubID = $clubId");
                }

                $this->db->commit();
                $_SESSION['success_message'] = "Antrenör bilgileri ve grupları kaydedildi.";
                header("Location: index.php?page=coach_list");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                $_SESSION['error_message'] = "Hata: " . $e->getMessage();
                header("Location: index.php?page=coach_list");
                exit;
            }
        }
    }

    // --- PASİFE AL ---
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            
            // Antrenörün grubu var mı kontrol et
            $check = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE CoachID = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                $_SESSION['error_message'] = "Bu antrenörün yönettiği gruplar var! Önce grupları boşa çıkarın.";
            } else {
                $this->db->prepare("UPDATE Users SET IsActive = 0 WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
                $_SESSION['success_message'] = "Antrenör pasife alındı.";
            }
        }
        header("Location: index.php?page=coach_list");
        exit;
    }

    // --- AKTİF ET ---
    public function restore() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            $this->db->prepare("UPDATE Users SET IsActive = 1 WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
            $_SESSION['success_message'] = "Antrenör aktif edildi.";
        }
        header("Location: index.php?page=coach_list&show=passive");
        exit;
    }

    // --- KALICI SİL (HARD DELETE) ---
    public function hard_delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            try {
                // Grup kontrolü
                $checkGroup = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE CoachID = ?");
                $checkGroup->execute([$id]);
                if ($checkGroup->fetchColumn() > 0) {
                    $_SESSION['error_message'] = "Grup yöneten antrenör silinemez.";
                    header("Location: index.php?page=coach_list&show=passive");
                    exit;
                }

                // Veli kontrolü (Öğrenci tablosunda veli olarak geçiyor mu?)
                // Antrenör aynı zamanda veli ise silme işlemi foreign key hatası verir.
                $checkParent = $this->db->prepare("SELECT COUNT(*) FROM Students WHERE ParentID = ?");
                $checkParent->execute([$id]);
                if ($checkParent->fetchColumn() > 0) {
                    $_SESSION['error_message'] = "Bu kullanıcı aynı zamanda bir veli. Silinemez.";
                    header("Location: index.php?page=coach_list&show=passive");
                    exit;
                }

                $this->db->prepare("DELETE FROM Users WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
                $_SESSION['success_message'] = "Kayıt tamamen silindi.";

            } catch (Exception $e) {
                $_SESSION['error_message'] = "Veritabanı hatası: " . $e->getMessage();
            }
        }
        header("Location: index.php?page=coach_list&show=passive");
        exit;
    }

    private function render($view, $data = []) {
        if(isset($_SESSION)) $data = array_merge($_SESSION, $data);
        extract($data);
        ob_start();
        $baseDir = __DIR__ . '/../';
        $viewsFolder = is_dir($baseDir . 'Views') ? 'Views' : 'views';
        $viewFile = $baseDir . $viewsFolder . "/admin/{$view}.php";
        if (file_exists($viewFile)) include $viewFile;
        $content = ob_get_clean();
        $layoutPath = $baseDir . $viewsFolder . '/layouts/admin_layout.php';
        if (file_exists($layoutPath)) include $layoutPath; else echo $content;
    }
}