<?php
require_once '../db_connection.php';

class ServicesCMS {
    private $pdo;
    private $upload_dir = '../uploads/health_services/';
    private $table_name = 'health_services';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableStructure();
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    private function ensureTableStructure() {
        try {
            // Check if the table exists
            $tableExists = false;
            try {
                $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->table_name} LIMIT 1");
                $stmt->execute();
                $tableExists = true;
            } catch (PDOException $e) {
                $tableExists = false;
            }

            // If table doesn't exist, create it
            if (!$tableExists) {
                $this->pdo->exec("CREATE TABLE `{$this->table_name}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `section_title` varchar(255) NOT NULL DEFAULT 'Our Services',
                    `section_description` text NOT NULL DEFAULT 'We provide a range of healthcare services to support the well-being of our university community.',
                    `icon_class` varchar(255) NOT NULL,
                    `service_title` varchar(255) NOT NULL,
                    `service_description` text NOT NULL,
                    `is_visible` tinyint(1) NOT NULL DEFAULT 1,
                    `created_at` timestamp NULL DEFAULT current_timestamp(),
                    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

                // Insert default row
                $this->pdo->exec("INSERT INTO `{$this->table_name}` 
                    (`section_title`, `section_description`, `icon_class`, `service_title`, `service_description`, `is_visible`) 
                    VALUES (
                        'Our Services',
                        'We provide a range of healthcare services to support the well-being of our university community.',
                        'fas fa-heartbeat',
                        'Medical Consultation',
                        'Professional medical consultation services for students and staff.',
                        1
                    )");

                error_log("Table {$this->table_name} created successfully");
            }
        } catch (PDOException $e) {
            error_log("Error checking/creating {$this->table_name} table: " . $e->getMessage());
            throw $e;
        }
    }

    // Get all services
    public function getAllServices($page = 1, $perPage = 6) {
        try {
            // Get total count
            $countStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table_name}");
            $totalServices = $countStmt->fetchColumn();
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get paginated services
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'services' => $services,
                'total' => $totalServices,
                'pages' => ceil($totalServices / $perPage),
                'current_page' => $page
            ];
        } catch (PDOException $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return [
                'services' => [],
                'total' => 0,
                'pages' => 1,
                'current_page' => 1
            ];
        }
    }

    // Get main section content
    public function getMainSection() {
        $stmt = $this->pdo->query("SELECT section_title, section_description, is_visible FROM {$this->table_name} LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update main section
    public function updateMainSection($title, $description, $isVisible) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table_name} 
                                   SET section_title = ?, section_description = ?, is_visible = ? 
                                   WHERE id = 1");
        return $stmt->execute([$title, $description, $isVisible]);
    }

    private function handleIconUpload($files, $currentIcon = '') {
        try {
            // If URL is provided and not empty, validate and return it
            if (isset($_POST['icon_url']) && !empty($_POST['icon_url'])) {
                $url = trim($_POST['icon_url']);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    error_log("Using URL as icon: " . $url);
                    return $url;
                }
            }
            
            // If no file uploaded and no URL, keep current icon (for updates)
            if (!isset($files['icon_file']) || $files['icon_file']['error'] === UPLOAD_ERR_NO_FILE) {
                error_log("No new file uploaded, keeping current icon: " . $currentIcon);
                return $currentIcon;
            }

            $file = $files['icon_file'];
            error_log("Processing uploaded file: " . $file['name']);
            
            // Validate file
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowed));
            }

            // Generate unique filename
            $filename = uniqid() . '.' . $ext;
            $filepath = $this->upload_dir . $filename;
            error_log("Generated filepath: " . $filepath);

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                error_log("File uploaded successfully");
                // Delete old file if it exists and is not a URL
                if ($currentIcon && !filter_var($currentIcon, FILTER_VALIDATE_URL)) {
                    $oldPath = $this->upload_dir . basename($currentIcon);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                        error_log("Deleted old icon: " . $oldPath);
                    }
                }
                // Return the relative path from the web root
                return 'uploads/health_services/' . $filename;
            }

            throw new Exception('Failed to upload file');
        } catch (Exception $e) {
            error_log("Error in handleIconUpload: " . $e->getMessage());
            throw $e;
        }
    }

    public function addService($data) {
        try {
            // Handle icon upload
            $iconPath = $this->handleIconUpload($_FILES);
            
            $stmt = $this->pdo->prepare("INSERT INTO {$this->table_name} (section_title, section_description, icon_class, service_title, service_description, is_visible)
                                       VALUES (:section_title, :section_description, :icon_class, :service_title, :service_description, :is_visible)");
            
            return $stmt->execute([
                'section_title' => $data['section_title'],
                'section_description' => $data['section_description'],
                'icon_class' => $iconPath,
                'service_title' => $data['service_title'],
                'service_description' => $data['service_description'],
                'is_visible' => $data['is_visible']
            ]);
        } catch (Exception $e) {
            error_log("Error adding service: " . $e->getMessage());
            return false;
        }
    }

    public function updateService($id, $data) {
        try {
            // Get current icon
            $stmt = $this->pdo->prepare("SELECT icon_class FROM {$this->table_name} WHERE id = ?");
            $stmt->execute([$id]);
            $currentIcon = $stmt->fetchColumn();
            error_log("Current icon for service ID $id: " . $currentIcon);

            // Handle icon update
            $iconPath = $currentIcon; // Default to current icon
            
            // Check if new file is uploaded
            if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] === UPLOAD_ERR_OK) {
                error_log("New file uploaded for service ID: " . $id);
                $iconPath = $this->handleIconUpload($_FILES, $currentIcon);
            }
            // Check if new URL is provided
            elseif (isset($_POST['icon_url']) && !empty($_POST['icon_url'])) {
                error_log("New URL provided for service ID: " . $id);
                $iconPath = $this->handleIconUpload($_FILES, $currentIcon);
            }

            error_log("Final icon path for update: " . $iconPath);
            
            $stmt = $this->pdo->prepare("UPDATE {$this->table_name} 
                                       SET section_title = :section_title,
                                           section_description = :section_description,
                                           icon_class = :icon_class,
                                           service_title = :service_title,
                                           service_description = :service_description,
                                           is_visible = :is_visible
                                       WHERE id = :id");
            
            $result = $stmt->execute([
                'id' => $id,
                'section_title' => $data['section_title'],
                'section_description' => $data['section_description'],
                'icon_class' => $iconPath,
                'service_title' => $data['service_title'],
                'service_description' => $data['service_description'],
                'is_visible' => $data['is_visible']
            ]);

            if (!$result) {
                error_log("Failed to update service. PDO Error: " . json_encode($stmt->errorInfo()));
                throw new Exception("Database update failed");
            }

            return true;
        } catch (Exception $e) {
            error_log("Error updating service: " . $e->getMessage());
            throw new Exception("Failed to update service: " . $e->getMessage());
        }
    }

    // Delete service
    public function deleteService($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table_name} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

// Instantiate
$cms = new ServicesCMS($pdo);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_main'])) {
            $cms->updateMainSection(
                $_POST['main_title'],
                $_POST['main_description'],
                isset($_POST['main_visible']) ? 1 : 0
            );
            $_SESSION['success'] = 'Main section updated successfully';
        }
        
        if (isset($_POST['add_service'])) {
            $serviceData = [
                'section_title' => $_POST['section_title'] ?? 'Our Services',
                'section_description' => $_POST['section_description'] ?? 'We provide a range of healthcare services.',
                'service_title' => $_POST['service_title'],
                'service_description' => $_POST['service_description'],
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0
            ];

            if ($cms->addService($serviceData)) {
                $_SESSION['success'] = 'Service added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add service';
            }
        }

        if (isset($_POST['update_service'])) {
            $serviceData = [
                'section_title' => $_POST['section_title'] ?? 'Our Services',
                'section_description' => $_POST['section_description'] ?? 'We provide a range of healthcare services.',
                'service_title' => $_POST['service_title'],
                'service_description' => $_POST['service_description'],
                'is_visible' => isset($_POST['is_visible']) ? 1 : 0
            ];

            // Handle icon update
            if (!empty($_FILES['icon_file']['name'])) {
                $serviceData['icon_file'] = $_FILES['icon_file'];
            } elseif (!empty($_POST['icon_url'])) {
                $serviceData['icon_class'] = $_POST['icon_url'];
            }

            if ($cms->updateService($_POST['id'], $serviceData)) {
                $_SESSION['success'] = 'Service updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update service';
            }
        }

        if (isset($_POST['delete_service'])) {
            if ($cms->deleteService($_POST['id'])) {
                $_SESSION['success'] = 'Service deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete service';
            }
        }
    } catch (Exception $e) {
        error_log("Error in form processing: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }

    // If it's an AJAX request, send JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => !isset($_SESSION['error']),
            'message' => isset($_SESSION['error']) ? $_SESSION['error'] : $_SESSION['success']
        ]);
        exit;
    }

    // For regular form submissions, redirect
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$mainSection = $cms->getMainSection();
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$servicesData = $cms->getAllServices($page);
$services = $servicesData['services'];

// Convert services to JSON for JavaScript
$servicesJson = json_encode($services);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Affairs Services CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f8f9fa;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .preview-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-card i {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 15px;
            display: block;
        }

        .preview-card img {
            width: 48px;
            height: 48px;
            object-fit: contain;
            margin-bottom: 15px;
            display: block;
        }

        .preview-card h3 {
            margin: 10px 0;
            color: #333;
        }

        .preview-card p {
            color: #666;
            font-size: 0.9em;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .status-active {
            background: #28a745;
            color: white;
        }

        .status-inactive {
            background: #dc3545;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            overflow-y: auto;
        }

        .modal-content {
            position: relative;
            background: white;
            margin: 5% auto;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .add-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-btn:hover {
            background: #218838;
        }

        .icon-preview {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 4px;
            margin: 10px 0;
            border: 1px solid #ddd;
        }

        .icon-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .icon-suggestions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .icon-suggestion {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9em;
        }

        .icon-suggestion:hover {
            background: #e9ecef;
        }

        .tab-container {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .tab-button {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 8px 15px;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }

        .tab-button.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }

        .tab-content {
            display: none;
            padding: 15px;
            border: 1px solid #ddd;
            border-top: none;
            background: white;
        }

        .tab-content.active {
            display: block;
        }

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

        .editor {
            min-height: 200px;
            padding: 20px;
            background: white;
            border: none;
            outline: none;
            font-size: 16px;
            line-height: 1.6;
        }

        td img {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }

        .pagination-button {
            padding: 8px 16px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            color: #007bff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination-button:hover {
            background: #e9ecef;
            color: #0056b3;
        }

        .pagination-button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
    <script>
        // Make services data available to JavaScript
        const services = <?= $servicesJson ?>;
    </script>
</head>
<body>
    <div class="container">
        <!-- Main Section Settings -->
        <div class="section">
            <h2>Main Section Settings</h2>
<form method="POST">
                <div class="form-group">
                    <label>Section Title:</label>
                    <input type="text" name="main_title" value="<?= htmlspecialchars($mainSection['section_title']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Section Description:</label>
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" onclick="execMainCommand('bold')" class="tooltip" data-tooltip="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('italic')" class="tooltip" data-tooltip="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('underline')" class="tooltip" data-tooltip="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
                                    <i class="fas fa-strikethrough"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <select onchange="execMainCommandWithArg('fontSize', this.value)" class="font-size-select tooltip" data-tooltip="Font Size">
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
                                <button type="button" onclick="execMainCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                    <i class="fas fa-align-justify"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" onclick="execMainCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('indent')" class="tooltip" data-tooltip="Indent">
                                    <i class="fas fa-indent"></i>
                                </button>
                                <button type="button" onclick="execMainCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                    <i class="fas fa-outdent"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" onclick="execMainCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                    <i class="fas fa-eraser"></i>
                                </button>
                                <button type="button" onclick="createMainLink()" class="tooltip" data-tooltip="Insert Link">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                        <div class="editor" id="main_description" contenteditable="true"><?= htmlspecialchars($mainSection['section_description']) ?></div>
                        <input type="hidden" name="main_description" id="main_description_input">
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="main_visible" <?= $mainSection['is_visible'] ? 'checked' : '' ?>>
                        Show this section
                    </label>
                </div>
                <button type="submit" name="update_main">Update Main Section</button>
</form>
        </div>

        <!-- Services Management -->
        <div class="section">
            <h2>Services Management</h2>

            <!-- Add Service Button -->
            <button type="button" onclick="openAddModal()" class="add-btn">
                <i class="fas fa-plus"></i> Add New Service
            </button>

            <!-- Services Table -->
<h3>Existing Services</h3>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Icon</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($services as $service): ?>
                    <tr data-id="<?= $service['id'] ?>">
                        <td><?= htmlspecialchars($service['service_title']) ?></td>
                        <td>
                            <?= htmlspecialchars(substr(strip_tags($service['service_description']), 0, 50)) ?>...
                            <input type="hidden" name="full_description" value="<?= htmlspecialchars($service['service_description']) ?>">
                        </td>
                        <td>
                            <?php if (strpos($service['icon_class'], 'http') === 0 || strpos($service['icon_class'], '../') === 0): ?>
                                <img src="<?= htmlspecialchars($service['icon_class']) ?>" alt="Icon" style="width: 24px; height: 24px; object-fit: contain;">
                            <?php elseif (strpos($service['icon_class'], 'uploads/') === 0): ?>
                                <img src="../<?= htmlspecialchars($service['icon_class']) ?>" alt="Icon" style="width: 24px; height: 24px; object-fit: contain;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $service['is_visible'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $service['is_visible'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d', strtotime($service['created_at'])) ?></td>
                        <td>
                            <button onclick="editService(<?= $service['id'] ?>)" type="button">Edit</button>
                            <form method="POST" style="display: inline;">
        <input type="hidden" name="id" value="<?= $service['id'] ?>">
                                <button type="submit" name="delete_service" class="delete-btn" 
                                        onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Preview Cards -->
            <h3>Preview Cards</h3>
            <div class="preview-grid">
                <?php foreach ($services as $service): ?>
                <?php if ($service['is_visible']): ?>
                <div class="preview-card">
                    <?php if (strpos($service['icon_class'], 'http') === 0 || strpos($service['icon_class'], 'data:image') === 0 || strpos($service['icon_class'], '../') === 0): ?>
                        <img src="<?= htmlspecialchars($service['icon_class']) ?>" alt="Icon" style="width: 48px; height: 48px; object-fit: contain; margin-bottom: 15px;">
                    <?php else: ?>
                        <i class="<?= htmlspecialchars($service['icon_class']) ?>"></i>
                    <?php endif; ?>
                    <h3><?= htmlspecialchars($service['service_title']) ?></h3>
                    <p><?= htmlspecialchars($service['service_description']) ?></p>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($servicesData['pages'] > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>#services" class="pagination-button" onclick="saveScrollPosition()">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $servicesData['pages']; $i++): ?>
                    <a href="?page=<?= $i ?>#services" class="pagination-button <?= $i === $page ? 'active' : '' ?>" onclick="saveScrollPosition()">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $servicesData['pages']): ?>
                    <a href="?page=<?= $page + 1 ?>#services" class="pagination-button" onclick="saveScrollPosition()">Next &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h3>Add New Service</h3>
            <form method="POST" id="addServiceForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Service Title:</label>
                    <input type="text" name="service_title" required>
                </div>
                
                <div class="form-group">
                    <label>Icon:</label>
                    <div class="tab-container">
                        <button type="button" class="tab-button active" onclick="switchIconTab('file')">Upload File</button>
                        <button type="button" class="tab-button" onclick="switchIconTab('url')">Icon URL</button>
                    </div>

                    <div id="icon-file-tab" class="tab-content active">
                        <input type="file" id="icon_file" name="icon_file" accept="image/*" onchange="previewIconFile(this)">
                    </div>

                    <div id="icon-url-tab" class="tab-content">
                        <input type="url" id="icon_url" name="icon_url" placeholder="Enter icon URL" onchange="updateIconFromUrl(this)">
                    </div>

                    <input type="hidden" name="icon_class" id="icon_class">
                    <div class="icon-preview">
                        <img id="icon_preview_img" style="display: none; max-width: 48px; max-height: 48px; object-fit: contain;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Service Description:</label>
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
                        <div class="editor" id="service_description" contenteditable="true"></div>
                        <input type="hidden" name="service_description" id="service_description_input">
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_visible" checked>
                        Visible
                    </label>
                </div>
                <button type="submit" name="add_service">Add Service</button>
            </form>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h3>Edit Service</h3>
            <form method="POST" id="editServiceForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Service Title:</label>
                    <input type="text" name="service_title" id="edit_service_title" required>
                </div>

                <div class="form-group">
                    <label>Icon:</label>
                    <div class="tab-container">
                        <button type="button" class="tab-button" onclick="switchEditIconTab('file')">Upload File</button>
                        <button type="button" class="tab-button" onclick="switchEditIconTab('url')">Image URL</button>
                    </div>

                    <div id="edit-icon-file-tab" class="tab-content">
                        <input type="file" name="icon_file" accept="image/*" onchange="previewEditIcon(this)">
                    </div>

                    <div id="edit-icon-url-tab" class="tab-content">
                        <input type="url" name="icon_url" id="edit_icon_url" placeholder="Enter icon URL" onchange="previewEditIconUrl(this)">
                    </div>

                    <div class="icon-preview">
                        <img id="edit-icon-preview" style="display: none; max-width: 100px; margin-top: 10px;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Service Description:</label>
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" onclick="execEditCommand('bold')" class="tooltip" data-tooltip="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('italic')" class="tooltip" data-tooltip="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('underline')" class="tooltip" data-tooltip="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
                                    <i class="fas fa-strikethrough"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <select onchange="execEditCommandWithArg('fontSize', this.value)" class="font-size-select tooltip" data-tooltip="Font Size">
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
                                <button type="button" onclick="execEditCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                    <i class="fas fa-align-left"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                    <i class="fas fa-align-center"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                    <i class="fas fa-align-right"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                    <i class="fas fa-align-justify"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" onclick="execEditCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('indent')" class="tooltip" data-tooltip="Indent">
                                    <i class="fas fa-indent"></i>
                                </button>
                                <button type="button" onclick="execEditCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                    <i class="fas fa-outdent"></i>
                                </button>
                            </div>

                            <div class="toolbar-group">
                                <button type="button" onclick="execEditCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                    <i class="fas fa-eraser"></i>
                                </button>
                                <button type="button" onclick="createEditLink()" class="tooltip" data-tooltip="Insert Link">
                                    <i class="fas fa-link"></i>
                                </button>
                            </div>
                        </div>
                        <div class="editor" id="edit_service_description" contenteditable="true"></div>
                        <input type="hidden" name="service_description" id="edit_description_input">
                    </div>
                </div>

                <div class="form-group">
        <label>
                        <input type="checkbox" name="is_visible" id="edit_is_visible">
                        Visible
                    </label>
                </div>

                <button type="submit" name="update_service">Update Service</button>
    </form>
        </div>
    </div>

    <script>
        // Add Service Modal Functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }

        // Edit Service Modal Functions
        function editService(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;

            const title = row.querySelector('td:first-child').textContent;
            const description = row.querySelector('input[name="full_description"]').value;
            const iconCell = row.querySelector('td:nth-child(3)');
            const iconImg = iconCell.querySelector('img');
            const isVisible = row.querySelector('.status-badge').classList.contains('status-active');

            // Set form values
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_service_title').value = title;
            document.getElementById('edit_service_description').innerHTML = description;
            document.getElementById('edit_is_visible').checked = isVisible;

            // Handle icon preview
            const preview = document.getElementById('edit-icon-preview');
            if (iconImg) {
                preview.src = iconImg.src;
                preview.style.display = 'block';
                
                // If it's a URL or uploaded file path
                if (iconImg.src.startsWith('http') || iconImg.src.includes('uploads/')) {
                    document.getElementById('edit-icon-url-tab').classList.add('active');
                    document.getElementById('edit_icon_url').value = iconImg.src;
                } else {
                    document.getElementById('edit-icon-file-tab').classList.add('active');
                }
            } else {
                preview.style.display = 'none';
            }

            // Show modal
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            // Reset form
            document.getElementById('editServiceForm').reset();
            document.getElementById('edit-icon-preview').style.display = 'none';
            document.getElementById('edit_service_description').innerHTML = '';
        }

        // Icon Functions
        function setIcon(iconClass) {
            document.getElementById('icon_class').value = iconClass;
            updateIconPreview(document.getElementById('icon_class'));
        }

        function setEditIcon(iconClass) {
            document.getElementById('edit_icon').value = iconClass;
            updateEditIconPreview(document.getElementById('edit_icon'));
        }

        function updateIconPreview(input) {
            const preview = document.getElementById('icon_preview_icon');
            const imgPreview = document.getElementById('icon_preview_img');
            const iconClassInput = document.getElementById('icon_class');
            
            if (!input.value) {
                preview.style.display = 'none';
                imgPreview.style.display = 'none';
                return;
            }
            
            if (input.value.startsWith('http') || input.value.startsWith('data:image') || input.value.startsWith('../')) {
                imgPreview.src = input.value;
                imgPreview.style.display = 'block';
                preview.style.display = 'none';
                iconClassInput.value = input.value;
            } else {
                preview.className = input.value;
                preview.style.display = 'block';
                imgPreview.style.display = 'none';
                iconClassInput.value = input.value;
            }
        }

        function updateEditIconPreview(input) {
            const preview = document.getElementById('edit_icon_preview_icon');
            const imgPreview = document.getElementById('edit_icon_preview_img');
            const iconClassInput = document.getElementById('edit_icon');
            
            if (!input.value) {
                preview.style.display = 'none';
                imgPreview.style.display = 'none';
                return;
            }
            
            if (input.value.startsWith('http') || input.value.startsWith('data:image') || input.value.startsWith('../')) {
                imgPreview.src = input.value;
                imgPreview.style.display = 'block';
                preview.style.display = 'none';
                iconClassInput.value = input.value;
            } else {
                preview.className = input.value;
                preview.style.display = 'block';
                imgPreview.style.display = 'none';
                iconClassInput.value = input.value;
            }
        }

        function previewIconFile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const preview = document.getElementById('icon_preview_img');
                const iconPreview = document.getElementById('icon_preview_icon');
                const iconClassInput = document.getElementById('icon_class');
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    iconPreview.style.display = 'none';
                    iconClassInput.value = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function switchIconTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(`icon-${tabName}-tab`).classList.add('active');
            event.target.classList.add('active');

            // Reset preview based on active tab
            const iconClassInput = document.getElementById('icon_class');
            if (tabName === 'custom') {
                iconClassInput.value = 'fas fa-'; // Default FontAwesome prefix
            } else {
                iconClassInput.value = ''; // Clear for other tabs
            }
            updateIconPreview(iconClassInput);
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

        // Sync editor content to hidden input before form submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const editor = this.querySelector('.editor');
                    const hiddenInput = editor.nextElementSibling;
                    if (hiddenInput && hiddenInput.type === 'hidden') {
                        hiddenInput.value = editor.innerHTML;
                    }
                });
            });
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Edit modal functions
        function switchEditIconTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('#editModal .tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('#editModal .tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(`edit-icon-${tabName}-tab`).classList.add('active');
            event.target.classList.add('active');

            // Reset preview based on active tab
            const iconInput = document.getElementById('edit_icon');
            if (tabName === 'custom') {
                iconInput.value = iconInput.value || 'fas fa-'; // Keep existing value or set default
            }
            updateEditIconPreview(iconInput);
        }

        function previewEditIcon(input) {
            const preview = document.getElementById('edit-icon-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewEditIconUrl(input) {
            const preview = document.getElementById('edit-icon-preview');
            if (input.value) {
                preview.src = input.value;
                preview.style.display = 'block';
                preview.onerror = function() {
                    alert('Failed to load image from URL');
                    preview.style.display = 'none';
                    input.value = '';
                };
            }
        }

        function execEditCommand(command) {
            document.execCommand(command, false, null);
            updateEditToolbarState();
        }

        function execEditCommandWithArg(command, arg) {
            document.execCommand(command, false, arg);
            updateEditToolbarState();
        }

        function createEditLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
            updateEditToolbarState();
        }

        function updateEditToolbarState() {
            const buttons = document.querySelectorAll('#edit_service_description .editor-toolbar button');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Add functions for main section editor
        function execMainCommand(command) {
            document.execCommand(command, false, null);
            updateMainToolbarState();
        }

        function execMainCommandWithArg(command, arg) {
            document.execCommand(command, false, arg);
            updateMainToolbarState();
        }

        function createMainLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
            updateMainToolbarState();
        }

        function updateMainToolbarState() {
            const buttons = document.querySelectorAll('#main_description .editor-toolbar button');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Initialize main editor
        document.addEventListener('DOMContentLoaded', function() {
            const mainEditor = document.getElementById('main_description');
            
            // Update toolbar state when selection changes
            mainEditor.addEventListener('keyup', updateMainToolbarState);
            mainEditor.addEventListener('mouseup', updateMainToolbarState);
            
            // Sync editor content to hidden input before form submission
            document.querySelector('form').addEventListener('submit', function() {
                document.getElementById('main_description_input').value = mainEditor.innerHTML;
            });
        });

        // Initialize edit service description editor
        document.addEventListener('DOMContentLoaded', function() {
            const editEditor = document.getElementById('edit_service_description');
            
            // Update toolbar state when selection changes
            editEditor.addEventListener('keyup', updateEditToolbarState);
            editEditor.addEventListener('mouseup', updateEditToolbarState);
            
            // Sync editor content to hidden input before form submission
            document.getElementById('editServiceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                formData.append('update_service', '1');
                
                // Add description from editor
                const description = document.getElementById('edit_service_description').innerHTML;
                formData.set('service_description', description);

                // Submit form
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the service');
                });
            });

            // Handle pagination clicks
            document.querySelectorAll('.pagination-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Store current scroll position
                    const scrollPosition = window.scrollY;
                    sessionStorage.setItem('servicesScrollPosition', scrollPosition);
                    
                    // Navigate to the page
                    window.location.href = this.href;
                });
            });

            // Restore scroll position if exists
            if (sessionStorage.getItem('servicesScrollPosition')) {
                const savedPosition = parseInt(sessionStorage.getItem('servicesScrollPosition'));
                window.scrollTo(0, savedPosition);
                sessionStorage.removeItem('servicesScrollPosition'); // Clear stored position
            }
        });

        function saveScrollPosition() {
            sessionStorage.setItem('servicesScrollPosition', window.scrollY);
        }

        // Restore scroll position on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash === '#services') {
                const savedPosition = sessionStorage.getItem('servicesScrollPosition');
                if (savedPosition !== null) {
                    window.scrollTo(0, parseInt(savedPosition));
                    sessionStorage.removeItem('servicesScrollPosition');
                }
            }
        });
    </script>
</body>
</html>

