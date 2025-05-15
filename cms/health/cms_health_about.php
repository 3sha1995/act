<?php
require_once __DIR__ . '/../db_connection.php';

class CMS_AboutSection {
    private $pdo;
    protected $upload_dir;

    public function __construct() {
        $this->pdo = getPDOConnection();
        $this->upload_dir = '../../uploads/health/';
        // Create uploads directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `about` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `section` varchar(50) NOT NULL,
                `title` varchar(255) NOT NULL DEFAULT 'Health Services',
                `description` text DEFAULT NULL,
                `image_type` varchar(10) DEFAULT 'file',
                `image_path` varchar(255) DEFAULT NULL,
                `is_visible` tinyint(1) DEFAULT 1,
                `created_at` timestamp NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
            $this->pdo->exec($sql);
            
            // Check if default record exists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `about` WHERE section = 'health'");
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
            $stmt = $this->pdo->prepare("SELECT * FROM `about` WHERE section = 'health'");
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
            $sql = "INSERT INTO `about` (`section`, `title`, `description`, `image_type`, `image_path`, `is_visible`) 
                    VALUES ('health', 'Health Services', 'Default content. Please update this in the CMS.', 'file', '../imgs/cte.jpg', 1)";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating default content: " . $e->getMessage());
        }
    }

    public function updateContent($title, $image_path, $description, $is_visible) {
        try {
            $stmt = $this->pdo->prepare("UPDATE `about` SET 
                `title` = ?, 
                `image_path` = ?, 
                `description` = ?, 
                `is_visible` = ?
                WHERE section = 'health'");
            
            $result = $stmt->execute([$title, $image_path, $description, $is_visible]);
            
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
            $stmt = $pdo->prepare("SELECT * FROM `about` WHERE section = 'health'");
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
                'title' => $content['title'],
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS - Health Services About</title>
    
    <!-- Bootstrap CSS -->
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
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
                <h1 class="card-title">Health Services About Section</h1>
            </div>
            <div class="card-body">
                <?php
                // Initialize CMS About Section
                $cmsAbout = new CMS_AboutSection();
                
                // Handle form submission
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $title = $_POST['title'];
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
                                $image_path = 'uploads/health/' . $new_filename;
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

                    $updated = $cmsAbout->updateContent($title, $image_path, $description, $is_visible);
                    
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

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <h2>Section Content</h2>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title:</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($content['title'] ?? '') ?>" required>
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
                                <div class="editor-toolbar">
                                    <div class="toolbar-group">
                                        <button type="button" data-command="bold" onclick="execCommand('bold')" class="tooltip" data-tooltip="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" data-command="italic" onclick="execCommand('italic')" class="tooltip" data-tooltip="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" data-command="underline" onclick="execCommand('underline')" class="tooltip" data-tooltip="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <button type="button" data-command="strikeThrough" onclick="execCommand('strikeThrough')" class="tooltip" data-tooltip="Strike">
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
                                        <button type="button" data-command="justifyLeft" onclick="execCommand('justifyLeft')" class="tooltip" data-tooltip="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" data-command="justifyCenter" onclick="execCommand('justifyCenter')" class="tooltip" data-tooltip="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" data-command="justifyRight" onclick="execCommand('justifyRight')" class="tooltip" data-tooltip="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                        <button type="button" data-command="justifyFull" onclick="execCommand('justifyFull')" class="tooltip" data-tooltip="Justify">
                                            <i class="fas fa-align-justify"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" data-command="insertUnorderedList" onclick="execCommand('insertUnorderedList')" class="tooltip" data-tooltip="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" data-command="insertOrderedList" onclick="execCommand('insertOrderedList')" class="tooltip" data-tooltip="Number List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <button type="button" data-command="indent" onclick="execCommand('indent')" class="tooltip" data-tooltip="Indent">
                                            <i class="fas fa-indent"></i>
                                        </button>
                                        <button type="button" data-command="outdent" onclick="execCommand('outdent')" class="tooltip" data-tooltip="Outdent">
                                            <i class="fas fa-outdent"></i>
                                        </button>
                                    </div>

                                    <div class="toolbar-group">
                                        <button type="button" data-command="removeFormat" onclick="execCommand('removeFormat')" class="tooltip" data-tooltip="Clear Format">
                                            <i class="fas fa-eraser"></i>
                                        </button>
                                        <button type="button" onclick="createLink()" class="tooltip" data-tooltip="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                    </div>
                                </div>
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



    <div id="previewModal" class="preview-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePreview()">&times;</span>
            <div id="previewContent"></div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function previewImage(input) {
            const preview = document.querySelector('.current-image-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Rich text editor functions
        function execCommand(command) {
            document.getElementById('editor').focus();
            document.execCommand(command, false, null);
            updateToolbarState();
        }

        function execCommandWithArg(command, arg) {
            document.getElementById('editor').focus();
            document.execCommand(command, false, arg);
            updateToolbarState();
        }

        function createLink() {
            const url = prompt('Enter URL:', 'http://');
            if (url) {
                document.getElementById('editor').focus();
                document.execCommand('createLink', false, url);
            }
            updateToolbarState();
        }

        function updateToolbarState() {
            const buttons = document.querySelectorAll('.editor-toolbar button[data-command]');
            buttons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (command && document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }

        // Initialize editor
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('editor');
            
            // Update toolbar state when selection changes
            editor.addEventListener('keyup', updateToolbarState);
            editor.addEventListener('mouseup', updateToolbarState);
            
            // Initial toolbar state
            updateToolbarState();
        });

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            document.getElementById('description_input').value = document.getElementById('editor').innerHTML;
            
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        function showPreview() {
            const modal = document.getElementById('previewModal');
            const previewContent = document.getElementById('previewContent');
            
            // Get current form values
            const title = document.getElementById('title').value;
            const description = document.getElementById('editor').innerHTML;
            const imagePreview = document.querySelector('.current-image-preview');
            const imagePath = imagePreview.style.display !== 'none' ? imagePreview.src : 
                             document.querySelector('.current-image-preview')?.src || '../imgs/cte.jpg';

            // Create preview HTML using the same structure and classes as the main page
            const previewHTML = `
                <section class="student_affairs_about_section">
                    <div class="student_affairs_about_container">
                        <div class="student_affairs_about_header">
                            <div class="student_affairs_about_ontop_title">${title}</div>
                            <h2 class="student_affairs_about_title">Health Services</h2>
                            <div class="student_affair_about_divider"></div>
                        </div>
                        
                        <div class="student_affairs_about_content">
                            <div class="student_affairs_about_image">
                                <img src="${imagePath}" alt="Preview Image">
                            </div>
                            <div class="student_affairs_about_text">
                                <div class="student_affairs_about_description">
                                    ${description}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            `;

            previewContent.innerHTML = previewHTML;
            modal.style.display = 'block';
        }

        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
