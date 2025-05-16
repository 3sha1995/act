<?php
require_once __DIR__ . '/../db_connection.php';

class FacilitiesCMS {
    private $pdo;
    protected $upload_dir;
    private $items_per_page = 6;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/facilities/';
        $this->ensureUploadDirectory();
        $this->ensureTableExists();
        $this->ensureMainTitleExists();
    }

    private function ensureUploadDirectory() {
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS af_page_facilities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                main_title VARCHAR(255),
                title VARCHAR(255),
                description TEXT,
                image VARCHAR(255),
                operating_hours TEXT,
                is_visible BOOLEAN DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Create settings table for section visibility
            $sql = "CREATE TABLE IF NOT EXISTS af_page_facilities_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_visible BOOLEAN DEFAULT 1,
                main_title VARCHAR(255) DEFAULT 'OUR FACILITIES'
            )";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating facilities table: " . $e->getMessage());
            throw $e;
        }
    }

    private function ensureMainTitleExists() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_facilities_settings");
            if ($stmt->fetchColumn() == 0) {
                $sql = "INSERT INTO af_page_facilities_settings (main_title, section_visible) VALUES ('OUR FACILITIES', 1)";
                $this->pdo->exec($sql);
            }
        } catch (PDOException $e) {
            error_log("Error ensuring main title exists: " . $e->getMessage());
        }
    }

    public function getSettings() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_facilities_settings WHERE id = 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            return ['main_title' => 'OUR FACILITIES', 'section_visible' => 1];
        }
    }

    public function updateSettings($mainTitle, $sectionVisible) {
        try {
            $sql = "UPDATE af_page_facilities_settings SET main_title = :main_title, section_visible = :section_visible WHERE id = 1";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':main_title' => $mainTitle,
                ':section_visible' => $sectionVisible ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return false;
        }
    }

    public function getAllFacilities($page = 1) {
        try {
            $offset = ($page - 1) * $this->items_per_page;
            $stmt = $this->pdo->prepare("SELECT * FROM af_page_facilities ORDER BY id DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $this->items_per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching facilities: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalPages() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_facilities");
            $total = $stmt->fetchColumn();
            return ceil($total / $this->items_per_page);
        } catch (PDOException $e) {
            error_log("Error getting total pages: " . $e->getMessage());
            return 1;
        }
    }

    public function getFacilityById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM af_page_facilities WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching facility: " . $e->getMessage());
            return null;
        }
    }

    public function addFacility($data) {
        try {
            $sql = "INSERT INTO af_page_facilities (title, description, image, operating_hours, is_visible) 
                    VALUES (:title, :description, :image, :operating_hours, :is_visible)";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':image' => $data['image'],
                ':operating_hours' => $data['operating_hours'],
                ':is_visible' => isset($data['is_visible']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error adding facility: " . $e->getMessage());
            return false;
        }
    }

    public function updateFacility($id, $data) {
        try {
            $sql = "UPDATE af_page_facilities SET 
                    title = :title,
                    description = :description,
                    image = :image,
                    operating_hours = :operating_hours,
                    is_visible = :is_visible
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':image' => $data['image'],
                ':operating_hours' => $data['operating_hours'],
                ':is_visible' => isset($data['is_visible']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error updating facility: " . $e->getMessage());
            return false;
        }
    }

    public function deleteFacility($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM af_page_facilities WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting facility: " . $e->getMessage());
            return false;
        }
    }

    public function handleImage($file) {
        if ($file && $file['error'] === 0) {
            $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $imageFileType;
            $targetPath = $this->upload_dir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return 'uploads/facilities/' . $newFileName;
            }
            error_log("Failed to move uploaded file to: " . $targetPath);
        }
        return null;
    }
}

// Initialize CMS
$facilitiesCMS = new FacilitiesCMS();
$settings = $facilitiesCMS->getSettings();

// Get current page for pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$facilities = $facilitiesCMS->getAllFacilities($page);
$totalPages = $facilitiesCMS->getTotalPages();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                if ($facilitiesCMS->updateSettings(
                    $_POST['main_title'],
                    isset($_POST['section_visible'])
                )) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?error=Failed to update settings');
                }
                exit;

            case 'add':
                $imageFile = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $imageFile = $facilitiesCMS->handleImage($_FILES['image']);
                }
                
                $data = [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'image' => $imageFile ?? '../imgs/cte.jpg',
                    'operating_hours' => $_POST['operating_hours'],
                    'is_visible' => isset($_POST['is_visible'])
                ];

                if ($facilitiesCMS->addFacility($data)) {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF'] . '?error=Failed to add facility');
                }
                exit;

            case 'update':
                if (isset($_POST['id'])) {
                    $id = $_POST['id'];
                    $currentFacility = $facilitiesCMS->getFacilityById($id);
                    
                    $imageFile = $currentFacility['image'];
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $newImage = $facilitiesCMS->handleImage($_FILES['image']);
                        if ($newImage) {
                            $imageFile = $newImage;
                        }
                    }

                    $data = [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'],
                        'image' => $imageFile,
                        'operating_hours' => $_POST['operating_hours'],
                        'is_visible' => isset($_POST['is_visible'])
                    ];

                    if ($facilitiesCMS->updateFacility($id, $data)) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                    } else {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=Failed to update facility');
                    }
                }
                exit;

            case 'delete':
                if (isset($_POST['id'])) {
                    if ($facilitiesCMS->deleteFacility($_POST['id'])) {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                    } else {
                        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=Failed to delete facility');
                    }
                }
                exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities Management</title>
    <link rel="stylesheet" href="student_affairs_sidebar.css">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin-left: 250px;
            padding: 0 20px;
            transition: all 0.3s ease;
            background-color: #f0f4f8;
            color: #1a365d;
            line-height: 1.6;
        }

        .content-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
        }

        @media (max-width: 768px) {
            body {
                margin-left: 0;
                padding: 0 15px;
            }
            body.sidebar-open {
                margin-left: 250px;
            }
        }

        .form-group, .mb-3 {
            margin-bottom: 20px;
        }

        label, .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        input[type="text"], select, .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        input[type="text"]:focus, select:focus, .form-control:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .button, .btn, button[type="submit"], input[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .button-primary, .btn-primary {
            background: #3182ce;
            color: white;
        }

        .btn-success {
            background: #38a169;
            color: white;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-warning {
            background: #dd6b20;
            color: white;
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .button:hover, .btn:hover, button[type="submit"]:hover, input[type="submit"]:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
        }

        .btn-primary:hover, .button-primary:hover {
            background: #2b6cb0;
        }

        .btn-success:hover {
            background: #2f855a;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .btn-warning:hover {
            background: #c05621;
        }

        .btn-secondary:hover {
            background: #4a5568;
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            background: #fff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            cursor: pointer;
        }

        input[type="file"]:hover {
            border-color: #93c5fd;
        }

        .section, .card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        .card-header {
            background: #ebf8ff;
            border-bottom: 1px solid #bee3f8;
            border-radius: 12px 12px 0 0;
            padding: 15px 25px;
            font-weight: 600;
            color: #2c5282;
        }

        .card-body {
            padding: 20px 25px;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 25px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        .table th, .table td {
            padding: 16px;
            border: 1px solid #bee3f8;
            text-align: left;
            vertical-align: middle;
        }

        .table th {
            background: #ebf8ff;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tr:hover {
            background-color: #f0f5ff;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            position: relative;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        .alert-danger {
            background-color: #fed7d7;
            color: #822727;
            border: 1px solid #feb2b2;
        }

        .btn-close {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            color: inherit;
            border: none;
            background: transparent;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #2c5282;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
        }
        
        .status-active {
            background-color: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background-color: #fed7d7;
            color: #822727;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 54, 93, 0.5);
            backdrop-filter: blur(4px);
            overflow-y: auto;
            padding: 20px 0;
        }

        .modal-content {
            background-color: #ffffff;
            margin: 2% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 16px;
            position: relative;
            box-shadow: 0 10px 25px rgba(49, 130, 206, 0.13);
            border: 1px solid #bee3f8;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 30px;
            background: #ebf8ff;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid #bee3f8;
        }

        .modal-footer {
            padding: 20px 30px;
            background: #ebf8ff;
            border-radius: 0 0 16px 16px;
            border-top: 1px solid #bee3f8;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            font-weight: normal;
            cursor: pointer;
            color: #4a5568;
            transition: color 0.2s ease, background 0.2s;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close:hover {
            color: #2c5282;
            background-color: #ebf8ff;
        }

        /* Switch styles */
        .switch-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 28px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e0;
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }

        input:checked + .slider {
            background-color: #3182ce;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #3182ce;
        }

        .preview-image {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(49,130,206,0.1);
            transition: transform 0.2s ease;
            border: 1px solid #bee3f8;
        }

        .preview-image:hover {
            transform: scale(1.05);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .page-item {
            list-style: none;
        }
        
        .page-link {
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            color: #3182ce;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .page-link:hover {
            background: #ebf8ff;
            border-color: #93c5fd;
        }
        
        .page-item.active .page-link {
            background: #3182ce;
            color: white;
            border-color: #3182ce;
        }

        /* Editor styles - keep existing but make sure to match services.php */
        .editor-container {
            border: 1px solid #bee3f8;
            border-radius: 12px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(49,130,206,0.07);
            background: #ffffff;
        }

        .editor-toolbar {
            background: linear-gradient(to right, #ebf8ff, #f0f7ff);
            border-bottom: 1px solid #bee3f8;
            padding: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar-group {
            display: flex;
            gap: 8px;
            padding: 0 12px;
            border-right: 1px solid #bee3f8;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .editor-toolbar button {
            background: #ffffff;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #3b82f6;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .editor-toolbar button:hover {
            background: #f0f5ff;
            border-color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(49,130,206,0.1);
        }

        .editor-toolbar button.active {
            background: #f0f5ff;
            border-color: #3182ce;
            color: #1d4ed8;
        }

        .editor-toolbar button i {
            font-size: 14px;
        }

        .editor {
            min-height: 300px;
            padding: 20px;
            background: #ffffff;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
            color: #1e3a8a;
        }

        .editor:focus {
            outline: none;
            background: #fafbff;
        }

        /* Tooltip styles */
        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            padding: 6px 12px;
            background: #1e3a8a;
            color: white;
            font-size: 13px;
            font-weight: 500;
            border-radius: 6px;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(49,130,206,0.13);
            opacity: 0;
            animation: fadeIn 0.2s ease forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                bottom: 105%;
            }
        }

        /* Font size select styles */
        .font-size-select {
            padding: 8px 16px;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            background: #ffffff;
            cursor: pointer;
            color: #3b82f6;
            font-size: 14px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .font-size-select:hover {
            background: #f0f5ff;
            border-color: #93c5fd;
            transform: translateY(-1px);
        }

        .font-size-select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
    </style>
</head>
<body>

<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <h1>Facilities Management</h1>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Changes have been successfully saved!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Section Settings -->
    <div class="section">
        <h2>Section Settings</h2>
        <form action="" method="POST" class="form-container">
            <input type="hidden" name="action" value="update_settings">
            <div class="form-group">
                <label for="main_title" class="form-label">Main Title</label>
                <input type="text" class="form-control" id="main_title" name="main_title" 
                       value="<?= htmlspecialchars($settings['main_title']) ?>" required>
            </div>
            <div class="form-group">
                <label class="switch-label">
                    Section Visibility
                    <label class="switch">
                        <input type="checkbox" id="section_visible" name="section_visible" <?= $settings['section_visible'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>
            <button type="submit" class="button button-primary">Update Settings</button>
        </form>
    </div>

    <!-- Facilities Management -->
    <div class="section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Facilities Management</h2>
            <button type="button" class="button button-primary" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                <i class="fas fa-plus"></i> Add New Facility
            </button>
        </div>

        <!-- Facilities Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Image</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facilities as $facility): ?>
                    <tr>
                        <td><?= htmlspecialchars($facility['title']) ?></td>
                        <td>
                            <?php if ($facility['image']): ?>
                                <img src="<?= str_starts_with($facility['image'], 'http') ? $facility['image'] : '../../' . $facility['image'] ?>" 
                                     class="preview-image" alt="Facility image">
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $facility['is_visible'] ? 'status-active' : 'status-inactive' ?>" 
                                  style="cursor: pointer;" 
                                  onclick="updateVisibility(<?= $facility['id'] ?>, <?= $facility['is_visible'] ? 'false' : 'true' ?>)">
                                <?= $facility['is_visible'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <button class="button btn-sm btn-warning" onclick="editFacility(<?= htmlspecialchars(json_encode($facility)) ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="button btn-sm btn-danger" onclick="deleteFacility(<?= $facility['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Facilities pagination" class="mt-4">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Add Facility Modal -->
    <div class="modal fade" id="addFacilityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="addFacilityForm">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold', 'description')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic', 'description')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline', 'description')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('strikeThrough', 'description')" class="tooltip" data-tooltip="Strike">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <select onchange="execCommandWithArg('fontSize', this.value, 'description')" class="font-size-select tooltip" data-tooltip="Font Size">
                                            <option value="1">Very Small</option>
                                            <option value="2">Small</option>
                                            <option value="3">Normal</option>
                                            <option value="4">Large</option>
                                            <option value="5">Very Large</option>
                                            <option value="6">Extra Large</option>
                                            <option value="7">Huge</option>
                                        </select>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('justifyLeft', 'description')" class="tooltip" data-tooltip="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyCenter', 'description')" class="tooltip" data-tooltip="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyRight', 'description')" class="tooltip" data-tooltip="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyFull', 'description')" class="tooltip" data-tooltip="Justify">
                                            <i class="fas fa-align-justify"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList', 'description')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList', 'description')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('indent', 'description')" class="tooltip" data-tooltip="Indent">
                                            <i class="fas fa-indent"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('outdent', 'description')" class="tooltip" data-tooltip="Outdent">
                                            <i class="fas fa-outdent"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat', 'description')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                        <button type="button" onclick="createLink('description')" class="tooltip" data-tooltip="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="description" contenteditable="true"></div>
                                <input type="hidden" name="description" id="description_input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="operating_hours" class="form-label">Operating Hours</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold', 'operating_hours')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic', 'operating_hours')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline', 'operating_hours')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList', 'operating_hours')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList', 'operating_hours')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat', 'operating_hours')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="operating_hours" contenteditable="true"></div>
                                <input type="hidden" name="operating_hours" id="operating_hours_input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="is_visible" name="is_visible" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="button button-primary" onclick="submitForm('addFacilityForm')">Add Facility</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Facility Modal -->
    <div class="modal fade" id="editFacilityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Facility</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data" id="editFacilityForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label for="edit_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_description" class="form-label">Description</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold', 'edit_description')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic', 'edit_description')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline', 'edit_description')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('strikeThrough', 'edit_description')" class="tooltip" data-tooltip="Strike">
                                            <i class="fas fa-strikethrough"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <select onchange="execCommandWithArg('fontSize', this.value, 'edit_description')" class="font-size-select tooltip" data-tooltip="Font Size">
                                            <option value="1">Very Small</option>
                                            <option value="2">Small</option>
                                            <option value="3">Normal</option>
                                            <option value="4">Large</option>
                                            <option value="5">Very Large</option>
                                            <option value="6">Extra Large</option>
                                            <option value="7">Huge</option>
                                        </select>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('justifyLeft', 'edit_description')" class="tooltip" data-tooltip="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyCenter', 'edit_description')" class="tooltip" data-tooltip="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyRight', 'edit_description')" class="tooltip" data-tooltip="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('justifyFull', 'edit_description')" class="tooltip" data-tooltip="Justify">
                                            <i class="fas fa-align-justify"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList', 'edit_description')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList', 'edit_description')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('indent', 'edit_description')" class="tooltip" data-tooltip="Indent">
                                            <i class="fas fa-indent"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('outdent', 'edit_description')" class="tooltip" data-tooltip="Outdent">
                                            <i class="fas fa-outdent"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat', 'edit_description')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                        <button type="button" onclick="createLink('edit_description')" class="tooltip" data-tooltip="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="edit_description" contenteditable="true"></div>
                                <input type="hidden" name="description" id="edit_description_input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_operating_hours" class="form-label">Operating Hours</label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('bold', 'edit_operating_hours')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('italic', 'edit_operating_hours')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('underline', 'edit_operating_hours')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('insertUnorderedList', 'edit_operating_hours')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" onclick="execCommand('insertOrderedList', 'edit_operating_hours')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" onclick="execCommand('removeFormat', 'edit_operating_hours')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="editor" id="edit_operating_hours" contenteditable="true"></div>
                                <input type="hidden" name="operating_hours" id="edit_operating_hours_input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="edit_image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                            <div id="current_image" class="mt-2"></div>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="edit_is_visible" name="is_visible">
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="button button-primary" onclick="submitForm('editFacilityForm')">Update Facility</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Store facility data for use in the updateVisibility function
        window.cg_facilities = <?= json_encode($facilities) ?>;
        
        function execCommand(command, editorId) {
            document.getElementById(editorId).focus();
            document.execCommand(command, false, null);
            updateToolbarState(editorId);
        }

        function execCommandWithArg(command, arg, editorId) {
            document.getElementById(editorId).focus();
            document.execCommand(command, false, arg);
            updateToolbarState(editorId);
        }

        function createLink(editorId) {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.getElementById(editorId).focus();
                document.execCommand('createLink', false, url);
            }
            updateToolbarState(editorId);
        }

        function updateToolbarState(editorId) {
            const toolbar = document.getElementById(editorId).closest('.editor-container').querySelector('.editor-toolbar');
            const buttons = toolbar.querySelectorAll('button');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        function submitForm(formId) {
            const form = document.getElementById(formId);
            
            // Update hidden inputs with editor content
            if (formId === 'addFacilityForm') {
                document.getElementById('description_input').value = document.getElementById('description').innerHTML;
                document.getElementById('operating_hours_input').value = document.getElementById('operating_hours').innerHTML;
            } else if (formId === 'editFacilityForm') {
                document.getElementById('edit_description_input').value = document.getElementById('edit_description').innerHTML;
                document.getElementById('edit_operating_hours_input').value = document.getElementById('edit_operating_hours').innerHTML;
            }
            
            form.submit();
        }

        function editFacility(facility) {
            document.getElementById('edit_id').value = facility.id;
            document.getElementById('edit_title').value = facility.title;
            document.getElementById('edit_description').value = facility.description;
            document.getElementById('edit_operating_hours').value = facility.operating_hours;
            document.getElementById('edit_is_visible').checked = facility.is_visible == 1;

            const imagePreview = document.getElementById('current_image');
            if (facility.image) {
                const imagePath = facility.image.startsWith('http') ? facility.image : '../../' + facility.image;
                imagePreview.innerHTML = `<img src="${imagePath}" class="preview-image" alt="Current facility image">`;
            } else {
                imagePreview.innerHTML = '';
            }

            const editModal = new bootstrap.Modal(document.getElementById('editFacilityModal'));
            editModal.show();
        }

        function deleteFacility(id) {
            if (confirm('Are you sure you want to delete this facility?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateVisibility(id, isVisible) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            // Get current facility data
            const facilityDataURL = typeof window.cg_facilities !== 'undefined' ? window.cg_facilities : {};
            const currentData = facilityDataURL[id] || {};
            
            // We need to include all required form fields, not just visibility
            const titleInput = document.createElement('input');
            titleInput.type = 'hidden';
            titleInput.name = 'title';
            titleInput.value = currentData.title || '';

            const descInput = document.createElement('input');
            descInput.type = 'hidden';
            descInput.name = 'description';
            descInput.value = currentData.description || '';

            const hoursInput = document.createElement('input');
            hoursInput.type = 'hidden';
            hoursInput.name = 'operating_hours';
            hoursInput.value = currentData.operating_hours || '';

            const imageInput = document.createElement('input');
            imageInput.type = 'hidden';
            imageInput.name = 'image';
            imageInput.value = currentData.image || '';

            const visibilityInput = document.createElement('input');
            visibilityInput.type = 'hidden';
            visibilityInput.name = 'is_visible';
            visibilityInput.value = isVisible ? '1' : '0';

            form.appendChild(actionInput);
            form.appendChild(idInput);
            form.appendChild(titleInput);
            form.appendChild(descInput);
            form.appendChild(hoursInput);
            form.appendChild(imageInput);
            form.appendChild(visibilityInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Initialize editors
        document.addEventListener('DOMContentLoaded', function() {
            const editors = ['description', 'operating_hours', 'edit_description', 'edit_operating_hours'];
            editors.forEach(editorId => {
                const editor = document.getElementById(editorId);
                if (editor) {
                    editor.addEventListener('keyup', () => updateToolbarState(editorId));
                    editor.addEventListener('mouseup', () => updateToolbarState(editorId));
                }
            });
        });
    </script>

</div>

    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_persistent.js"></script>
</body>
</html>
