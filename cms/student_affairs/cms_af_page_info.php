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

        .input-group {
            display: flex;
            margin-bottom: 20px;
        }

        .input-group-text {
            padding: 12px;
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-right: none;
            border-radius: 8px 0 0 8px;
            color: #2c5282;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .form-check-input {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        .form-check-label {
            font-weight: 500;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .d-flex {
            display: flex;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        .align-items-center {
            align-items: center;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mb-5 {
            margin-bottom: 20px;
        }

        .tab-container {
            display: flex;
            margin-bottom: 15px;
        }

        .btn-outline-primary {
            background: #f0f7ff;
            border: 1px solid #bee3f8;
            color: #3182ce;
        }

        .btn-outline-primary:hover, .btn-outline-primary.active {
            background: #ebf8ff;
            border-color: #3182ce;
            color: #2b6cb0;
        }

        .mt-3 {
            margin-top: 12px;
        }

        .me-2 {
            margin-right: 8px;
        }

        .text-muted {
            color: #718096;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
    <h1 class="mb-4">Page Info Management</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
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
    <section class="section mb-5">
        <h2>Page Info</h2>
        <form method="post" class="form-container" novalidate>
            <input type="hidden" name="update_info" value="1">
            
            <div class="form-group">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($info['title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
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

            <div class="form-group">
                <label class="switch-label">
                    Visibility
                    <label class="switch">
                        <input type="checkbox" id="visible" name="visible" value="1" <?= ($info['visibility'] ?? 1) ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" class="button button-primary">Update Info</button>
        </form>
    </section>

    <!-- Process Steps Section -->
    <section class="section mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Process Steps</h2>
            <button type="button" class="button button-primary" data-bs-toggle="modal" data-bs-target="#addStepModal">
                <i class="fas fa-plus"></i> Add Step
            </button>
        </div>

        <!-- Section Title Form -->
        <div class="form-container">
            <form method="post" class="mb-4">
                <input type="hidden" name="update_process_section" value="1">
                <div class="input-group">
                    <span class="input-group-text">Section Title</span>
                    <input type="text" class="form-control" name="process_section_title" 
                           value="<?= htmlspecialchars($process_settings['section_title']) ?>" required>
                    <button type="submit" class="button button-primary">Update Section Title</button>
                </div>
            </form>
        </div>

        <!-- Steps List -->
        <div class="table-responsive">
            <table class="table">
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
                        <td>
                            <span class="status-badge <?= $step['visibility'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $step['visibility'] ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td>
                            <button class="button btn-sm btn-warning" onclick="editStep(<?= htmlspecialchars(json_encode($step)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="button btn-sm btn-danger" onclick="deleteStep(<?= $step['id'] ?>)">
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
    <section class="section mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Downloads</h2>
            <button type="button" class="button button-primary" data-bs-toggle="modal" data-bs-target="#addDownloadModal">
                <i class="fas fa-plus"></i> Add Download
            </button>
        </div>

        <!-- Section Title Form -->
        <div class="form-container">
            <form method="post" class="mb-4">
                <input type="hidden" name="update_download_section" value="1">
                <div class="input-group">
                    <span class="input-group-text">Section Title</span>
                    <input type="text" class="form-control" name="download_section_title" 
                           value="<?= htmlspecialchars($download_settings['section_title']) ?>" required>
                    <button type="submit" class="button button-primary">Update Section Title</button>
                </div>
            </form>
        </div>

        <!-- Downloads List -->
        <div class="table-responsive">
            <table class="table">
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
                        <td>
                            <span class="status-badge <?= $download['visibility'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $download['visibility'] ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td>
                            <button class="button btn-sm btn-warning" onclick="editDownload(<?= htmlspecialchars(json_encode($download)) ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="button btn-sm btn-danger" onclick="deleteDownload(<?= $download['id'] ?>)">
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
                        
                        <div class="form-group">
                            <label for="step_title" class="form-label">Step Title</label>
                            <input type="text" class="form-control" id="step_title" name="step_title" required>
                        </div>

                        <div class="form-group">
                            <label for="step_description" class="form-label">Step Description</label>
                            <textarea class="form-control" id="step_description" name="step_description" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="step_visible" name="step_visible" value="1" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="button button-primary">Add Step</button>
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
                        
                        <div class="form-group">
                            <label for="edit_step_title" class="form-label">Step Title</label>
                            <input type="text" class="form-control" id="edit_step_title" name="step_title" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_step_description" class="form-label">Step Description</label>
                            <textarea class="form-control" id="edit_step_description" name="step_description" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="edit_step_visible" name="step_visible" value="1">
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="button button-primary">Update Step</button>
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
                        
                        <div class="form-group">
                            <label for="download_title" class="form-label">Download Title</label>
                            <input type="text" class="form-control" id="download_title" name="download_title" required>
                        </div>

                        <div class="form-group">
                            <label for="download_description" class="form-label">Description</label>
                            <textarea class="form-control" id="download_description" name="download_description" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">File Source</label>
                            <div class="tab-container">
                                <button type="button" class="button btn-outline-primary me-2 active" onclick="switchDownloadTab('file', 'add')">Upload File</button>
                                <button type="button" class="button btn-outline-primary" onclick="switchDownloadTab('url', 'add')">File URL</button>
                            </div>

                            <div id="add-file-tab" class="mt-3">
                                <input type="file" class="form-control" id="download_file" name="download_file">
                            </div>

                            <div id="add-url-tab" class="mt-3" style="display: none;">
                                <input type="url" class="form-control" id="download_url" name="download_url" placeholder="Enter file URL">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="download_visible" name="download_visible" value="1" checked>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="button button-primary">Add Download</button>
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
                        
                        <div class="form-group">
                            <label for="edit_download_title" class="form-label">Download Title</label>
                            <input type="text" class="form-control" id="edit_download_title" name="download_title" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_download_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_download_description" name="download_description" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">File Source</label>
                            <div class="tab-container">
                                <button type="button" class="button btn-outline-primary me-2 active" onclick="switchDownloadTab('file', 'edit')">Upload File</button>
                                <button type="button" class="button btn-outline-primary" onclick="switchDownloadTab('url', 'edit')">File URL</button>
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

                        <div class="form-group">
                            <label class="switch-label">
                                Visibility
                                <label class="switch">
                                    <input type="checkbox" id="edit_download_visible" name="download_visible" value="1">
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="button button-primary">Update Download</button>
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

</div>

    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_persistent.js"></script>
</body>
</html>
