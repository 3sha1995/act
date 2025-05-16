<?php
require_once __DIR__ . '/../db_connection.php';

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
<html>
<head>
    <title>Officer CMS</title>
    <link rel="stylesheet" href="student_affairs_sidebar.css">
    <style>
        /* Base styles */
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
        
        /* Mobile styles */
        @media (max-width: 768px) {
            body {
                margin-left: 0;
                padding: 0 15px;
            }
            body.sidebar-open {
                margin-left: 250px;
            }
        }

        /* Messages */
        .success {
            background-color: #c6f6d5;
            color: #22543d;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #9ae6b4;
        }
        
        .error {
            background-color: #fed7d7;
            color: #822727;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #feb2b2;
        }

        /* Forms and Inputs */
        form {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            margin: 20px 0;
            border: 1px solid #bee3f8;
        }

        input[type=text], select {
            width: 100%;
            padding: 12px;
            margin: 8px 0 20px;
            border: 1px solid #bee3f8;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        input[type=text]:focus, select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        /* Buttons */
        button {
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

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
            background: #2b6cb0;
        }

        button[name="delete"] {
            background: #e53e3e;
        }

        button[name="delete"]:hover {
            background: #c53030;
        }

        /* Table Styles */
        table {
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

        th, td {
            padding: 16px;
            border: 1px solid #bee3f8;
            text-align: left;
        }

        th {
            background: #ebf8ff;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f0f5ff;
        }

        /* Modal Styles */
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
            padding: 0;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 16px;
            position: relative;
            box-shadow: 0 10px 25px rgba(49, 130, 206, 0.13);
            border: 1px solid #bee3f8;
            display: flex;
            flex-direction: column;
            max-height: 85vh;
        }
        
        #addModal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: scroll;
            background-color: rgba(26, 54, 93, 0.5);
        }
        
        #addModal .modal-content {
            margin: 20px auto;
            overflow-y: visible;
            max-height: none;
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
            z-index: 11;
        }
        
        .close:hover {
            color: #2c5282;
            background-color: #ebf8ff;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c5282;
            font-size: 0.95rem;
        }

        /* Image Preview */
        .image-preview {
            max-width: 200px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(49, 130, 206, 0.07);
            border: 1px solid #bee3f8;
        }
        
        .preview-container {
            margin: 15px 0;
            text-align: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #bee3f8;
        }

        /* Tabs */
        .tab-container {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #bee3f8;
        }
        
        .tab-button {
            background: #f0f7ff;
            border: 1px solid #bee3f8;
            border-bottom: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            font-weight: 500;
            color: #4a5568;
            margin-right: 5px;
        }
        
        .tab-button.active {
            background: #ebf8ff;
            color: #2c5282;
            border-color: #93c5fd;
        }
        
        .tab-content {
            display: none;
            padding: 15px 0;
        }
        
        .tab-content.active {
            display: block;
        }

        /* File Input */
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

        /* Checkbox Style */
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        /* Modal Header */
        .modal-header {
            padding: 20px 30px;
            background: #f8fafc;
            border-radius: 16px 16px 0 0;
            border-bottom: 1px solid #bee3f8;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-header h2 {
            margin: 0;
            color: #2c5282;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .modal-body {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
            min-height: 200px;
        }

        .modal-footer {
            padding: 20px 30px;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
            border-top: 1px solid #bee3f8;
            text-align: right;
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .modal-footer button {
            margin-left: 10px;
        }

        /* Add Button */
        .add-button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #3182ce;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.13);
            background: #2b6cb0;
        }

        .add-button i {
            font-size: 16px;
        }

        /* Table preview image */
        .image-preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .table-preview-image {
            max-width: 80px;
            max-height: 80px;
            border-radius: 6px;
            object-fit: cover;
            border: 1px solid #bee3f8;
            padding: 2px;
        }

        .image-url {
            font-size: 12px;
            color: #718096;
            word-break: break-all;
            max-width: 200px;
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

        h1, h2 {
            color: #2c5282;
        }
    </style>
</head>
<body>
<?php include 'student_affairs_sidebar.php'; ?>

<div class="content-wrapper">
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
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore background scrolling
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
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore background scrolling
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
                document.body.style.overflow = 'auto'; // Restore background scrolling
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

    <!-- Include the sidebar persistence script -->
    <script src="student_affairs_persistent.js"></script>
</body>
</html>
