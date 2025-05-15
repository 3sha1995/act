<?php
require_once __DIR__ . '/../db_connection.php';
require_once 'includes/editor.php';

class ServicesCMS {
    private $pdo;
    private $upload_dir = '../uploads/services/';

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTablesExist();
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    private function ensureTablesExist() {
        // Create main section settings table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_services_main (
            id INT PRIMARY KEY DEFAULT 1,
            section_title VARCHAR(255) DEFAULT 'Our Services',
            section_description TEXT,
            is_visible TINYINT(1) DEFAULT 1,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Modify services table to include created_at
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS af_page_services (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service_title VARCHAR(255) NOT NULL,
            service_description TEXT,
            icon_class VARCHAR(100),
            is_visible TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        // Insert default main section if not exists
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_services_main");
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("INSERT INTO af_page_services_main (section_title, section_description, is_visible) 
                             VALUES ('Our Services', 'We provide a range of services to support our university community.', 1)");
        }
    }

    // Get main section content
    public function getMainSection() {
        $stmt = $this->pdo->query("SELECT * FROM af_page_services_main WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update main section
    public function updateMainSection($title, $description, $isVisible) {
        $stmt = $this->pdo->prepare("UPDATE af_page_services_main 
                                    SET section_title = ?, section_description = ?, is_visible = ? 
                                    WHERE id = 1");
        return $stmt->execute([$title, $description, $isVisible]);
    }

    // Fetch all services
    public function getAllServices($page = 1, $perPage = 6) {
        try {
            // Get total count
            $countStmt = $this->pdo->query("SELECT COUNT(*) FROM af_page_services");
            $totalServices = $countStmt->fetchColumn();
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get paginated services
            $stmt = $this->pdo->prepare("SELECT * FROM af_page_services ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
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
                return 'uploads/services/' . $filename;
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
            
            $stmt = $this->pdo->prepare("INSERT INTO af_page_services (service_title, service_description, icon_class, is_visible)
                                       VALUES (:service_title, :service_description, :icon_class, :is_visible)");
            
            return $stmt->execute([
                'service_title' => $data['service_title'],
                'service_description' => $data['service_description'],
                'icon_class' => $iconPath,
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
            $stmt = $this->pdo->prepare("SELECT icon_class FROM af_page_services WHERE id = ?");
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
            
            $stmt = $this->pdo->prepare("UPDATE af_page_services 
                                       SET service_title = :service_title,
                                           service_description = :service_description,
                                           icon_class = :icon_class,
                                           is_visible = :is_visible
                                       WHERE id = :id");
            
            $result = $stmt->execute([
                'id' => $id,
                'service_title' => $data['service_title'],
                'service_description' => $data['service_description'],
                'icon_class' => $iconPath,
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
        $stmt = $this->pdo->prepare("DELETE FROM af_page_services WHERE id = ?");
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
            if ($cms->addService([
            'service_title' => $_POST['service_title'],
            'service_description' => $_POST['service_description'],
            'is_visible' => isset($_POST['is_visible']) ? 1 : 0
            ])) {
                $_SESSION['success'] = 'Service added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add service';
            }
        }

        if (isset($_POST['update_service'])) {
            $serviceData = [
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
    <title>Services Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php editorStyles(); ?>
</head>
<body class="container py-5">
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

    <?php editorScripts(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Make services data available to JavaScript
        const services = <?= $servicesJson ?>;
    </script>
</body>
</html>
