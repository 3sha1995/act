<?php
require_once '../db_connection.php';

class CMS_CareerAbout {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/career/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `career_about` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `ontop_title` varchar(255) DEFAULT NULL,
                `main_title` varchar(255) DEFAULT NULL,
                `image_path` varchar(255) DEFAULT NULL,
                `description` text DEFAULT NULL,
                `is_visible` tinyint(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            $this->pdo->exec($sql);
            
            // Check if default record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `career_about` WHERE id = 1");
            if ($stmt->fetchColumn() == 0) {
                $this->createDefaultContent();
            }
        } catch (PDOException $e) {
            error_log("Error ensuring table exists: " . $e->getMessage());
        }
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

    public function getContent() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `career_about` WHERE id = 1");
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
            $sql = "INSERT INTO `career_about` (`ontop_title`, `main_title`, `description`, `image_path`, `is_visible`) 
                    VALUES ('Career Services', 'Career Services', 'Default content. Please update this in the CMS.', '../imgs/cte.jpg', 1)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function updateContent($ontop_title, $main_title, $image_path, $description, $is_visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE `career_about` SET 
                `ontop_title` = ?, 
                `main_title` = ?, 
                `image_path` = ?, 
                `description` = ?, 
                `is_visible` = ?
                WHERE id = 1");
            
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

    public static function getAboutContent() {
        try {
            $pdo = getPDOConnection();
            $stmt = $pdo->prepare("SELECT * FROM `career_about` WHERE id = 1");
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

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize CMS About Section
$cmsAbout = new CMS_CareerAbout();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ontop_title = $_POST['ontop_title'];
    $main_title = $_POST['main_title'];
    $description = $_POST['description'];
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    
    // Handle image upload
    $image_path = '';
    if (!empty($_FILES['image_file']['name'])) {
        $file_extension = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $cmsAbout->getUploadDir() . $new_filename;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/career/' . $new_filename;
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Image uploaded successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
            } else {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Error uploading file: " . error_get_last()['message'] . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
            }
        } else {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Invalid file type. Allowed types: jpg, jpeg, png, gif
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
        }
    } elseif (!empty($_POST['existing_image'])) {
        $image_path = $_POST['existing_image'];
    }

    $updated = $cmsAbout->updateContent($ontop_title, $main_title, $image_path, $description, $is_visible);
    
    if ($updated) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                Content updated successfully! Visibility is " . ($is_visible ? "enabled" : "disabled") . "
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
    } else {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                Error updating content. Please check the error logs.
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
    }
}

// Fetch current content
$content = $cmsAbout->getContent();
if ($content === null) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
            Error fetching content from database.
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
}

// Display visibility status
if ($content !== null) {
    echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
            Current visibility status: <strong>" . ($content['is_visible'] == 1 ? "Visible" : "Hidden") . "</strong>
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
        </div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Career Services About</title>
    
    <!-- Bootstrap CSS -->
  
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Add your existing styles here */
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

        /* Add styles for the font size select */
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

        /* Image preview styles */
        .image-preview {
            max-width: 200px;
            margin: 10px 0;
            display: none;
        }

        .current-image-container {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .current-image-preview {
            max-width: 300px;
            margin: 10px 0;
            border: 2px solid #dee2e6;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .current-image-label {
            display: block;
            font-weight: 500;
            margin-bottom: 10px;
            color: #495057;
        }

        .no-image-placeholder {
            padding: 20px;
            background: #e9ecef;
            border: 2px dashed #ced4da;
            border-radius: 4px;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Career Services About Section</h1>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <h2>Section Content</h2>
                        
                        <div class="mb-3">
                            <label for="ontop_title" class="form-label">Top Title:</label>
                            <input type="text" class="form-control" id="ontop_title" name="ontop_title" 
                                   value="<?= htmlspecialchars($content['ontop_title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="main_title" class="form-label">Main Title:</label>
                            <input type="text" class="form-control" id="main_title" name="main_title" 
                                   value="<?= htmlspecialchars($content['main_title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image:</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" 
                                   accept="image/*" onchange="previewImage(this)">
                            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($content['image_path'] ?? '') ?>">
                        </div>

                        <div class="current-image-container">
                            <h3><i class="fas fa-image"></i> Current Image</h3>
                            <?php if (!empty($content['image_path'])): ?>
                                <label class="current-image-label">Current image path: <?= htmlspecialchars($content['image_path']) ?></label>
                                <?php
                                $imagePath = $content['image_path'];
                                if (strpos($imagePath, '../') === 0) {
                                    $imagePath = substr($imagePath, 3);
                                }
                                $displayPath = '../../' . $imagePath;
                                ?>
                                <img src="<?= htmlspecialchars($displayPath) ?>" 
                                     alt="Current image" 
                                     class="current-image-preview"
                                     onerror="this.src='../../imgs/cte.jpg';">
                            <?php else: ?>
                                <div class="no-image-placeholder">
                                    <i class="fas fa-image"></i>
                                    <p>No image currently set</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description:</label>
                            <div class="editor-container">
                                <!-- Add your existing editor toolbar here -->
                                <div class="editor" id="editor" contenteditable="true"><?= $content['description'] ?? '' ?></div>
                                <input type="hidden" name="description" id="description_input">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="switch-label d-flex align-items-center gap-2">
                                <span>Section Visibility:</span>
                                <label class="switch">
                                    <input type="checkbox" name="is_visible" <?= ($content['is_visible'] ?? false) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
   

    <script>
        // Add your existing JavaScript functions here
    </script>
</body>
</html>
