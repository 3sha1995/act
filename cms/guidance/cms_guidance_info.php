<?php
require_once __DIR__ . '/../db_connection.php';

class PageInfoCMS {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/downloads/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        try {
            // Create process steps table
            $sql = "CREATE TABLE IF NOT EXISTS af_page_process_steps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255),
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                visibility TINYINT(1) NOT NULL DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Create downloadable table
            $sql = "CREATE TABLE IF NOT EXISTS af_page_downloadable (
                id INT AUTO_INCREMENT PRIMARY KEY,
                section_title VARCHAR(255),
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                file_path VARCHAR(255),
                visibility TINYINT(1) NOT NULL DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Create info header table
            $sql = "CREATE TABLE IF NOT EXISTS af_pagec_info_header (
                id INT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                visibility TINYINT(1) NOT NULL DEFAULT 1
            )";
            $this->pdo->exec($sql);

            // Insert default header if not exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_pagec_info_header");
            if ($stmt->fetchColumn() == 0) {
                $this->pdo->exec("INSERT INTO af_pagec_info_header (id, title, description, visibility) 
                                VALUES (1, 'Student Affairs', 'Welcome to Student Affairs', 1)");
            }
        } catch (PDOException $e) {
            error_log("Error ensuring tables exist: " . $e->getMessage());
        }
    }

    // Info Header
    public function getPageInfo() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_pagec_info_header WHERE id = 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching page info: " . $e->getMessage());
            return null;
        }
    }

    public function updatePageInfo($title, $description, $visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_pagec_info_header SET title = ?, description = ?, visibility = ? WHERE id = 1");
            return $stmt->execute([$title, $description, $visible]);
        } catch (PDOException $e) {
            error_log("Error updating page info: " . $e->getMessage());
            return false;
        }
    }

    // Process Steps
    public function getProcessSteps() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_process_steps ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching process steps: " . $e->getMessage());
            return [];
        }
    }

    public function addProcessStep($title, $description, $visible = true, $section_title = 'How to Apply for Our Services') {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO af_page_process_steps (section_title, title, description, visibility) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$section_title, $title, $description, $visible]);
        } catch (PDOException $e) {
            error_log("Error adding process step: " . $e->getMessage());
            return false;
        }
    }

    public function updateProcessStep($id, $title, $description, $visible, $section_title = null) {
        try {
            if ($section_title !== null) {
                $stmt = $this->pdo->prepare("UPDATE af_page_process_steps SET section_title = ?, title = ?, description = ?, visibility = ? WHERE id = ?");
                return $stmt->execute([$section_title, $title, $description, $visible, $id]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE af_page_process_steps SET title = ?, description = ?, visibility = ? WHERE id = ?");
                return $stmt->execute([$title, $description, $visible, $id]);
            }
        } catch (PDOException $e) {
            error_log("Error updating process step: " . $e->getMessage());
            return false;
        }
    }

    public function deleteProcessStep($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM af_page_process_steps WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting process step: " . $e->getMessage());
            return false;
        }
    }

    // Downloads
    public function getDownloads() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM af_page_downloadable ORDER BY id DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching downloads: " . $e->getMessage());
            return [];
        }
    }

    public function addDownload($title, $desc, $file_data, $visible = true, $section_title = 'Downloadable Forms') {
        try {
            $file_path = '';
            
            // Handle URL
            if (isset($file_data['url']) && !empty($file_data['url'])) {
                $file_path = $file_data['url'];
            }
            // Handle file upload
            else if (isset($file_data['file']) && !empty($file_data['file']['name'])) {
                $file = $file_data['file'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $this->upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $file_path = 'uploads/downloads/' . $new_filename;
                } else {
                    throw new Exception("Error uploading file");
                }
            }

            $stmt = $this->pdo->prepare("INSERT INTO af_page_downloadable (section_title, title, description, file_path, visibility) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$section_title, $title, $desc, $file_path, $visible]);
        } catch (Exception $e) {
            error_log("Error adding download: " . $e->getMessage());
            return false;
        }
    }

    public function updateDownload($id, $title, $desc, $file_data, $visible, $section_title = null) {
        try {
            $file_path = $file_data['existing_path'];
            
            // Handle URL
            if (isset($file_data['url']) && !empty($file_data['url'])) {
                $file_path = $file_data['url'];
            }
            // Handle file upload
            else if (isset($file_data['file']) && !empty($file_data['file']['name'])) {
                $file = $file_data['file'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $this->upload_dir . $new_filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // Delete old file if it exists and is not a URL
                    if (!empty($file_data['existing_path']) && 
                        !filter_var($file_data['existing_path'], FILTER_VALIDATE_URL)) {
                        $old_file = '../../' . $file_data['existing_path'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    $file_path = 'uploads/downloads/' . $new_filename;
                } else {
                    throw new Exception("Error uploading file");
                }
            }

            if ($section_title !== null) {
                $stmt = $this->pdo->prepare("UPDATE af_page_downloadable SET section_title = ?, title = ?, description = ?, file_path = ?, visibility = ? WHERE id = ?");
                return $stmt->execute([$section_title, $title, $desc, $file_path, $visible, $id]);
            } else {
                $stmt = $this->pdo->prepare("UPDATE af_page_downloadable SET title = ?, description = ?, file_path = ?, visibility = ? WHERE id = ?");
                return $stmt->execute([$title, $desc, $file_path, $visible, $id]);
            }
        } catch (Exception $e) {
            error_log("Error updating download: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDownload($id) {
        try {
            // Get the file path before deleting
            $stmt = $this->pdo->prepare("SELECT file_path FROM af_page_downloadable WHERE id = ?");
            $stmt->execute([$id]);
            $download = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($download && !filter_var($download['file_path'], FILTER_VALIDATE_URL)) {
                $file_path = '../../' . $download['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            $stmt = $this->pdo->prepare("DELETE FROM af_page_downloadable WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting download: " . $e->getMessage());
            return false;
        }
    }

    // Section Settings
    public function getProcessSettings() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT section_title FROM af_page_process_steps WHERE section_title IS NOT NULL LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'section_title' => $result['section_title'] ?? 'How to Apply for Our Services',
                'section_visible' => 1
            ];
        } catch (PDOException $e) {
            error_log("Error fetching process settings: " . $e->getMessage());
            return [
                'section_title' => 'How to Apply for Our Services',
                'section_visible' => 1
            ];
        }
    }

    public function getDownloadSettings() {
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT section_title FROM af_page_downloadable WHERE section_title IS NOT NULL LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'section_title' => $result['section_title'] ?? 'Downloadable Forms',
                'section_visible' => 1
            ];
        } catch (PDOException $e) {
            error_log("Error fetching download settings: " . $e->getMessage());
            return [
                'section_title' => 'Downloadable Forms',
                'section_visible' => 1
            ];
        }
    }

    public function updateProcessSettings($title, $visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_process_steps SET section_title = ?");
            return $stmt->execute([$title]);
        } catch (PDOException $e) {
            error_log("Error updating process settings: " . $e->getMessage());
            return false;
        }
    }

    public function updateDownloadSettings($title, $visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_downloadable SET section_title = ?");
            return $stmt->execute([$title]);
        } catch (PDOException $e) {
            error_log("Error updating download settings: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize CMS
$cms = new PageInfoCMS();

// Get existing data
$info = $cms->getPageInfo();
$steps = $cms->getProcessSteps();
$downloads = $cms->getDownloads();
$process_settings = $cms->getProcessSettings();
$download_settings = $cms->getDownloadSettings();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_info'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $visible = isset($_POST['visible']) ? 1 : 0;
        
        if ($cms->updatePageInfo($title, $description, $visible)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit;
        }
    }
    
    if (isset($_POST['step_action'])) {
        $action = $_POST['step_action'];
        $id = $_POST['step_id'] ?? '';
        $title = $_POST['step_title'] ?? '';
        $description = $_POST['step_description'] ?? '';
        $visible = isset($_POST['step_visible']) ? 1 : 0;
        
        if ($action === 'add') {
            if ($cms->addProcessStep($title, $description, $visible)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=2");
                exit;
            }
        } else if ($action === 'edit') {
            if ($cms->updateProcessStep($id, $title, $description, $visible)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=2");
                exit;
            }
        } else if ($action === 'delete') {
            if ($cms->deleteProcessStep($id)) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=4");
                exit;
            }
        }
    }
    
    if (isset($_POST['add_download'])) {
        $title = $_POST['download_title'] ?? '';
        $description = $_POST['download_description'] ?? '';
        $visible = isset($_POST['download_visible']) ? 1 : 0;
        
        $file_data = [
            'file' => $_FILES['download_file'] ?? null,
            'url' => $_POST['download_url'] ?? ''
        ];
        
        if ($cms->addDownload($title, $description, $file_data, $visible)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=3");
            exit;
        } else {
            $error = "Failed to add download";
        }
    }

    if (isset($_POST['download_action']) && $_POST['download_action'] === 'edit') {
        $id = $_POST['download_id'] ?? '';
        $title = $_POST['download_title'] ?? '';
        $description = $_POST['download_description'] ?? '';
        $visible = isset($_POST['download_visible']) ? 1 : 0;
        
        $file_data = [
            'file' => $_FILES['download_file'] ?? null,
            'url' => $_POST['download_url'] ?? '',
            'existing_path' => $_POST['existing_file_path'] ?? ''
        ];
        
        if ($cms->updateDownload($id, $title, $description, $file_data, $visible)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=5");
            exit;
        } else {
            $error = "Failed to update download";
        }
    }

    if (isset($_POST['download_action']) && $_POST['download_action'] === 'delete') {
        $id = $_POST['download_id'] ?? '';
        
        if ($cms->deleteDownload($id)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=6");
            exit;
        } else {
            $error = "Failed to delete download";
        }
    }

    if (isset($_POST['update_process_section'])) {
        $section_title = $_POST['process_section_title'] ?? 'How to Apply for Our Services';
        if ($cms->updateProcessSettings($section_title, 1)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=7");
            exit;
        }
    }

    if (isset($_POST['update_download_section'])) {
        $section_title = $_POST['download_section_title'] ?? 'Downloadable Forms';
        if ($cms->updateDownloadSettings($section_title, 1)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=8");
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
    <title>Page Info Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .editor-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .toolbar-group {
            display: flex;
            gap: 5px;
            padding: 0 10px;
            border-right: 1px solid #e0e0e0;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .editor-toolbar button {
            background: white;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #444;
            transition: all 0.2s ease;
        }

        .editor-toolbar button:hover {
            background: #e9ecef;
            border-color: #bbb;
        }

        .editor-toolbar button.active {
            background: #e9ecef;
            border-color: #0056b3;
            color: #0056b3;
        }

        .editor-toolbar button i {
            font-size: 14px;
        }

        .editor {
            min-height: 300px;
            padding: 20px;
            background: white;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        .editor:focus {
            outline: none;
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
            padding: 4px 8px;
            background: #333;
            color: white;
            font-size: 12px;
            border-radius: 4px;
            white-space: nowrap;
            z-index: 1000;
        }

        /* Font size select styles */
        .font-size-select {
            padding: 6px 12px;
            border: 1px solid #d1d1d1;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            color: #444;
            font-size: 14px;
        }

        .font-size-select:hover {
            background: #e9ecef;
            border-color: #bbb;
        }

        .font-size-select:focus {
            outline: none;
            border-color: #0056b3;
        }

        /* Modal styles */
        .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-footer {
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>
<body class="container py-5">
    <h1 class="mb-4">Page Info Management</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php
            switch ($_GET['success']) {
                case 1:
                    echo "Page info updated successfully!";
                    break;
                case 2:
                    echo "Process step added/updated successfully!";
                    break;
                case 3:
                    echo "Download added successfully!";
                    break;
                case 4:
                    echo "Process step deleted successfully!";
                    break;
                case 5:
                    echo "Download updated successfully!";
                    break;
                case 6:
                    echo "Download deleted successfully!";
                    break;
                case 7:
                    echo "Process section settings updated successfully!";
                    break;
                case 8:
                    echo "Downloads section settings updated successfully!";
                    break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Info Section -->
    <section class="mb-5">
        <h2>Page Info</h2>
        <form method="post" class="needs-validation" novalidate>
            <input type="hidden" name="update_info" value="1">
            
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($info['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <div class="editor-container">
                    <div class="editor-toolbar">
                        <div class="toolbar-group">
                            <button type="button" onclick="execCommand('bold')" class="tooltip" data-tooltip="Bold">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" onclick="execCommand('italic')" class="tooltip" data-tooltip="Italic">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" onclick="execCommand('underline')" class="tooltip" data-tooltip="Underline">
                                <i class="fas fa-underline"></i>
                            </button>
                            <button type="button" onclick="execCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
                                <i class="fas fa-strikethrough"></i>
                            </button>
                        </div>

                        <div class="toolbar-group">
                            <select onchange="execCommandWithArg('fontSize', this.value)" class="font-size-select tooltip" data-tooltip="Font Size">
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
                            <button type="button" onclick="execCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                <i class="fas fa-align-left"></i>
                            </button>
                            <button type="button" onclick="execCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                <i class="fas fa-align-center"></i>
                            </button>
                            <button type="button" onclick="execCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                <i class="fas fa-align-right"></i>
                            </button>
                            <button type="button" onclick="execCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                <i class="fas fa-align-justify"></i>
                            </button>
                        </div>

                        <div class="toolbar-group">
                            <button type="button" onclick="execCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                <i class="fas fa-list-ul"></i>
                            </button>
                            <button type="button" onclick="execCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                <i class="fas fa-list-ol"></i>
                            </button>
                            <button type="button" onclick="execCommand('indent')" class="tooltip" data-tooltip="Indent">
                                <i class="fas fa-indent"></i>
                            </button>
                            <button type="button" onclick="execCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                <i class="fas fa-outdent"></i>
                            </button>
                        </div>

                        <div class="toolbar-group">
                            <button type="button" onclick="execCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                <i class="fas fa-eraser"></i>
                            </button>
                            <button type="button" onclick="createLink()" class="tooltip" data-tooltip="Insert Link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                    <div class="editor" id="description" contenteditable="true"><?= $info['description'] ?? '' ?></div>
                    <input type="hidden" name="description" id="description_input">
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="visible" name="visible" value="1" <?= ($info['visibility'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="visible">Visible</label>
            </div>

            <button type="submit" class="btn btn-primary">Update Info</button>
        </form>
    </section>

    <!-- Process Steps Section -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Process Steps</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStepModal">
                <i class="fas fa-plus"></i> Add Step
            </button>
        </div>

        <!-- Section Title Form -->
        <form method="post" class="mb-4">
            <input type="hidden" name="update_process_section" value="1">
            <div class="input-group">
                <span class="input-group-text">Section Title</span>
                <input type="text" class="form-control" name="process_section_title" 
                       value="<?= htmlspecialchars($process_settings['section_title']) ?>" required>
                <button type="submit" class="btn btn-primary">Update Section Title</button>
            </div>
        </form>

        <!-- Steps List -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($steps as $step): ?>
                    <tr>
                        <td><?= htmlspecialchars($step['title']) ?></td>
                        <td><?= htmlspecialchars($step['description']) ?></td>
                        <td><?= $step['visibility'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editStep(<?= htmlspecialchars(json_encode($step)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteStep(<?= $step['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Downloads Section -->
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Downloads</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDownloadModal">
                <i class="fas fa-plus"></i> Add Download
            </button>
        </div>

        <!-- Section Title Form -->
        <form method="post" class="mb-4">
            <input type="hidden" name="update_download_section" value="1">
            <div class="input-group">
                <span class="input-group-text">Section Title</span>
                <input type="text" class="form-control" name="download_section_title" 
                       value="<?= htmlspecialchars($download_settings['section_title']) ?>" required>
                <button type="submit" class="btn btn-primary">Update Section Title</button>
            </div>
        </form>

        <!-- Downloads List -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>File Path</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($downloads as $download): ?>
                    <tr>
                        <td><?= htmlspecialchars($download['title']) ?></td>
                        <td><?= htmlspecialchars($download['description']) ?></td>
                        <td><?= htmlspecialchars($download['file_path']) ?></td>
                        <td><?= $download['visibility'] ? 'Yes' : 'No' ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editDownload(<?= htmlspecialchars(json_encode($download)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteDownload(<?= $download['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Add Step Modal -->
    <div class="modal fade" id="addStepModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="step_action" value="add">
                        
                        <div class="mb-3">
                            <label for="step_title" class="form-label">Step Title</label>
                            <input type="text" class="form-control" id="step_title" name="step_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="step_description" class="form-label">Step Description</label>
                            <textarea class="form-control" id="step_description" name="step_description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="step_visible" name="step_visible" value="1" checked>
                            <label class="form-check-label" for="step_visible">Visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Step Modal -->
    <div class="modal fade" id="editStepModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Process Step</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="step_action" value="edit">
                        <input type="hidden" name="step_id" id="edit_step_id">
                        
                        <div class="mb-3">
                            <label for="edit_step_title" class="form-label">Step Title</label>
                            <input type="text" class="form-control" id="edit_step_title" name="step_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_step_description" class="form-label">Step Description</label>
                            <textarea class="form-control" id="edit_step_description" name="step_description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_step_visible" name="step_visible" value="1">
                            <label class="form-check-label" for="edit_step_visible">Visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Download Modal -->
    <div class="modal fade" id="addDownloadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Download</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="add_download" value="1">
                        
                        <div class="mb-3">
                            <label for="download_title" class="form-label">Download Title</label>
                            <input type="text" class="form-control" id="download_title" name="download_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="download_description" class="form-label">Description</label>
                            <textarea class="form-control" id="download_description" name="download_description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Source</label>
                            <div class="tab-container">
                                <button type="button" class="btn btn-outline-primary me-2 active" onclick="switchDownloadTab('file', 'add')">Upload File</button>
                                <button type="button" class="btn btn-outline-primary" onclick="switchDownloadTab('url', 'add')">File URL</button>
                            </div>

                            <div id="add-file-tab" class="mt-3">
                                <input type="file" class="form-control" id="download_file" name="download_file">
                            </div>

                            <div id="add-url-tab" class="mt-3" style="display: none;">
                                <input type="url" class="form-control" id="download_url" name="download_url" placeholder="Enter file URL">
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="download_visible" name="download_visible" value="1" checked>
                            <label class="form-check-label" for="download_visible">Visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Download Modal -->
    <div class="modal fade" id="editDownloadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Download</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" class="needs-validation" novalidate enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="download_action" value="edit">
                        <input type="hidden" name="download_id" id="edit_download_id">
                        
                        <div class="mb-3">
                            <label for="edit_download_title" class="form-label">Download Title</label>
                            <input type="text" class="form-control" id="edit_download_title" name="download_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_download_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_download_description" name="download_description" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Source</label>
                            <div class="tab-container">
                                <button type="button" class="btn btn-outline-primary me-2 active" onclick="switchDownloadTab('file', 'edit')">Upload File</button>
                                <button type="button" class="btn btn-outline-primary" onclick="switchDownloadTab('url', 'edit')">File URL</button>
                            </div>

                            <div id="edit-file-tab" class="mt-3">
                                <input type="file" class="form-control" id="edit_download_file" name="download_file">
                                <small class="text-muted">Leave empty to keep existing file</small>
                            </div>

                            <div id="edit-url-tab" class="mt-3" style="display: none;">
                                <input type="url" class="form-control" id="edit_download_url" name="download_url" placeholder="Enter file URL">
                            </div>

                            <input type="hidden" name="existing_file_path" id="edit_existing_file_path">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_download_visible" name="download_visible" value="1">
                            <label class="form-check-label" for="edit_download_visible">Visible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Download</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Rich text editor functions
        function execCommand(command) {
            document.getElementById('description').focus();
            document.execCommand(command, false, null);
            updateToolbarState();
        }

        function execCommandWithArg(command, arg) {
            document.getElementById('description').focus();
            document.execCommand(command, false, arg);
            updateToolbarState();
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.getElementById('description').focus();
                document.execCommand('createLink', false, url);
            }
            updateToolbarState();
        }

        function updateToolbarState() {
            const buttons = document.querySelectorAll('.editor-toolbar button');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Form submission handler
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('#description')) {
                    document.getElementById('description_input').value = 
                        document.getElementById('description').innerHTML;
                }
                
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Step management functions
        function editStep(step) {
            document.getElementById('edit_step_id').value = step.id;
            document.getElementById('edit_step_title').value = step.title;
            document.getElementById('edit_step_description').value = step.description;
            document.getElementById('edit_step_visible').checked = step.visibility == 1;
            
            const modal = new bootstrap.Modal(document.getElementById('editStepModal'));
            modal.show();
        }

        function deleteStep(id) {
            if (confirm('Are you sure you want to delete this step?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="step_action" value="delete">
                    <input type="hidden" name="step_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Download management functions
        function switchDownloadTab(tabName, mode) {
            const fileTab = document.getElementById(`${mode}-file-tab`);
            const urlTab = document.getElementById(`${mode}-url-tab`);
            const buttons = document.querySelectorAll(`#${mode}DownloadModal .tab-container button`);
            
            if (tabName === 'file') {
                fileTab.style.display = 'block';
                urlTab.style.display = 'none';
                buttons[0].classList.add('active');
                buttons[1].classList.remove('active');
            } else {
                fileTab.style.display = 'none';
                urlTab.style.display = 'block';
                buttons[0].classList.remove('active');
                buttons[1].classList.add('active');
            }
        }

        function editDownload(download) {
            document.getElementById('edit_download_id').value = download.id;
            document.getElementById('edit_download_title').value = download.title;
            document.getElementById('edit_download_description').value = download.description;
            document.getElementById('edit_existing_file_path').value = download.file_path;
            document.getElementById('edit_download_visible').checked = download.visibility == 1;
            
            // If the existing path is a URL, switch to URL tab and populate it
            if (download.file_path.startsWith('http://') || download.file_path.startsWith('https://')) {
                switchDownloadTab('url', 'edit');
                document.getElementById('edit_download_url').value = download.file_path;
            } else {
                switchDownloadTab('file', 'edit');
            }
            
            const modal = new bootstrap.Modal(document.getElementById('editDownloadModal'));
            modal.show();
        }

        function deleteDownload(id) {
            if (confirm('Are you sure you want to delete this download?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="download_action" value="delete">
                    <input type="hidden" name="download_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Initialize editor
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('description');
            
            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>
