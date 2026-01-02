<?php
class CoachController {
    private $db;

    public function __construct() {
        if (file_exists(__DIR__ . '/../Config/Database.php')) require_once __DIR__ . '/../Config/Database.php';
        $this->db = (new Database())->getConnection();
    }

    // --- LİSTELEME (FİLTRELİ) ---
    public function index() {
        $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
        
        // URL'den durum bilgisi al (Varsayılan: 1 yani Aktifler)
        $showStatus = isset($_GET['show']) && $_GET['show'] == 'passive' ? 0 : 1;

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
                
                $id = $_POST['coach_id'] ?? null;
                $fullName = trim($_POST['full_name']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);
                $password = $_POST['password']; 
                $canViewReports = isset($_POST['can_view_reports']) ? 1 : 0;

                $hashedPassword = null;
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                }

                if ($id) {
                    if ($hashedPassword) {
                        $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanViewReports = ?, PasswordHash = ? WHERE UserID = ? AND ClubID = ?";
                        $params = [$fullName, $email, $phone, $canViewReports, $hashedPassword, $id, $clubId];
                    } else {
                        $sql = "UPDATE Users SET FullName = ?, Email = ?, Phone = ?, CanViewReports = ? WHERE UserID = ? AND ClubID = ?";
                        $params = [$fullName, $email, $phone, $canViewReports, $id, $clubId];
                    }
                    $this->db->prepare($sql)->execute($params);
                    $coachId = $id; 
                } else {
                    $finalPass = $hashedPassword ?: password_hash('123456', PASSWORD_DEFAULT);
                    $sql = "INSERT INTO Users (ClubID, FullName, Email, PasswordHash, Phone, RoleID, CanViewReports, IsActive, CreatedAt) 
                            VALUES (?, ?, ?, ?, ?, 3, ?, 1, GETDATE())";
                    $this->db->prepare($sql)->execute([$clubId, $fullName, $email, $finalPass, $phone, $canViewReports]);
                    $coachId = $this->db->lastInsertId();
                }

                $selectedGroupIds = $_POST['group_ids'] ?? [];
                $this->db->prepare("UPDATE Groups SET CoachID = NULL WHERE CoachID = ?")->execute([$coachId]);

                if (!empty($selectedGroupIds)) {
                    $ids = array_map('intval', $selectedGroupIds);
                    $inQuery = implode(',', $ids);
                    $this->db->query("UPDATE Groups SET CoachID = $coachId WHERE GroupID IN ($inQuery) AND ClubID = $clubId");
                }

                $this->db->commit();
                $_SESSION['success_message'] = "İşlem başarıyla kaydedildi.";
                header("Location: index.php?page=coach_list");
                exit;

            } catch (Exception $e) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                die("Hata: " . $e->getMessage());
            }
        }
    }

    // --- PASİFE AL (SOFT DELETE) ---
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            
            $check = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE CoachID = ?");
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                echo "<script>alert('Bu antrenörün grupları var! Pasife almadan önce grupları boşa çıkarın.'); window.location.href='index.php?page=coach_list';</script>";
                exit;
            }

            $this->db->prepare("UPDATE Users SET IsActive = 0 WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
            $_SESSION['success_message'] = "Antrenör pasife alındı (Arşivlendi).";
        }
        header("Location: index.php?page=coach_list");
        exit;
    }

    // --- GERİ YÜKLE (RESTORE) ---
    public function restore() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];
            $this->db->prepare("UPDATE Users SET IsActive = 1 WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
            $_SESSION['success_message'] = "Antrenör tekrar aktif edildi.";
        }
        header("Location: index.php?page=coach_list&show=passive");
        exit;
    }

    // --- TAMAMEN SİL (HARD DELETE) ---
    public function hard_delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            $clubId = $_SESSION['selected_club_id'] ?? $_SESSION['club_id'];

            try {
                // 1. Grup Kontrolü (Hala bir grubu yönetiyor mu?)
                $checkGroup = $this->db->prepare("SELECT COUNT(*) FROM Groups WHERE CoachID = ?");
                $checkGroup->execute([$id]);
                if ($checkGroup->fetchColumn() > 0) {
                    $_SESSION['sweet_alert'] = [
                        'icon' => 'warning',
                        'title' => 'Grup Yönetiyor!',
                        'text' => 'Bu antrenörün yönettiği gruplar var. Önce grupları başka antrenöre atayın.'
                    ];
                    header("Location: index.php?page=coach_list&show=passive");
                    exit;
                }

                // 2. Veli Kontrolü (ÖĞRENCİ İSİMLERİNİ GETİRİR)
                $checkParent = $this->db->prepare("SELECT FullName FROM Students WHERE ParentID = ?");
                $checkParent->execute([$id]);
                $students = $checkParent->fetchAll(PDO::FETCH_COLUMN); // İsimleri dizi olarak al

                if (!empty($students)) {
                    // İsimleri virgülle birleştir
                    $studentNamesList = implode(', ', $students);
                    // Çoğul eki kontrolü
                    $suffix = count($students) > 1 ? 'öğrencilerinin' : 'öğrencisinin';

                    // UYARI MESAJI (İsimli)
                    $_SESSION['sweet_alert'] = [
                        'icon' => 'warning',
                        'title' => 'Silinemez (Veli Kaydı)',
                        'html' => "Bu kullanıcı, <b>{$studentNamesList}</b> isimli {$suffix} velisidir.<br><br>Veri kaybı olmaması için bu kişiyi tamamen silemezsiniz. Bunun yerine <b>Pasif (Arşiv)</b> modunda bırakmalısınız."
                    ];
                    header("Location: index.php?page=coach_list&show=passive");
                    exit;
                }

                // 3. Engel Yoksa SİL
                $this->db->prepare("DELETE FROM Users WHERE UserID = ? AND ClubID = ?")->execute([$id, $clubId]);
                $_SESSION['success_message'] = "Kullanıcı ve tüm verileri kalıcı olarak silindi.";

            } catch (PDOException $e) {
                $_SESSION['sweet_alert'] = [
                    'icon' => 'error',
                    'title' => 'Veritabanı Hatası',
                    'text' => 'Bu kullanıcı başka kayıtlarla ilişkili olduğu için silinemiyor. Pasif modda bırakmanız önerilir.'
                ];
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