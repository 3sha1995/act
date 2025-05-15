<?php
require_once __DIR__ . '/../db_connection.php';
require_once 'includes/editor.php';

try {
    $pdo = getPDOConnection();

    // Create table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS `af_page_officer` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `position` VARCHAR(100) NOT NULL,
        `image_url` VARCHAR(255) NOT NULL,
        `section_title` VARCHAR(255) NOT NULL,
        `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
        `section_visible` TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($createTableSQL);

    // Add section_visible column if it doesn't exist
    try {
        $pdo->query("SELECT section_visible FROM af_page_officer LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE af_page_officer ADD section_visible TINYINT(1) NOT NULL DEFAULT 1");
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . "/../../uploads/officers";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}

class OfficerCMS {
    private $pdo;
    private $uploadDir;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->uploadDir = __DIR__ . "/../../uploads/officers/";
    }

    public function handleImageUpload($file) {
        if (!empty($file['name'])) {
            // Create directory if it doesn't exist
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }

            // Generate unique filename
            $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file['name']);
            $targetPath = $this->uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return "../uploads/officers/" . $filename;
            } else {
                error_log("Failed to move uploaded file: " . error_get_last()['message']);
                return false;
            }
        }
        return false;
    }

    public function getAllOfficers() {
        try {
        $stmt = $this->pdo->query("SELECT * FROM af_page_officer ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting officers: " . $e->getMessage());
            return [];
        }
    }

    public function getSectionTitle() {
        try {
        $stmt = $this->pdo->query("SELECT section_title FROM af_page_officer ORDER BY id DESC LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['section_title'] : 'Meet Our Officers';
        } catch (PDOException $e) {
            error_log("Error getting section title: " . $e->getMessage());
            return 'Meet Our Officers';
        }
    }

    public function addOfficer($name, $position, $imageUrl, $isVisible = 1) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO af_page_officer (name, position, image_url, is_visible) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$name, $position, $imageUrl, $isVisible]);
        } catch (PDOException $e) {
            error_log("Error adding officer: " . $e->getMessage());
            return false;
        }
    }

    public function updateOfficer($id, $name, $position, $imageUrl, $isVisible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_officer SET name = ?, position = ?, image_url = ?, is_visible = ? WHERE id = ?");
            return $stmt->execute([$name, $position, $imageUrl, $isVisible, $id]);
        } catch (PDOException $e) {
            error_log("Error updating officer: " . $e->getMessage());
            return false;
        }
    }

    public function deleteOfficer($id) {
        try {
            // Get the current image URL before deleting
            $stmt = $this->pdo->prepare("SELECT image_url FROM af_page_officer WHERE id = ?");
            $stmt->execute([$id]);
            $officer = $stmt->fetch();

            // Delete the record
        $stmt = $this->pdo->prepare("DELETE FROM af_page_officer WHERE id = ?");
            $success = $stmt->execute([$id]);

            // If deletion was successful and image is in uploads directory, delete the file
            if ($success && $officer && strpos($officer['image_url'], 'uploads/officers/') === 0) {
                $imagePath = __DIR__ . "/../../" . $officer['image_url'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error deleting officer: " . $e->getMessage());
            return false;
        }
    }

    public function toggleVisibility($id, $visible) {
        try {
        $stmt = $this->pdo->prepare("UPDATE af_page_officer SET is_visible = ? WHERE id = ?");
            return $stmt->execute([$visible, $id]);
        } catch (PDOException $e) {
            error_log("Error toggling visibility: " . $e->getMessage());
            return false;
        }
    }

    public function updateSectionTitle($newTitle) {
        try {
        $stmt = $this->pdo->prepare("UPDATE af_page_officer SET section_title = ?");
            return $stmt->execute([$newTitle]);
        } catch (PDOException $e) {
            error_log("Error updating section title: " . $e->getMessage());
            return false;
        }
    }

    public function getSectionVisibility() {
        try {
            $stmt = $this->pdo->query("SELECT section_visible FROM af_page_officer LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['section_visible'] : 1;
        } catch (PDOException $e) {
            error_log("Error getting section visibility: " . $e->getMessage());
            return 1;
        }
    }

    public function updateSectionVisibility($visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE af_page_officer SET section_visible = ?");
            return $stmt->execute([$visible]);
        } catch (PDOException $e) {
            error_log("Error updating section visibility: " . $e->getMessage());
            return false;
        }
    }
}

// Initialize CMS
try {
$cms = new OfficerCMS($pdo);
    $message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add']) || isset($_POST['update'])) {
            $name = $_POST['name'];
            $position = $_POST['position'];
            $isVisible = isset($_POST['is_visible']) ? 1 : 0;
            
            // Handle image upload or URL
            if (!empty($_FILES['image_file']['name'])) {
                $imageUrl = $cms->handleImageUpload($_FILES['image_file']);
                if (!$imageUrl) {
                    throw new Exception("Failed to upload image file.");
                }
            } elseif (!empty($_POST['image_url'])) {
                $imageUrl = $_POST['image_url'];
            } else {
                throw new Exception("Please provide either an image file or URL.");
            }

            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing officer
                if ($cms->updateOfficer($_POST['id'], $name, $position, $imageUrl, $isVisible)) {
                    $message = '<div class="success">Officer updated successfully!</div>';
                } else {
                    $message = '<div class="error">Error updating officer.</div>';
                }
            } else {
                // Add new officer
                if ($cms->addOfficer($name, $position, $imageUrl, $isVisible)) {
                    $message = '<div class="success">Officer added successfully!</div>';
                } else {
                    $message = '<div class="error">Error adding officer.</div>';
                }
            }
    }

    if (isset($_POST['delete'])) {
            if ($cms->deleteOfficer($_POST['id'])) {
                $message = '<div class="success">Officer deleted successfully!</div>';
            } else {
                $message = '<div class="error">Error deleting officer.</div>';
            }
    }

    if (isset($_POST['toggle'])) {
            if ($cms->toggleVisibility($_POST['id'], $_POST['is_visible'])) {
                $message = '<div class="success">Visibility updated successfully!</div>';
            } else {
                $message = '<div class="error">Error updating visibility.</div>';
            }
    }

    if (isset($_POST['update_title'])) {
            if ($cms->updateSectionTitle($_POST['section_title'])) {
                $message = '<div class="success">Section title updated successfully!</div>';
            } else {
                $message = '<div class="error">Error updating section title.</div>';
            }
        }

        if (isset($_POST['toggle_section'])) {
            $isVisible = $_POST['section_visible'] ? 1 : 0;
            if ($cms->updateSectionVisibility($isVisible)) {
                $message = '<div class="success">Section visibility updated successfully!</div>';
            } else {
                $message = '<div class="error">Error updating section visibility.</div>';
            }
        }

        // Only redirect if no error message
        if (strpos($message, 'success') !== false) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit;
        }
}

    // Get data for display
$officers = $cms->getAllOfficers();
$sectionTitle = $cms->getSectionTitle();
    $sectionVisible = $cms->getSectionVisibility();

    // Add success message from redirect
    if (isset($_GET['success'])) {
        $message = '<div class="success">Operation completed successfully!</div>';
    }

} catch (Exception $e) {
    error_log("CMS Error: " . $e->getMessage());
    $message = '<div class="error">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</div>';
    $officers = [];
    $sectionTitle = 'Meet Our Officers';
    $sectionVisible = 1;
}

// Function to format image URL for display
function formatImageUrl($imageUrl) {
    if (empty($imageUrl)) return '';
    
    // If it's an absolute URL, return as is
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        return $imageUrl;
    }
    
    // If it starts with ../uploads, remove one dot
    if (strpos($imageUrl, '../uploads/') === 0) {
        return substr($imageUrl, 1);
    }
    
    // If it starts with ./uploads or uploads, add proper path
    if (strpos($imageUrl, './uploads/') === 0) {
        return substr($imageUrl, 1);
    }
    if (strpos($imageUrl, 'uploads/') === 0) {
        return '/' . $imageUrl;
    }
    
    return $imageUrl;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officers Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php editorStyles(); ?>
</head>
<body class="container py-5">
    <h1>Officer Management System</h1>
    
    <?php echo $message; ?>

    <h2>Edit Section Title</h2>
    <form method="POST">
        <div class="form-group">
            <label for="section_title">Section Title:</label>
            <input type="text" id="section_title" name="section_title" value="<?= htmlspecialchars($sectionTitle) ?>" required>
        </div>
        <button type="submit" name="update_title">Update Title</button>
    </form>

    <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
        <h2>Section Settings</h2>
        <form method="POST" style="margin: 0; padding: 0; background: none; box-shadow: none; display: flex; align-items: center; gap: 10px;">
            <label class="switch-label">
                Section Visibility:
                <div class="switch">
                    <input type="checkbox" name="section_visible" <?= $sectionVisible ? 'checked' : '' ?> onchange="this.form.submit()">
                    <span class="slider round"></span>
                </div>
            </label>
            <input type="hidden" name="toggle_section" value="1">
    </form>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
        <h2 style="margin: 0;">Officers</h2>
        <button class="add-button" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Add New Officer
        </button>
    </div>
    
    <table>
        <tr>
            <th>Name</th>
            <th>Position</th>
            <th>Image</th>
            <th>Visible</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($officers as $officer): ?>
        <tr>
            <td><?= htmlspecialchars($officer['name']) ?></td>
            <td><?= htmlspecialchars($officer['position']) ?></td>
            <td>
                <?php $displayUrl = formatImageUrl($officer['image_url']); ?>
                <div class="image-preview-container">
                    <img src="<?= htmlspecialchars($displayUrl) ?>" 
                         alt="<?= htmlspecialchars($officer['name']) ?>" 
                         class="table-preview-image"
                         onerror="this.src='../imgs/default-avatar.png'; this.onerror=null;">
                    <div class="image-url"><?= htmlspecialchars($officer['image_url']) ?></div>
                </div>
            </td>
            <td>
                <form method="POST" style="display: inline; background: none; padding: 0; margin: 0; box-shadow: none;">
                    <input type="hidden" name="id" value="<?= $officer['id'] ?>">
                    <select name="is_visible" onchange="this.form.submit()" style="width: auto;">
                        <option value="1" <?= $officer['is_visible'] ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= !$officer['is_visible'] ? 'selected' : '' ?>>No</option>
                    </select>
                    <input type="hidden" name="toggle" value="1">
                </form>
                </td>
                <td>
                <button onclick="openEditModal(<?= htmlspecialchars(json_encode($officer)) ?>)" style="margin-right: 10px;">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form method="POST" style="display: inline; background: none; padding: 0; margin: 0; box-shadow: none;">
                    <input type="hidden" name="id" value="<?= $officer['id'] ?>">
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this officer?')" style="background: #dc3545;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Add Officer Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Officer</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="addOfficerForm">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required placeholder="Enter officer's name">
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" required placeholder="Enter officer's position">
                    </div>
                    
                    <div class="tab-container">
                        <button type="button" class="tab-button active" onclick="switchTab('file', this)">Upload File</button>
                        <button type="button" class="tab-button" onclick="switchTab('url', this)">Image URL</button>
                    </div>
                    
                    <div id="fileTab" class="tab-content active">
                        <label>Choose Image File:</label>
                        <input type="file" name="image_file" accept="image/*" onchange="previewImage(this)">
                    </div>
                    
                    <div id="urlTab" class="tab-content">
                        <label>Image URL:</label>
                        <input type="text" name="image_url" placeholder="Enter image URL">
                    </div>
                    
                    <div class="preview-container">
                        <img id="imagePreview" class="image-preview" style="display: none;">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_visible" name="is_visible" checked>
                        <label for="is_visible">Visible on website</label>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeAddModal()" style="background: #6c757d; margin-right: 10px;">Cancel</button>
                <button type="submit" form="addOfficerForm" name="add">Add Officer</button>
            </div>
        </div>
    </div>

    <!-- Edit Officer Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Officer</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data" id="editOfficerForm">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="form-group">
                        <label for="edit_name">Name:</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_position">Position:</label>
                        <input type="text" id="edit_position" name="position" required>
                    </div>
                    
                    <div class="tab-container">
                        <button type="button" class="tab-button active" onclick="switchTab('edit_file', this)">Upload File</button>
                        <button type="button" class="tab-button" onclick="switchTab('edit_url', this)">Image URL</button>
                    </div>
                    
                    <div id="edit_fileTab" class="tab-content active">
                        <label>Choose Image File:</label>
                        <input type="file" name="image_file" accept="image/*" onchange="previewImage(this, 'edit_imagePreview')">
                    </div>
                    
                    <div id="edit_urlTab" class="tab-content">
                        <label>Image URL:</label>
                        <input type="text" id="edit_image_url" name="image_url" placeholder="Enter image URL">
                    </div>
                    
                    <div class="preview-container">
                        <img id="edit_imagePreview" class="image-preview">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="edit_is_visible" name="is_visible">
                        <label for="edit_is_visible">Visible on website</label>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" style="background: #6c757d; margin-right: 10px;">Cancel</button>
                <button type="submit" form="editOfficerForm" name="update">Update Officer</button>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <?= renderEditor('description', $officer['description'] ?? '', 'description') ?>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset form and scroll position
            document.getElementById('addOfficerForm').reset();
            document.querySelector('#addModal .modal-body').scrollTop = 0;
        }
        
        function openEditModal(officer) {
            const modal = document.getElementById('editModal');
            document.getElementById('edit_id').value = officer.id;
            document.getElementById('edit_name').value = officer.name;
            document.getElementById('edit_position').value = officer.position;
            document.getElementById('edit_image_url').value = officer.image_url;
            document.getElementById('edit_is_visible').checked = officer.is_visible == 1;
            
            // Update image preview
            const preview = document.getElementById('edit_imagePreview');
            preview.src = formatImageUrl(officer.image_url);
            preview.style.display = 'block';
            preview.onerror = function() {
                this.src = '../imgs/default-avatar.png';
                this.onerror = null;
            };
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            // Reset scroll position
            document.querySelector('#editModal .modal-body').scrollTop = 0;
        }
        
        // Improved image preview function
        function previewImage(input, previewId = 'imagePreview') {
            const preview = document.getElementById(previewId);
            const fileTab = input.closest('.tab-content');
            const urlTab = document.getElementById(fileTab.id === 'fileTab' ? 'urlTab' : 'edit_urlTab');
            const urlInput = urlTab.querySelector('input[type="text"]');
            
            // Clear the URL input when file is selected
            if (input.type === 'file') {
                urlInput.value = '';
            }
            
            if (input.type === 'file' && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    preview.onerror = function() {
                        this.src = '../imgs/default-avatar.png';
                        this.onerror = null;
                    };
                }
                reader.readAsDataURL(input.files[0]);
            } else if (input.type === 'text') {
                // Clear file input when URL is entered
                const fileInput = fileTab.querySelector('input[type="file"]');
                fileInput.value = '';
                
                if (input.value.trim()) {
                    preview.src = input.value;
                    preview.style.display = 'block';
                    preview.onerror = function() {
                        this.src = '../imgs/default-avatar.png';
                        this.onerror = null;
                    };
                } else {
                    preview.style.display = 'none';
                }
            }
        }
        
        // Add event listener for URL input
        document.querySelectorAll('input[name="image_url"]').forEach(input => {
            input.addEventListener('input', function() {
                previewImage(this, this.closest('.modal-content').querySelector('.image-preview').id);
            });
        });
        
        // Improved tab switching function
        function switchTab(tabId, button) {
            const container = button.closest('.modal-content');
            const tabs = container.getElementsByClassName('tab-content');
            const buttons = container.getElementsByClassName('tab-button');
            const preview = container.querySelector('.image-preview');
            
            // Reset preview when switching tabs
            preview.style.display = 'none';
            preview.src = '';
            
            for (let tab of tabs) {
                tab.classList.remove('active');
            }
            for (let btn of buttons) {
                btn.classList.remove('active');
            }
            
            button.classList.add('active');
            document.getElementById(tabId + 'Tab').classList.add('active');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        // Helper function to format image URL
        function formatImageUrl(url) {
            if (!url) return '../imgs/default-avatar.png';
            
            if (url.startsWith('http://') || url.startsWith('https://')) {
                return url;
            }
            
            if (url.startsWith('../uploads/')) {
                return url;
            }
            
            if (url.startsWith('./uploads/')) {
                return '.' + url;
            }
            
            if (url.startsWith('uploads/')) {
                return '../' + url;
            }
            
            return url;
        }
    </script>
    <?php editorScripts(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
