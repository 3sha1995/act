<?php
require_once __DIR__ . '/../db_connection.php';

class CMS_AboutSection {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

    public function getContent() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `af_page` WHERE `id` = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                // If no record exists, create one with default values
                $this->createDefaultContent();
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Database error in getContent: " . $e->getMessage());
            return null;
        }
    }

    private function createDefaultContent() {
        try {
            $sql = "INSERT INTO `af_page` (`id`, `ontop_title`, `main_title`, `image_path`, `description`, `is_visible`) 
                    VALUES (1, 'ABOUT US', 'STUDENT AFFAIRS', '../imgs/cte.jpg', 
                    'Default content. Please update this in the CMS.', 1)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function updateContent($ontop_title, $main_title, $image_path, $description, $is_visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE `af_page` SET 
                `ontop_title` = ?, 
                `main_title` = ?, 
                `image_path` = ?, 
                `description` = ?, 
                `is_visible` = ? 
                WHERE `id` = 1");
            
            $result = $stmt->execute([$ontop_title, $main_title, $image_path, $description, $is_visible]);
            
            if (!$result) {
                error_log("Update failed: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
            return true;
        } catch (PDOException $e) {
            error_log("Database error in updateContent: " . $e->getMessage());
            return false;
        }
    }

    // Add this new function to get formatted content for the view
    public static function getAboutContent() {
        try {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("SELECT * FROM `af_page` WHERE `id` = 1");
            $stmt->execute();
            $content = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$content) {
                return null;
            }

            // Format image path
            $imagePath = $content['image_path'];
            if (strpos($imagePath, 'uploads/') === 0) {
                $imagePath = '../' . $imagePath;
            }

            return [
                'is_visible' => $content['is_visible'],
                'ontop_title' => $content['ontop_title'],
                'main_title' => $content['main_title'],
                'image_path' => $imagePath,
                'description' => $content['description']
            ];
        } catch (PDOException $e) {
            error_log("Error getting about content: " . $e->getMessage());
            return null;
        }
    }
}

class CMS_Dashboard {
    private $pdo;

    public function __construct() {
        $this->pdo = getPDOConnection();
    }

    public function getStats() {
        try {
            $stats = [
                'services' => $this->countRows('af_services'),
                'officers' => $this->countRows('af_officers'),
                'activities' => $this->countRows('af_activities'),
                'facilities' => $this->countRows('af_facilities'),
                'processes' => $this->countRows('af_processes')
            ];
            return $stats;
        } catch (PDOException $e) {
            error_log("Database error in getStats: " . $e->getMessage());
            return [];
        }
    }

    private function countRows($table) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting rows in $table: " . $e->getMessage());
            return 0;
        }
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - About Section</title>
    <link rel="stylesheet" href="student_affairs_sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        form {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            margin: 20px 0;
            border: 1px solid #bee3f8;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }
        
        input[type="text"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
            margin-bottom: 20px;
        }

        input[type="text"]:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        
        input[type="submit"], button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #3182ce;
            color: white;
        }

        input[type="submit"]:hover, button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
            background: #2b6cb0;
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
        
        .visibility-status {
            margin: 10px 0;
            padding: 15px;
            background: #ebf8ff;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            color: #2c5282;
            font-size: 0.95rem;
        }
        
        h1, h2, h3 {
            color: #2c5282;
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

        .image-preview {
            max-width: 200px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        .current-image-container {
            margin: 22px 0;
            padding: 22px;
            background: #ebf8ff;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
        }

        .current-image-preview {
            max-width: 320px;
            margin: 17px 0;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
            transition: transform 0.2s;
        }

        .current-image-preview:hover {
            transform: scale(1.03);
        }

        .current-image-label {
            display: block;
            font-weight: 600;
            margin-bottom: 13px;
            color: #2c5282;
        }

        .no-image-placeholder {
            padding: 25px;
            background: #f0f7ff;
            border: 1px dashed #93c5fd;
            border-radius: 8px;
            text-align: center;
            color: #3182ce;
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
        
        .button-primary {
            background: #3182ce;
            color: white;
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
        
        .visibility-status {
            margin: 10px 0;
            display: flex;
            align-items: center;
        }

        .form-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }
        
        .section {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }

        /* Dashboard styles */
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .dashboard-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
            border: 1px solid #bee3f8;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(49, 130, 206, 0.15);
        }
        
        .dashboard-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .dashboard-card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3182ce, #0056b3);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-right: 15px;
        }
        
        .dashboard-card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c5282;
            margin: 0;
        }
        
        .dashboard-card-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c5282;
            margin: 10px 0;
        }
        
        .dashboard-card-description {
            color: #4a5568;
            margin-bottom: 0;
            font-size: 0.9rem;
        }
        
        .dashboard-actions {
            margin-top: 20px;
        }
        
        .dashboard-action-link {
            display: inline-block;
            padding: 8px 15px;
            background: #ebf8ff;
            color: #3182ce;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .dashboard-action-link:hover {
            background: #bee3f8;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #3182ce, #0056b3);
            color: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
        }
        
        .welcome-title {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
            margin-bottom: 20px;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .quick-action-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }
        
        .quick-action-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
            color: white;
        }
        
        .services-icon { background: linear-gradient(135deg, #3182ce, #0056b3); }
        .officers-icon { background: linear-gradient(135deg, #38a169, #276749); }
        .activities-icon { background: linear-gradient(135deg, #e53e3e, #9b2c2c); }
        .facilities-icon { background: linear-gradient(135deg, #d69e2e, #975a16); }
        .processes-icon { background: linear-gradient(135deg, #805ad5, #553c9a); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
        }
        
        .stat-label {
            color: #4a5568;
            font-size: 1rem;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <?php include 'student_affairs_sidebar.php'; ?>
    
    <div class="content-wrapper">
    <?php
    // Change the title based on the section parameter
    $section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
    $title = ($section === 'about') ? 'About Section CMS' : 'Student Affairs Dashboard';
    ?>
    <h1><?php echo $title; ?></h1>

    <?php
    if ($section === 'dashboard') {
        // Dashboard content
        $dashboard = new CMS_Dashboard();
        $stats = $dashboard->getStats();
        ?>
        <div class="welcome-section">
            <h2 class="welcome-title">Welcome to the Student Affairs CMS</h2>
            <p class="welcome-subtitle">Manage all aspects of the Student Affairs website from this dashboard.</p>
            <div class="quick-actions">
                <a href="cms_af_page.php?section=about" class="quick-action-button">
                    <i class="fa fa-pen"></i> Edit About Section
                </a>
                <a href="cms_af_page_services.php" class="quick-action-button">
                    <i class="fa fa-clipboard-list"></i> Manage Services
                </a>
                <a href="cms_af_page_activities.php" class="quick-action-button">
                    <i class="fa fa-calendar-alt"></i> Manage Activities
                </a>
            </div>
        </div>

        <h2>Content Overview</h2>
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon services-icon">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <p class="stat-value"><?php echo $stats['services']; ?></p>
                <p class="stat-label">Services</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon officers-icon">
                    <i class="fa fa-user-tie"></i>
                </div>
                <p class="stat-value"><?php echo $stats['officers']; ?></p>
                <p class="stat-label">Officers</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon activities-icon">
                    <i class="fa fa-calendar-alt"></i>
                </div>
                <p class="stat-value"><?php echo $stats['activities']; ?></p>
                <p class="stat-label">Activities</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon facilities-icon">
                    <i class="fa fa-building"></i>
                </div>
                <p class="stat-value"><?php echo $stats['facilities']; ?></p>
                <p class="stat-label">Facilities</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon processes-icon">
                    <i class="fa fa-cogs"></i>
                </div>
                <p class="stat-value"><?php echo $stats['processes']; ?></p>
                <p class="stat-label">Processes</p>
            </div>
        </div>
        
        <h2>Recent Updates</h2>
        <div class="dashboard-container">
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-icon">
                        <i class="fa fa-clipboard-list"></i>
                    </div>
                    <h3 class="dashboard-card-title">Services</h3>
                </div>
                <p class="dashboard-card-description">Manage the services offered by Student Affairs</p>
                <div class="dashboard-actions">
                    <a href="cms_af_page_services.php" class="dashboard-action-link">
                        <i class="fa fa-arrow-right"></i> Manage Services
                    </a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-icon">
                        <i class="fa fa-user-tie"></i>
                    </div>
                    <h3 class="dashboard-card-title">Officers</h3>
                </div>
                <p class="dashboard-card-description">Update information about officers and staff</p>
                <div class="dashboard-actions">
                    <a href="cms_af_page_officer.php" class="dashboard-action-link">
                        <i class="fa fa-arrow-right"></i> Manage Officers
                    </a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-icon">
                        <i class="fa fa-calendar-alt"></i>
                    </div>
                    <h3 class="dashboard-card-title">Activities</h3>
                </div>
                <p class="dashboard-card-description">Add and manage activities and events</p>
                <div class="dashboard-actions">
                    <a href="cms_af_page_activities.php" class="dashboard-action-link">
                        <i class="fa fa-arrow-right"></i> Manage Activities
                    </a>
                </div>
            </div>
            
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <div class="dashboard-card-icon">
                        <i class="fa fa-building"></i>
                    </div>
                    <h3 class="dashboard-card-title">Facilities</h3>
                </div>
                <p class="dashboard-card-description">Update information about facilities and resources</p>
                <div class="dashboard-actions">
                    <a href="cms_af_page_facilities.php" class="dashboard-action-link">
                        <i class="fa fa-arrow-right"></i> Manage Facilities
                    </a>
                </div>
            </div>
        </div>
    <?php
    } else if ($section === 'about') {
        // About section content
    $cmsAbout = new CMS_AboutSection();

    // Handling the POST request for updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ontop_title = $_POST['ontop_title'];
        $main_title = $_POST['main_title'];
        $description = $_POST['description'];
        $is_visible = isset($_POST['is_visible']) ? 1 : 0;
        
        // Handle image upload or URL
        $image_path = '';
        if (!empty($_FILES['image_file']['name'])) {
            $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            
            if (in_array($file_extension, $allowed_extensions)) {
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $cmsAbout->getUploadDir() . $new_filename;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/' . $new_filename; // Store path relative to root
                        echo "<div class='status-badge status-active'>Image uploaded successfully!</div>";
                } else {
                        echo "<div class='status-badge status-inactive'>Error uploading file: " . error_get_last()['message'] . "</div>";
                }
            } else {
                    echo "<div class='status-badge status-inactive'>Invalid file type. Allowed types: jpg, jpeg, png, gif</div>";
            }
        } elseif (!empty($_POST['image_url'])) {
            $image_path = $_POST['image_url'];
        } elseif (!empty($_POST['existing_image'])) {
            $image_path = $_POST['existing_image'];
        }

        $updated = $cmsAbout->updateContent($ontop_title, $main_title, $image_path, $description, $is_visible);
        
        if ($updated) {
                echo "<div class='status-badge status-active'>Content updated successfully!</div>";
        } else {
                echo "<div class='status-badge status-inactive'>Error updating content. Please check the error logs.</div>";
        }
    }

    // Fetching and displaying the updated content
    $content = $cmsAbout->getContent();
    if ($content === null) {
            echo "<div class='status-badge status-inactive'>Error fetching content from database.</div>";
    }

    // Add visibility status indicator
    if ($content !== null) {
            echo "<div class='visibility-status'>";
            echo "<span class='status-badge " . ($content['is_visible'] == 1 ? "status-active" : "status-inactive") . "'>";
            echo ($content['is_visible'] == 1 ? "Visible" : "Hidden");
            echo "</span>";
        echo "</div>";
    }
    ?>
        <div class="section">
        <h2>About Section Settings</h2>
        <form method="POST" enctype="multipart/form-data" class="form-container">
            <div class="form-group">
        <label for="ontop_title">About Title:</label>
                <input type="text" id="ontop_title" name="ontop_title" value="<?= htmlspecialchars($content['ontop_title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
        <label for="main_title">Main Title:</label>
                <input type="text" id="main_title" name="main_title" value="<?= htmlspecialchars($content['main_title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
        <label>Image:</label>
            <input type="file" id="image_file" name="image_file" accept="image/*" onchange="previewImage(this)">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($content['image_path'] ?? '') ?>">
                <img id="image-preview" class="image-preview" alt="Preview" style="display: none;">
        </div>

        <div class="current-image-container">
            <h3><i class="fas fa-image"></i> Current Image</h3>
            <?php if (!empty($content['image_path'])): ?>
                <label class="current-image-label">Current image path: <?= htmlspecialchars($content['image_path']) ?></label>
                <?php
                $imagePath = $content['image_path'];
                // If path starts with ../, remove it to get correct path relative to CMS
                if (strpos($imagePath, '../') === 0) {
                    $imagePath = substr($imagePath, 3);
                }
                // Construct the correct path
                $displayPath = '../../' . $imagePath;
                ?>
                <img src="<?= htmlspecialchars($displayPath) ?>" 
                     alt="Current image" 
                     class="current-image-preview"
                     onerror="this.src='../../imgs/cte.jpg'; console.log('Failed to load image: ' + this.src);">
            <?php else: ?>
                <div class="no-image-placeholder">
                    <i class="fas fa-image"></i>
                    <p>No image currently set</p>
                </div>
            <?php endif; ?>
        </div>

            <div class="form-group">
        <label for="description">Description:</label>
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
            <div class="editor" id="editor" contenteditable="true"><?= $content['description'] ?? '' ?></div>
        <input type="hidden" name="description" id="description">
                </div>
            </div>

            <div class="form-group">
                <label class="switch-label">
                    Visibility:
                    <label class="switch">
                        <input type="checkbox" id="is_visible" name="is_visible" <?= ($content['is_visible'] ?? false) ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" class="button button-primary">Update Content</button>
    </form>
    </div>
    <?php
    }
    ?>
    </div> <!-- Close content-wrapper -->
    <!-- Include the sidebar JS -->
    <script src="student_affairs_sidebar.js"></script>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Rich text editor functions
        function execCommand(command) {
            document.execCommand(command, false, null);
            updateToolbarState();
        }

        function execCommandWithArg(command, arg) {
            document.execCommand(command, false, arg);
            updateToolbarState();
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
            form.addEventListener('submit', function(e) {
                    // Set hidden input value from editor content
                    const editor = document.getElementById('editor');
                    const descriptionField = document.getElementById('description');
                    if (editor && descriptionField) {
                        descriptionField.value = editor.innerHTML;
                    }
                });
            }
            
            // Setup editor
            const editor = document.getElementById('editor');
            if (editor) {
                editor.addEventListener('keyup', updateToolbarState);
                editor.addEventListener('mouseup', updateToolbarState);
                editor.focus();
                updateToolbarState();
            }
        });
    </script>
</body>
</html>